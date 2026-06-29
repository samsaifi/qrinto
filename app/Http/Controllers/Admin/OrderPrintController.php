<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPrintJob;
use App\Models\Order;
use App\Models\PrintLog;
use App\Models\Store;
use Illuminate\Http\Request;

class OrderPrintController extends Controller
{
    /**
     * Send a print command for the given order to the selected store's printer.
     */
    public function sendPrint(Request $request, Order $order)
    {
        $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'print_type' => 'nullable|string|in:design,invoice',
        ]);

        $store = Store::findOrFail($request->store_id);

        if (!$store->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This store is currently inactive.',
            ], 422);
        }

        // Create a print log entry for history
        $printLog = \App\Models\PrintLog::create([
            'order_id' => $order->id,
            'store_id' => $store->id,
            'user_id'  => auth()->id(),
            'status'   => 'queued',
        ]);

        try {
            // If using the Local Print Agent, we save the job to the database instead
            // of trying to send it directly from this server.
            $printType = $request->input('print_type', 'design');
            
            $order->load(['items.product', 'user']);
            $item = $order->items->first();
            $assetUrl = '';
            
            if ($item) {
                if (!empty($item->pdf_path)) {
                    $assetUrl = asset('storage/' . $item->pdf_path);
                } elseif (!empty($item->uploaded_images) && is_array($item->uploaded_images) && count($item->uploaded_images) > 0) {
                    $assetUrl = asset('storage/' . $item->uploaded_images[0]);
                } elseif (!empty($item->customization_data['preview_url'])) {
                    $assetUrl = $item->customization_data['preview_url'];
                }
            }

            // Save the job for the Python Print Agent to pick up
            \App\Models\PrintJob::create([
                'job_id'        => uniqid('JOB-'),
                'order_id'      => $order->id,
                'store_id'      => $store->id,
                'asset_url'     => $assetUrl,
                'media_size'    => $store->paper_size ?? 'A4',
                'product_name'  => $item ? $item->product_name : 'Design Print',
                'quantity'      => $item ? $item->quantity : 1,
                'customer_name' => $order->user ? $order->user->name : ($order->guest_email ?? 'Guest'),
                'status'        => 'pending',
            ]);

            // We comment out the old direct-server FTP job because cPanel can't reach the store
            // SendPrintJob::dispatch($order, $store, $printLog, $printType);

            return response()->json([
                'success' => true,
                'message' => "Print job saved for the Print Agent to process at {$store->store_name}.",
                'data'    => [
                    'print_log_id' => $printLog->id,
                    'store_name'   => $store->store_name,
                ],
            ]);
        } catch (\Exception $e) {
            $printLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Print error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show a dedicated print page for the order's design image.
     */
    public function printPage(Order $order)
    {
        $order->load(['items.product', 'store']);

        $designUrl = null;
        $item = $order->items->first();
        if ($item) {
            // If PDF exists, redirect to it
            if (!empty($item->pdf_path)) {
                return redirect(asset('storage/' . $item->pdf_path));
            }

            $uploadedImages = $item->uploaded_images;
            if ($uploadedImages && is_array($uploadedImages) && count($uploadedImages) > 0) {
                $designUrl = asset('storage/' . $uploadedImages[0]);
            } elseif (!empty($item->customization_data['preview_url'])) {
                $designUrl = $item->customization_data['preview_url'];
            }
        }

        return view('quick-flow.print', compact('order', 'designUrl'));
    }
}
