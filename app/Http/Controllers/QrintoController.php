<?php

namespace App\Http\Controllers;

use App\Models\CustomerUpload;
use App\Models\QrintoSize;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrintoController extends Controller
{
    /**
     * Show the qrinto page.
     */
    public function index()
    {
        $sizes = QrintoSize::active()->get()
            ->map(fn($s) => $s->toFrontendArray())
            ->values();

        return view('products.qrinto', compact('sizes'));
    }

    /**
     * Handle the design file upload.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max
        ]);

        $file = $request->file('image');
        $path = $file->store('qrintos/' . date('Y/m'), 'public');

        // Get image dimensions
        $imageInfo = getimagesize($file->getRealPath());
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        $upload = CustomerUpload::create([
            'user_id'       => auth()->id(),
            'session_id'    => session()->getId(),
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
        ]);

        return response()->json([
            'success' => true,
            'upload'  => [
                'id'            => $upload->id,
                'url'           => asset('storage/' . $upload->file_path),
                'original_name' => $upload->original_name,
                'width'         => $upload->width,
                'height'        => $upload->height,
            ],
        ]);
    }

    /**
     * Add the qrinto to the cart.
     */
    public function addToCart(Request $request, CartService $cartService)
    {
        // Get valid size keys from DB
        $validKeys = QrintoSize::active()->pluck('key')->toArray();
        $validKeysStr = implode(',', $validKeys);

        $request->validate([
            'upload_id' => 'required|integer|exists:customer_uploads,id',
            'size'      => "required|string|in:$validKeysStr",
            'quantity'  => 'required|integer|min:1|max:10',
        ]);

        $upload = CustomerUpload::findOrFail($request->upload_id);
        $sizeKey = $request->size;

        // Get size config from DB
        $sizeRecord = QrintoSize::where('key', $sizeKey)->where('is_active', true)->firstOrFail();
        $unitPrice = (float) $sizeRecord->price;
        $quantity = $request->quantity;

        // Store as customization data for order processing
        $customizationData = [
            'type'             => 'qrinto',
            'upload_id'        => $upload->id,
            'upload_path'      => $upload->file_path,
            'upload_url'       => asset('storage/' . $upload->file_path),
            'original_filename'=> $upload->original_name,
            'size_key'         => $sizeKey,
            'size_label'       => $sizeRecord->label,
            'size_dimensions'  => $sizeRecord->dimensions,
        ];

        // Use null product_id for qrinto items (column is nullable).
        $cartItem = $cartService->addItem(
            productId: null,
            quantity: $quantity,
            unitPrice: $unitPrice,
            customizationData: $customizationData,
            selectedOptions: ['size' => $sizeKey],
        );

        $cart = $cartService->getCart();

        return response()->json([
            'success'    => true,
            'message'    => 'Qrinto added to cart!',
            'cart_count'  => $cart->item_count,
        ]);
    }
}
