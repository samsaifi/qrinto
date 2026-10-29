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

        // Printer paper sizes offered as Custom-size presets. Only sizes that fit
        // the 2–12 in custom range; E2E (borderless sheet) variants are skipped.
        $labels = \App\Models\Store::traySizeOptions();
        $paperPresets = collect(\App\Models\Store::traySizeDimensions())
            ->reject(fn($dims, $code) => str_contains(strtolower($code . ' ' . ($labels[$code] ?? '')), 'e2e'))
            ->filter(fn($dims) => min($dims) >= 2 && max($dims) <= 12)
            ->map(fn($dims, $code) => [
                'label' => trim(preg_replace('/\s*\d+(\.\d+)?\s*x\s*\d+(\.\d+)?\s*(in|mm)$/i', '', $labels[$code] ?? $code)),
                'w'     => $dims[0],
                'h'     => $dims[1],
            ])
            ->values()
            ->all();

        return view($this->view('sizes'), compact('type', 'subTypes', 'paperPresets'));
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
                $pdf->setPaper([0, 0, $pdfWidth, $pdfHeight]);
            } else {
                $pdfWidth =max($width, $height);
                $pdfHeight =  min($width, $height);
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
                $pdf->setPaper([0, 0, $pdfWidth, $pdfHeight]);
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
            'storeName'    => $store->store_name ?? null,
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
        // Kind caps how many images / PDF pages are used: single 1, duplex 2, fold 4.
        $maxByKind = ['single' => 1, 'duplex' => 2, 'fold' => 4];
        $kind  = array_key_exists($request->input('kind'), $maxByKind) ? $request->input('kind') : 'single';
        $max   = $maxByKind[$kind];
        $shape = $request->input('shape') === 'landscape' ? 'landscape' : 'portrait';

        $request->validate([
            // Both the real content type (mimetypes, sniffed from the bytes) and the
            // client file extension must be PDF / JPEG / PNG / WebP — so renamed
            // executables or scripts (e.g. evil.php → evil.pdf) are rejected.
            'file'     => 'required_without:images|file|mimetypes:application/pdf|extensions:pdf|max:204800',
            'images'   => "required_without:file|array|max:{$max}",
            'images.*' => 'file|mimetypes:image/jpeg,image/png,image/webp|extensions:jpg,jpeg,png,webp|max:204800',
        ], [
            'images.max'          => "This print type takes at most {$max} " . ($max === 1 ? 'image' : 'images') . '.',
            'file.mimetypes'      => 'Only PDF files can be uploaded here.',
            'file.extensions'     => 'Only PDF files can be uploaded here.',
            'images.*.mimetypes'  => 'Only JPEG, PNG or WebP images can be uploaded.',
            'images.*.extensions' => 'Only JPEG, PNG or WebP images can be uploaded.',
        ]);

        $files = $request->hasFile('file') ? [$request->file('file')] : array_values($request->file('images', []));
        // Random name + an extension picked from the validated content type —
        // never the client's file name, so nothing executable lands in storage.
        $extFor = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $paths = array_map(
            fn($f) => $f->storeAs('local-print', \Illuminate\Support\Str::random(40) . '.' . ($extFor[$f->getMimeType()] ?? 'bin'), 'public'),
            $files
        );
        $first = $files[0];
        $mime  = $first->getMimeType();

        session([
            'local_print_file' => [
                'path'       => $paths[0],
                'paths'      => $paths,
                'name'       => count($files) > 1 ? count($files) . ' images' : $first->getClientOriginalName(),
                'size'       => array_sum(array_map(fn($f) => $f->getSize(), $files)),
                'type'       => str_starts_with($mime, 'image/') ? 'image' : 'pdf',
                'kind'       => $kind,
                'shape'      => $shape,
                'page_limit' => $max,
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
        // Choices from the upload screen (older sessions predate them).
        $file['kind']       = $file['kind'] ?? 'single';
        $file['shape']      = $file['shape'] ?? 'portrait';
        $file['page_limit'] = $file['page_limit'] ?? 1;
        $file['images']     = $file['type'] === 'image'
            ? array_map(fn($p) => asset('storage/' . ltrim($p, '/')), $file['paths'] ?? [$file['path']])
            : [];

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
                'desc'     => trim((\App\Models\Store::customTraySizeLabel($r)
                    ?? ($r['size'] ? str_replace('x', ' × ', $r['size']) : '')) . ' ' . ($r['media'] ?? '')),
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
            'storeName'    => $store->store_name ?? null,
            'sizeOptions'  => \App\Models\Store::traySizeOptions(),
            'mediaOptions' => \App\Models\Store::trayMediaOptions(),
            'gsmOptions'   => \App\Models\Store::trayGsmOptions(),
        ]);
    }

    /**
     * Testing duplicate of check(): identical data, renders the test-check
     * view (which loads js/test-print-settings.js and the test modal partial).
     * Reached via /testing/print/pdf/check.
     */
    public function testCheck()
    {
        return $this->renderTestCheck('test-check');
    }

    /**
     * Same data as testCheck(), rendering new-test-check — the testing page for
     * the NEW PrintTrays (Adobe) bridge. Reached via /new-testing/print/pdf/check.
     */
    public function newTestCheck()
    {
        return $this->renderTestCheck('new-test-check');
    }

    protected function renderTestCheck(string $viewName)
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
                'desc'     => trim((\App\Models\Store::customTraySizeLabel($r)
                    ?? ($r['size'] ? str_replace('x', ' × ', $r['size']) : '')) . ' ' . ($r['media'] ?? '')),
                'user_type' => $r['user_type'] ?? null,
            ])->all();
        } else {
            $trays = [];
        }

        return view($this->view($viewName), [
            'file'         => $file,
            'trays'        => $trays,
            'trayDims'     => $trayDimsJs,
            'loggedIn'     => (bool) $store,
            'storeName'    => $store->store_name ?? null,
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

    /**
     * Save a browser-regenerated PDF (rebuilt to the selected paper size and
     * cover-filled) back to storage, overwriting the working print file. Called
     * from the preview flow's "Apply" button so the SAVED file matches exactly
     * what will be printed. Returns the fresh public URL.
     */
    public function saveRegenerated(Request $request)
    {
        $data = $request->validate([
            'pdf'  => 'required|string',           // raw base64 (no data: prefix)
            'name' => 'nullable|string|max:255',
        ]);

        // Strip an optional "data:application/pdf;base64," prefix, then decode.
        $b64 = preg_replace('/^data:[^;]+;base64,/', '', $data['pdf']);
        $bin = base64_decode($b64, true);
        if ($bin === false || strncmp($bin, '%PDF', 4) !== 0) {
            return response()->json(['ok' => false, 'error' => 'Invalid PDF data.'], 422);
        }

        $pdfDir = storage_path('app/public/local-print/pdfs');
        if (!File::isDirectory($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true, true);
        }

        $pdfName = 'LocalPrint_' . now()->format('Ymd_His') . '_' . session()->getId() . '.pdf';
        $pdfPath = $pdfDir . '/' . $pdfName;
        File::put($pdfPath, $bin);

        $rel = 'local-print/pdfs/' . $pdfName;

        // NOTE: we intentionally DO NOT overwrite session('local_print_file') here.
        // That session entry is the ORIGINAL uploaded file shown in the preview on
        // the check page; overwriting it would replace the uploaded image with this
        // regenerated PDF on the next page load (refresh). The regenerated file is
        // only needed for the current print, which the front-end tracks in memory
        // (savedPdfUrl) and fetches by the URL we return below.

        return response()->json([
            'ok'   => true,
            'url'  => asset('storage/' . $rel),
            'name' => $pdfName,
            'size' => strlen($bin),
        ]);
    }

    /* ── Local-print audit logging (spec §1/§11) ─────────
     * Both print pages (/print/pdf/check and the customizer preview) call these
     * two endpoints around their existing pp.print() call. We create ONE record
     * before sending to PrintTrays and update that SAME record with the result,
     * without touching the print configuration itself. */

    public function logStart(Request $request, \App\Services\LocalPrintLogService $service)
    {
        $payload = $request->validate([
            'source'           => 'nullable|string|max:60',
            'source_url'       => 'nullable|string|max:255',
            'print_type'       => 'nullable|string|max:40',
            'device_name'      => 'nullable|string|max:160',
            'file'             => 'nullable|array',
            'request_payload'  => 'nullable|array',
            'printer_settings' => 'nullable|array',
            'design_settings'  => 'nullable|array',
        ]);

        try {
            $log = $service->start($payload, $request);
            return response()->json(['ok' => true, 'id' => $log->id]);
        } catch (\Throwable $e) {
            // Logging must never break printing — fail soft with id = null.
            report($e);
            return response()->json(['ok' => false, 'id' => null, 'error' => 'log_failed']);
        }
    }

    public function logComplete(Request $request, \App\Models\LocalPrintLog $localPrintLog, \App\Services\LocalPrintLogService $service)
    {
        $result = $request->validate([
            'ok'          => 'required|boolean',
            'error'       => 'nullable|string',
            'response'    => 'nullable',
            'job_id'      => 'nullable|string|max:120',
            'duration_ms' => 'nullable|integer',
        ]);

        try {
            $service->complete($localPrintLog, $result);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'log_update_failed']);
        }
    }

    /**
     * Save the exact print-ready PDF that was sent to PrintTrays and attach it to
     * its audit-log row. The file is uploaded as MULTIPART (binary) — not base64
     * JSON — so a large print PDF does not blow past the web server's body limit
     * (which previously caused HTTP 413 on the regenerate-save flow). Fail-soft:
     * a save error never breaks the print.
     */
    public function logAttachFile(Request $request, \App\Models\LocalPrintLog $localPrintLog)
    {
        $request->validate([
            'pdf' => 'required|file|mimetypes:application/pdf|max:51200', // ≤ 50 MB
        ]);

        try {
            $dir = storage_path('app/public/local-print/pdfs');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $name = 'LPLog_' . $localPrintLog->id . '_' . now()->format('Ymd_His') . '.pdf';
            $request->file('pdf')->move($dir, $name);

            $rel = 'local-print/pdfs/' . $name;
            $localPrintLog->update([
                'generated_pdf_path' => $rel,
                'generated_pdf_url'  => asset('storage/' . $rel),
            ]);

            return response()->json(['ok' => true, 'url' => asset('storage/' . $rel)]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'pdf_save_failed']);
        }
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
