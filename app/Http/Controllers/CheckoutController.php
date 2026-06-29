<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected PaymentService $paymentService;

    public function __construct(CartService $cartService, PaymentService $paymentService)
    {
        // Middleware 'auth' is applied via route group in web.php
        $this->cartService = $cartService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $addresses = auth()->check() ? auth()->user()->addresses : collect();

        // Ensure a default store is selected if none currently active
        if (!session('active_store_id')) {
            $defaultStore = \App\Models\Store::where('store_name', 'like', '%Buena Park%')->first();
            if ($defaultStore) {
                session(['active_store_id' => $defaultStore->id]);
            }
        }

        return view('checkout.index', compact('cart', 'addresses'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'address_id' => auth()->check() ? 'required_without:new_address|exists:addresses,id' : 'nullable',
            'new_address' => 'nullable|boolean',
            'guest_email' => auth()->check() ? 'nullable' : 'required|email|max:255',
            'full_name' => 'required_if:new_address,1|string|max:255',
            'phone' => 'required_if:new_address,1|string|max:20',
            'address_line_1' => 'required_if:new_address,1|string|max:500',
            'city' => 'required_if:new_address,1|string|max:255',
            'state' => 'required_if:new_address,1|string|max:255',
            'postal_code' => 'required_if:new_address,1|string|max:10',
        ]);

        $cart = $this->cartService->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Handle address
        if ($request->new_address) {
            $addressData = [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->get('country', 'USA'),
            ];

            if (auth()->check()) {
                $address = auth()->user()->addresses()->create($addressData);
                $shippingAddressArray = $address->toArray();
            } else {
                $shippingAddressArray = $addressData;
            }
        } else {
            $address = Address::findOrFail($request->address_id);
            $shippingAddressArray = $address->toArray();
        }

        // Create order
        $order = DB::transaction(function () use ($cart, $shippingAddressArray, $request) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'guest_email' => $request->guest_email,
                'guest_phone' => $request->phone,
                'order_number' => Order::generateOrderNumber(),
                'invoice_number' => Order::generateInvoiceNumber(),
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'discount_amount' => $cart->discount,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'total' => $cart->total,
                'currency' => CurrencyService::getCode(),
                'coupon_code' => $cart->coupon?->code,
                'shipping_address' => $shippingAddressArray,
                'payment_gateway' => config('services.payment_gateway'),
                'store_id' => session('active_store_id'),
            ]);

            foreach ($cart->items as $item) {
                // Determine product name: custom prints store name in customization_data
                if ($item->product_id === null && isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print') {
                    $productName = 'Custom Print - ' . ($item->customization_data['size_label'] ?? '') . ' (' . ($item->customization_data['size_dimensions'] ?? '') . ')';
                } else {
                    $productName = $item->product->name ?? 'Custom Item';
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $productName,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total,
                    'customization_data' => $item->customization_data,
                    'selected_options' => $item->selected_options,
                    'uploaded_images' => $item->customization_data['uploaded_images'] ?? null,
                ]);
            }

            // Update coupon usage
            if ($cart->coupon) {
                $cart->coupon->increment('used_count');
            }

            return $order;
        });

        // Create payment
        try {
            $paymentData = $this->paymentService->createPaymentOrder($order);
        } catch (\Exception $e) {
            // If payment gateway fails, still show order with COD option
            $paymentData = null;
        }

        // Clear cart
        $this->cartService->clearCart();

        return view('checkout.payment', compact('order', 'paymentData'));
    }

    public function express()
    {
        $cart = $this->cartService->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Ensure a default store is selected if none currently active
        if (!session('active_store_id')) {
            $defaultStore = \App\Models\Store::where('store_name', 'like', '%Buena Park%')->first();
            if ($defaultStore) {
                session(['active_store_id' => $defaultStore->id]);
            }
        }

        $shippingAddressArray = [
            'full_name' => auth()->check() ? auth()->user()->name : 'In-Store Customer',
            'phone' => auth()->check() ? auth()->user()->phone : '',
            'address_line_1' => 'In-Store Pickup',
            'city' => 'Buena Park',
            'state' => 'CA',
            'postal_code' => '90620',
            'country' => 'USA',
        ];

        // Create order
        $order = DB::transaction(function () use ($cart, $shippingAddressArray) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'guest_email' => auth()->check() ? null : 'guest_' . time() . '@instore.local',
                'guest_phone' => $shippingAddressArray['phone'],
                'order_number' => Order::generateOrderNumber(),
                'invoice_number' => Order::generateInvoiceNumber(),
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'discount_amount' => $cart->discount,
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'total' => $cart->total,
                'currency' => CurrencyService::getCode(),
                'coupon_code' => $cart->coupon?->code,
                'shipping_address' => $shippingAddressArray,
                'payment_gateway' => config('services.payment_gateway'),
                'store_id' => session('active_store_id'),
            ]);

            foreach ($cart->items as $item) {
                // Determine product name: custom prints store name in customization_data
                if ($item->product_id === null && isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print') {
                    $productName = 'Custom Print - ' . ($item->customization_data['size_label'] ?? '') . ' (' . ($item->customization_data['size_dimensions'] ?? '') . ')';
                } else {
                    $productName = $item->product->name ?? 'Custom Item';
                }

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $productName,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total,
                    'customization_data' => $item->customization_data,
                    'selected_options' => $item->selected_options,
                    'uploaded_images' => $item->customization_data['uploaded_images'] ?? null,
                ]);
            }

            // Update coupon usage
            if ($cart->coupon) {
                $cart->coupon->increment('used_count');
            }

            return $order;
        });

        // Clear cart
        $this->cartService->clearCart();
        
        session(['last_order_id' => $order->id]);

        return redirect()->route('checkout.payment', $order->id);
    }

    public function verifyPayment(Request $request)
    {
        $verified = $this->paymentService->verifyPayment($request->all());

        if ($request->wantsJson() || $request->ajax()) {
            if ($verified) {
                session(['last_order_id' => $request->order_id]);
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('orders.confirmation', $request->order_id)
                ]);
            }
            return response()->json([
                'success' => false,
                'redirect_url' => route('orders.show', $request->order_id)
            ], 400);
        }

        if ($verified) {
            session(['last_order_id' => $request->order_id]);
            return redirect()->route('orders.confirmation', $request->order_id)
                ->with('success', 'Payment successful! Your order has been confirmed.');
        }

        return redirect()->route('orders.show', $request->order_id)
            ->with('error', 'Payment verification failed. Please try again or contact support.');
    }

    public function confirmation(Order $order)
    {
        if ($order->user_id !== null && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->user_id === null && session('last_order_id') != $order->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load('items.product');

        return view('checkout.confirmation', compact('order'));
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== null && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->user_id === null && session('last_order_id') != $order->id && $order->payment_status !== 'paid') {
            abort(403, 'Unauthorized access to order payment route.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('customer.orders.show', $order)->with('info', 'Order is already paid.');
        }

        try {
            $paymentData = $this->paymentService->createPaymentOrder($order);
        } catch (\Exception $e) {
            $paymentData = null;
        }

        return view('checkout.payment', compact('order', 'paymentData'));
    }
}
