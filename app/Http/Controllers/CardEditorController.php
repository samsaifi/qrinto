<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
use Illuminate\Http\Request;

/**
 * CardEditorController
 * --------------------
 * Standalone, self-contained card editor (v2) inspired by the
 * Greetings Island customizer: HTML/CSS overlay engine with
 * percentage-positioned elements, on-canvas rich-text editing and
 * multi-page support.
 *
 * This controller is completely independent from QuickFlowController and
 * the existing Fabric.js customizer. It does not touch any existing
 * blade, JS or route logic. Safe to remove without side effects.
 */
class CardEditorController extends Controller
{
    /**
     * Render the new editor.
     *
     * Optionally accepts a product slug/id purely to pre-fill the canvas
     * size; falls back to a sensible default 5x7 card when none is given.
     */
    public function index(Request $request)
    {
        // Which sides the card has: single (front), double (front + back), folded (front, inside, back).
        $type = $request->route('type') ?: 'folded';
        // portrait = tall card; landscape = width and height swapped (wide card).
        $orientation = $request->route('orientation') === 'landscape' ? 'landscape' : 'portrait';
        $product = $request->route('product');

        $found = null;
        if ($product) {
            $found = Product::where('slug', $product)
                ->orWhere('id', $product)
                ->first();
        }

        // Default card geometry (inches). Overridden by product if present.
        $size = [
            'width'  => 5.0,
            'height' => 7.0,
            'unit'   => 'in',
        ];
        if ($orientation === 'landscape') {
            [$size['width'], $size['height']] = [$size['height'], $size['width']];
        }

        // ?size=4x3.25 (inches): the canvas takes exactly this ratio, and its orientation follows it.
        $cardSize = null;
        if (preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)$/i', (string) $request->query('size'), $m) && $m[1] > 0 && $m[2] > 0) {
            $cardSize = ['w' => (float) $m[1], 'h' => (float) $m[2]];
            $size['width'] = $cardSize['w'];
            $size['height'] = $cardSize['h'];
            $orientation = $cardSize['w'] > $cardSize['h'] ? 'landscape' : 'portrait';
        }

