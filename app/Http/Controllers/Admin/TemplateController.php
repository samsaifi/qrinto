<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
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
            'icon_type'     => $request->icon_type,
            'icon_value'    => $iconValue,
            'canvas_config' => $request->canvas_config,
            'is_active'     => $request->boolean('is_active', true),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully!');
    }

    public function edit(Template $template)
    {
        return view('admin.templates.form', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
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
            'icon_type'     => $request->icon_type,
            'icon_value'    => $iconValue,
            'canvas_config' => $request->canvas_config,
            'is_active'     => $request->boolean('is_active', false),
            'sort_order'    => (int) $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully!');
    }

    public function destroy(Template $template)
    {
        if ($template->icon_type === 'upload' && $template->icon_value) {
            Storage::disk('public')->delete($template->icon_value);
        }
        $template->delete();

        return redirect()->route('admin.templates.index')
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
