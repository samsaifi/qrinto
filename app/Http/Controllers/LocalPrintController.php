<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Direct print on the customer's own Noritsu 931BL (Windows, spec §6).
 * This is the web front-end of the flow - start → upload PDF → preflight → print.
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

    /* ── "Design something" → size selection ────────── */
    public function design()
    {
        return redirect()->route('localprint.sizes');
    }

    /* ── Own-design: size selection (like /cards) ────── */
    public function sizes()
    {
        $type = \App\Models\ProductType::whereIn('slug', ['cards', 'greeting-cards', 'custom-cards'])
            ->orWhere('name', 'like', '%card%')
            ->parents()
            ->first() ?? \App\Models\ProductType::parents()->first();

        $subTypes = $type->children()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        return view($this->view('sizes'), compact('type', 'subTypes'));
    }

    /* ── Own-design: customizer (empty canvas) ──────── */
    public function customizer(Request $request)
    {
        $orientation = $request->query('orientation', 'portrait');
        $sizeCode    = $request->query('size');
        $titleSlug   = $request->query('title');
        $isPortrait  = $orientation === 'portrait';

        $flowData = [
            'type_name'     => 'Local Print',
            'type_slug'     => 'local-print',
            'category_name' => 'Local Print',
            'category_slug' => 'local-print',
            'local_print'   => true,
            'orientation'   => $orientation,
        ];

        if ($sizeCode) {
            [$w, $h] = array_pad(explode('x', strtolower($sizeCode)), 2, null);
            if ($w && $h) {
                $flowData['size_width']  = (float) $w;
                $flowData['size_height'] = (float) $h;
                $flowData['size_unit']   = 'in';
                $flowData['size_name']   = $w . ' × ' . $h . ' in';
                $flowData['size_title']  = $titleSlug ? ucwords(str_replace('-', ' ', $titleSlug)) : ($w . '×' . $h);
                $isPortrait = (float) $h > (float) $w;
                $flowData['orientation'] = $isPortrait ? 'portrait' : 'landscape';
            }
        }

        $flowData['size_price'] = 0;
        session(['quick_flow_data' => $flowData]);

        $product = \App\Models\Product::where('is_active', 1)->first();
        if (!$product) {
            return redirect()->route('localprint.sizes');
        }
        $product->load('images', 'productType');

        $unitPrice          = 0;
        $oldPrice           = null;
        $localPrintMode     = true;
        $template           = null;

        $qfc = new \App\Http\Controllers\QuickFlowController;
        $activeTemplates    = $qfc->buildTemplatesForJsPublic();
        $templateCategories = $qfc->buildTemplateCategoriesForJsPublic();

        $titleLower = strtolower($titleSlug ?? '');
        if (str_contains($titleLower, 'folded')) {
            $noOfPages = 4;
        } elseif (str_contains($titleLower, 'double')) {
            $noOfPages = 2;
        } else {
            $noOfPages = 1;
        }

        $sizeW = $flowData['size_width'] ?? ($isPortrait ? 3.5 : 5);
        $sizeH = $flowData['size_height'] ?? ($isPortrait ? 5 : 3.5);
        $maxCanvasPx = 560;
        $aspect = $sizeW / $sizeH;
        if ($aspect >= 1) {
            $canvasW = $maxCanvasPx;
            $canvasH = round($maxCanvasPx / $aspect);
        } else {
            $canvasH = $maxCanvasPx;
            $canvasW = round($maxCanvasPx * $aspect);
        }

        $localPrintOverrides = [
            'noOfPages'    => $noOfPages,
            'isPortrait'   => $isPortrait,
            'canvasWidth'  => $canvasW,
            'canvasHeight' => $canvasH,
        ];

        return view('quick-flow-pc.customize', compact(
            'product', 'unitPrice', 'oldPrice', 'flowData',
            'activeTemplates', 'templateCategories', 'template',
            'localPrintMode', 'localPrintOverrides'
        ));
    }

    /* ── Own-design: preview + print ────────────────── */
    public function preview(Request $request)
    {
        $productId = $request->input('product_id');
        $product   = \App\Models\Product::findOrFail($productId);

        $uploadIdsJson = $request->input('upload_ids');
        $uploadIds     = $uploadIdsJson ? json_decode($uploadIdsJson, true) : [];

        $uploads = collect();
        if (!empty($uploadIds)) {
            $uploads = \App\Models\CustomerUpload::whereIn('id', array_values($uploadIds))
                ->get()
                ->keyBy('id');
        }

        $flowData    = session('quick_flow_data', []);
        $orientation = $request->input('orientation', $flowData['orientation'] ?? 'portrait');
        $noOfPages   = count($uploadIds);

        // Build uploadedImages map (slot => file_path)
        $uploadedImages = [];
        foreach ($uploadIds as $slot => $uid) {
            $upload = $uploads->get($uid);
            if ($upload) {
                $uploadedImages[$slot] = $upload->file_path;
            }
        }

        $pdfUrl  = null;
        $pdfName = 'LocalPrint_' . now()->format('Ymd_His') . '_' . session()->getId() . '.pdf';
        $pdfDir  = storage_path('app/public/local-print/pdfs');
        $pdfPath = $pdfDir . '/' . $pdfName;

        $qfc = new \App\Http\Controllers\QuickFlowController;

        if ($noOfPages == 4) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'background_image' => 'rotate_0',
                'frame_image' => 'rotate_180_plus',
                'overlay_image' => 'rotate_0',
            ];
            $portrait_imageTypes = [
                'frame_image' => 'rotate_90_minus',
                'overlay_image' => 'rotate_90_minus',
                'sample_image' => 'rotate_90_plus',
                'background_image' => 'rotate_90_plus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = array_keys($imageTypes);
        } elseif ($noOfPages == 2) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'frame_image'  => 'rotate_180_plus',
            ];
            $portrait_imageTypes = [
                'frame_image'  => 'rotate_90_minus',
                'sample_image' => 'rotate_90_minus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = ['frame_image', 'sample_image'];
        } else {
            $imageTypes = [];
            $slots = [];
        }

        if ($noOfPages >= 2) {
            $absolutePaths = [];

            foreach ($slots as $key) {
                $localPath = null;

                if (!empty($uploadedImages[$key])) {
                    $localPath = storage_path('app/public/' . $uploadedImages[$key]);
                    if (!file_exists($localPath)) {
                        $localPath = public_path('storage/' . $uploadedImages[$key]);
                    }
                }

                if (empty($localPath) || !file_exists($localPath)) {
                    $dbPath = $product ? $product->getRawOriginal($key) : null;
                    if ($dbPath) {
                        $localPath = storage_path('app/public/' . $dbPath);
                        if (!file_exists($localPath)) {
                            $localPath = public_path('storage/' . $dbPath);
                        }
                    }
                }

                if ($localPath && file_exists($localPath)) {
                    $rotation = $imageTypes[$key] ?? 'rotate_0';
                    $rotatedAbsPath = $qfc->physicallyRotateImage($localPath, $rotation);
                    $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
                } else {
                    $absolutePaths[$key] = null;
                }
            }

            $width = floatval($request->input('size_width', $flowData['size_width'] ?? 3.5));
            $height = floatval($request->input('size_height', $flowData['size_height'] ?? 5));
            $pdfWidth = min($width, $height);
            $pdfHeight = max($width, $height);
            $pdfOrientation = $orientation === 'landscape' ? 'landscape' : 'landscape';

            if (!File::isDirectory($pdfDir)) {
                File::makeDirectory($pdfDir, 0755, true, true);
            }

            if ($noOfPages == 4) {
                $pdf = Pdf::loadView('quick-flow.pdf.design', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            } else {
                $widthVal = floatval($request->input('size_width', $flowData['size_width'] ?? 5));
                $heightVal = floatval($request->input('size_height', $flowData['size_height'] ?? 7));
                $unit = strtolower(trim($request->input('size_unit', $flowData['size_unit'] ?? 'in')));
                $cssUnit = ($unit === 'inch') ? 'in' : $unit;

                $pdf = Pdf::loadView('quick-flow.pdf.design-double', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'cssUnit' => $cssUnit,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            }

            $pdf->save($pdfPath);
        } else {
            // Single-page product
            $absolutePath = null;

            if (!empty($uploadedImages)) {
                $firstImage = is_string(reset($uploadedImages)) ? reset($uploadedImages) : null;
                if ($firstImage) {
                    $localPath = storage_path('app/public/' . $firstImage);
                    if (file_exists($localPath)) {
                        $absolutePath = str_replace('\\', '/', $localPath);
                    } else {
                        $localPath = public_path('storage/' . $firstImage);
                        if (file_exists($localPath)) {
                            $absolutePath = str_replace('\\', '/', $localPath);
                        }
                    }
                }
            }

            if ($absolutePath) {
                $widthVal = floatval($request->input('size_width', $flowData['size_width'] ?? 5));
                $heightVal = floatval($request->input('size_height', $flowData['size_height'] ?? 7));
                $unit = strtolower(trim($request->input('size_unit', $flowData['size_unit'] ?? 'in')));

                if ($orientation === 'landscape') {
                    $pdfWidthVal = max($widthVal, $heightVal);
                    $pdfHeightVal = min($widthVal, $heightVal);
                } else {
                    $pdfWidthVal = min($widthVal, $heightVal);
                    $pdfHeightVal = max($widthVal, $heightVal);
                }

                $cssUnit = ($unit === 'inch') ? 'in' : $unit;
                $ptsPerUnit = 72;
                if ($unit === 'cm') {
                    $ptsPerUnit = 72 / 2.54;
                } elseif ($unit === 'mm') {
                    $ptsPerUnit = 72 / 25.4;
                } elseif ($unit === 'px' || $unit === 'pixel') {
                    $ptsPerUnit = 0.75;
                }

                $pdfWidthPts = $pdfWidthVal * $ptsPerUnit;
                $pdfHeightPts = $pdfHeightVal * $ptsPerUnit;

                if (!File::isDirectory($pdfDir)) {
                    File::makeDirectory($pdfDir, 0755, true, true);
                }

                $pdf = Pdf::loadView('quick-flow.pdf.design-single', [
                    'image' => $absolutePath,
                    'width' => $pdfWidthVal,
                    'height' => $pdfHeightVal,
                    'cssUnit' => $cssUnit,
                ]);
                $pdf->setPaper([0, 0, $pdfWidthPts, $pdfHeightPts]);
                $pdf->save($pdfPath);
            }
        }

        if (file_exists($pdfPath)) {
            $pdfUrl = asset('storage/local-print/pdfs/' . $pdfName);
            session(['local_print_file' => [
                'path' => 'local-print/pdfs/' . $pdfName,
                'name' => $pdfName,
                'size' => filesize($pdfPath),
                'type' => 'pdf',
            ]]);
        }

        $user  = auth()->user();
        $store = ($user && $user->store_id) ? $user->store : null;
        $trayDims   = \App\Models\Store::traySizeDimensions();
        $trayDimsJs = [];
        foreach ($trayDims as $key => [$w, $h]) {
            $trayDimsJs[$key] = ['w' => $w, 'h' => $h];
        }

        return view($this->view('preview'), [
            'product'      => $product,
            'uploads'      => $uploads,
            'uploadIds'    => $uploadIds,
            'flowData'     => $flowData,
            'pdfUrl'       => $pdfUrl,
            'loggedIn'     => (bool) $store,
            'storeName'    => $store->name ?? null,
            'sizeOptions'  => \App\Models\Store::traySizeOptions(),
            'mediaOptions' => \App\Models\Store::trayMediaOptions(),
            'gsmOptions'   => \App\Models\Store::trayGsmOptions(),
            'trayDims'     => $trayDimsJs,
        ]);
    }

    /* ── Screen 2: upload a PDF ──────────────────────── */
    public function pdf()
    {
        return view($this->view('pdf'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png,image/webp,image/tiff|max:204800',
        ]);

        $file = $request->file('file');
        $path = $file->store('local-print', 'public');
        $mime = $file->getMimeType();

        session([
            'local_print_file' => [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => str_starts_with($mime, 'image/') ? 'image' : 'pdf',
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

        $file['url']   = asset('storage/' . ltrim($file['path'], '/'));
        $absPath       = storage_path('app/public/' . $file['path']);
        $file['bytes'] = is_file($absPath) ? filesize($absPath) : ($file['size'] ?? 0);
        $file['type']  = $file['type'] ?? 'pdf';

        $trayDims = \App\Models\Store::traySizeDimensions();
        $trayDimsJs = [];
        foreach ($trayDims as $key => [$w, $h]) {
            $trayDimsJs[$key] = ['w' => $w, 'h' => $h];
        }

        $user  = auth()->user();
        $store = ($user && $user->store_id) ? $user->store : null;

        if ($store) {
            $storeTrays = collect($store->trayRows())->where('enabled', true)->values();
            $trays = $storeTrays->map(fn($r) => [
                'key'      => $r['key'],
                'label'    => $r['label'],
                'printer'  => $r['printer'],
                'size'     => $r['size'],
                'media'    => $r['media'],
                'gsm'      => $r['gsm'],
                'density'  => $r['density'] ?? null,
                'loaded'   => true,
                'out'      => false,
                'desc'     => trim(($r['size'] ? str_replace('x', ' × ', $r['size']) : '') . ' ' . ($r['media'] ?? '')),
                'user_type' => $r['user_type'] ?? null,
            ])->all();
        } else {
            $trays = [];
        }

        return view($this->view('check'), [
            'file'         => $file,
            'trays'        => $trays,
            'trayDims'     => $trayDimsJs,
            'loggedIn'     => (bool) $store,
            'storeName'    => $store->name ?? null,
            'sizeOptions'  => \App\Models\Store::traySizeOptions(),
            'mediaOptions' => \App\Models\Store::trayMediaOptions(),
            'gsmOptions'   => \App\Models\Store::trayGsmOptions(),
        ]);
    }

    public function doPrint(Request $request)
    {
        $data = $request->validate([
            'tray'   => 'required|string',
            'copies' => 'required|integer|min:1|max:999',
        ]);

        $file = session('local_print_file');
        $selectedTray = collect($this->trays())->firstWhere('key', $data['tray']);

        session()->flash('print_summary', [
            'name'      => $file['name'] ?? 'your file',
            'tray'      => $selectedTray['label'] ?? 'the selected tray',
            'tray_key'  => $selectedTray['key'] ?? $data['tray'],
            'size'      => $selectedTray['size'] ?? null,
            'media'     => $selectedTray['media'] ?? null,
            'gsm'       => $selectedTray['gsm'] ?? null,
            'user_type' => $selectedTray['user_type'] ?? null,
            'printer'   => $selectedTray['printer'] ?? null,
            'copies'    => $data['copies'],
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
