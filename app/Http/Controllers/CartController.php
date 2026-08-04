<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use App\Models\CustomerUpload;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    protected function getViewPath($viewName)
    {
        return "quick-flow.{$viewName}";
    }

    protected function getRoutePrefix()
    {
        return 'flow.';
    }

    public function index()
    {
        $cart = $this->cartService->getCart();

        $allUploadIds = [];
        foreach ($cart->items as $item) {
            $customization = $item->customization_data ?? [];
            if (!empty($customization['upload_ids'])) {
                foreach ($customization['upload_ids'] as $id) {
                    if ($id) $allUploadIds[] = $id;
                }
            }
        }
        $uploads = $allUploadIds
            ? CustomerUpload::whereIn('id', $allUploadIds)->get()->keyBy('id')
            : collect();

        $routePrefix = $this->getRoutePrefix();

        return view($this->getViewPath('cart'), compact('cart', 'uploads', 'routePrefix'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100',
            'upload_ids' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->input('quantity', 1);
        $uploadIds = $request->input('upload_ids')
            ? json_decode($request->input('upload_ids'), true)
            : [];

        $flowData = session('quick_flow_data', []);

        if ($product->store_id) {
            $unitPrice = (float) $product->base_price;
        } else {
            $unitPrice = isset($flowData['size_price'])
                ? (float) $flowData['size_price']
                : (float) $product->base_price;
        }

        $customizationData = [
            'upload_ids' => $uploadIds,
            'size_name' => $flowData['size_name'] ?? null,
            'size_width' => $flowData['size_width'] ?? null,
            'size_height' => $flowData['size_height'] ?? null,
            'size_unit' => $flowData['size_unit'] ?? null,
            'type_name' => $flowData['type_name'] ?? null,
        ];

        $selectedOptions = !empty($uploadIds)
            ? ['design_key' => md5(json_encode($uploadIds))]
            : null;

        $this->cartService->addItem(
            $product->id,
            $quantity,
            $unitPrice,
            $customizationData,
            $selectedOptions,
        );

        return redirect()->route($this->getRoutePrefix() . 'cart.index')->with('success', 'Design added to cart!');
    }

    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $this->cartService->updateQuantity($itemId, $request->quantity);

        if ($request->expectsJson()) {
            $cart = $this->cartService->getCart();
            $item = $cart->items()->find($itemId);
            return response()->json([
                'success' => true,
                'item_total' => $item ? $item->total : 0,
                'subtotal' => $cart->subtotal,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'cart_count' => $cart->item_count,
            ]);
        }

        return redirect()->route($this->getRoutePrefix() . 'cart.index');
    }

    public function remove(int $itemId)
    {
        $this->cartService->removeItem($itemId);

        if (request()->expectsJson()) {
            $cart = $this->cartService->getCart();
            return response()->json([
                'success' => true,
                'subtotal' => $cart->subtotal,
                'discount' => $cart->discount,
                'total' => $cart->total,
                'cart_count' => $cart->item_count,
            ]);
        }

        return redirect()->route($this->getRoutePrefix() . 'cart.index');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $result = $this->cartService->applyCoupon($request->code);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route($this->getRoutePrefix() . 'cart.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function removeCoupon()
    {
        $this->cartService->removeCoupon();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route($this->getRoutePrefix() . 'cart.index');
    }

    public function count()
    {
        $cart = $this->cartService->getCart();
        return response()->json(['count' => $cart->item_count]);
    }
}
