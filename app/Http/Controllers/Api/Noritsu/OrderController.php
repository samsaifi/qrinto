<?php

namespace App\Http\Controllers\Api\Noritsu;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Start a new session / Create a draft order (optional flow if we want to track abandoned carts effectively).
     * For MVP, we can just return config.
     */
    public function session(): JsonResponse
    {
        return response()->json([
            'session_id' => session()->getId(), // or custom UUID
            'upload_config' => [
                'max_file_size' => 10 * 1024 * 1024,
                'allowed_types' => ['image/jpeg', 'image/png']
            ]
        ]);
    }

    /**
     * Create a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required_if:fulfillment_type,pickup|exists:stores,id',
            'fulfillment_type' => 'required|in:pickup,shipment',
            'guest_email' => 'required|email',
            'guest_phone' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.template_id' => 'required|exists:templates,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.uploaded_images' => 'required|array', // Assuming URLs or IDs from upload endpoint
            'items.*.customization_data' => 'nullable|array',
            'payment_token' => 'required|string', // Token from Stripe/Payment Provider
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $itemsPayload = [];

            // Calculate totals and validate items
            foreach ($request->items as $itemData) {
                $template = Template::findOrFail($itemData['template_id']);
                
                $unitPrice = $template->price; // Base price
                $quantity = $itemData['quantity'];
                $totalPrice = $unitPrice * $quantity;

                $subtotal += $totalPrice;

                $itemsPayload[] = [
                    'template' => $template, // keep reference
                    'data' => $itemData,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice
                ];
            }

            // Simple tax calculation (e.g. 8%) - Replace with real tax logic later
            $taxAmount = $subtotal * 0.08;
            $shippingAmount = ($request->fulfillment_type === 'shipment') ? 5.99 : 0.00; // Flat rate for now
            $total = $subtotal + $taxAmount + $shippingAmount;

            // Create Order
            $order = Order::create([
                'user_id' => null, // Guest
                'guest_email' => $request->guest_email,
                'guest_phone' => $request->guest_phone,
                'store_id' => $request->store_id,
                'fulfillment_type' => $request->fulfillment_type,
                'status' => 'pending', // Pending payment capture
                'order_number' => Order::generateOrderNumber(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total' => $total,
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address ?? [], // If shipment
                'estimated_delivery_date' => now()->addDays(3), // Placeholder
            ]);

            // Create Order Items
            foreach ($itemsPayload as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => 1, // Hack: default product ID if needed, or create a dummy product for "Custom Print"
                    'template_id' => $item['template']->id,
                    'product_name' => $item['template']->name,
                    'quantity' => $item['data']['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'customization_data' => $item['data']['customization_data'] ?? [],
                    'uploaded_images' => $item['data']['uploaded_images'],
                ]);
            }

            // Implement Payment Logic here (e.g., Stripe charge)
            // ...
            // If success:
            $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            
            // Dispatch PDF Generation Job here
            // dispatch(new GenerateNoritsuPdf($order));

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Order creation failed. Please try again.'], 500);
        }
    }

    /**
     * Get order status by ID (for tracking page).
     */
    public function show($id): JsonResponse
    {
        // Simple security - user should provide email or order number combo to verify access
        // For MVP, if we use UUIDs or signed URLs, direct access might be okay-ish, or require auth token.
        // Assuming secure token logic on frontend or session.
        
        $order = Order::with(['items', 'store'])->findOrFail($id);
        
        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)), // e.g., delivered_store -> Delivered store
            'estimated_delivery' => $order->estimated_delivery_date?->format('Y-m-d'),
            'store' => $order->store?->name,
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'preview' => $item->customization_data['preview_url'] ?? null, // Assuming frontend generated a preview URL
                ];
            })
        ]);
    }
}
