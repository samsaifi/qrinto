<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Direct print on the customer's own Noritsu 931BL (Windows, spec §6).
 * This is the web front-end of the flow — start → upload PDF → preflight → print.
 * Live printer/tray state and true preflight come from the local helper app;
 * until that is wired, this drives the flow with the loaded-tray configuration.
 */
class LocalPrintController extends Controller
{
    protected function view(string $name): string
    {
        return "quick-flow-pc.local-print.{$name}";
    }

    /**
     * The trays the local 931BL reports as loaded. Session-overridable via the
     * "what is loaded in each tray" screen; falls back to a sensible default.
     */
    protected function trays(): array
    {
        $default = [
            ['key' => 'mp',    'label' => 'MP tray', 'size' => null,     'media' => null,           'gsm' => '270-324', 'loaded' => true,  'out' => false, 'printer' => 'Noritsu 931BL (MP Tray)'],
            ['key' => 'tray1', 'label' => 'Tray 1',  'size' => '5x7',    'media' => 'photo_glossy', 'gsm' => '150-270', 'loaded' => true,  'out' => false, 'printer' => 'Noritsu 931BL (Tray 1)'],
            ['key' => 'tray2', 'label' => 'Tray 2',  'size' => '5x7',    'media' => 'photo_lustre', 'gsm' => '150-270', 'loaded' => true,  'out' => false, 'printer' => 'Noritsu 931BL (Tray 2)'],
            ['key' => 'tray3', 'label' => 'Tray 3',  'size' => '4x6',    'media' => 'photo_glossy', 'gsm' => '120-150', 'loaded' => true,  'out' => false, 'printer' => 'Noritsu 931BL (Tray 3)'],
            ['key' => 'tray4', 'label' => 'Tray 4',  'size' => '8.5x11', 'media' => 'plain',        'gsm' => '120',     'loaded' => false, 'out' => true,  'printer' => 'Noritsu 931BL (Tray 4)'],
            ['key' => 'tray5', 'label' => 'Tray 5',  'size' => '4x6',    'media' => 'photo_lustre', 'gsm' => '120-150', 'loaded' => false, 'out' => true,  'printer' => 'Noritsu 931BL (Tray 5)'],
        ];

        $rows = session('local_print_trays', $default);

        $mediaShort = ['plain' => 'plain', 'cardstock' => 'cardstock', 'cardstock_scored' => 'cardstock', 'photo_glossy' => 'glossy', 'photo_lustre' => 'lustre', 'photo_matte' => 'matte', 'film' => 'film', 'envelopes' => 'envelopes', 'labels' => 'labels', 'magnets' => 'magnets'];
        $sizePretty = ['4x6' => '4 × 6', '5x7' => '5 × 7', '7x10' => '7 × 10', '8.5x11' => '8.5 × 11'];

        foreach ($rows as &$r) {
            if ($r['key'] === 'mp') {
                $r['desc'] = 'Load anything, up to 324gsm';
            } else {
                $r['desc'] = trim(($sizePretty[$r['size']] ?? $r['size'] ?? '') . ' ' . ($mediaShort[$r['media']] ?? $r['media'] ?? ''));
            }
            $r['user_type'] = \App\Models\Store::deriveUserType($r['size'] ?? null, $r['media'] ?? null, $r['gsm'] ?? null);
        }
        unset($r);

        return $rows;
    }

    /* ── Screen 1: start ─────────────────────────────── */
    public function start()
    {
        $trays = $this->trays();
        $loaded = collect($trays)->where('loaded', true)->count();

        return view($this->view('start'), [
            'trays'       => $trays,
            'loadedCount' => $loaded,
            'trayTotal'   => count($trays),
        ]);
    }

    /* ── "Design something" → home / landing ─────────── */
    public function design()
    {
        return redirect('/');
    }

    /* ── Screen 2: upload a PDF ──────────────────────── */
    public function pdf()
    {
        return view($this->view('pdf'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimetypes:application/pdf|max:204800', // 200 MB
        ]);

        $file = $request->file('pdf');
        $path = $file->store('local-print', 'public');

        session([
            'local_print_file' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ],
        ]);

        return redirect()->route('localprint.check');
    }

    /* ── Screen 3: preflight + print ─────────────────── */
    public function check()
    {
        $file = session('local_print_file');
        if (!$file) {
            return redirect()->route('localprint.pdf');
        }

        // Use asset() so the URL is same-origin with the current request (avoids
        // CORS blocking PDF.js when APP_URL differs from the browser host).
        $file['url']   = asset('storage/' . ltrim($file['path'], '/'));
        $absPath       = storage_path('app/public/' . $file['path']);
        $file['bytes'] = is_file($absPath) ? filesize($absPath) : ($file['size'] ?? 0);

        // Feed the client the physical dimensions of each tray media so the
        // preflight can compare the PDF against the selected tray in real time.
        $trayDims = [
            '4x6'    => ['w' => 4.0, 'h' => 6.0],
            '5x7'    => ['w' => 5.0, 'h' => 7.0],
            '7x10'   => ['w' => 7.0, 'h' => 10.0],
            '8.5x11' => ['w' => 8.5, 'h' => 11.0],
        ];

        return view($this->view('check'), [
            'file'     => $file,
            'trays'    => $this->trays(),
            'trayDims' => $trayDims,
        ]);
    }

    public function doPrint(Request $request)
    {
        $data = $request->validate([
            'tray'   => 'required|string',
            'copies' => 'required|integer|min:1|max:999',
        ]);

        $file = session('local_print_file');

        session()->flash('print_summary', [
            'name'   => $file['name'] ?? 'your file',
            'tray'   => collect($this->trays())->firstWhere('key', $data['tray'])['label'] ?? 'the selected tray',
            'copies' => $data['copies'],
        ]);

        return redirect()->route('localprint.sent');
    }

    public function sent()
    {
        return view($this->view('sent'), ['summary' => session('print_summary')]);
    }

    /* ── Helper setup (recovery link) ────────────────── */
    public function setup()
    {
        return view($this->view('setup'));
    }

    /* ── Tray setup for the local printer (session) ──── */
    public function trayScreen()
    {
        return view($this->view('trays'), [
            'rows'         => $this->traySetupRows(),
            'sizeOptions'  => \App\Models\Store::traySizeOptions(),
            'mediaOptions' => \App\Models\Store::trayMediaOptions(),
            'gsmOptions'   => \App\Models\Store::trayGsmOptions(),
        ]);
    }

    protected function traySetupRows(): array
    {
        $rows = $this->trays();
        foreach ($rows as &$r) {
            $r['enabled'] = !($r['out'] ?? false);
        }
        return $rows;
    }

    public function saveTrays(Request $request)
    {
        $data = $request->validate(['trays' => 'required|array']);

        $rows = [];
        foreach (['mp', 'tray1', 'tray2', 'tray3', 'tray4', 'tray5'] as $key) {
            $t = $data['trays'][$key] ?? [];
            $rows[] = [
                'key'    => $key,
                'label'  => \App\Models\Store::trayLabels()[$key],
                'size'   => $t['size'] ?? null,
                'media'  => $t['media'] ?? null,
                'gsm'    => $t['gsm'] ?? null,
                'loaded' => $key === 'mp' ? true : (bool) ($t['enabled'] ?? false),
                'out'    => $key === 'mp' ? false : !((bool) ($t['enabled'] ?? false)),
            ];
        }

        session(['local_print_trays' => $rows]);

        return redirect()->route('localprint.trays')->with('success', 'Tray setup saved.');
    }
}
