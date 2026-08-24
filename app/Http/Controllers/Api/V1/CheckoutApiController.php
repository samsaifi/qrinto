<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutApiController extends Controller
{
    /**
     * Submit In-Store Pickup / Cash Payment Order
     */
    public function checkoutCash(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_name'         => 'required|string|max:255',
            'pickup_email'        => 'required|email|max:255',
            'contact_number'      => 'required|string|max:20',
            'store_id'            => 'nullable|exists:stores,id',
            'special_instructions'=> 'nullable|string',
            'cart_items'          => 'nullable|array', // optional direct items if not using session cart
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $cartService = app(CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty() && !$request->has('cart_items')) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $storeId = $request->input('store_id', session('active_store_id'));
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            $order = Order::create([
                'user_id'              => auth()->id(),
                'order_number'         => $orderNumber,
                'status'               => 'pending',
                'subtotal'             => $cart->subtotal,
                'discount_amount'      => $cart->discount_amount,
                'tax_amount'           => 0,
                'shipping_amount'      => 0,
                'total'                => $cart->total,
                'currency'             => 'INR',
                'coupon_code'          => $cart->coupon_code,
                'payment_gateway'      => 'cash',
                'payment_status'       => 'pending',
                'fulfillment_type'     => 'store_pickup',
                'store_id'             => $storeId,
                'guest_email'          => $request->pickup_email,
                'guest_phone'          => $request->contact_number,
                'special_instructions' => $request->special_instructions,
                'billing_address'      => [
                    'name'  => $request->pickup_name,
                    'email' => $request->pickup_email,
                    'phone' => $request->contact_number,
                ],
            ]);

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $cartItem->product_id,
                    'product_name'      => $product ? $product->name : 'Custom Print',
                    'quantity'          => $cartItem->quantity,
                    'unit_price'        => $cartItem->unit_price,
                    'total_price'       => $cartItem->total_price,
                    'customization_data'=> $cartItem->customization_data,
                ]);
            }

            // Clear cart after order creation
            $cartService->clearCart();
            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Order submitted successfully!',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => (float) $order->total,
                'formatted_total' => CurrencyService::format($order->total),
                'confirmation_url' => route('flow.confirmation', $order->id),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API CheckoutCash Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit order. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create PayPal Order Transaction
     */
    public function createPaypalTransaction(Request $request)
    {
        $cartService = app(CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 400);
        }

        return response()->json([
            'success'  => true,
            'amount'   => number_format((float)$cart->total, 2, '.', ''),
            'currency' => 'USD',
        ]);
    }
}
