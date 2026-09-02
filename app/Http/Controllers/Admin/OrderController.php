<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdateMail;

class OrderController extends Controller
{
     public function index(Request $request)
    {
        $query = Order::with('user', 'items', 'store');

        // Store Isolation: If the logged-in user is a store admin (has store_id)
        if (auth()->user()->store_id) {
            $query->where('store_id', auth()->user()->store_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        $query->where('created_at', '>=', now()->subDays(30));
        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Advance an order one step along the shared pickup state machine
     * (New -> Printing -> Ready for pickup -> Picked up). This is the single
     * action button on each store-queue card. Only the transition into
     * "Ready for pickup" notifies the customer.
     */
    public function advanceStatus(Request $request, Order $order)
    {
        // Store Isolation
        if (auth()->user()->store_id && $order->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized access to this store\'s order.');
        }

        $action = $order->forward_action;

        if (!$action) {
            return redirect()->back()->with('error', 'This order has no further step to advance.');
        }

        $oldStatus = $order->status;
        $newStatus = $action['to'];

        $order->update(['status' => $newStatus]);

        \App\Models\OrderStatusHistory::create([
            'order_id'   => $order->id,
            'user_id'    => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes'      => 'Advanced to ' . ($action['label'] ?? $newStatus) . ' via store queue',
        ]);

        $mailFailed = false;

        // Only the "Ready for pickup" transition sends the customer email.
        if (!empty($action['email'])) {
            $customerEmail = $order->guest_email
                ?? ($order->shipping_address['pickup_email'] ?? ($order->shipping_address['email'] ?? ($order->user->email ?? null)));

            if ($customerEmail) {
                try {
                    Mail::to($customerEmail)->send(new OrderStatusUpdateMail($order, null));
                } catch (\Throwable $e) {
                    $mailFailed = true;
                    \Log::error('Order ready email failed to send', [
                        'order_id' => $order->id,
                        'email'    => $customerEmail,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($mailFailed) {
            return redirect()->back()->with('warning', 'Order marked ready, but the customer email could not be sent.');
        }

        return redirect()->back()->with('success', 'Order moved to ' . ($action['label'] ?? ucfirst($newStatus)) . '.');
    }

    /**
     * Render the PDF design template as HTML for visual preview/debugging.
     * Uses the same view (quick-flow.pdf.design) but renders as a browser page
     * so you can tweak the layout without regenerating the PDF each time.
     */
    public function show_pdf(Order $order)
    {
        $order->load('items.product');
        $item = $order->items->first();

        if (!$item) {
            abort(404, 'No order item found.');
        }

        // Rebuild the image paths (same mapping used in processOrderAndNotify)
        $uploadedImages = $item->uploaded_images ?? [];
        $product = $item->product;

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
        $absolutePaths = [];

        foreach ($imageTypes as $key => $rotation) {
            $sourcePath = null;
            // First try uploaded/edited images stored on the order item
            if (!empty($uploadedImages[$key])) {
                $sourcePath = storage_path('app/public/' . $uploadedImages[$key]);
            }
            // Fallback to product's default image
            elseif ($product) {
                $dbPath = $product->getRawOriginal($key);
                if ($dbPath) {
                    $sourcePath = storage_path('app/public/' . $dbPath);
                }
            }
            
            if ($sourcePath && file_exists($sourcePath)) {
                $rotatedAbsPath = $this->physicallyRotateImage($sourcePath, $rotation);
                
                if ($rotatedAbsPath === $sourcePath) {
                    // Not rotated, use original public path
                    if (!empty($uploadedImages[$key])) {
                        $absolutePaths[$key] = asset('storage/' . $uploadedImages[$key]);
                    } elseif ($product && $product->getRawOriginal($key)) {
                        $absolutePaths[$key] = asset('storage/' . $product->getRawOriginal($key));
                    }
                } else {
                    // Rotated image is in temp_rotations
                    $relativePath = 'temp_rotations/' . basename($rotatedAbsPath);
                    $absolutePaths[$key] = asset('storage/' . $relativePath);
                }
            } else {
                $absolutePaths[$key] = null;
            }
        }

        // Rebuild the PDF dimensions from flow_data (same logic as processOrderAndNotify)
        $flowData = $order->flow_data ?? [];
        $width = floatval($flowData['size_width'] ?? 3.5);
        $height = floatval($flowData['size_height'] ?? 5);

        if (isset($flowData['size_dimensions'])) {
            $dims = explode('x', strtolower($flowData['size_dimensions']));
            if (count($dims) === 2) {
                $width = floatval($dims[0]);
                $height = floatval($dims[1]);
            }
        }

        // $orientation already computed above
        // Always display in portrait mode (Top/Bottom fold)
        $pdfWidth = $width;
        $pdfHeight = $height;

        // Render a browser-friendly preview (NOT the raw DomPDF template)
        return view('admin.orders.pdf-preview', [
            'order'       => $order,
            'item'        => $item,
            'images'      => $absolutePaths,
            'rotations'   => $imageTypes,
            'width'       => $pdfWidth,
            'height'      => $pdfHeight,
            'orientation' => $orientation,
            'flowData'    => $flowData,
        ]);
    }

    /**
     * Render the raw PDF design template as HTML for the exact DomPDF layout testing.
     * Accessible via /admin/orders/{order}/realtime_pdf
     */
    public function realtimePdf(Order $order)
    {
        $order->load('items.product');
        $item = $order->items->first();

        if (!$item) {
            abort(404, 'No order item found.');
        }

        $uploadedImages = $item->uploaded_images ?? [];
        $product = $item->product;

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
        $absolutePaths = [];

        foreach ($imageTypes as $key => $rotation) {
            $sourcePath = null;
            if (!empty($uploadedImages[$key])) {
                $sourcePath = storage_path('app/public/' . $uploadedImages[$key]);
            } elseif ($product) {
                $dbPath = $product->getRawOriginal($key);
                if ($dbPath) {
                    $sourcePath = storage_path('app/public/' . $dbPath);
                }
            }
            
            if ($sourcePath && file_exists($sourcePath)) {
                $rotatedAbsPath = $this->physicallyRotateImage($sourcePath, $rotation);
                
                if ($rotatedAbsPath === $sourcePath) {
                    // Not rotated, use original public path
                    if (!empty($uploadedImages[$key])) {
                        $absolutePaths[$key] = asset('storage/' . $uploadedImages[$key]);
                    } elseif ($product && $product->getRawOriginal($key)) {
                        $absolutePaths[$key] = asset('storage/' . $product->getRawOriginal($key));
                    }
                } else {
                    // Rotated image is in temp_rotations
                    $relativePath = 'temp_rotations/' . basename($rotatedAbsPath);
                    $absolutePaths[$key] = asset('storage/' . $relativePath);
                }
            } else {
                $absolutePaths[$key] = null;
            }
        }

        $flowData = $order->flow_data ?? [];
        $width = floatval($flowData['size_width'] ?? 3.5);
        $height = floatval($flowData['size_height'] ?? 5);

        if (isset($flowData['size_dimensions'])) {
            $dims = explode('x', strtolower($flowData['size_dimensions']));
            if (count($dims) === 2) {
                $width = floatval($dims[0]);
                $height = floatval($dims[1]);
            }
        }

        $pdfWidth = min($width, $height);
        $pdfHeight = max($width, $height);

        return view('quick-flow.pdf.design', [
            'images'      => $absolutePaths,
            'rotations'   => $imageTypes,
            'width'       => $pdfWidth,
            'height'      => $pdfHeight,
            'orientation' => $orientation
        ]);
    }

    public function show(Order $order)
    {
        // Store Isolation
        if (auth()->user()->store_id && $order->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized access to this store\'s order.');
        }

        // Automatic PDF self-healing: Check & generate missing PDFs for any items in this order
        try {
            app(\App\Services\OrderPdfService::class)->ensureOrderPdfsExist($order);
        } catch (\Throwable $e) {
            \Log::error("Admin Order Show PDF Auto-Repair Error: " . $e->getMessage(), ['order_id' => $order->id]);
        }

        $order->load('user', 'items.product', 'payments', 'statusHistories.user', 'store');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Download the print-ready PDF for an order (generated at placement).
     * Ensures the file exists first, then streams it as an attachment.
     */
    public function downloadPdf(Order $order)
    {
        // Store Isolation
        if (auth()->user()->store_id && $order->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized access to this store\'s order.');
        }

        // Make sure the PDF(s) exist (self-heals if missing).
        try {
            app(\App\Services\OrderPdfService::class)->ensureOrderPdfsExist($order);
        } catch (\Throwable $e) {
            \Log::error('Order PDF download self-heal failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        $order->loadMissing('items');

        $item = $order->items->firstWhere(fn($i) => !empty($i->pdf_path));

        if (!$item || empty($item->pdf_path)) {
            return redirect()->back()->with('error', 'No print-ready PDF is available for this order yet.');
        }

        $absPath = storage_path('app/public/' . ltrim($item->pdf_path, '/'));

        if (!is_file($absPath)) {
            return redirect()->back()->with('error', 'The print-ready PDF could not be found on disk.');
        }

        return response()->download($absPath, 'Design_' . $order->order_number . '.pdf');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
            'tracking_number' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;
        $mailFailed = false;

        $updates = [
            'status' => $newStatus,
            'admin_notes' => $request->admin_notes ?? $order->admin_notes
        ];

        if ($request->filled('payment_status')) {
            $updates['payment_status'] = $request->payment_status;
            if ($request->payment_status === 'paid' && !$order->paid_at) {
                $updates['paid_at'] = now();
            }
        }

        $order->update($updates);

        if ($request->filled('tracking_number')) {
            $order->update([
                'tracking_number' => $request->tracking_number,
                'tracking_url' => $request->tracking_url,
            ]);
        }

        if ($oldStatus !== $newStatus || $request->filled('admin_notes')) {
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notes' => $request->admin_notes,
            ]);

            // Trigger Email to Customer on Status Change
            if ($oldStatus !== $newStatus) {
                $customerEmail = $order->guest_email ?? ($order->shipping_address['pickup_email'] ?? ($order->user->email ?? null));
                if ($customerEmail) {
                    // Don't let a mail/SMTP failure roll back or 500 the status update.
                    try {
                        Mail::to($customerEmail)->send(new OrderStatusUpdateMail($order, $request->admin_notes));
                    } catch (\Throwable $e) {
                        $mailFailed = true;
                        \Log::error('Order status email failed to send', [
                            'order_id' => $order->id,
                            'email' => $customerEmail,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $redirect = redirect()->route('admin.orders.show', $order);

        if ($mailFailed) {
            return $redirect->with('warning', 'Order status updated, but the customer notification email could not be sent.');
        }

        return $redirect->with('success', 'Order status updated successfully');
    }

    /**
     * Undo steps the order back exactly ONE canonical pickup stage
     * (Done → Ready, Ready → Printing, Printing → New) - per the spec.
     * Never sends a retraction email.
     */
    public function undoStatus(Request $request, Order $order)
    {
        $currentStage = $order->queue_stage;

        // Target DB status for one stage back. Values must be members of the
        // orders.status ENUM.
        $target = match ($currentStage) {
            Order::STAGE_DONE     => 'delivered_store', // Done → Ready
            Order::STAGE_READY    => 'printing',        // Ready → Printing
            Order::STAGE_PRINTING => 'pending',         // Printing → New
            default               => null,
        };

        if (!$target) {
            return redirect()->back()->with('error', 'Nothing to undo from this state.');
        }

        $oldStatus = $order->status;
        $order->update(['status' => $target]);

        \App\Models\OrderStatusHistory::create([
            'order_id'   => $order->id,
            'user_id'    => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $target,
            'notes'      => 'Status undone via store panel (no retraction email sent)',
        ]);

        return redirect()->back()->with('success', 'Order moved back one step.');
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

    public function duplicate(Request $request, Order $order)
    {
        // Store Isolation
        if (auth()->user()->store_id && $order->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized access to this store\'s order.');
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $newQuantity = intval($request->quantity);

        // Replicate Order
        $newOrder = $order->replicate();
        $newOrder->order_number = Order::generateOrderNumber();
        $newOrder->invoice_number = Order::generateInvoiceNumber();
        $newOrder->status = 'pending';
        $newOrder->payment_status = 'pending';
        $newOrder->paid_at = null;
        $newOrder->tracking_number = null;
        $newOrder->tracking_url = null;
        $newOrder->estimated_delivery_date = null;
        $newOrder->print_job_id = null;

        $originalSubtotal = floatval($order->subtotal);
        if ($originalSubtotal > 0) {
            $newSubtotal = 0;
            foreach ($order->items as $item) {
                $newSubtotal += floatval($item->unit_price) * $newQuantity;
            }
            $scale = $newSubtotal / $originalSubtotal;

            $newOrder->subtotal = $newSubtotal;
            $newOrder->discount_amount = floatval($order->discount_amount) * $scale;
            $newOrder->tax_amount = floatval($order->tax_amount) * $scale;
            $newOrder->shipping_amount = floatval($order->shipping_amount); // Shipping flat
            $newOrder->total = $newOrder->subtotal - $newOrder->discount_amount + $newOrder->tax_amount + $newOrder->shipping_amount;
        } else {
            $newOrder->subtotal = 0;
            $newOrder->discount_amount = 0;
            $newOrder->tax_amount = 0;
            $newOrder->shipping_amount = 0;
            $newOrder->total = 0;
        }

        // Update flow_data quantity if present
        $flowData = $newOrder->flow_data;
        if (is_array($flowData)) {
            $flowData['quantity'] = $newQuantity;
            $newOrder->flow_data = $flowData;
        }

        $newOrder->save();

        // Replicate Items
        foreach ($order->items as $item) {
            $newItem = $item->replicate();
            $newItem->order_id = $newOrder->id;
            $newItem->quantity = $newQuantity;
            $newItem->total_price = floatval($item->unit_price) * $newQuantity;

            // Handle PDF duplication if a PDF file exists
            if ($item->pdf_path) {
                $oldPdfPath = storage_path('app/public/' . $item->pdf_path);
                if (file_exists($oldPdfPath)) {
                    $newPdfName = 'Design_' . $newOrder->order_number . '.pdf';
                    $newPdfRelPath = 'orders/pdfs/' . $newPdfName;
                    $newPdfPath = storage_path('app/public/' . $newPdfRelPath);
                    
                    // Make directory if not exists
                    $pdfDirectory = dirname($newPdfPath);
                    if (!\Illuminate\Support\Facades\File::isDirectory($pdfDirectory)) {
                        \Illuminate\Support\Facades\File::makeDirectory($pdfDirectory, 0755, true, true);
                    }
                    
                    copy($oldPdfPath, $newPdfPath);
                    $newItem->pdf_path = $newPdfRelPath;
                }
            }

            $newItem->save();
        }

        // Create status history log
        \App\Models\OrderStatusHistory::create([
            'order_id' => $newOrder->id,
            'user_id' => auth()->id(),
            'old_status' => null,
            'new_status' => 'pending',
            'notes' => 'Duplicated from Order #' . $order->order_number,
        ]);

        // Send Confirmation Emails (User + Admin/Store)
        $newPdfPath = isset($newPdfRelPath) ? storage_path('app/public/' . $newPdfRelPath) : null;
        
        try {
            if ($newOrder->guest_email) {
                Mail::to($newOrder->guest_email)->send(new \App\Mail\QuickFlowOrderMail($newOrder, false));
            }
        } catch (\Exception $e) {
            \Log::error("Duplicate Order User Email Error: " . $e->getMessage());
        }

        try {
            $adminEmail = config('mail.from.address', 'admin@example.com');
            Mail::to($adminEmail)->send(new \App\Mail\QuickFlowOrderMail($newOrder, true, $newPdfPath));
            if ($newOrder->store && $newOrder->store->email) {
                Mail::to($newOrder->store->email)->send(new \App\Mail\QuickFlowOrderMail($newOrder, true, $newPdfPath));
            }
        } catch (\Exception $e) {
            \Log::error("Duplicate Order Admin Email Error: " . $e->getMessage());
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order duplicated successfully as Order #' . $newOrder->order_number);
    }
}
