<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'selected_options' => 'nullable|array',
            'customization_data' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $selectedOptions = $request->input('selected_options', []);
        $unitPrice = $product->calculatePrice(array_values($selectedOptions));

        $this->cartService->addItem(
            $request->product_id,
            $request->quantity,
            $unitPrice,
            $request->customization_data,
            $selectedOptions,
        );

        if ($request->expectsJson()) {
            $cart = $this->cartService->getCart();
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart!',
                'cart_count' => $cart->item_count,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
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

        return redirect()->route('cart.index')->with('success', 'Cart updated!');
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

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $result = $this->cartService->applyCoupon($request->code);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route('cart.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function removeCoupon()
    {
        $this->cartService->removeCoupon();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cart.index')->with('success', 'Coupon removed.');
    }
}
