<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * List user orders (authenticated)
     */
    public function index(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product', 'store'])
            ->latest()
            ->paginate(15);

        $transformed = $orders->getCollection()->map(function ($order) {
            return [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'total'           => (float) $order->total,
                'formatted_total' => CurrencyService::format($order->total),
                'items_count'     => $order->items->sum('quantity'),
                'store_name'      => $order->store?->store_name,
                'created_at'      => $order->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
            'data'    => $transformed,
        ]);
    }

    /**
     * Get single order detail
     */
    public function show($idOrNumber)
    {
        $order = Order::where('id', $idOrNumber)
            ->orWhere('order_number', $idOrNumber)
            ->with(['items.product', 'store', 'payments'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $order->id,
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'subtotal'        => (float) $order->subtotal,
                'discount_amount' => (float) $order->discount_amount,
                'total'           => (float) $order->total,
                'formatted_total' => CurrencyService::format($order->total),
                'payment_gateway' => $order->payment_gateway,
                'payment_status'  => $order->payment_status,
                'fulfillment_type'=> $order->fulfillment_type,
                'created_at'      => $order->created_at?->toIso8601String(),
                'store'           => $order->store ? [
                    'id'           => $order->store->id,
                    'store_name'   => $order->store->store_name,
                    'full_address' => $order->store->full_address,
                    'phone'        => $order->store->phone,
                ] : null,
                'items'           => $order->items->map(function ($item) {
                    return [
                        'id'                 => $item->id,
                        'product_name'       => $item->product_name,
                        'quantity'           => (int) $item->quantity,
                        'unit_price'         => (float) $item->unit_price,
                        'total_price'        => (float) $item->total_price,
                        'formatted_total'    => CurrencyService::format($item->total_price),
                        'customization_data' => $item->customization_data,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Track Order status by Order Number & Email / Phone
     */
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'email_phone'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $orderNumber = trim($request->order_number);
        $emailPhone  = trim($request->email_phone ?? '');

        $query = Order::where('order_number', $orderNumber);

        if (!empty($emailPhone)) {
            $query->where(function ($q) use ($emailPhone) {
                $q->where('guest_email', $emailPhone)
                  ->orWhere('guest_phone', $emailPhone)
                  ->orWhereHas('user', function ($uq) use ($emailPhone) {
                      $uq->where('email', $emailPhone)->orWhere('phone', $emailPhone);
                  });
            });
        }

        $order = $query->with(['items', 'store'])->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No order found matching the provided order number and contact detail.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'order_number'    => $order->order_number,
                'status'          => $order->status,
                'payment_status'  => $order->payment_status,
                'total'           => (float) $order->total,
                'formatted_total' => CurrencyService::format($order->total),
                'created_at'      => $order->created_at?->toIso8601String(),
                'store_name'      => $order->store?->store_name,
                'items'           => $order->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'quantity'     => $i->quantity,
                ]),
            ],
        ]);
    }
}
