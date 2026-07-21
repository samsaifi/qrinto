<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'productType', 'images');

        // Store Isolation: store admins see only their own products
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $query->where('store_id', auth()->user()->store_id);
            $query->orWhere('product_store', auth()->user()->store_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {   
        $no_of_pages_array = (new Product())->no_of_pages_array;
         
        $categories = Category::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->where('is_active',1)->get();
        $allTags = Product::pluck('tags')->flatten()->filter()->unique()->values()->all();
        return view('admin.products.form', compact('categories', 'productTypes', 'allTags','no_of_pages_array'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'no_of_pages' => 'nullable|integer|in:' . implode(',', array_keys((new Product())->no_of_pages_array)),
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'min_images' => 'nullable|integer|min:1',
            'max_images' => 'nullable|integer|min:1',
            'allowed_print_types' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'frame_image' => 'nullable|image|max:5120',
            'sample_image' => 'nullable|image|max:5120',
            'background_image' => 'nullable|image|max:5120',
            'overlay_image' => 'nullable|image|max:5120',
            'tags' => 'nullable|array',
            'store_id' => 'nullable|integer',
            'tags.*' => 'string',
            'pdf_orientation' => 'nullable|string|in:portrait,landscape',
        ]);

        if ($request->hasFile('frame_image')) {
            $validated['frame_image'] = $request->file('frame_image')
                ->store('products/frames', 'public');
        }

        if ($request->hasFile('sample_image')) {
            $validated['sample_image'] = $request->file('sample_image')
                ->store('products/samples', 'public');
        }else{
            $validated['sample_image'] =  "products/samples/WMtw3jx2aDVGYw33jcNr0pNKWggk49tYaTtB7o6K.jpg";
        }

        if ($request->hasFile('background_image')) {
            $validated['background_image'] = $request->file('background_image')
                ->store('products/backgrounds', 'public');
        }else{
            $validated['background_image'] =  "products/samples/WMtw3jx2aDVGYw33jcNr0pNKWggk49tYaTtB7o6K.jpg";
        }

        if ($request->hasFile('overlay_image')) {
            $validated['overlay_image'] = $request->file('overlay_image')
                ->store('products/overlays', 'public');
        }else{
            $validated['overlay_image'] =  "products/overlays/RhiG0tY76CYU50upFbP9sBi6TGmhuRZEPNv2dsws.jpg";
        }

        $validated['product_store'] = $request->store_id;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        // Auto-assign store_id for store admins
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $validated['store_id'] = auth()->user()->store_id;
        }

        $product = Product::create($validated);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Product created successfully!');
    }
    public function authrised_check($product)
    {
        if (
            auth()->user()->isStoreAdmin() &&
            $product->store_id != auth()->user()->store_id &&
            $product->product_store != auth()->user()->store_id
        ) {
            abort(403, 'Unauthorized access to this product.');
        }
    }
    public function edit(Product $product)
    {   
        // Store Isolation: store admins can only edit their own products
        $this->authrised_check($product);

        $no_of_pages_array = (new Product())->no_of_pages_array; 
        $product->load('images', 'optionGroups.values', 'category', 'productType');
        $categories = Category::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->where('is_active',1)->get();
        $allTags = Product::pluck('tags')->flatten()->filter()->unique()->values()->all();

        return view('admin.products.form', compact('product', 'categories', 'productTypes', 'allTags', 'no_of_pages_array'));
    }

    public function update(Request $request, Product $product)
    {
        // Store Isolation: store admins can only update their own products
        $this->authrised_check($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'no_of_pages' => 'nullable|integer|in:' . implode(',', array_keys((new Product())->no_of_pages_array)),
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'min_images' => 'nullable|integer|min:1',
            'max_images' => 'nullable|integer|min:1',
            'allowed_print_types' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'frame_image' => 'nullable|image|max:5120',
            'sample_image' => 'nullable|image|max:5120',
            'background_image' => 'nullable|image|max:5120',
            'overlay_image' => 'nullable|image|max:5120',
            'tags' => 'nullable|array',
            'store_id' => 'nullable|integer',
            'tags.*' => 'string',
            'pdf_orientation' => 'nullable|string|in:portrait,landscape',
        ]);

        if ($request->hasFile('frame_image')) {
            if ($product->frame_image) {
                Storage::disk('public')->delete($product->frame_image);
            }
            $validated['frame_image'] = $request->file('frame_image')
                ->store('products/frames', 'public');
        }

        if ($request->hasFile('sample_image')) {
            if ($product->sample_image) {
                Storage::disk('public')->delete($product->sample_image);
            }
            $validated['sample_image'] = $request->file('sample_image')
                ->store('products/samples', 'public');
        }

        if ($request->hasFile('background_image')) {
            if ($product->background_image) {
                Storage::disk('public')->delete($product->background_image);
            }
            $validated['background_image'] = $request->file('background_image')
                ->store('products/backgrounds', 'public');
        }

        if ($request->hasFile('overlay_image')) {
            if ($product->overlay_image) {
                Storage::disk('public')->delete($product->overlay_image);
            }
            $validated['overlay_image'] = $request->file('overlay_image')
                ->store('products/overlays', 'public');
        } 
        
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        $product->update($validated);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Store Isolation: store admins can only delete their own products
        $this->authrised_check($product);

        // Soft delete only – product becomes hidden from listings
        // Images are preserved so a developer can manually restore if needed
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $query = Product::whereIn('id', $request->ids);

        // Store Isolation: store admins can only bulk delete their own products
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $query->where('store_id', auth()->user()->store_id);
        }

        $query->each(function ($product) {
            $product->delete(); // soft delete
        });

        return redirect()->route('admin.products.index')
            ->with('success', count($request->ids) . ' product(s) deleted.');
    }

    // Option Groups Management
    public function storeOptionGroup(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'display_type' => 'required|in:buttons,cards,dropdown',
            'is_required' => 'nullable|boolean',
        ]);

        $product->optionGroups()->create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'display_type' => $request->display_type,
            'is_required' => $request->boolean('is_required', true),
            'sort_order' => $product->optionGroups()->count(),
        ]);

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Option group added!');
    }

    public function storeOptionValue(Request $request, ProductOptionGroup $optionGroup)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'price_modifier' => 'nullable|numeric',
        ]);

        $optionGroup->values()->create([
            'label' => $request->label,
            'value' => \Str::slug($request->label),
            'price_modifier' => $request->price_modifier ?? 0,
            'is_active' => true,
            'sort_order' => $optionGroup->values()->count(),
        ]);

        return redirect()->route('admin.products.edit', $optionGroup->product)
            ->with('success', 'Option value added!');
    }

    public function deleteOptionGroup(ProductOptionGroup $optionGroup)
    {
        $product = $optionGroup->product;
        $optionGroup->delete();

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Option group removed.');
    }

    public function deleteOptionValue(ProductOptionValue $optionValue)
    {
        $product = $optionValue->optionGroup->product;
        $optionValue->delete();

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Option value removed.');
    }

    public function deleteImage($imageId)
    {
        $image = \App\Models\ProductImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $product = $image->product;
        $image->delete();

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Image removed.');
    }

    // Mask Editor
    public function maskEditor(Product $product)
    {
        return view('admin.products.mask', compact('product'));
    }

    public function saveMask(Request $request, Product $product)
    {
        $request->validate([
            'mask_data' => 'required|json',
        ]);

        $maskData = json_decode($request->mask_data, true);

        // Support both legacy (flat) and new (per-image keyed) formats
        $product->update([
            'mask_data' => $maskData,
        ]);

        return redirect()->route('admin.products.mask', $product)
            ->with('success', 'All mask data saved successfully!');
    }
}