        return view('card-editor.index', [
            'layouts' => self::layoutsForEditor(),
            'library' => self::libraryForEditor(),
            'product' => $found,
            'size'    => $size,
            'type'    => $type,
            'orientation' => $orientation,
            'cardSize' => $cardSize,
        ]);
    }

    /** Active admin-managed layouts grouped by view: {front: [{name, pages}], inside: [...], back: [...]}. */
    public static function layoutsForEditor(): array
    {
        return Template::layout()->active()->orderBy('sort_order')->orderBy('name')->get()
            ->groupBy(fn($t) => $t->canvas_config['view'] ?? 'front')
            ->map(fn($list) => $list->map(fn($t) => array_filter([
                'name'  => $t->name,
                'pages' => $t->canvas_config['pages'] ?? [],
                'bg'    => $t->canvas_config['bg'] ?? null,
            ]))->values())
            ->toArray();
    }

    /**
     * Admin-managed Text styles, Sticker sets and Background palettes:
     * {pairs: {category: [[line, ...], ...]}, stickers: {category: [emoji]}, bgColors: {theme: [hex]}}.
     */
    public static function libraryForEditor(): array
    {
        $rows = Template::whereIn('kind', [Template::KIND_TEXT, Template::KIND_STICKER, Template::KIND_BG, Template::KIND_SVG])
            ->active()->orderBy('sort_order')->orderBy('id')->get()->groupBy('kind');

        $pairs = [];
        foreach ($rows[Template::KIND_TEXT] ?? [] as $t) {
            $lines = $t->canvas_config['lines'] ?? [];
            if ($lines) {
                $pairs[$t->canvas_config['category'] ?? 'More'][] = $lines;
            }
        }
        $sets = fn($kind) => collect($rows[$kind] ?? [])
            ->filter(fn($t) => !empty($t->canvas_config['items']))
            ->mapWithKeys(fn($t) => [$t->name => array_values($t->canvas_config['items'])])
            ->all();

        // SVG artwork grouped for the Stickers panel: {group: [{name, src (absolute), x, y, w, h}]}
        $assetBase = asset('assets/templates') . '/';
        $art = [];
        foreach ($rows[Template::KIND_SVG] ?? [] as $t) {
            $c = $t->canvas_config ?? [];
            if (empty($c['src'])) {
                continue;
            }
            $art[$c['group'] ?? 'Artwork'][] = [
                'name' => $t->name,
                'src'  => preg_match('#^(https?:|data:|/)#', $c['src']) ? $c['src'] : $assetBase . $c['src'],
                'x' => $c['x'] ?? 110, 'y' => $c['y'] ?? 180, 'w' => $c['w'] ?? 140, 'h' => $c['h'] ?? 140,
            ];
        }

        return ['pairs' => $pairs, 'stickers' => $sets(Template::KIND_STICKER), 'bgColors' => $sets(Template::KIND_BG), 'art' => $art];
    }

    /**
     * Editor template library as JSON, in the same shape as the old static
     * public/assets/templates/templates.json. Asset paths are made absolute.
     */
    public function templates()
    {
        $assetBase = asset('assets/templates') . '/';
        $absolute = fn($src) => preg_match('#^(https?:|data:|/)#', $src) ? $src : $assetBase . ltrim($src, '/');

        $templates = Template::card()->active()->orderBy('sort_order')->orderBy('name')->get()
            ->map(function (Template $t) use ($absolute) {
                $c = $t->canvas_config ?? [];
                $pages = $c['pages'] ?? [];
                foreach ($pages as $pid => $page) {
                    foreach (($page['els'] ?? []) as $i => $el) {
                        if (!empty($el['src'])) {
                            $pages[$pid]['els'][$i]['src'] = $absolute($el['src']);
                        }
                    }
                }
                return [
                    'id'       => $t->slug,
                    'name'     => $t->name,
                    'category' => $c['category'] ?? 'other',
                    'categoryName' => $c['category_name'] ?? null,
                    'style'    => $c['style'] ?? '',
                    'tags'     => $c['tags'] ?? [],
                    'featured' => (bool) ($c['featured'] ?? false),
                    'popular'  => (int) ($c['popular'] ?? 0),
                    'pages'    => $pages,
                ];
            })
            ->filter(fn($t) => !empty($t['pages']['front']))
            ->values();

        $categories = $templates->groupBy('category')
            ->map(fn($items, $id) => [
                'id'    => $id,
                'name'  => $items->first()['categoryName'] ?: ucwords(str_replace('-', ' ', $id)),
                'count' => $items->count(),
            ])
            ->values();

        return response()->json([
            'version'    => 1,
            'page'       => ['width' => 360, 'height' => 504],
            'categories' => $categories,
            'templates'  => $templates->map(fn($t) => collect($t)->except('categoryName')),
        ])->header('Cache-Control', 'no-cache');
    }

    /** Editor page id -> customizer slot, per card type (same order as the old local-print customizer). */
    public const SLOTS = [
        'single' => ['front' => 'frame_image'],
        'double' => ['front' => 'frame_image', 'back' => 'sample_image'],
        'folded' => ['front' => 'frame_image', 'inL' => 'sample_image', 'inR' => 'background_image', 'back' => 'overlay_image'],
    ];

    /**
     * Review dialog -> Print on 931BL / Export PDF / Pickup from store.
     * Puts the card size in quick_flow_data (what localprint.preview and cart.add read) the way
     * LocalPrintController::customizer does, and returns the product the design is attached to.
     */
    public function prepareCheckout(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:single,double,folded',
            'width'       => 'required|numeric|min:1|max:30',
            'height'      => 'required|numeric|min:1|max:30',
            'orientation' => 'required|in:portrait,landscape',
        ]);

        $w = (float) $data['width'];
        $h = (float) $data['height'];
        $fmt = fn($v) => rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
        $title = ['single' => 'Flat', 'double' => 'Flat, double-sided', 'folded' => 'Folded'][$data['type']];

        session(['quick_flow_data' => [
            'type_name'     => 'Card Editor',
            'type_slug'     => 'card-editor',
            'category_name' => 'Card Editor',
            'category_slug' => 'card-editor',
            'local_print'   => true,
            'orientation'   => $data['orientation'],
            'size_width'    => $w,
            'size_height'   => $h,
            'size_unit'     => 'in',
            'size_name'     => $fmt($w) . ' × ' . $fmt($h) . ' in',
            'size_title'    => $title . ' card',
            'size_price'    => 0,
        ]]);

        // Same product the local-print customizer uses (the design itself comes from the uploads).
        $product = Product::where('is_active', 1)->first();
        if (!$product) {
            return response()->json(['message' => 'No active product to attach the design to.'], 422);
        }

        return response()->json([
            'product_id' => $product->id,
            'slots'      => self::SLOTS[$data['type']],
        ]);
    }

    /**
     * Review dialog -> Save as product template (admins only, route is behind auth + admin).
     * Saves the rendered pages as product images and opens admin/products/create pre-filled.
     */
    public function productDraft(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:single,double,folded',
            'orientation' => 'required|in:portrait,landscape',
            'name'        => 'nullable|string|max:255',
            'images'      => 'required|array',
            'images.*'    => 'required|string',
        ]);

        $dir = 'products/drafts/' . date('Y/m');
        $images = [];
        foreach (self::SLOTS[$data['type']] as $slot) {
            $uri = $data['images'][$slot] ?? null;
            if (!$uri || !preg_match('#^data:image/(png|jpe?g);base64,#', $uri, $m)) {
                continue;
            }
            $bytes = base64_decode(substr($uri, strpos($uri, ',') + 1), true);
            if ($bytes === false || @getimagesizefromstring($bytes) === false) {
                continue;
            }
            $path = $dir . '/' . \Illuminate\Support\Str::random(32) . '.' . ($m[1] === 'png' ? 'png' : 'jpg');
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $bytes);
            $images[$slot] = $path;
        }
        if (!$images) {
            return response()->json(['message' => 'The card pages could not be saved.'], 422);
        }

        session(['card_editor_product_draft' => [
            'name'            => $data['name'] ?? null,
            'no_of_pages'     => ['single' => 1, 'double' => 2, 'folded' => 4][$data['type']],
            'pdf_orientation' => $data['orientation'],
            'images'          => $images,
        ]]);

        return response()->json(['redirect' => route('admin.products.create', ['from_editor' => 1])]);
    }
}
