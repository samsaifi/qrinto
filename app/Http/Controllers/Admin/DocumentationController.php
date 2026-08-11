<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentationGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    public function __construct()
    {
        // Ensure database table & default content are seeded seamlessly
        DocumentationGuide::ensureTableAndSeeded();
        \App\Services\DocumentationSeederService::seedDefaultGuides();
    }

    /**
     * Documentation Home Page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role ?? 'staff';

        $query = DocumentationGuide::where('is_published', true)->orderBy('sort_order', 'asc');

        // Handle Search
        $search = trim($request->input('search', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $allGuides = $query->get()->filter(fn($guide) => $guide->isVisibleToRole($role));

        // Group by category
        $categoryMap = [
            'getting_started' => [
                'title' => 'Getting Started',
                'description' => 'Admin overview, dashboard guide, navigation, and user role basics.',
                'icon' => 'compass',
                'color' => 'brand',
            ],
            'customer_experience' => [
                'title' => 'Customer Experience & Custom Ordering',
                'description' => 'Detailed step-by-step layman guides on how mobile and desktop customers custom edit images and place custom orders.',
                'icon' => 'smartphone',
                'color' => 'pink',
            ],
            'orders' => [
                'title' => 'Orders',
                'description' => 'Viewing, searching, processing orders, multi-item designs, and PDF printing.',
                'icon' => 'package',
                'color' => 'emerald',
            ],
            'catalog' => [
                'title' => 'Catalog',
                'description' => 'Managing products, categories, card types, templates, coupons, events, and paper types.',
                'icon' => 'box',
                'color' => 'amber',
            ],
            'stores' => [
                'title' => 'Stores',
                'description' => 'Store location management, printer configurations, FTP settings, and staff access.',
                'icon' => 'store',
                'color' => 'purple',
            ],
            'users' => [
                'title' => 'Users',
                'description' => 'Managing administrative users, assigning roles, and store location permissions.',
                'icon' => 'users',
                'color' => 'blue',
            ],
        ];

        $groupedCategories = [];
        foreach ($categoryMap as $key => $meta) {
            $guides = $allGuides->where('category', $key)->values();
            if ($guides->count() > 0 || empty($search)) {
                $groupedCategories[$key] = array_merge($meta, [
                    'key' => $key,
                    'guides' => $guides,
                ]);
            }
        }

        // Popular Guides
        $popularGuides = $allGuides->sortByDesc('views_count')->take(5);

        return view('admin.docs.index', compact('groupedCategories', 'popularGuides', 'search', 'role'));
    }

    /**
     * Display a specific documentation guide
     */
    public function show($slug)
    {
        $user = auth()->user();
        $role = $user->role ?? 'staff';

        $guide = DocumentationGuide::where('slug', $slug)->firstOrFail();

        if (!$guide->isVisibleToRole($role)) {
            abort(403, 'You do not have permission to view this documentation guide.');
        }

        // Increment view count
        $guide->increment('views_count');

        // Fetch category guides accessible to user
        $categoryGuides = DocumentationGuide::where('category', $guide->category)
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->filter(fn($g) => $g->isVisibleToRole($role))
            ->values();

        $currentIndex = $categoryGuides->search(fn($g) => $g->id === $guide->id);

        $previousGuide = ($currentIndex !== false && $currentIndex > 0) ? $categoryGuides[$currentIndex - 1] : null;
        $nextGuide = ($currentIndex !== false && $currentIndex < $categoryGuides->count() - 1) ? $categoryGuides[$currentIndex + 1] : null;

        // Related guides in same category excluding current
        $relatedGuides = $categoryGuides->filter(fn($g) => $g->id !== $guide->id)->take(3);

        return view('admin.docs.show', compact('guide', 'categoryGuides', 'previousGuide', 'nextGuide', 'relatedGuides', 'role'));
    }

    /**
     * Documentation CMS Management List (Super Admin only)
     */
    public function manage(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to documentation management.');
        }

        $query = DocumentationGuide::orderBy('sort_order', 'asc')->orderBy('category', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $guides = $query->paginate(20)->withQueryString();

        return view('admin.docs.manage', compact('guides'));
    }

    /**
     * Create Guide Form
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $guide = new DocumentationGuide();
        return view('admin.docs.form', compact('guide'));
    }

    /**
     * Store new Guide
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentation_guides,slug',
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'icon' => 'nullable|string',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'visible_roles' => 'nullable|array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_published'] = $request->has('is_published');
        $validated['visible_roles'] = $request->input('visible_roles', ['admin', 'store_admin', 'staff']);

        DocumentationGuide::create($validated);

        return redirect()->route('admin.docs.manage')->with('success', 'Documentation guide created successfully.');
    }

    /**
     * Edit Guide Form
     */
    public function edit($slug)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $guide = DocumentationGuide::where('slug', $slug)->firstOrFail();
        return view('admin.docs.form', compact('guide'));
    }

    /**
     * Update Guide
     */
    public function update(Request $request, $slug)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $guide = DocumentationGuide::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:documentation_guides,slug,' . $guide->id,
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'icon' => 'nullable|string',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'visible_roles' => 'nullable|array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['visible_roles'] = $request->input('visible_roles', ['admin', 'store_admin', 'staff']);

        $guide->update($validated);

        return redirect()->route('admin.docs.manage')->with('success', 'Documentation guide updated successfully.');
    }

    /**
     * Delete Guide
     */
    public function destroy($slug)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $guide = DocumentationGuide::where('slug', $slug)->firstOrFail();
        $guide->delete();

        return redirect()->route('admin.docs.manage')->with('success', 'Documentation guide deleted successfully.');
    }

    /**
     * Toggle Publish Status
     */
    public function toggle($slug)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $guide = DocumentationGuide::where('slug', $slug)->firstOrFail();
        $guide->update(['is_published' => !$guide->is_published]);

        return redirect()->back()->with('success', 'Guide visibility status updated.');
    }
}
