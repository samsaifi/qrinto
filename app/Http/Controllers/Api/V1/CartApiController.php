<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartApiController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get active cart
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        $items = $cart->items->map(function ($item) {
            $product = $item->product;
            return [
                'id'                 => $item->id,
                'product_id'         => $item->product_id,
                'product_name'       => $product ? $product->name : 'Custom Print',
                'featured_image_url' => $product ? ($product->featured_image_url ?? $product->sample_image_url ?? $product->frame_image_url) : null,
                'quantity'           => (int) $item->quantity,
                'unit_price'         => (float) $item->unit_price,
                'total_price'        => (float) $item->total_price,
                'formatted_total'    => CurrencyService::format($item->total_price),
                'customization_data' => $item->customization_data,
            ];
        });

        return response()->json([
            'success'            => true,
            'data'               => [
                'cart_id'            => $cart->id,
                'items_count'        => $items->sum('quantity'),
                'subtotal'           => (float) $cart->subtotal,
                'discount_amount'    => (float) $cart->discount_amount,
                'total'              => (float) $cart->total,
                'formatted_subtotal' => CurrencyService::format($cart->subtotal),
                'formatted_total'    => CurrencyService::format($cart->total),
                'coupon_code'        => $cart->coupon_code,
                'items'              => $items,
            ],
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'         => 'required|exists:products,id',
            'quantity'           => 'nullable|integer|min:1',
            'unit_price'         => 'nullable|numeric|min:0',
            'customization_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item = $this->cartService->addItem(
            $request->product_id,
            (int) $request->input('quantity', 1),
            $request->input('customization_data', []),
            $request->filled('unit_price') ? (float) $request->unit_price : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully.',
            'data'    => [
                'item_id'     => $item->id,
                'product_id'  => $item->product_id,
                'quantity'    => $item->quantity,
                'unit_price'  => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ],
        ], 201);
    }

    /**
     * Update item in cart
     */
    public function update(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item = $this->cartService->updateQuantity($itemId, (int) $request->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'data'    => [
                'item_id'     => $item->id,
                'quantity'    => $item->quantity,
                'total_price' => (float) $item->total_price,
            ],
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove($itemId)
    {
        $this->cartService->removeItem($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->cartService->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * Apply Coupon to cart
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $result = $this->cartService->applyCoupon($request->code);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Invalid coupon code.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'discount_amount' => $result['discount_amount'] ?? 0,
        ]);
    }

    /**
     * Remove Coupon from cart
     */
    public function removeCoupon()
    {
        $this->cartService->removeCoupon();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
        ]);
    }
}
