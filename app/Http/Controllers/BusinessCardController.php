<?php

namespace App\Http\Controllers;

use App\Models\UserDesign;
use App\Services\BusinessCard\BusinessCard;
use App\Services\BusinessCard\BusinessCardDesignService;
use App\Services\BusinessCard\BusinessCardPdfService;
use App\Services\BusinessCard\CardSheetLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Double-sided business card (own-design / local print).
 *
 * Reuses the generic customizer view in its 2-page mode (Front = frame_image,
 * Back = sample_image) but with fixed 3.5 × 2 in landscape dimensions, and
 * saves through its own endpoint into the existing `user_designs` storage.
 */
class BusinessCardController extends Controller
{
    /**
     * Longest base64 data URI accepted per side. The editor sends JPEG at 0.95,
     * so a 1200 DPI side (4200 × 2400) is well under 16 MB decoded; base64 adds
     * a third. Two sides stay ~43 MB per request, inside common post_max_size.
     */
    protected const MAX_IMAGE_CHARS = 22 * 1024 * 1024;

    /** Session key holding the last business card saved in this session. */
    public const SESSION_DESIGN = 'business_card_design_id';
    /* ── Customizer (reached from /print/own-design/sizes/customizer?title=business-card) ── */
    public function customizer(Request $request)
    {
        // The size picker may send ?size=2x3.5&orientation=portrait - the card
        // is always 3.5 × 2 in landscape, so the query is deliberately ignored.
        $w = BusinessCard::WIDTH_IN;
        $h = BusinessCard::HEIGHT_IN;

        $flowData = [
            'type_name'     => 'Local Print',
            'type_slug'     => 'local-print',
            'category_name' => 'Local Print',
            'category_slug' => 'local-print',
            'local_print'   => true,
            'business_card' => true,
            'orientation'   => BusinessCard::ORIENTATION,
            'size_width'    => $w,
            'size_height'   => $h,
            'size_unit'     => 'in',
            'size_name'     => $w . ' × ' . (int) $h . ' in',
            'size_title'    => 'Business Card',
            'size_price'    => 0,
        ];
        session(['quick_flow_data' => $flowData]);

        $product = \App\Models\Product::where('is_active', 1)->first();
        if (!$product) {
            return redirect()->route('localprint.sizes');
        }
        $product->load('images', 'productType');

        $unitPrice      = 0;
        $oldPrice       = null;
        $localPrintMode = true;
        $template       = null;

        $qfc = new QuickFlowController;
        $activeTemplates    = $qfc->buildTemplatesForJsPublic();
        $templateCategories = $qfc->buildTemplateCategoriesForJsPublic();

        // Same 560px long edge the generic customizer uses → 560 × 320.
        $canvasW = 560;
        $canvasH = (int) round($canvasW * $h / $w);

        $localPrintOverrides = [
            'noOfPages'    => 2,
            'isPortrait'   => false,
            'canvasWidth'  => $canvasW,
            'canvasHeight' => $canvasH,
        ];

        $businessCard = [
            'sides'         => ['frame_image' => 'Front', 'sample_image' => 'Back'],
            'saveUrl'       => route('business-card.save'),
            'storagePrefix' => 'qrinto_bizcard_v1',
            'widthIn'       => $w,
            'heightIn'      => $h,
            'orientation'   => BusinessCard::ORIENTATION,
            'imageWidth'    => BusinessCard::imageWidthPx(),
            'imageHeight'   => BusinessCard::imageHeightPx(),
            'dpi'           => BusinessCard::DPI,
            'dpis'          => BusinessCard::DPIS,
        ];

        return view('quick-flow-pc.customize', compact(
            'product', 'unitPrice', 'oldPrice', 'flowData',
            'activeTemplates', 'templateCategories', 'template',
            'localPrintMode', 'localPrintOverrides', 'businessCard'
        ));
    }

