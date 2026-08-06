<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Services\CurrencyService;
use App\Models\CustomerUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;
use App\Mail\AdminOrderAlertMail;
use App\Mail\StoreOrderAlertMail;
use App\Mail\QuickFlowOrderMail;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class QuickFlowController extends Controller
{
    /**
     * Default to the mobile view folder.
     */
    protected function getViewPath($viewName)
    {
        return "quick-flow.{$viewName}";
    }

    /**
     * Default to the mobile route prefix.
     */
    protected function getRoutePrefix()
    {
        return 'flow.';
    }

    /**
     * Step 1: Category Selection
     */
    public function index()
    {
        if (!session()->has('active_store_id')) {
            return redirect()->route($this->getRoutePrefix() . 'find-store');
        }

        $productTypes = ProductType::parents()
            ->orderBy('sort_order')
            ->get();

        return view($this->getViewPath('index'), compact('productTypes'));
    }

    /**
     * Step 2: Size Selection (within a product type)
     */

    public function findEventCategoryIds($storeId)
    {
        $today = now()->format('Y-m-d');

        $eventIds = Event::whereHas('stores', fn($q) => $q->where('stores.id', $storeId))
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('is_active',   '1')
            ->pluck('id');

        if ($eventIds->isEmpty()) {
            return ['eventIds' => [], 'categoryIds' => []];
        }

        $categoryIds = \DB::table('category_event')
            ->whereIn('event_id', $eventIds)
            ->pluck('category_id')
            ->unique()
            ->values()
            ->toArray();

        return [
            'eventIds' => $eventIds->toArray(),
            'categoryIds' => $categoryIds,
        ];
    }
    public function category(ProductType $type)
    {
        $flowData = session('quick_flow_data', []);
        $active_store_id = session('active_store_id', null);

         

        if (!$type->parent_id) {
            $flowData = [
                'type_id'       => $type->id,
                'type_name'     => $type->name,
                'type_slug'     => $type->slug,
                'category_name' => $type->name,
                'category_slug' => $type->slug,
            ];
        } else {
            $flowData['size_id']        = $type->id;
            $flowData['size_name']      = $type->name;
            $flowData['size_slug']      = $type->slug;
            $flowData['size_width']     = $type->width;
            $flowData['size_height']    = $type->height;
            $flowData['size_unit']      = $type->unit;
            $flowData['size_price']     = $type->price;
            $flowData['size_title']     = $type->title;
            $flowData['size_old_price'] = $type->old_price;
        }

        session(['quick_flow_data' => $flowData]);

        $subTypes = $type->children()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if ($subTypes->isNotEmpty()) {
            return view($this->getViewPath('category'), compact('type', 'subTypes'));
        }

        $q = Product::where('is_active', 1)
            ->where(function ($query) use ($type) {
                $query->where('product_type_id', $type->parent_id)
                    ->orWhere('product_type_id', $type->id);
            })
            ->where(function ($query) use ($active_store_id) {
                $query->where('store_id', $active_store_id)
                    ->orWhereNull('store_id');
            });

        switch ($flowData['size_title'] ?? '') {
            case 'Flat':
                $q->where('no_of_pages', 1);
                break;
            case 'Flat - double':
                $q->where('no_of_pages', 2);
                break;
            case 'Folded':
                $q->where('no_of_pages', 4);
                break;
        }

        // Priority 1: Event Templates
         $eventTemplates = collect();
        if (!empty($active_store_id)) {
            $eventTemplates = (clone $q)
                ->whereNotNull('event_id')
                ->where('product_store', $active_store_id)
                ->orderBy('sort_order')
                ->get();
        }

        // Priority 2: Store Templates
        $productStoreTemplates = collect();
        if (!empty($active_store_id)) {
            $productStoreTemplates = (clone $q)
                ->where('product_store', $active_store_id)
                ->whereNull('event_id')
                ->orderBy('sort_order')
                ->get();
        }

        // Priority 3: Remaining Templates
        $otherTemplates = (clone $q) 
            ->when(!empty($active_store_id), function ($query) use ($active_store_id) {
                $query->where(function ($q) use ($active_store_id) {
                    $q->where('product_store', '!=', $active_store_id)
                        ->orWhereNull('product_store');
                });
            })
            ->orderBy('sort_order')
            ->get();

        $templates = $eventTemplates
            ->concat($productStoreTemplates)
            ->concat($otherTemplates)
            ->unique('id')
            ->values();

        $categories = Category::parents()
            ->orderBy('sort_order')
            ->get();

        return view(
            $this->getViewPath('templates'),
            compact('type', 'templates', 'categories')
        );
    } 

    /**
     * Step 3: Template Selection
     */
    public function product(Product $product)
    {
        return redirect()->route($this->getRoutePrefix() . 'customize', $product->slug);
    }

    /**
     * Step 4: Customization
     */
    public function customize(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
       
        $product->load('images', 'productType');

        // Use sub-type price from session if available
        $flowData = session('quick_flow_data', []);
        
        // $unitPrice = isset($flowData['size_price']) ? $flowData['size_price'] : $product->base_price;
        if (!empty($product->store_id)) {
            $unitPrice = (float) $product->base_price;
        } else {
            $unitPrice = isset($flowData['size_price'])
                ? (float) $flowData['size_price']
                : $product->base_price;
        }
        $oldPrice = isset($flowData['size_old_price']) ? $flowData['size_old_price'] : $product->compare_price;
        
        $activeTemplates     = $this->buildTemplatesForJs();
        $templateCategories  = $this->buildTemplateCategoriesForJs();
          
        if($product->no_of_pages == 4){
            return view($this->getViewPath('customize'), compact('product', 'unitPrice', 'oldPrice', 'activeTemplates', 'templateCategories'));
        }elseif($product->no_of_pages == 2){
            return view($this->getViewPath('customize-double'), compact('product', 'unitPrice', 'oldPrice', 'flowData', 'activeTemplates', 'templateCategories'));
        }else {  
            return view($this->getViewPath('customize-single'), compact('product', 'unitPrice', 'oldPrice', 'flowData', 'activeTemplates', 'templateCategories'));
        // }else{
        //     return view($this->getViewPath('customize-single'), compact('product', 'unitPrice', 'oldPrice', 'flowData', 'activeTemplates', 'templateCategories'));
        }


    }

    /**
     * Build all active templates keyed by slug for JS consumption.
     */
    private function buildTemplatesForJs(): array
    {
        return Template::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($tpl) {
                $config = $tpl->canvas_config ?? [];
                return [$tpl->slug => array_merge($config, [
                    'label'      => $tpl->name,
                    'icon'       => $tpl->icon_type === 'lucide' ? $tpl->icon_value : 'layout-template',
                    'iconUrl'    => $tpl->icon_type === 'upload' ? asset('storage/' . $tpl->icon_value) : null,
                    'categoryId' => $tpl->category_id,
                ])];
            })
            ->toArray();
    }

    /**
     * Build the list of template categories for JS consumption.
     */
    private function buildTemplateCategoriesForJs(): array
    {
        return Template::active()
            ->whereNotNull('category_id')
            ->with('category')
            ->get()
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    /**
     * AJAX: Handle image upload during customization
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $path = $file->store('customer-uploads/' . date('Y/m'), 'public');

        $upload = CustomerUpload::create([
            'user_id'       => auth()->id(),
            'session_id'    => session()->getId(),
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'upload_id' => $upload->id,
            'url' => asset('storage/' . $path)
        ]);
    }

    /**
     * AJAX: Save the generated composite image (image + text)
     */
    public function uploadComposite(Request $request)
    {
        $request->validate([
            'image_data' => 'required|string', // base64
        ]);

        $imageData = $request->input('image_data');
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                return response()->json(['success' => false, 'error' => 'Invalid image type']);
            }
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['success' => false, 'error' => 'Base64 decode failed']);
            }
        } else {
            return response()->json(['success' => false, 'error' => 'Did not match data URI with image data']);
        }

        $fileName = uniqid('composite_') . '.' . $type;
        $path = 'customer-uploads/' . date('Y/m') . '/' . $fileName;

        Storage::disk('public')->put($path, $imageData);

        $upload = CustomerUpload::create([
            'user_id'       => auth()->id(),
            'session_id'    => session()->getId(),
            'original_name' => 'Customized_Design.' . $type,
            'file_path'     => $path,
            'mime_type'     => 'image/' . $type,
            'file_size'     => strlen($imageData),
        ]);

        return response()->json([
            'success' => true,
            'upload_id' => $upload->id,
            'url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Step 5: Review & Pay
     */
    public function checkout(Request $request)
    {
        $productId  = $request->input('product_id');
        $product    = Product::findOrFail($productId);
        $quantity   = $request->input('quantity', 1);
        $message    = $request->input('message');
        $styleData  = json_decode($request->input('style_data'), true);

        // Handle multiple uploads
        $uploadIdsJson = $request->input('upload_ids');
        $uploadIds     = $uploadIdsJson ? json_decode($uploadIdsJson, true) : [];

        $uploads = [];
        if (!empty($uploadIds)) {
            $uploads = CustomerUpload::whereIn('id', array_values($uploadIds))
                ->get()
                ->keyBy('id');
        }

        // For compatibility with checkout view
        $upload = !empty($uploadIds) ? $uploads->first() : null;

        $paypalClientId = config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'));

        // ── Unit-price resolution ────────────────────────────────────────────
        // Priority:
        //  1. Store-exclusive product + Canadian store  → use CAD-converted base_price
        //  2. Store-exclusive product + non-Canadian store → use base_price (USD)
        //  3. No store restriction → honour size_price from session, else base_price
        // ────────────────────────────────────────────────────────────────────
        $flowData = session('quick_flow_data', []);

        if ($product->store_id) {
             $unitPrice = (float) $product->base_price;
        } else {
            // Generic product: honour the size-tier price stored in the session
            $unitPrice = isset($flowData['size_price'])
                ? $flowData['size_price']
                : $product->base_price;
        }

        return view($this->getViewPath('checkout'), compact(
            'product', 'quantity', 'message', 'styleData',
            'upload', 'uploads', 'uploadIds', 'paypalClientId', 'unitPrice'
        ));
    }


    /**
     * PayPal: Create Order (AJAX)
     */
    public function createPaypalOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;

        // Use sub-type price from session if available, else fallback to product base price
        $flowData = session('quick_flow_data', []);
        // $unitPrice = isset($flowData['size_price']) ? $flowData['size_price'] : $product->base_price;
        if (!empty($product->store_id)) {
            $unitPrice = (float) $product->base_price;
        } else {
            $unitPrice = isset($flowData['size_price'])
                ? $flowData['size_price']
                : $product->base_price;
        }
        $unitPrice = CurrencyService::convert((float) $unitPrice);
        $subtotal = $unitPrice * $quantity;
        $discountAmount = 0;

        // Apply coupon if provided
        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            }
        }

        $subtotal = $unitPrice * $quantity;
        $discountAmount = 0;

        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            }
        }

        // PayPal requires specific formatting
        $fmtSubtotal = number_format($subtotal, 2, '.', '');
        $fmtDiscount = number_format($discountAmount, 2, '.', '');
        $fmtTotal = number_format($subtotal - $discountAmount, 2, '.', '');

        $accessToken = $this->getPaypalAccessToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Unable to authenticate with PayPal'], 500);
        }

        $apiBase = $this->getPaypalApiBase();

        $response = Http::withToken($accessToken)
            ->post("{$apiBase}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => uniqid('FLOW-'),
                    'description' => $product->name . ' × ' . $quantity,
                    'amount' => [
                        'currency_code' => CurrencyService::getCode(),
                        'value' => $fmtTotal,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => CurrencyService::getCode(),
                                'value' => $fmtSubtotal,
                            ],
                            'discount' => [
                                'currency_code' => CurrencyService::getCode(),
                                'value' => $fmtDiscount,
                            ],
                        ],
                    ],
                    'items' => [[
                        'name' => $product->name,
                        'quantity' => (string)$quantity,
                        'unit_amount' => [
                            'currency_code' => CurrencyService::getCode(),
                            'value' => number_format($unitPrice, 2, '.', ''),
                        ],
                    ]],
                ]],
                'application_context' => [
                    'brand_name' => config('app.name', 'CustomPrint'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        \Log::error('PayPal Create Order Error', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return response()->json(['error' => 'Failed to create PayPal order: ' . json_encode($response->json())], 500);
    }

    /**
     * PayPal: Capture Order (AJAX) — creates our internal Order
     */
    public function capturePaypalOrder(Request $request)
    {
        \Log::info('PayPal Capture Started', $request->all());
        $request->validate([
            'paypal_order_id' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:255',
            'style_data' => 'nullable|string',
            'upload_ids' => 'nullable|array',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'coupon_code' => 'nullable|string',
            'special_instructions' => 'nullable|string|max:2000',
        ]);

        $accessToken = $this->getPaypalAccessToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Unable to authenticate with PayPal'], 500);
        }

        $apiBase = $this->getPaypalApiBase();
        $paypalOrderId = $request->paypal_order_id;

        // Capture the PayPal order — PayPal requires Content-Type: application/json but NO body
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->send('POST', "{$apiBase}/v2/checkout/orders/{$paypalOrderId}/capture");

        if (!$response->successful()) {
            Log::error('PayPal Capture Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(['error' => 'Payment capture failed'], 500);
        }

        $captureData = $response->json();
        $captureStatus = $captureData['status'] ?? 'UNKNOWN';

        if ($captureStatus !== 'COMPLETED') {
            return response()->json(['error' => 'Payment not completed. Status: ' . $captureStatus], 400);
        }

        // Extract payment details
        $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;

        // Create our internal order
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;

        // Use sub-type price from session if available
        $flowData = session('quick_flow_data', []);
        $unitPrice = isset($flowData['size_price']) ? $flowData['size_price'] : $product->base_price;
        $unitPrice = CurrencyService::convert((float) $unitPrice);
        $subtotal = $unitPrice * $quantity;
        $discountAmount = 0;

        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = $subtotal - $discountAmount;

        // Determine store from session
        $storeId = session('active_store_id');

        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'invoice_number' => Order::generateInvoiceNumber(),
            'status' => 'confirmed',
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'coupon_code' => $request->coupon_code,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total' => $total,
            'currency' => CurrencyService::getCode(),
            'shipping_address' => [
                'type' => 'store_pickup',
                'name' => $request->pickup_name,
                'email' => $request->pickup_email,
                'phone' => $request->contact_number,
            ],
            'payment_gateway' => 'paypal',
            'payment_id' => $captureId,
            'payment_status' => 'paid',
            'paid_at' => now(),
            'fulfillment_type' => 'store_pickup',
            'store_id' => $storeId,
            'guest_email' => $request->pickup_email,
            'guest_phone' => $request->contact_number,
            'notes' => 'Pickup: ' . $request->pickup_name . ' | Phone: ' . $request->contact_number,
            'special_instructions' => $request->special_instructions,
            'flow_data' => session('quick_flow_data'),
        ]);

        // Create order item
        $styleData = $request->style_data ? json_decode($request->style_data, true) : null;
        $uploadIds = $request->upload_ids ?? [];

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice, // Fixed from product->base_price to unitPrice
            'total_price' => $total,
            'customization_data' => [
                'message' => $request->message,
                'style' => $styleData,
                'canvas_mapped_ids' => $uploadIds,
                'dimensions' => ($flowData['size_width'] ?? '') . ' x ' . ($flowData['size_height'] ?? ''),
                'unit' => $flowData['size_unit'] ?? '',
                'size_label' => $flowData['size_name'] ?? '',
            ],
        ]);

        // Finalize Order (PDF + Emails)
        $this->processOrderAndNotify($order, $uploadIds, $product);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
        ]);
    }

    /**
     * Cash: Create Order with Cash (AJAX)
     */
    public function checkoutCash(Request $request)
    {   
        

        \Log::info('Checkout Cash Hit', $request->all());
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:255',
            'style_data' => 'nullable|string',
            'upload_ids' => 'nullable|array',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'coupon_code' => 'nullable|string',
            'special_instructions' => 'nullable|string|max:2000',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;

        // Use sub-type price from session if available
        $flowData = session('quick_flow_data', []);
        // $unitPrice = isset($flowData['size_price']) ? $flowData['size_price'] : $product->base_price;
        if (!empty($product->store_id)) {
    $unitPrice = (float) $product->base_price;
} else {
    $unitPrice = isset($flowData['size_price'])
        ? $flowData['size_price']
        : $product->base_price;
}
        $unitPrice = CurrencyService::convert((float) $unitPrice);
        $subtotal = $unitPrice * $quantity;
        $discountAmount = 0;

        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = $subtotal - $discountAmount;
        $storeId = session('active_store_id');

        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'invoice_number' => Order::generateInvoiceNumber(),
            'status' => 'pending', // Unpaid/pending
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'coupon_code' => $request->coupon_code,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total' => $total,
            'currency' => CurrencyService::getCode(),
            'shipping_address' => [
                'type' => 'store_pickup',
                'name' => $request->pickup_name,
                'email' => $request->pickup_email,
                'phone' => $request->contact_number,
            ],
            'payment_gateway' => 'cash',
            'payment_status' => 'pending',
            'fulfillment_type' => 'store_pickup',
            'store_id' => $storeId,
            'guest_email' => $request->pickup_email,
            'guest_phone' => $request->contact_number,
            'notes' => 'Pickup: ' . $request->pickup_name . ' | Phone: ' . $request->contact_number,
            'special_instructions' => $request->special_instructions,
            'flow_data' => session('quick_flow_data'),
        ]);

        $styleData = $request->style_data ? json_decode($request->style_data, true) : null;
        $uploadIds = $request->upload_ids ?? [];

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'customization_data' => [
                'message' => $request->message,
                'style' => $styleData,
                'canvas_mapped_ids' => $uploadIds,
                'dimensions' => ($flowData['size_width'] ?? '') . ' x ' . ($flowData['size_height'] ?? ''),
                'unit' => $flowData['size_unit'] ?? '',
                'size_label' => $flowData['size_name'] ?? '',
            ],
        ]);

        // Finalize Order (PDF + Emails)
        $this->processOrderAndNotify($order, $uploadIds, $product);

        \Log::info('Checkout Cash Success', ['order_id' => $order->id]);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
        ]);
    }

    /**
     * Cart Checkout: Show checkout page from cart
     */
    public function cartCheckout()
    {
        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty()) {
            return redirect()->route($this->getRoutePrefix() . 'cart.index');
        }

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

        $paypalClientId = config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'));

        return view($this->getViewPath('cart-checkout'), compact('cart', 'uploads', 'paypalClientId'));
    }

    /**
     * Cart Checkout: Cash payment
     */
    public function cartCheckoutCash(Request $request)
    {
        $request->validate([
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'coupon_code' => 'nullable|string',
            'special_instructions' => 'nullable|string|max:2000',
        ]);

        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Cart is empty.']);
        }

        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += CurrencyService::convert((float) $item->unit_price) * $item->quantity;
        }

        $discountAmount = 0;
        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = $subtotal - $discountAmount;
        $storeId = session('active_store_id');

        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'invoice_number' => Order::generateInvoiceNumber(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'coupon_code' => $request->coupon_code,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total' => $total,
            'currency' => CurrencyService::getCode(),
            'shipping_address' => [
                'type' => 'store_pickup',
                'name' => $request->pickup_name,
                'email' => $request->pickup_email,
                'phone' => $request->contact_number,
            ],
            'payment_gateway' => 'cash',
            'payment_status' => 'pending',
            'fulfillment_type' => 'store_pickup',
            'store_id' => $storeId,
            'guest_email' => $request->pickup_email,
            'guest_phone' => $request->contact_number,
            'notes' => 'Pickup: ' . $request->pickup_name . ' | Phone: ' . $request->contact_number,
            'special_instructions' => $request->special_instructions,
            'flow_data' => session('quick_flow_data'),
        ]);

        $firstProduct = null;
        $firstUploadIds = [];

        foreach ($cart->items as $item) {
            $customization = $item->customization_data ?? [];
            $uploadIds = $customization['upload_ids'] ?? [];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Custom Print',
                'quantity' => $item->quantity,
                'unit_price' => CurrencyService::convert((float) $item->unit_price),
                'total_price' => CurrencyService::convert((float) $item->unit_price) * $item->quantity,
                'customization_data' => [
                    'message' => null,
                    'style' => null,
                    'canvas_mapped_ids' => $uploadIds,
                    'dimensions' => ($customization['size_width'] ?? '') . ' x ' . ($customization['size_height'] ?? ''),
                    'unit' => $customization['size_unit'] ?? '',
                    'size_label' => $customization['size_name'] ?? '',
                ],
            ]);

            if (!$firstProduct && $item->product) {
                $firstProduct = $item->product;
                $firstUploadIds = $uploadIds;
            }
        }

        if ($firstProduct) {
            $this->processOrderAndNotify($order, $firstUploadIds, $firstProduct);
        }

        $cartService->clearCart();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
        ]);
    }

    /**
     * Cart Checkout: Create PayPal order
     */
    public function cartPaypalCreate(Request $request)
    {
        $request->validate([
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'coupon_code' => 'nullable|string',
            'special_instructions' => 'nullable|string|max:2000',
        ]);

        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty.'], 400);
        }

        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += CurrencyService::convert((float) $item->unit_price) * $item->quantity;
        }

        $discountAmount = 0;
        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            }
        }

        $total = max(0, $subtotal - $discountAmount);
        $currencyCode = CurrencyService::getCode();

        $paypalClientId = config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'));
        $paypalSecret = config('services.paypal.secret', env('PAYPAL_SECRET'));
        $paypalMode = config('services.paypal.mode', env('PAYPAL_MODE', 'sandbox'));
        $baseUrl = $paypalMode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $authResponse = Http::withBasicAuth($paypalClientId, $paypalSecret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$authResponse->successful()) {
            return response()->json(['error' => 'PayPal authentication failed.'], 500);
        }

        $accessToken = $authResponse->json('access_token');

        $items = [];
        foreach ($cart->items as $item) {
            $itemPrice = number_format(CurrencyService::convert((float) $item->unit_price), 2, '.', '');
            $items[] = [
                'name' => $item->product->name ?? 'Custom Print',
                'quantity' => (string) $item->quantity,
                'unit_amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $itemPrice,
                ],
            ];
        }

        $itemTotal = number_format($subtotal, 2, '.', '');
        $discountFormatted = number_format($discountAmount, 2, '.', '');
        $totalFormatted = number_format($total, 2, '.', '');

        $breakdown = [
            'item_total' => ['currency_code' => $currencyCode, 'value' => $itemTotal],
        ];
        if ($discountAmount > 0) {
            $breakdown['discount'] = ['currency_code' => $currencyCode, 'value' => $discountFormatted];
        }

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $totalFormatted,
                    'breakdown' => $breakdown,
                ],
                'items' => $items,
            ]],
        ];

        $orderResponse = Http::withToken($accessToken)
            ->post("{$baseUrl}/v2/checkout/orders", $orderData);

        if (!$orderResponse->successful()) {
            \Log::error('PayPal Create Order Error', $orderResponse->json());
            return response()->json(['error' => 'Failed to create PayPal order.'], 500);
        }

        return response()->json($orderResponse->json());
    }

    /**
     * Cart Checkout: Capture PayPal payment
     */
    public function cartPaypalCapture(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
            'coupon_code' => 'nullable|string',
            'special_instructions' => 'nullable|string|max:2000',
        ]);

        $paypalClientId = config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'));
        $paypalSecret = config('services.paypal.secret', env('PAYPAL_SECRET'));
        $paypalMode = config('services.paypal.mode', env('PAYPAL_MODE', 'sandbox'));
        $baseUrl = $paypalMode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $authResponse = Http::withBasicAuth($paypalClientId, $paypalSecret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$authResponse->successful()) {
            return response()->json(['success' => false, 'error' => 'PayPal authentication failed.'], 500);
        }

        $accessToken = $authResponse->json('access_token');

        $captureResponse = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/v2/checkout/orders/{$request->paypal_order_id}/capture", []);

        if (!$captureResponse->successful()) {
            \Log::error('PayPal Capture Error', $captureResponse->json());
            return response()->json(['success' => false, 'error' => 'Payment capture failed.'], 500);
        }

        $captureData = $captureResponse->json();
        $paypalTransactionId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->getCart();

        if ($cart->items->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Cart is empty.']);
        }

        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += CurrencyService::convert((float) $item->unit_price) * $item->quantity;
        }

        $discountAmount = 0;
        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $total = $subtotal - $discountAmount;
        $storeId = session('active_store_id');

        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'invoice_number' => Order::generateInvoiceNumber(),
            'status' => 'confirmed',
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'coupon_code' => $request->coupon_code,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total' => $total,
            'currency' => CurrencyService::getCode(),
            'shipping_address' => [
                'type' => 'store_pickup',
                'name' => $request->pickup_name,
                'email' => $request->pickup_email,
                'phone' => $request->contact_number,
            ],
            'payment_gateway' => 'paypal',
            'payment_status' => 'paid',
            'payment_id' => $paypalTransactionId,
            'fulfillment_type' => 'store_pickup',
            'store_id' => $storeId,
            'guest_email' => $request->pickup_email,
            'guest_phone' => $request->contact_number,
            'notes' => 'Pickup: ' . $request->pickup_name . ' | Phone: ' . $request->contact_number,
            'special_instructions' => $request->special_instructions,
            'flow_data' => session('quick_flow_data'),
        ]);

        $firstProduct = null;
        $firstUploadIds = [];

        foreach ($cart->items as $item) {
            $customization = $item->customization_data ?? [];
            $uploadIds = $customization['upload_ids'] ?? [];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'Custom Print',
                'quantity' => $item->quantity,
                'unit_price' => CurrencyService::convert((float) $item->unit_price),
                'total_price' => CurrencyService::convert((float) $item->unit_price) * $item->quantity,
                'customization_data' => [
                    'message' => null,
                    'style' => null,
                    'canvas_mapped_ids' => $uploadIds,
                    'dimensions' => ($customization['size_width'] ?? '') . ' x ' . ($customization['size_height'] ?? ''),
                    'unit' => $customization['size_unit'] ?? '',
                    'size_label' => $customization['size_name'] ?? '',
                ],
            ]);

            if (!$firstProduct && $item->product) {
                $firstProduct = $item->product;
                $firstUploadIds = $uploadIds;
            }
        }

        if ($firstProduct) {
            $this->processOrderAndNotify($order, $firstUploadIds, $firstProduct);
        }

        $cartService->clearCart();

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
        ]);
    }

    /**
     * Step 6: Order Confirmation
     */
    public function confirmation(Order $order)
    {
        $order->load(['items.product', 'store']);
        return view($this->getViewPath('confirmation'), compact('order'));
    }

    /**
     * Dedicated Print Page: shows just the design image in a print-friendly layout
     */
    public function printDesign(Order $order)
    {
        $order->load(['items.product', 'store']);

        $item = $order->items->first();
        $product = $item?->product;
        $designUrl = null;
        $pdfUrl = null;

        $pdfName = 'Design_' . $order->order_number . '.pdf';
        $pdfDirectory = storage_path('app/public/orders/pdfs');
        $pdfPath = $pdfDirectory . '/' . $pdfName;

        $noOfPages = $product->no_of_pages ?? 1;
        $flowData = $order->flow_data ?? [];
        $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'portrait';

        if ($noOfPages == 4) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'background_image' => 'rotate_0',
                'frame_image' => 'rotate_180_plus',
                'overlay_image' => 'rotate_0',
            ];
            $portrait_imageTypes = [
                'frame_image' => 'rotate_90_minus',
                'overlay_image' => 'rotate_90_minus',
                'sample_image' => 'rotate_90_plus',
                'background_image' => 'rotate_90_plus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = array_keys($imageTypes);
        } elseif ($noOfPages == 2) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'frame_image'  => 'rotate_180_plus',
            ];
            $portrait_imageTypes = [
                'frame_image'  => 'rotate_90_minus',
                'sample_image' => 'rotate_90_minus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = ['frame_image', 'sample_image'];
        } else {
            $imageTypes = [];
            $slots = [];
        }

        if ($noOfPages >= 2 && $item && $product) {
            $uploadedImages = $item->uploaded_images ?? [];
            $absolutePaths = [];

            foreach ($slots as $key) {
                $localPath = null;

                if (!empty($uploadedImages[$key])) {
                    $localPath = storage_path('app/public/' . $uploadedImages[$key]);
                    if (!file_exists($localPath)) {
                        $localPath = public_path('storage/' . $uploadedImages[$key]);
                    }
                }

                if (empty($localPath) || !file_exists($localPath)) {
                    $dbPath = $product->getRawOriginal($key);
                    if ($dbPath) {
                        $localPath = storage_path('app/public/' . $dbPath);
                        if (!file_exists($localPath)) {
                            $localPath = public_path('storage/' . $dbPath);
                        }
                    }
                }

                if ($localPath && file_exists($localPath)) {
                    $rotation = $imageTypes[$key] ?? 'rotate_0';
                    $rotatedAbsPath = $this->physicallyRotateImage($localPath, $rotation);
                    $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
                } else {
                    $absolutePaths[$key] = null;
                    \Log::warning("printDesign Image Missing: Key {$key} for order {$order->order_number}");
                }
            }

            $firstAvailable = collect($absolutePaths)->filter()->first();
            if ($firstAvailable) {
                $relativePath = str_replace(str_replace('\\', '/', storage_path('app/public/')), '', $firstAvailable);
                $designUrl = asset('storage/' . $relativePath);
            }

            $width = floatval($flowData['size_width'] ?? 3.5);
            $height = floatval($flowData['size_height'] ?? 5);
            $pdfWidth = min($width, $height);
            $pdfHeight = max($width, $height);
            $pdfOrientation = ($product && $product->pdf_orientation) ? $product->pdf_orientation : 'landscape';

            if (!File::isDirectory($pdfDirectory)) {
                File::makeDirectory($pdfDirectory, 0755, true, true);
            }

            if ($noOfPages == 4) {
                $pdf = PDF::loadView('quick-flow.pdf.design', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            } else {
                $widthVal = floatval($flowData['size_width'] ?? 5.00);
                $heightVal = floatval($flowData['size_height'] ?? 7.00);
                
                $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));
                $cssUnit = ($unit === 'inch') ? 'in' : $unit;

                $pdf = PDF::loadView('quick-flow.pdf.design-double', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'cssUnit' => $cssUnit,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            }
            
            $pdf->save($pdfPath);
        } else {
            // Single-page product
            if ($item) {
                $uploadedImages = $item->uploaded_images;
                $absolutePath = null;
                $relPath = null;

                if ($uploadedImages && is_array($uploadedImages) && count($uploadedImages) > 0) {
                    $firstImage = is_string(reset($uploadedImages)) ? reset($uploadedImages) : null;
                    if ($firstImage) {
                        $relPath = $firstImage;
                        $localPath = storage_path('app/public/' . $firstImage);
                        if (file_exists($localPath)) {
                            $absolutePath = str_replace('\\', '/', $localPath);
                        } else {
                            $localPath = public_path('storage/' . $firstImage);
                            if (file_exists($localPath)) {
                                $absolutePath = str_replace('\\', '/', $localPath);
                            }
                        }
                    }
                }

                if (!$absolutePath && !empty($item->customization_data['preview_url'])) {
                    $designUrl = $item->customization_data['preview_url'];
                } elseif ($relPath) {
                    $designUrl = str_starts_with($relPath, 'http') ? $relPath : asset('storage/' . $relPath);
                }

                if (!file_exists($pdfPath) && $absolutePath) {
                    $widthVal = floatval($flowData['size_width'] ?? 5.00);
                    $heightVal = floatval($flowData['size_height'] ?? 7.00);
                    $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));

                    if ($orientation === 'landscape') {
                        $pdfWidthVal = $heightVal;
                        $pdfHeightVal = $widthVal;
                    } else {
                        $pdfWidthVal = $widthVal;
                        $pdfHeightVal = $heightVal;
                    }

                    $cssUnit = ($unit === 'inch') ? 'in' : $unit;
                    $ptsPerUnit = 72;
                    if ($unit === 'cm') {
                        $ptsPerUnit = 72 / 2.54;
                    } elseif ($unit === 'mm') {
                        $ptsPerUnit = 72 / 25.4;
                    } elseif ($unit === 'px' || $unit === 'pixel') {
                        $ptsPerUnit = 0.75;
                    }

                    $pdfWidthPts = $pdfWidthVal * $ptsPerUnit;
                    $pdfHeightPts = $pdfHeightVal * $ptsPerUnit;

                    if (!File::isDirectory($pdfDirectory)) {
                        File::makeDirectory($pdfDirectory, 0755, true, true);
                    }

                    $pdf = PDF::loadView('quick-flow.pdf.design-single', [
                        'image' => $absolutePath,
                        'width' => $pdfWidthVal,
                        'height' => $pdfHeightVal,
                        'cssUnit' => $cssUnit,
                    ]);
                    $pdf->setPaper([0, 0, $pdfWidthPts, $pdfHeightPts]);
                    $pdf->save($pdfPath);
                }
            }
        }
        
        if (file_exists($pdfPath)) {
            $pdfUrl = asset('storage/orders/pdfs/' . $pdfName);
        }

        return view($this->getViewPath('print'), compact('order', 'designUrl', 'pdfUrl'));
    }

    public function viewPdf(Order $order)
    {
        $order->load(['items.product']);

        $item = $order->items->first();
        $product = $item?->product;
        $flowData = $order->flow_data ?? [];
        $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'portrait';
        $noOfPages = $product->no_of_pages ?? 1;

        $pdfName = 'Design_' . $order->order_number . '.pdf';
        $pdfDirectory = storage_path('app/public/orders/pdfs');
        $pdfPath = $pdfDirectory . '/' . $pdfName;

        // if (file_exists($pdfPath)) {
        //     return response()->file($pdfPath, [
        //         'Content-Type' => 'application/pdf',
        //         'Content-Disposition' => 'inline; filename="' . $pdfName . '"',
        //     ]);
        // }

        if (!$item || !$product) {
            abort(404, 'Order has no product.');
        }

        if ($noOfPages == 4) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'background_image' => 'rotate_0',
                'frame_image' => 'rotate_180_plus',
                'overlay_image' => 'rotate_0',
            ];
            $portrait_imageTypes = [
                'frame_image' => 'rotate_90_minus',
                'overlay_image' => 'rotate_90_minus',
                'sample_image' => 'rotate_90_plus',
                'background_image' => 'rotate_90_plus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = array_keys($imageTypes);
        } elseif ($noOfPages == 2) {
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'frame_image'  => 'rotate_180_plus',
            ];
            $portrait_imageTypes = [
                'frame_image'  => 'rotate_90_minus',
                'sample_image' => 'rotate_90_minus',
            ];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $slots = ['frame_image', 'sample_image'];
        } else {
            $imageTypes = [];
            $slots = [];
        }

        if (!File::isDirectory($pdfDirectory)) {
            File::makeDirectory($pdfDirectory, 0755, true, true);
        }

        if ($noOfPages >= 2) {
            $uploadedImages = $item->uploaded_images ?? [];
            $absolutePaths = [];

            foreach ($slots as $key) {
                $localPath = null;

                if (!empty($uploadedImages[$key])) {
                    $localPath = storage_path('app/public/' . $uploadedImages[$key]);
                    if (!file_exists($localPath)) {
                        $localPath = public_path('storage/' . $uploadedImages[$key]);
                    }
                }

                if (empty($localPath) || !file_exists($localPath)) {
                    $dbPath = $product->getRawOriginal($key);
                    if ($dbPath) {
                        $localPath = storage_path('app/public/' . $dbPath);
                        if (!file_exists($localPath)) {
                            $localPath = public_path('storage/' . $dbPath);
                        }
                    }
                }

                if ($localPath && file_exists($localPath)) {
                    $rotation = $imageTypes[$key] ?? 'rotate_0';
                    $rotatedAbsPath = $this->physicallyRotateImage($localPath, $rotation);
                    $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
                } else {
                    $absolutePaths[$key] = null;
                }
            }

            $width = floatval($flowData['size_width'] ?? 3.5);
            $height = floatval($flowData['size_height'] ?? 5);
            $pdfWidth = min($width, $height);
            $pdfHeight = max($width, $height);
            $pdfOrientation = ($product && $product->pdf_orientation) ? $product->pdf_orientation : 'landscape';
             
            if ($noOfPages == 4) {
                $pdf = PDF::loadView('quick-flow.pdf.design', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            } else {
                 
                    $a = $pdfWidth;
                    $pdfWidth = $pdfHeight;
                    $pdfHeight = $a;
                 
                // dd($pdfWidth, $pdfHeight, $pdfOrientation);
                $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));
                $cssUnit = ($unit === 'inch') ? 'in' : $unit;

                $pdf = PDF::loadView('quick-flow.pdf.design-double', [
                    'images' => $absolutePaths,
                    'rotations' => $imageTypes,
                    'width' => $pdfWidth,
                    'height' => $pdfHeight,
                    'cssUnit' => $cssUnit,
                    'orientation' => $pdfOrientation,
                ]);
                $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
            }
        } else {
            $uploadedImages = $item->uploaded_images ?? [];
            $absolutePath = null;

            if (is_array($uploadedImages) && count($uploadedImages) > 0) {
                $firstImage = reset($uploadedImages);
                if (is_string($firstImage)) {
                    $localPath = storage_path('app/public/' . $firstImage);
                    if (file_exists($localPath)) {
                        $absolutePath = str_replace('\\', '/', $localPath);
                    }
                }
            }

            $widthVal = floatval($flowData['size_width'] ?? 5.00);
            $heightVal = floatval($flowData['size_height'] ?? 7.00);
            $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));

            if ($orientation === 'landscape') {
                $pdfWidthVal = $heightVal;
                $pdfHeightVal = $widthVal;
            } else {
                $pdfWidthVal = $widthVal;
                $pdfHeightVal = $heightVal;
            }

            $cssUnit = ($unit === 'inch') ? 'in' : $unit;
            $ptsPerUnit = 72;
            if ($unit === 'cm') {
                $ptsPerUnit = 72 / 2.54;
            } elseif ($unit === 'mm') {
                $ptsPerUnit = 72 / 25.4;
            } elseif ($unit === 'px' || $unit === 'pixel') {
                $ptsPerUnit = 0.75;
            }

            $pdf = PDF::loadView('quick-flow.pdf.design-single', [
                'image' => $absolutePath,
                'width' => $pdfWidthVal,
                'height' => $pdfHeightVal,
                'cssUnit' => $cssUnit,
            ]);
            $pdf->setPaper([0, 0, $pdfWidthVal * $ptsPerUnit, $pdfHeightVal * $ptsPerUnit]);
        }

        $pdf->save($pdfPath);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfName . '"',
        ]);
    }

    /**
     * Order Tracking: Form
     */
    public function trackForm()
    {
        return view($this->getViewPath('track-form'));
    }

    /**
     * Order Tracking: Process Form
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $orderNumber = trim($request->input('order_number'));

        return redirect()->route($this->getRoutePrefix() . 'track.order', ['orderNumber' => $orderNumber]);
    }

    /**
     * Order Tracking: Details
     */
    public function trackOrder($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with(['items.product', 'store'])->first();

        if (!$order) {
            return redirect()->route($this->getRoutePrefix() . 'track.form')->with('error', 'Order not found. Please check your order number.');
        }

        return view($this->getViewPath('track'), compact('order'));
    }

    /**
     * Store Selection: Form to find a store
     */
    public function findStore(Request $request)
    {
        $query = $request->input('q');
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        
        $stores = collect();
        $nearbyStores = collect();
        $geolocationAttempted = false;

        if ($lat && $lon) {
            $nearbyStores = $this->getNearbyStoresFromCoords($lat, $lon);
            $geolocationAttempted = true;
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'stores' => $nearbyStores
                ]);
            }
        }

        if ($query) {
            $stores = \App\Models\Store::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('store_name', 'like', "%{$query}%")
                        ->orWhere('store_code', 'like', "%{$query}%")
                        ->orWhere('city', 'like', "%{$query}%")
                        ->orWhere('state', 'like', "%{$query}%")
                        ->orWhere('zip_code', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%")
                        ->orWhere('country', 'like', "%{$query}%")
                        ->orWhere('owner_name', 'like', "%{$query}%");
                })
                ->take(10)
                ->get();
                
            if ($stores->count() == 0) {
                try {
                    $geocodeResponse = \Illuminate\Support\Facades\Http::withHeaders([
                        'User-Agent' => 'Qrinto/1.0 (contact@qrinto.com)'
                    ])->timeout(5)->get("https://nominatim.openstreetmap.org/search", [
                        'q' => $query,
                        'format' => 'jsonv2',
                        'limit' => 1
                    ]);

                    if ($geocodeResponse->successful()) {
                        $geocodeData = $geocodeResponse->json();
                        if (!empty($geocodeData) && isset($geocodeData[0]['lat']) && isset($geocodeData[0]['lon'])) {
                            $searchLat = $geocodeData[0]['lat'];
                            $searchLon = $geocodeData[0]['lon'];

                            $stores = \App\Models\Store::where('is_active', true)
                                ->whereNotNull('lat')
                                ->whereNotNull('lon')
                                ->selectRaw("*,
                                ( 3959 * acos( cos( radians(?) ) *
                                cos( radians( lat ) ) *
                                cos( radians( lon ) - radians(?) ) +
                                sin( radians(?) ) *
                                sin( radians( lat ) ) )
                                ) AS distance", [$searchLat, $searchLon, $searchLat])
                                ->having('distance', '<=', 300)
                                ->orderBy('distance')
                                ->take(3)
                                ->get();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Geocoding search error: " . $e->getMessage());
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['stores' => $stores]);
        }

        return view($this->getViewPath('find-store'), compact('stores', 'query', 'nearbyStores', 'geolocationAttempted'));
    }

    private function getNearbyStoresFromCoords($lat, $lon)
    {
        try {
            return \App\Models\Store::where('is_active', true)
                ->whereNotNull('lat')
                ->whereNotNull('lon')
                ->selectRaw("*,
                ( 3959 * acos( cos( radians(?) ) *
                cos( radians( lat ) ) *
                cos( radians( lon ) - radians(?) ) +
                sin( radians(?) ) *
                sin( radians( lat ) ) )
                ) AS distance", [$lat, $lon, $lat])
                ->having('distance', '<=', 300)
                ->orderBy('distance')
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            \Log::error("Nearby coords error: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Store Selection: Set store in session
     */
    public function setStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        session(['active_store_id' => $request->store_id]);

        return redirect()->route($this->getRoutePrefix() . 'index')->with('success', 'Store selected successfully!');
    }

    /**
     * AJAX: Validate and apply a coupon
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        $coupon = \App\Models\Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        if (!$coupon->is_active) {
            return response()->json(['success' => false, 'message' => 'This coupon is no longer active.']);
        }

        if ($coupon->store_id) {
            $activeStoreId = session('active_store_id');
            if ($activeStoreId && $coupon->store_id != $activeStoreId) {
                $storeName = $coupon->store->store_name ?? 'another store';
                return response()->json(['success' => false, 'message' => "This coupon is only valid for {$storeName}."]);
            }
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return response()->json(['success' => false, 'message' => 'This coupon is not active yet. It starts on ' . $coupon->starts_at->format('M d, Y') . '.']);
        }

        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
        }

        if ($coupon->min_order_amount) {
            $minAmount = \App\Services\CurrencyService::convert((float) $coupon->min_order_amount);
            if ($request->amount < $minAmount) {
                return response()->json(['success' => false, 'message' => 'Minimum order amount of ' . \App\Services\CurrencyService::format($minAmount) . ' required for this coupon.']);
            }
        }

        $discount = $coupon->calculateDiscount($request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied!',
            'discount' => $discount,
            'code' => $coupon->code
        ]);
    }

    /**
     * Qrinto Direct Checkout: View
     */
    public function qrinto()
    {
        if (!session()->has('active_store_id')) {
            return redirect()->route('flow.find-store');
        }

        $productTypes = \App\Models\ProductType::parents()
            ->active()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'title' => $parent->title,
                    'icon_svg' => $parent->icon_svg,
                    'subtypes' => $parent->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'title' => $child->title,
                            'price' => (float)$child->active_price,
                            'dimensions' => $child->width . ' x ' . $child->height . ' (' . strtoupper($child->unit) . ')',
                            'popular' => $child->sort_order === 1,
                        ];
                    })
                ];
            });

        $paypalClientId = config('services.paypal.client_id', env('PAYPAL_CLIENT_ID'));

        return view($this->getViewPath('qrinto'), [
            'productTypes' => $productTypes,
            'paypalClientId' => $paypalClientId
        ]);
    }

    /**
     * Qrinto Direct Checkout: Cash
     */
    public function qrintoCheckoutCash(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|exists:customer_uploads,id',
            'size_id' => 'required|exists:product_types,id',
            'quantity' => 'required|integer|min:1',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        try {
            $order = $this->storeCustomPrintOrder($request, 'cash', null, 'pending');
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Qrinto Cash Checkout Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Could not process order'], 500);
        }
    }

    /**
     * Qrinto Direct Checkout: PayPal Create Order
     */
    public function qrintoPaypalCreate(Request $request)
    {
        $request->validate([
            'size_id' => 'required|exists:product_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $type = \App\Models\ProductType::findOrFail($request->size_id);
        $total = number_format($type->active_price * $request->quantity, 2, '.', '');

        $accessToken = $this->getPaypalAccessToken();
        if (!$accessToken) return response()->json(['error' => 'PayPal Auth Failed'], 500);

        $apiBase = $this->getPaypalApiBase();

        $response = Http::withToken($accessToken)
            ->post("{$apiBase}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'description' => 'Custom Print (' . $type->name . ')',
                    'amount' => [
                        'currency_code' => CurrencyService::getCode(),
                        'value' => $total,
                    ],
                ]],
            ]);

        return response()->json($response->json(), $response->status());
    }

    /**
     * Qrinto Direct Checkout: PayPal Capture Order
     */
    public function qrintoPaypalCapture(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'upload_id' => 'required|exists:customer_uploads,id',
            'size_id' => 'required|exists:product_types,id',
            'quantity' => 'required|integer|min:1',
            'pickup_name' => 'required|string|max:255',
            'pickup_email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        $accessToken = $this->getPaypalAccessToken();
        $apiBase = $this->getPaypalApiBase();

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("{$apiBase}/v2/checkout/orders/{$request->paypal_order_id}/capture");

        if (!$response->successful() || ($response->json()['status'] ?? '') !== 'COMPLETED') {
            Log::error('Qrinto PayPal Capture Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(['error' => 'Payment failed'], 500);
        }

        $captureId = $response->json()['purchase_units'][0]['payments']['captures'][0]['id'] ?? $request->paypal_order_id;

        try {
            $order = $this->storeCustomPrintOrder($request, 'paypal', $captureId, 'paid');

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route($this->getRoutePrefix() . 'confirmation', $order->id),
            ]);
        } catch (\Exception $e) {
            Log::error('Qrinto PayPal Finalize Error: ' . $e->getMessage());
            return response()->json(['error' => 'Order storage failed'], 500);
        }
    }

    /**
     * Shared logic to store Custom Print orders without PDF generation
     */
    private function storeCustomPrintOrder($request, $paymentGateway, $paymentId = null, $paymentStatus = 'pending')
    {  
         
        $upload = CustomerUpload::findOrFail($request->upload_id);
        $type = \App\Models\ProductType::findOrFail($request->size_id);
        $quantity = (int)$request->quantity;
        $product = $request->product_id ? Product::find($request->product_id) : null;
        if($product && $product->store_id){
            $unitPrice = (float) $product->base_price;
        }else{
            $unitPrice = $type->active_price;
        }
        $total = $unitPrice * $quantity;
        $storeId = session('active_store_id');

        // Build descriptive label
        $parentName = $type->parent ? $type->parent->name : 'Custom Print';
        $fullLabel = $parentName . ': ' . $type->name;

        // Create the order
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'invoice_number' => Order::generateInvoiceNumber(),
            'status' => ($paymentStatus === 'paid') ? 'confirmed' : 'pending',
            'subtotal' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total' => $total,
            'currency' => CurrencyService::getCode(),
            'shipping_address' => [
                'type' => 'store_pickup',
                'name' => $request->pickup_name,
                'email' => $request->pickup_email,
                'phone' => $request->contact_number,
            ],
            'payment_gateway' => $paymentGateway,
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'paid_at' => ($paymentStatus === 'paid') ? now() : null,
            'fulfillment_type' => 'store_pickup',
            'store_id' => $storeId,
            'guest_email' => $request->pickup_email,
            'guest_phone' => $request->contact_number,
            'notes' => 'Custom Print Checkout | ' . $fullLabel,
            'special_instructions' => $request->special_instructions,
            'flow_data' => [
                'type' => 'custom_print',
                'product_type_id' => $type->parent_id ?? $type->id,
                'size_id' => $type->id,
                'size_label' => $type->name,
                'category_label' => $parentName,
                'dimensions' => $type->width . ' x ' . $type->height . ' ' . $type->unit,
                'price_per_unit' => $unitPrice,
                'quantity' => $quantity
            ],
        ]);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null, // No specific product template used
            'product_name' => 'Custom Print: ' . $fullLabel,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $total,
            'customization_data' => [
                'type' => 'direct_upload',
                'upload_id' => $upload->id,
                'dimensions' => $type->width . ' x ' . $type->height,
                'unit' => $type->unit,
                'size_label' => $type->name,
                'category_label' => $parentName
            ],
            'uploaded_images' => [
                $upload->file_path
            ],
        ]);

        // Send notifications (Email only, no PDF)
        $this->notifyCustomPrintOrder($order);

        return $order;
    }

    private function notifyCustomPrintOrder(Order $order)
    {
        // 1. Customer Email
        try {
            Mail::to($order->guest_email)->send(new QuickFlowOrderMail($order, false));
        } catch (\Exception $e) {
            Log::error("Custom Print Customer Email Error: " . $e->getMessage());
        }

        // 2. Admin/Store Email
        try {
            $adminEmail = config('mail.from.address', 'admin@qrinto.com');
            // We pass null for pdfPath
            Mail::to($adminEmail)->send(new QuickFlowOrderMail($order, true, null));

            if ($order->store && $order->store->email) {
                Mail::to($order->store->email)->send(new QuickFlowOrderMail($order, true, null));
            }
        } catch (\Exception $e) {
            Log::error("Custom Print Admin Email Error: " . $e->getMessage());
        }
    }

    // ─── PayPal Helpers ─────────────────────────────────────────────

    /**
     * Process Order Logic: PDF Generation & Emails
     */
   

    private function getPaypalApiBase(): string
    {
        $mode = env('PAYPAL_MODE', 'sandbox');
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function getPaypalAccessToken(): ?string
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $secret = env('PAYPAL_SECRET');
        $apiBase = $this->getPaypalApiBase();

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post("{$apiBase}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        Log::error('PayPal Auth Error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }
     private function processOrderAndNotify(Order $order, array $uploadIds, ?Product $product = null)
    {   
        // Prevent 500 error timeouts if SMTP server takes too long to fail
        set_time_limit(120);
        
        \Log::warning("number of pages  {$product->no_of_pages}");
        if($product->no_of_pages == 4){ 
            
            $orientation = ($product && $product->pdf_orientation) ? $product->pdf_orientation : 'landscape';

            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'background_image' => 'rotate_0',
                'frame_image' => 'rotate_180_plus',
                'overlay_image' => 'rotate_0',
            ];

            $portrait_imageTypes = [
                'frame_image' => 'rotate_90_minus',
                'overlay_image' => 'rotate_90_minus',
                'sample_image' => 'rotate_90_plus',
                'background_image' => 'rotate_90_plus',
            ];
            



            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;
            $mappedImages = [];
            $absolutePaths = [];

            foreach ($imageTypes as $key => $rotation) {
                $uploadId = $uploadIds[$key] ?? null;
                $localPath = null;

                if ($uploadId) {
                    $upload = CustomerUpload::find($uploadId);
                    if ($upload) {
                        $localPath = storage_path('app/public/' . $upload->file_path);
                        $mappedImages[$key] = $upload->file_path;
                    }
                }

                // Fallback to database image if not edited AND product exists
                if (empty($localPath) && $product) {
                    $dbPath = $product->getRawOriginal($key);
                    if ($dbPath) {
                        $localPath = storage_path('app/public/' . $dbPath);
                        if (!file_exists($localPath)) {
                            $localPath = public_path('storage/' . $dbPath);
                        }
                        $mappedImages[$key] = $dbPath;
                    }
                }

                if ($localPath && file_exists($localPath)) {
                    $rotatedAbsPath = $this->physicallyRotateImage($localPath, $rotation);
                    // DomPDF on Windows performs much better with forward slashes even for local paths
                    $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
                } else {
                    $absolutePaths[$key] = null;
                    \Log::warning("Print Image Missing: Key {$key} expected at {$localPath}");
                }
            }

            // Update Order Item with mapped images
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            if ($orderItem) {
                $orderItem->update([
                    'uploaded_images' => $mappedImages
                ]);
            }

            // 2. Generate PDF
            $flowData = $order->flow_data;
            $width = floatval($flowData['size_width'] ?? 3.5);
            $height = floatval($flowData['size_height'] ?? 5);

            // For direct uploads without product type dimensions, use flow_data if available
            if (isset($flowData['size_dimensions'])) {
                // dimensions often look like "3.5x5" or "4x6"
                $dims = explode('x', strtolower($flowData['size_dimensions']));
                if (count($dims) === 2) {
                    $width = floatval($dims[0]);
                    $height = floatval($dims[1]);
                }
            }

            $orientation = ($product && $product->pdf_orientation) ? $product->pdf_orientation : 'landscape';
            // Always display in portrait mode (Top/Bottom fold)
            $pdfWidth = min($width, $height);
            $pdfHeight = max($width, $height);

            // Dompdf works best with absolute local paths
            $pdf = PDF::loadView('quick-flow.pdf.design', [
                'images' => $absolutePaths,
                'rotations' => $imageTypes,
                'width' => $pdfWidth,
                'height' => $pdfHeight,
                'orientation' => $orientation
            ]);
            
            $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);

            $pdfName = 'Design_' . $order->order_number . '.pdf';
            $pdfDirectory = storage_path('app/public/orders/pdfs');
            if (!File::isDirectory($pdfDirectory)) {
                File::makeDirectory($pdfDirectory, 0755, true, true);
            }
            $pdfPath = $pdfDirectory . '/' . $pdfName;
            $pdf->save($pdfPath);        
        }elseif ($product->no_of_pages == 2) {
             // ── Double Page: two independent full-bleed images (Page 1 + Page 2) ──
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $flowData = session('quick_flow_data') ?? $order->flow_data ?? [];
            $widthVal = floatval($flowData['size_width'] ?? 5.00);
            $heightVal = floatval($flowData['size_height'] ?? 7.00);
            $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));

            $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'portrait';

            // if ($orientation === 'landscape') {
            //     $pdfWidthVal = $heightVal;
            //     $pdfHeightVal = $widthVal;
            // } else {
            //     $pdfWidthVal = $widthVal;
            //     $pdfHeightVal = $heightVal;
            // }

            $cssUnit = ($unit === 'inch') ? 'in' : $unit;

            // $ptsPerUnit = 72;
            // if ($unit === 'cm') {
            //     $ptsPerUnit = 72 / 2.54;
            // } elseif ($unit === 'mm') {
            //     $ptsPerUnit = 72 / 25.4;
            // } elseif ($unit === 'px' || $unit === 'pixel') {
            //     $ptsPerUnit = 0.75;
            // }

            // $pdfWidthPts = $pdfWidthVal * $ptsPerUnit;
            // $pdfHeightPts = $pdfHeightVal * $ptsPerUnit;

            // Rotation map for the 2-page flow.
            // Default to 'rotate_0' (no rotation) for full-bleed uploads.
            // Change individual values here ONLY if a specific slot/orientation
            // is confirmed to need correction (e.g. source asset stored sideways).
            $landscape_imageTypes = [
                'sample_image' => 'rotate_0',
                'frame_image'  => 'rotate_180_plus',
            ];
            $portrait_imageTypes = [
                'frame_image'  => 'rotate_90_minus',
                'sample_image' => 'rotate_90_minus',
            ]; 
            $slots = ['frame_image', 'sample_image'];
            $imageTypes = ($orientation === 'portrait') ? $portrait_imageTypes : $landscape_imageTypes;

            // Two ordered slots — customer edit first, else fall back to product image
            $slots = ['frame_image', 'sample_image'];
            $mappedImages = [];
            $absolutePaths = [];

            foreach ($slots as $key) {
                $uploadId = $uploadIds[$key] ?? null;
                $localPath = null;

                if ($uploadId) {
                    $upload = CustomerUpload::find($uploadId);
                    if ($upload) {
                        $localPath = storage_path('app/public/' . $upload->file_path);
                        $mappedImages[$key] = $upload->file_path;
                    }
                }

                if (empty($localPath) && $product) {
                    $dbPath = $product->getRawOriginal($key);
                    if ($dbPath) {
                        $localPath = storage_path('app/public/' . $dbPath);
                        if (!file_exists($localPath)) {
                            $localPath = public_path('storage/' . $dbPath);
                        }
                        $mappedImages[$key] = $dbPath;
                    }
                }

                if ($localPath && file_exists($localPath)) {
                    $rotation = $imageTypes[$key] ?? 'rotate_0';
                    $rotatedAbsPath = $this->physicallyRotateImage($localPath, $rotation);
                    $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
                } else {
                    $absolutePaths[$key] = null;
                    \Log::warning("Print Image Missing (double): Key {$key} expected at {$localPath}");
                }
            }

            if ($orderItem) {
                $orderItem->update(['uploaded_images' => $mappedImages]);
            }
            $pdfOrientation = ($product && $product->pdf_orientation) ? $product->pdf_orientation : 'landscape';
            $flowData = $order->flow_data;
            $width = floatval($flowData['size_width'] ?? 3.5);
            $height = floatval($flowData['size_height'] ?? 5);
            $pdfWidth = min($width, $height);
            $pdfHeight = max($width, $height);

            // Swap width/height for 2-page layout (same as viewPdf)
            $a = $pdfWidth;
            $pdfWidth = $pdfHeight;
            $pdfHeight = $a;

            $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));
            $cssUnit = ($unit === 'inch') ? 'in' : $unit;

            $pdf = PDF::loadView('quick-flow.pdf.design-double', [
                'images' => $absolutePaths,
                'rotations' => $imageTypes,
                'width' => $pdfWidth,
                'height' => $pdfHeight,
                'cssUnit' => $cssUnit,
                'orientation' => $pdfOrientation,
            ]);
            $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);

            $pdfName = 'Design_' . $order->order_number . '.pdf';
            $pdfDirectory = storage_path('app/public/orders/pdfs');
            if (!File::isDirectory($pdfDirectory)) {
                File::makeDirectory($pdfDirectory, 0755, true, true);
            }
            $pdfPath = $pdfDirectory . '/' . $pdfName;
            $pdf->save($pdfPath);
        }else{
            $orderItem = OrderItem::where('order_id', $order->id)->first();
            $flowData = session('quick_flow_data') ?? $order->flow_data ?? [];
            $widthVal = floatval($flowData['size_width'] ?? 5.00);
            $heightVal = floatval($flowData['size_height'] ?? 7.00);
            $unit = strtolower(trim($flowData['size_unit'] ?? 'inch'));
            
            $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'portrait';
            
            if ($orientation === 'landscape') {
                $pdfWidthVal = $heightVal;
                $pdfHeightVal = $widthVal;
            } else {
                $pdfWidthVal = $widthVal;
                $pdfHeightVal = $heightVal;
            }

            $cssUnit = ($unit === 'inch') ? 'in' : $unit;

            $ptsPerUnit = 72;
            if ($unit === 'cm') {
                $ptsPerUnit = 72 / 2.54;
            } elseif ($unit === 'mm') {
                $ptsPerUnit = 72 / 25.4;
            } elseif ($unit === 'px' || $unit === 'pixel') {
                $ptsPerUnit = 0.75;
            }
            
            $pdfWidthPts = $pdfWidthVal * $ptsPerUnit;
            $pdfHeightPts = $pdfHeightVal * $ptsPerUnit;

            $singleUploadId = !empty($uploadIds) ? reset($uploadIds) : null;
            $absolutePath = null;
            $relPath = null;
            
            if ($singleUploadId) {
                $upload = CustomerUpload::find($singleUploadId);
                if ($upload) {
                    $relPath = $upload->file_path;
                    $localPath = storage_path('app/public/' . $upload->file_path);
                    if (file_exists($localPath)) {
                        $absolutePath = str_replace('\\', '/', $localPath);
                    }
                }
            }

            if (!$absolutePath && $orderItem && !empty($orderItem->uploaded_images)) {
                $firstImage = reset($orderItem->uploaded_images);
                $relPath = $firstImage;
                $localPath = storage_path('app/public/' . $firstImage);
                if (file_exists($localPath)) {
                    $absolutePath = str_replace('\\', '/', $localPath);
                }
            }

            if ($orderItem && $relPath) {
                $orderItem->update(['uploaded_images' => [$relPath]]);
            }

            $pdf = PDF::loadView('quick-flow.pdf.design-single', [
                'image' => $absolutePath,
                'width' => $pdfWidthVal,
                'height' => $pdfHeightVal,
                'cssUnit' => $cssUnit
            ]);
             
            $pdf->setPaper([0, 0, $pdfWidthPts, $pdfHeightPts]);

            $pdfName = 'Design_' . $order->order_number . '.pdf';
            $pdfDirectory = storage_path('app/public/orders/pdfs');
            if (!File::isDirectory($pdfDirectory)) {
                File::makeDirectory($pdfDirectory, 0755, true, true);
            }
            $pdfPath = $pdfDirectory . '/' . $pdfName;
            $pdf->save($pdfPath);
            

        }
        // 3. User Confirmation Email
        try {
            Mail::to($order->guest_email)->send(new QuickFlowOrderMail($order, false));
        } catch (\Exception $e) {
            \Log::error("User Email Error: " . $e->getMessage());
        }

        // 4. Admin Notification Email (with PDF)
        try {
            $adminEmail = config('mail.from.address', 'admin@example.com');
            Mail::to($adminEmail)->send(new QuickFlowOrderMail($order, true, $pdfPath));

            // 5. CC Store Email if available
            if ($order->store && $order->store->email) {
                Mail::to($order->store->email)->send(new QuickFlowOrderMail($order, true, $pdfPath));
            }
        } catch (\Exception $e) {
            \Log::error("Admin Email Error: " . $e->getMessage());
        }

        // Update item with pdf path
        if ($orderItem) {
            $orderItem->update(['pdf_path' => 'orders/pdfs/' . $pdfName]);
        }
    }
    private function physicallyRotateImage($sourcePath, $rotationString)
    {
        $degrees = 0;
        if ($rotationString === 'rotate_90_minus') {
            $degrees = 90; // Counter-clockwise 90 for -90deg rotation
        } elseif ($rotationString === 'rotate_90_plus') {
            $degrees = 270; // Counter-clockwise 270 for +90deg rotation
        } elseif ($rotationString === 'rotate_180_minus' || $rotationString === 'rotate_180_plus') {
            $degrees = 180;
        }

        if ($degrees === 0 || !$sourcePath || !file_exists($sourcePath)) {
            return $sourcePath;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $image = null;

        if (in_array($extension, ['jpg', 'jpeg'])) {
            $image = @imagecreatefromjpeg($sourcePath);
        } elseif ($extension === 'png') {
            $image = @imagecreatefrompng($sourcePath);
        } elseif ($extension === 'webp') {
            $image = @imagecreatefromwebp($sourcePath);
        }

        if (!$image) {
            return $sourcePath;
        }

        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $rotated = imagerotate($image, $degrees, $transparent);

        if (in_array($extension, ['png', 'webp'])) {
            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);
        }

        $tempDir = storage_path('app/public/temp_rotations');
        if (!\Illuminate\Support\Facades\File::isDirectory($tempDir)) {
            \Illuminate\Support\Facades\File::makeDirectory($tempDir, 0755, true, true);
        }

        $filename = 'rot_' . $degrees . '_' . basename($sourcePath);
        $tempPath = $tempDir . '/' . $filename;

        if (in_array($extension, ['jpg', 'jpeg'])) {
            imagejpeg($rotated, $tempPath, 100);
        } elseif ($extension === 'png') {
            imagepng($rotated, $tempPath);
        } elseif ($extension === 'webp') {
            imagewebp($rotated, $tempPath, 100);
        }

        imagedestroy($image);
        imagedestroy($rotated);

        return $tempPath;
    }
}
