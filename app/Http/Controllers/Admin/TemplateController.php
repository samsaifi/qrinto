<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $kind = in_array($request->query('kind'), Template::CARD_EDITOR_KINDS, true)
            ? $request->query('kind') : Template::KIND_PHOTO;
        $templates = Template::where('kind', $kind)
            ->when($kind === Template::KIND_LAYOUT, fn($q) => $q->orderByRaw("FIELD(JSON_UNQUOTE(JSON_EXTRACT(canvas_config, '$.view')), 'front', 'inside', 'back')"))
            ->when($kind !== Template::KIND_PHOTO, fn($q) => $q->orderBy('sort_order'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $counts = Template::selectRaw('kind, count(*) as n')->groupBy('kind')->pluck('n', 'kind');
        return view('admin.templates.index', compact('templates', 'kind', 'counts'));
    }

    public function create(Request $request)
    {
        if (in_array($request->query('kind'), Template::CARD_EDITOR_KINDS, true)) {
            return view($this->cardEditorView($request->query('kind')), ['kind' => $request->query('kind')]);
        }
        $categories = Category::orderBy('name')->get();
        return view('admin.templates.form', compact('categories'));
    }

    public function store(Request $request)
    {
        if (in_array($request->input('kind'), [Template::KIND_TEXT, Template::KIND_STICKER, Template::KIND_BG, Template::KIND_SVG], true)) {
            $kind = $request->input('kind');
            $prefix = [Template::KIND_TEXT => 'text', Template::KIND_STICKER => 'emoji', Template::KIND_BG => 'bg', Template::KIND_SVG => 'svg'][$kind];
            Template::create($this->validatedLibrary($request, $kind)
                + ['slug' => $this->uniqueSlug($prefix . '-' . Str::slug($request->name)), 'kind' => $kind]);
            return redirect()->route($this->indexRoute(), ['kind' => $kind])->with('success', 'Saved.');
        }
        if ($request->input('kind') === Template::KIND_LAYOUT) {
            $data = $this->validatedLayout($request);
            Template::create($data + ['slug' => $this->uniqueSlug('layout-' . Str::slug($request->name)), 'kind' => Template::KIND_LAYOUT]);
            return redirect()->route($this->indexRoute(), ['kind' => 'layout'])->with('success', 'Layout created.');
        }
        if ($request->input('kind') === Template::KIND_CARD) {
            $data = $this->validatedCard($request);
            Template::create($data + ['slug' => $this->uniqueSlug(Str::slug($request->name)), 'kind' => Template::KIND_CARD]);
            return redirect()->route($this->indexRoute(), ['kind' => 'card'])->with('success', 'Card template created.');
        }
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'icon_type'     => 'required|in:lucide,upload',
            'icon_lucide'   => 'nullable|string|max:100',
            'icon_file'     => 'nullable|file|mimes:svg|max:1024',
            'canvas_config' => 'required|json',
            'is_active'     => 'nullable|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $slug = $this->uniqueSlug(Str::slug($request->name));

        $iconValue = $this->resolveIcon($request, null);

        Template::create([
            'name'          => $request->name,
            'slug'          => $slug,
            'category_id'   => $request->input('category_id') ?: null,
            'icon_type'     => $request->icon_type,
            'icon_value'    => $iconValue,
            'canvas_config' => json_decode($request->canvas_config, true),
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ]);

        $route = request()->is('store*') ? 'storepanel_cat.templates.index' : 'admin.templates.index';
        return redirect()->route($route)
            ->with('success', 'Template created successfully!');
    }

    public function edit(Template $template)
    {
        if (in_array($template->kind, Template::CARD_EDITOR_KINDS, true)) {
            return view($this->cardEditorView($template->kind), ['template' => $template, 'kind' => $template->kind]);
        }
        $categories = Category::orderBy('name')->get();
        return view('admin.templates.form', compact('template', 'categories'));
    }

    public function update(Request $request, Template $template)
    {
        if (in_array($template->kind, [Template::KIND_TEXT, Template::KIND_STICKER, Template::KIND_BG, Template::KIND_SVG], true)) {
            $template->update($this->validatedLibrary($request, $template->kind));
            return redirect()->route($this->indexRoute(), ['kind' => $template->kind])->with('success', 'Saved.');
        }
        if ($template->kind === Template::KIND_LAYOUT) {
            $template->update($this->validatedLayout($request));
            return redirect()->route($this->indexRoute(), ['kind' => 'layout'])->with('success', 'Layout updated.');
        }
        if ($template->kind === Template::KIND_CARD) {
            $template->update($this->validatedCard($request));
            return redirect()->route($this->indexRoute(), ['kind' => 'card'])->with('success', 'Card template updated.');
        }
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'icon_type'     => 'required|in:lucide,upload',
            'icon_lucide'   => 'nullable|string|max:100',
            'icon_file'     => 'nullable|file|mimes:svg|max:1024',
            'canvas_config' => 'required|json',
            'is_active'     => 'nullable|boolean',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $iconValue = $this->resolveIcon($request, $template);

        $template->update([
            'name'          => $request->name,
            'category_id'   => $request->input('category_id') ?: null,
            'icon_type'     => $request->icon_type,
            'icon_value'    => $iconValue,
            'canvas_config' => json_decode($request->canvas_config, true),
            'is_active'     => $request->boolean('is_active', false),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ]);

        $route = request()->is('store*') ? 'storepanel_cat.templates.index' : 'admin.templates.index';
        return redirect()->route($route)
            ->with('success', 'Template updated successfully!');
    }

    public function destroy(Template $template)
    {
        if ($template->icon_type === 'upload' && $template->icon_value) {
            Storage::disk('public')->delete($template->icon_value);
        }
        $template->delete();

        return redirect()->route($this->indexRoute(), $template->kind !== Template::KIND_PHOTO ? ['kind' => $template->kind] : [])
            ->with('success', 'Template deleted.');
    }

    public function toggle(Template $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Template status updated.');
    }

    /**
     * AJAX: upload an image or SVG asset for use in the builder canvas.
     */
    public function uploadAsset(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120|mimes:jpeg,png,jpg,webp,svg',
            'type' => 'required|in:image,svg',
        ]);

        $folder = $request->type === 'svg' ? 'templates/svgs' : 'templates/images';
        $path   = $request->file('file')->store($folder, 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Open a card template in the full card editor (admin mode: loads the template, saves back to it). */
    public function design(Template $template)
    {
        abort_unless($template->kind === Template::KIND_CARD, 404);

        $assetBase = asset('assets/templates') . '/';
        $pages = $template->canvas_config['pages'] ?? [];
        foreach (['front', 'inL', 'inR', 'back'] as $pid) {
            $pages[$pid] = ($pages[$pid] ?? []) + ['bg' => '#ffffff', 'els' => []];
            foreach ($pages[$pid]['els'] as $i => $el) {
                if (!empty($el['src']) && !preg_match('#^(https?:|data:|/)#', $el['src'])) {
                    $pages[$pid]['els'][$i]['src'] = $assetBase . $el['src'];
                }
            }
        }

        return view('card-editor.index', [
            'product'     => null,
            'size'        => ['width' => 5.0, 'height' => 7.0, 'unit' => 'in'],
            'type'        => 'folded',
            'orientation' => 'portrait',
            'cardSize'    => null,
            'adminTemplate' => [
                'id'        => $template->id,
                'name'      => $template->name,
                'pages'     => $pages,
                'assetBase' => $assetBase,
                'saveUrl'   => route('admin.templates.design.save', $template),
                'backUrl'   => route('admin.templates.edit', $template),
            ],
        ]);
    }

    /** JSON save from the editor: {name, pages}. Asset URLs are stored relative to assets/templates/. */
    public function saveDesign(Request $request, Template $template)
    {
        abort_unless($template->kind === Template::KIND_CARD, 404);
        $data = $request->validate([
            'name'  => 'nullable|string|max:255',
            'pages' => 'required|array',
            'pages.front.els' => 'present|array',
            'pages.*'         => 'array',
            'pages.*.bg'      => 'nullable|string|max:64',
            'pages.*.els'     => 'sometimes|array',
            'pages.*.els.*'   => 'array',
            'pages.*.els.*.src' => 'nullable|string',
        ]);

        $assetBase = asset('assets/templates') . '/';
        $pages = [];
        foreach (['front', 'inL', 'inR', 'back'] as $pid) {
            // validate() only returns the keys named in the rules, so read the full pages from the request.
            $page = $request->input("pages.$pid") ?? ['bg' => '#ffffff', 'els' => []];
            $page['els'] = array_values(array_map(function ($el) use ($assetBase) {
                if (!empty($el['src']) && str_starts_with($el['src'], $assetBase)) {
                    $el['src'] = substr($el['src'], strlen($assetBase));
                }
                return $el;
            }, $page['els'] ?? []));
            $pages[$pid] = ['bg' => $page['bg'] ?? '#ffffff', 'els' => $page['els']];
        }

        $config = $template->canvas_config ?? [];
        $config['pages'] = $pages;
        $template->update(array_filter([
            'name' => trim((string) ($data['name'] ?? '')) ?: null,
        ]) + ['canvas_config' => $config]);

        return response()->json(['ok' => true]);
    }

    private function indexRoute(): string
    {
        return request()->is('store*') ? 'storepanel_cat.templates.index' : 'admin.templates.index';
    }

    private function cardEditorView(string $kind): string
    {
        return match ($kind) {
            Template::KIND_TEXT => 'admin.templates.text-form',
            Template::KIND_SVG => 'admin.templates.svg-form',
            Template::KIND_STICKER, Template::KIND_BG => 'admin.templates.library-form',
            default => 'admin.templates.card-form',
        };
    }

    /** Text style (lines) / emoji set / colour palette fields -> model attributes. */
    private function validatedLibrary(Request $request, string $kind): array
    {
        if ($kind === Template::KIND_SVG) {
            $request->validate([
                'name'       => 'required|string|max:255',
                'group'      => 'required|string|max:60',
                'src'        => 'required|string|max:500',
                'x'          => 'required|numeric', 'y' => 'required|numeric',
                'w'          => 'required|numeric|min:5', 'h' => 'required|numeric|min:5',
                'sort_order' => 'nullable|integer|min:0',
            ]);
            // Library files are stored relative to assets/templates/, uploads as their storage URL.
            $base = asset('assets/templates') . '/';
            $src = trim($request->src);
            if (str_starts_with($src, $base)) {
                $src = substr($src, strlen($base));
            }
            return [
                'name'          => $request->name,
                'icon_type'     => 'lucide',
                'icon_value'    => 'shapes',
                'canvas_config' => [
                    'group' => trim($request->group),
                    'src'   => $src,
                    'x'     => (int) round($request->x), 'y' => (int) round($request->y),
                    'w'     => (int) round($request->w), 'h' => (int) round($request->h),
                ],
                'is_active'     => $request->boolean('is_active'),
                'sort_order'    => (int) $request->input('sort_order', 0),
            ];
        }
        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'category'   => $kind === Template::KIND_TEXT ? 'required|string|max:60' : 'nullable',
            'lines'      => $kind === Template::KIND_TEXT ? 'required|json' : 'nullable',
            'items'      => $kind === Template::KIND_TEXT ? 'nullable' : 'required|string',
        ]);

        if ($kind === Template::KIND_TEXT) {
            $allowed = ['text', 'font', 'size', 'color', 'align', 'bold', 'italic', 'lh', 'ls', 'x', 'y', 'w'];
            $lines = collect(json_decode($request->lines, true) ?: [])
                ->filter(fn($l) => is_array($l) && trim((string) ($l['text'] ?? '')) !== '')
                ->map(fn($l) => array_intersect_key($l, array_flip($allowed)))
                ->values()->all();
            if (!$lines) {
                throw \Illuminate\Validation\ValidationException::withMessages(['lines' => 'Add at least one line of text.']);
            }
            $config = ['category' => trim($request->category), 'lines' => $lines];
        } else {
            // One emoji / colour per line, or separated by spaces or commas.
            $items = collect(preg_split('/[\s,]+/u', (string) $request->items, -1, PREG_SPLIT_NO_EMPTY))
                ->map(fn($v) => trim($v))
                ->when($kind === Template::KIND_BG, fn($c) => $c
                    ->map(fn($v) => strtolower(str_starts_with($v, '#') ? $v : '#' . $v))
                    ->filter(fn($v) => preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $v)))
                ->unique()->values()->all();
            if (!$items) {
                throw \Illuminate\Validation\ValidationException::withMessages(['items' => 'Add at least one item.']);
            }
            $config = ['items' => $items];
        }

        return [
            'name'          => $request->name,
            'icon_type'     => 'lucide',
            'icon_value'    => [Template::KIND_TEXT => 'type', Template::KIND_STICKER => 'smile', Template::KIND_BG => 'palette'][$kind],
            'canvas_config' => $config,
            'is_active'     => $request->boolean('is_active'),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];
    }

    /** Layout fields -> model attributes. pages = {pageId: [els]} for the pages of the chosen view. */
    private function validatedLayout(Request $request): array
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'view'       => 'required|in:front,inside,back',
            'sort_order' => 'nullable|integer|min:0',
            'pages'      => 'required|json',
        ]);

        $view = $request->input('view');
        $pids = ['front' => ['front'], 'inside' => ['inL', 'inR'], 'back' => ['back']][$view];
        $raw = json_decode($request->pages, true);
        $pages = [];
        foreach ($pids as $pid) {
            $els = $raw[$pid]['els'] ?? $raw[$pid] ?? [];
            $pages[$pid] = is_array($els) ? array_values($els) : [];
        }
        // Optional page background per page; omitted = keep the customer's background.
        $bg = collect(json_decode((string) $request->input('layout_bg'), true) ?: [])
            ->only($pids)
            ->filter(fn($c) => is_string($c) && preg_match('/^#[0-9a-f]{3,8}$/i', $c))
            ->all();

        return [
            'name'          => $request->name,
            'icon_type'     => 'lucide',
            'icon_value'    => 'layout-dashboard',
            'canvas_config' => array_filter(['view' => $view, 'pages' => $pages, 'bg' => $bg ?: null]),
            'is_active'     => $request->boolean('is_active'),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];
    }

    /** Card editor template fields -> model attributes (pages JSON is the editor's front/inL/inR/back). */
    private function validatedCard(Request $request): array
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:60',
            'category_name' => 'nullable|string|max:100',
            'style'         => 'nullable|string|max:150',
            'tags'          => 'nullable|string|max:500',
            'popular'       => 'nullable|integer|min:0|max:1000',
            'sort_order'    => 'nullable|integer|min:0',
            'pages'         => 'required|json',
        ]);

        $pages = json_decode($request->pages, true);
        if (!is_array($pages) || !isset($pages['front']['els']) || !is_array($pages['front']['els'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'pages' => 'Pages must be an object with at least "front": {"bg": "#fff", "els": [...]}.',
            ]);
        }

        $category = Str::slug($request->category);
        return [
            'name'          => $request->name,
            'icon_type'     => 'lucide',
            'icon_value'    => 'layout-template',
            'canvas_config' => [
                'category'      => $category,
                'category_name' => $request->input('category_name') ?: Str::title(str_replace('-', ' ', $category)),
                'style'         => (string) $request->input('style', ''),
                'tags'          => array_values(array_filter(array_map('trim', explode(',', (string) $request->input('tags', ''))))),
                'featured'      => $request->boolean('featured'),
                'popular'       => (int) $request->input('popular', 0),
                'pages'         => $pages,
            ],
            'is_active'     => $request->boolean('is_active'),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ];
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'template';
        $i    = 1;
        while (Template::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function resolveIcon(Request $request, ?Template $existing): ?string
    {
        if ($request->icon_type === 'lucide') {
            // Delete old upload if switching from upload to lucide
            if ($existing && $existing->icon_type === 'upload' && $existing->icon_value) {
                Storage::disk('public')->delete($existing->icon_value);
            }
            return $request->input('icon_lucide') ?: 'layout-template';
        }

        // icon_type === 'upload'
        if ($request->hasFile('icon_file')) {
            if ($existing && $existing->icon_type === 'upload' && $existing->icon_value) {
                Storage::disk('public')->delete($existing->icon_value);
            }
            return $request->file('icon_file')->store('templates/icons', 'public');
        }

        // Keep existing value if no new file uploaded
        return $existing ? $existing->icon_value : null;
    }
}