    /**
     * The editor sends the save as multipart FILES (payload.json + front/back
     * JPGs) because shared hosting (ModSecurity SecRequestBodyNoFilesLimit,
     * ~1 MB) answers 413 to large JSON / text bodies. Rebuild the original
     * JSON shape here so validation and the services stay unchanged.
     */
    protected function unpackMultipart(Request $request): void
    {
        $payload = $request->file('payload');
        if (!$payload || !$payload->isValid()) {
            return;
        }
        $data = json_decode((string) file_get_contents($payload->getRealPath()), true);
        if (!is_array($data)) {
            throw ValidationException::withMessages(['payload' => 'The design data could not be read.']);
        }
        foreach (['front', 'back'] as $side) {
            $file = $request->file("{$side}_image");
            if ($file && $file->isValid() && $file->getSize() <= self::MAX_IMAGE_CHARS) {
                $mime = $file->getMimeType() === 'image/png' ? 'image/png' : 'image/jpeg';
                $data['sides'][$side]['image'] = 'data:' . $mime . ';base64,'
                    . base64_encode((string) file_get_contents($file->getRealPath()));
            }
        }
        $request->replace($data);
    }

    /* ── Save both sides + generate the print PDFs ──────────────── */
    public function save(Request $request, BusinessCardDesignService $designs, BusinessCardPdfService $pdfs)
    {
        $this->unpackMultipart($request);

        $validated = $request->validate([
            'design_id'           => ['nullable', 'integer', 'min:1'],
            'product_id'          => ['required', 'integer', Rule::exists('products', 'id')],
            'width_in'            => ['required', 'numeric', $this->exactly(BusinessCard::WIDTH_IN, 'Business cards must be 3.5 inches wide.')],
            'height_in'           => ['required', 'numeric', $this->exactly(BusinessCard::HEIGHT_IN, 'Business cards must be 2 inches high.')],
            'orientation'         => ['required', 'string', Rule::in([BusinessCard::ORIENTATION])],
            'dpi'                 => ['nullable', 'integer', Rule::in(BusinessCard::DPIS)],
            'sides'               => ['required', 'array:front,back'],
            'sides.front'         => ['required', 'array:image,design'],
            'sides.back'          => ['required', 'array:image,design'],
            'sides.*.image'       => ['required', 'string', 'max:' . self::MAX_IMAGE_CHARS],
            'sides.*.design'      => ['required', 'array'],
            'sides.*.design.objects' => ['present', 'array'],
        ], [
            'orientation.in' => 'Business cards are always landscape.',
            'sides.front.required' => 'The front side design is missing.',
            'sides.back.required'  => 'The back side design is missing.',
        ]);

        $existing = null;
        if (!empty($validated['design_id'])) {
            $existing = UserDesign::find($validated['design_id']);
            if (!$existing || !BusinessCardDesignService::isBusinessCardDesign($existing)) {
                throw ValidationException::withMessages(['design_id' => 'The selected business card design does not exist.']);
            }
            $this->authorizeDesign($existing);
        }

        try {
            $design = $designs->save(
                $validated['sides'],
                (int) $validated['product_id'],
                auth()->id(),
                session()->getId(),
                $existing,
                (int) ($validated['dpi'] ?? BusinessCard::DPI)
            );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Business card save failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Your business card could not be saved. Please try again.',
            ], 500);
        }

        // The design is already saved - a PDF failure is reported, not fatal.
        $errors = $pdfs->generateAll($design);

        // The preview page opens the most recently saved card of this session.
        session([self::SESSION_DESIGN => $design->id]);

        return response()->json([
            'success'     => true,
            'design_id'   => $design->id,
            'preview_url' => route('print.own-design.customizer.preview.business-card', ['design' => $design->id]),
            'sides'       => array_keys($design->design_data['sides']),
            'width_in'    => BusinessCard::WIDTH_IN,
            'height_in'   => BusinessCard::HEIGHT_IN,
            'orientation' => BusinessCard::ORIENTATION,
            'pdfs'        => $this->pdfLinks($design),
            'pdf_errors'  => $errors,
            'message'     => $errors
                ? 'Your design was saved, but some PDFs could not be generated.'
                : 'Your business card was saved.',
        ], $existing ? 200 : 201);
    }

    /* ── Preview + print page (reuses the own-design preview view) ── */
    public function preview(Request $request)
    {
        $request->validate(['design' => ['nullable', 'integer', 'min:1']]);

        $designId = $request->query('design') ?: session(self::SESSION_DESIGN);
        if (!$designId) {
            return redirect()->route('localprint.customizer', ['size' => '3.5x2', 'orientation' => 'landscape', 'title' => 'business-card']);
        }

        $design = UserDesign::find($designId);
        if (!$design || !BusinessCardDesignService::isBusinessCardDesign($design)) {
            abort(404);
        }
        $this->authorizeDesign($design);

        $pdfs    = app(BusinessCardPdfService::class);
        $formats = [];
        foreach (BusinessCard::PAGE_FORMATS as $key) {
            $formats[$key] = $this->formatForView($key, BusinessCard::format($key), $pdfs, $design);
        }

        $w = BusinessCard::WIDTH_IN;
        $h = BusinessCard::HEIGHT_IN;
        $flowData = [
            'business_card' => true,
            'orientation'   => BusinessCard::ORIENTATION,
            'size_width'    => $w,
            'size_height'   => $h,
            'size_unit'     => 'in',
            'size_name'     => $w . ' × ' . (int) $h . ' in',
        ];

        $user  = auth()->user();
        $store = ($user && $user->store_id) ? $user->store : null;
        $trayDimsJs = [];
        foreach (\App\Models\Store::traySizeDimensions() as $key => [$tw, $th]) {
            $trayDimsJs[$key] = ['w' => $tw, 'h' => $th];
        }

        $businessCard = [
            'designId'      => $design->id,
            'defaultFormat' => BusinessCard::DEFAULT_FORMAT,
            'storageKey'    => 'qrinto_bizcard_v1_format_' . $design->id,
            'editUrl'       => route('localprint.customizer', ['size' => '3.5x2', 'orientation' => 'landscape', 'title' => 'business-card']),
            // Side renders with their edges mirrored out by artBleedIn, so the
            // preview can show any bleed by cropping (no stretching).
            'sides'         => [
                'front' => route('business-card.side', ['design' => $design->id, 'side' => 'front', 'bleed' => 1]),
                'back'  => route('business-card.side', ['design' => $design->id, 'side' => 'back', 'bleed' => 1]),
            ],
            'artBleedIn'    => BusinessCard::PREVIEW_BLEED_PX / BusinessCard::DPI,
            'bleedsMm'      => BusinessCard::BLEEDS_MM,
            'defaultBleed'  => BusinessCard::DEFAULT_BLEED_MM,
            'formats'       => $formats,
        ];

        return view('quick-flow-pc.local-print.preview', [
            'product'      => $design->product,
            'uploads'      => collect(),
            'uploadIds'    => [],
            'flowData'     => $flowData,
            'pdfUrl'       => $formats[BusinessCard::DEFAULT_FORMAT]['url'],
            'loggedIn'     => (bool) $store,
            'storeName'    => $store->store_name ?? null,
            'sizeOptions'  => \App\Models\Store::traySizeOptions(),
            'mediaOptions' => \App\Models\Store::trayMediaOptions(),
            'gsmOptions'   => \App\Models\Store::trayGsmOptions(),
            'trayDims'     => $trayDimsJs,
            'businessCard' => $businessCard,
        ]);
    }

    /* ── One side's render at its saved DPI (owner only, for the preview page) ── */
    public function side(Request $request, UserDesign $design, string $side, BusinessCardPdfService $pdfs)
    {
        if (!BusinessCardDesignService::isBusinessCardDesign($design)) {
            abort(404);
        }
        $this->authorizeDesign($design);

        $data = $design->design_data;
        $path = $data['sides'][$side]['image_path'] ?? null;
        $disk = Storage::disk($data['disk'] ?? BusinessCardDesignService::DISK);
        abort_unless($path && $disk->exists($path), 404);

        // ?bleed=1: the render with its edges mirrored out by PREVIEW_BLEED_PX.
        if ($request->boolean('bleed')) {
            // Same 1/8 in at any render DPI.
            $dpi = BusinessCard::dpi($data['dpi'] ?? BusinessCard::DPI);
            $path = $pdfs->bled($disk, $path, (int) round(BusinessCard::PREVIEW_BLEED_PX * $dpi / BusinessCard::DPI));
        }

        return $disk->response($path, $side . '.jpg', [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    /* ── Download a generated sheet (owner only) ─────────────────── */
    public function download(Request $request, UserDesign $design, string $format, BusinessCardPdfService $pdfs)
    {
        if ($format === 'edge_to_edge') {
            $format = 'seven_ten_10_up';
        }

        if (!BusinessCardDesignService::isBusinessCardDesign($design)) {
            abort(404);
        }
        $this->authorizeDesign($design);

        // Sheet-layout choices from the preview page; sizes stay server-side and
        // a gap is only accepted where that sheet still fits every card with it.
        $max = BusinessCard::NUDGE_MAX_MM;
        $opts = BusinessCard::printOptions($request->validate([
            'flip' => ['nullable', Rule::in(BusinessCard::FLIPS)],
            'dx'   => ['nullable', 'numeric', "between:-{$max},{$max}"],
            'dy'   => ['nullable', 'numeric', "between:-{$max},{$max}"],
            'test' => ['nullable', 'boolean'],
            'gap'  => ['nullable', 'integer', Rule::in(BusinessCard::format($format)['gutters'] ?? [])],
            'bleed' => ['nullable', Rule::in(array_map(fn($mm) => rtrim(rtrim(number_format($mm, 1, '.', ''), '0'), '.'), BusinessCard::BLEEDS_MM))],
        ]), $format);

        try {
            // Generates only if this exact variant is missing.
            $path = $pdfs->generate($design, $format, false, $opts);
        } catch (\Throwable $e) {
            Log::error('Business card PDF download failed', ['design_id' => $design->id, 'format' => $format, 'error' => $e->getMessage()]);
            abort(500, 'The PDF could not be generated.');
        }

        return Storage::disk($design->design_data['disk'] ?? BusinessCardDesignService::DISK)
            ->download($path, BusinessCard::variantFilename($format, $opts), ['Content-Type' => 'application/pdf']);
    }

    /** Validation rule: numeric value equal to the fixed card dimension. */
    protected function exactly(float $expected, string $message): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($expected, $message) {
            if (!is_numeric($value) || abs((float) $value - $expected) > 1e-9) {
                $fail($message);
            }
        };
    }

    /** Owner = same signed-in user, or (for guest designs) the same session. */
    protected function authorizeDesign(UserDesign $design): void
    {
        // Guest cards are claimed by the user at login (see AppServiceProvider),
        // so they stay reachable after the session ID is regenerated.
        $owns = $design->user_id
            ? (int) $design->user_id === (int) auth()->id()
            : hash_equals((string) $design->session_id, (string) session()->getId());

        abort_unless($owns, 403, 'You do not have access to this business card.');
    }

    /**
     * One sheet option for the preview page. Slot boxes are percentages of the
     * page, taken from the same CardSheetLayout the PDF is rendered from, so the
     * on-screen thumbnail is the real sheet layout scaled proportionally.
     * A sheet with selectable gutters carries the cells for every gutter in
     * `gaps` (keyed by sixteenths); its top-level cells are the default's.
     */
    protected function formatForView(string $key, array $spec, BusinessCardPdfService $pdfs, UserDesign $design): array
    {
        $edgeToEdge = $spec['gap'] === CardSheetLayout::GAP_NONE;
        $sheetName  = $spec['sheet_name'] ?? ($key === 'a4_centered' ? 'A4' : '8.5 × 11 in');

        $base = [
            'key'         => $key,
            'label'       => $spec['label'],
            'paper'       => $spec['paper'] ?? null,
            // Card turned 90° on the sheet: the preview turns the artwork to match.
            'rotated'     => !empty($spec['rotate']),
            'sheet_name'  => $sheetName,
            'description' => $spec['description'],
            'filename'    => $spec['filename'],
            'edge_to_edge'=> $edgeToEdge,
            'recommended' => !empty($spec['recommended']),
            'width_pt'    => $spec['width_pt'],
            'height_pt'   => $spec['height_pt'],
            'width_in'    => round($spec['width_pt'] / BusinessCard::POINTS_PER_INCH, 2),
            'height_in'   => round($spec['height_pt'] / BusinessCard::POINTS_PER_INCH, 2),
            'url'         => route('business-card.download', ['design' => $design->id, 'format' => $key]),
        ];

        if (!isset($spec['gutters'])) {
            return $base + $this->sheetGeometry($spec, $pdfs->layout($key));
        }

        $gaps = [];
        foreach ($spec['gutters'] as $n) {
            $gaps[$n] = $this->sheetGeometry($spec, $pdfs->layout($key, $n));
        }

        return $base + $gaps[$spec['gutter']] + ['gap_default' => $spec['gutter'], 'gaps' => $gaps];
    }

    /** Cells, cut lines and spacing of one computed layout, as page percentages / inches. */
    protected function sheetGeometry(array $spec, array $layout): array
    {
        $pct = fn(array $slots) => array_map(fn($s) => [
            'left'   => round($s['x'] / $spec['width_pt'] * 100, 4),
            'top'    => round($s['y'] / $spec['height_pt'] * 100, 4),
            'width'  => round($s['width'] / $spec['width_pt'] * 100, 4),
            'height' => round($s['height'] / $spec['height_pt'] * 100, 4),
        ], $slots);

        // Cut lines: every card edge that is not a page edge (front and back
        // share them, so each cut goes through both sides at once).
        $edges = function (string $axis, string $size, float $page) use ($layout) {
            $v = [];
            foreach ($layout['front'] as $s) {
                $v[] = round($s[$axis], 3);
                $v[] = round($s[$axis] + $s[$size], 3);
            }
            $v = array_values(array_unique(array_filter($v, fn($e) => $e > 0.01 && $e < $page - 0.01)));
            sort($v);

            return array_map(fn($e) => round($e / $page * 100, 4), $v);
        };
        $cutsX = $edges('x', 'width', $spec['width_pt']);
        $cutsY = $edges('y', 'height', $spec['height_pt']);
        $edgeToEdge = $spec['gap'] === CardSheetLayout::GAP_NONE;

        $numbered = fn(array $slots) => array_map(
            fn($cell, $i) => $cell + ['n' => $i + 1],
            $pct($slots), array_keys($slots)
        );

        return [
            'title'       => $layout['count'] . ' per sheet',
            'summary'     => $spec['summary'] ?? ($layout['cols'] . ' × ' . $layout['rows'] . ', '
                . ($edgeToEdge ? 'edge to edge' : 'centered with even gaps')
                . ' · ' . (count($cutsX) + count($cutsY)) . ' straight cuts'),
            'cards'       => $layout['count'],
            'cols'        => $layout['cols'],
            'rows'        => $layout['rows'],
            'gutter_in'   => [round($layout['gutter_x'] / 72, 3), round($layout['gutter_y'] / 72, 3)],
            'margin_in'   => [round($layout['margin_x'] / 72, 3), round($layout['margin_y'] / 72, 3)],
            // Cell n on a back sheet = the front card it is printed behind.
            'front'       => $numbered($layout['front']),
            'back'        => $numbered($layout['back']),
            'back_short'  => $numbered($layout['back_short']),
            'cuts_x'      => $cutsX,
            'cuts_y'      => $cutsY,
        ];
    }

    protected function pdfLinks(UserDesign $design): array
    {
        $links = [];
        foreach ($design->design_data['pdfs'] ?? [] as $format => $pdf) {
            $links[$format] = [
                'url'       => route('business-card.download', ['design' => $design->id, 'format' => $format]),
                'filename'  => $pdf['filename'],
                'label'     => BusinessCard::format($format)['label'],
                'width_pt'  => $pdf['width_pt'],
                'height_pt' => $pdf['height_pt'],
                'cards'     => $pdf['cards'],
            ];
        }

        return $links;
    }
}
