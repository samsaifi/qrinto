<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\PrintLog;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public Order $order,
        public Store $store,
        public PrintLog $printLog,
        public string $printType = 'design',
    ) {}

    public function handle(): void
    {
        $order = $this->order->load('items.product', 'user');
        $store = $this->store;

        try {
            $printData = $this->generatePrintContent($order, $store);

            // ------------------------------------------------------------------
            // FTP-based printer integration:
            // Upload the generated print file directly to the printer's FTP server.
            // The printer picks up files placed in its hot-folder automatically.
            // ------------------------------------------------------------------
            if (!$store->hasFtpConfig()) {
                throw new \RuntimeException(
                    "<div class='text-left space-y-3'>" .
                        "<p class='font-semibold text-red-600 border-b pb-2'>No FTP configuration for store: {$store->store_name}.</p>" .
                        "<div><strong class='text-surface-800'>How to Fix:</strong>" .
                        "<ul class='list-decimal pl-5 mt-1 space-y-1 text-surface-600'>" .
                        "<li>Go to <strong>Store Settings → Printer FTP Configuration</strong>.</li>" .
                        "<li>Enter the printer's FTP Host, Username, and Password.</li>" .
                        "<li>Set the Remote Path where print files should be uploaded.</li>" .
                        "<li>Click <strong>Test Connection</strong> to verify the setup.</li>" .
                        "</ul></div>" .
                        "</div>"
                );
            }

            // Determine the file extension based on print type
            $extension = 'ps'; // PostScript by default for printers
            if ($this->printType === 'invoice' && in_array($store->paper_size, ['Receipt_80mm', 'Receipt_58mm'])) {
                $extension = 'txt';
            } elseif (in_array(strtolower($store->printer_type), ['inkjet', 'laserjet', 'laser'])) {
                $extension = 'pdf';
            }

            // Generate a unique filename for the print job
            $fileName = "order_{$order->order_number}_{$this->printType}_" . now()->format('YmdHis') . ".{$extension}";

            // Save the print data to a temporary local file
            $tempPath = storage_path("app/print_jobs/{$fileName}");
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            file_put_contents($tempPath, $printData);

            // Connect to FTP and upload
            $connection = @ftp_connect($store->ftp_host, $store->ftp_port ?? 21, 30);
            if (!$connection) {
                throw new \RuntimeException(
                    "<div class='text-left space-y-3'>" .
                        "<p class='font-semibold text-red-600 border-b pb-2'>Cannot connect to FTP server for {$store->store_name}.</p>" .
                        "<div><strong>FTP Host:</strong> {$store->ftp_host}:{$store->ftp_port}</div>" .
                        "<div><strong class='text-surface-800'>How to Fix:</strong>" .
                        "<ul class='list-decimal pl-5 mt-1 space-y-1 text-surface-600'>" .
                        "<li>Verify the printer/FTP server is powered on and connected to the network.</li>" .
                        "<li>Check the FTP Host and Port in Store settings.</li>" .
                        "<li>Ensure no firewall is blocking FTP connections.</li>" .
                        "</ul></div></div>"
                );
            }

            $login = @ftp_login($connection, $store->ftp_username, $store->ftp_password ?? '');
            if (!$login) {
                ftp_close($connection);
                throw new \RuntimeException(
                    "FTP login failed for {$store->store_name}. Please check the FTP username and password in Store settings."
                );
            }

            // Enable passive mode if configured
            if ($store->ftp_passive_mode) {
                ftp_pasv($connection, true);
            }

            // Build the remote file path
            $remotePath = rtrim($store->ftp_remote_path ?? '/', '/') . '/' . $fileName;

            // Upload the file
            $uploaded = @ftp_put($connection, $remotePath, $tempPath, FTP_BINARY);
            ftp_close($connection);

            // Clean up temp file
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            if (!$uploaded) {
                throw new \RuntimeException(
                    "FTP upload failed for {$store->store_name}. Could not upload file to: {$remotePath}. " .
                    "Please verify the remote path exists and the FTP user has write permissions."
                );
            }

            // Update log as sent
            $this->printLog->update([
                'status'     => 'sent',
                'printed_at' => now(),
            ]);

            // Update order status
            $order->update(['status' => 'printing']);

            Log::info("Print file uploaded via FTP to {$store->store_name} ({$store->ftp_host}) for order #{$order->order_number}: {$remotePath}");
        } catch (\Exception $e) {
            Log::error("Print failed for order #{$order->order_number}: " . $e->getMessage());

            // Clean up temp file on failure
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            $this->printLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e; // re-throw so queue retries
        }
    }

    /**
     * Generate print content based on printer/paper type.
     */
    protected function generatePrintContent(Order $order, Store $store): string
    {
        // If explicitly requested to print the invoice text
        if ($this->printType === 'invoice') {
            if (in_array($store->paper_size, ['Receipt_80mm', 'Receipt_58mm'])) {
                return $this->generateEscPosReceipt($order, $store);
            }
            return $this->generateTextInvoice($order, $store);
        }

        // For Inkjet/Laser printers (Default: print design image as PDF)
        if (in_array(strtolower($store->printer_type), ['inkjet', 'laserjet', 'laser'])) {
            return $this->generateImagePdf($order, $store);
        }

        // Fallback if paper type doesn't match
        if (in_array($store->paper_size, ['Receipt_80mm', 'Receipt_58mm'])) {
            return $this->generateEscPosReceipt($order, $store);
        }

        return $this->generateTextInvoice($order, $store);
    }

    /**
     * Generate a PDF with the customized image for laser/inkjet printers.
     * PDF is the universal format understood by virtually all network printers via RAW/JetDirect on port 9100.
     */
    protected function generateImagePdf(Order $order, Store $store): string
    {
        $item = $order->items->first();
        if ($item && !empty($item->pdf_path)) {
            $absPdfPath = storage_path('app/public/' . $item->pdf_path);
            if (file_exists($absPdfPath)) {
                Log::info("Using pre-generated PDF for order #{$order->order_number}: {$item->pdf_path}");
                return file_get_contents($absPdfPath);
            }
        }

        $imageData = null;
        $mimeType  = 'image/jpeg';

        if ($item) {
            // Try uploaded_images first (the edited/composite image)
            $imagePath = $item->uploaded_images[0] ?? null;
            if ($imagePath) {
                $absPath = storage_path('app/public/' . $imagePath);
                if (file_exists($absPath)) {
                    $imageData = file_get_contents($absPath);
                    $mimeType  = mime_content_type($absPath) ?: 'image/jpeg';
                }
            }

            // Fallback to preview_url
            if (!$imageData) {
                $previewUrl = $item->customization_data['preview_url'] ?? null;
                if ($previewUrl) {
                    $parsed = parse_url($previewUrl, PHP_URL_PATH);
                    if ($parsed && preg_match('/\/storage\/(.+)$/', $parsed, $matches)) {
                        $localPath = storage_path('app/public/' . ltrim($matches[1], '/'));
                        if (file_exists($localPath)) {
                            $imageData = file_get_contents($localPath);
                            $mimeType  = mime_content_type($localPath) ?: 'image/jpeg';
                        }
                    }
                }
            }
        }

        if (!$imageData) {
            throw new \RuntimeException('No printable image found for this order.');
        }

        // Determine paper size from store settings
        $paperSize   = 'A4';
        $orientation = 'portrait';
        $storePaper  = strtolower($store->paper_size ?? 'A4');

        if (str_contains($storePaper, 'letter')) {
            $paperSize = 'letter';
        } elseif (str_contains($storePaper, 'a5')) {
            $paperSize = 'A5';
        } elseif (str_contains($storePaper, '4x6') || str_contains($storePaper, '4r')) {
            $paperSize = [0, 0, 288, 432]; // 4x6 inches in points
        }

        // Detect landscape if image is wider than tall
        $imgInfo = @getimagesizefromstring($imageData);
        if ($imgInfo && $imgInfo[0] > $imgInfo[1]) {
            $orientation = 'landscape';
        }

        // Encode image as base64 for embedding in HTML
        $base64Img = base64_encode($imageData);
        $dataUri   = "data:{$mimeType};base64,{$base64Img}";

        $html = '<!DOCTYPE html><html><head><style>'
            . '@page { margin: 0; }'
            . 'body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; }'
            . 'img { max-width: 100%; max-height: 100%; object-fit: contain; }'
            . '</style></head><body>'
            . '<img src="' . $dataUri . '">'
            . '</body></html>';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

        if (is_array($paperSize)) {
            $pdf->setPaper($paperSize, $orientation);
        } else {
            $pdf->setPaper($paperSize, $orientation);
        }

        Log::info("Generated PDF for order #{$order->order_number} ({$store->printer_type} printer, {$store->paper_size})");

        return $pdf->output();
    }

    /**
     * ESC/POS commands for thermal receipt printers.
     */
    protected function generateEscPosReceipt(Order $order, Store $store): string
    {
        $esc = chr(27);
        $gs  = chr(29);
        $lf  = chr(10);
        $cut = $gs . "V" . chr(66) . chr(3); // paper cut

        $output = '';

        // Initialize printer
        $output .= $esc . "@";

        // Center align
        $output .= $esc . "a" . chr(1);

        // Bold + double size for store name
        $output .= $esc . "E" . chr(1);
        $output .= $gs . "!" . chr(0x11); // double width + height
        $output .= strtoupper($store->store_name) . $lf;
        $output .= $gs . "!" . chr(0); // reset size
        $output .= $esc . "E" . chr(0);

        // Store address
        $output .= $store->full_address . $lf;
        if ($store->phone) $output .= "Tel: " . $store->phone . $lf;
        if ($store->gst_number) $output .= "Tax ID: " . $store->gst_number . $lf;

        // Divider
        $output .= str_repeat('-', 32) . $lf;

        // Left align
        $output .= $esc . "a" . chr(0);

        // Order info
        $output .= "Order: " . $order->order_number . $lf;
        $output .= "Date:  " . $order->created_at->format('d/m/Y H:i') . $lf;
        if ($order->user) {
            $output .= "Cust:  " . $order->user->name . $lf;
        }
        $output .= str_repeat('-', 32) . $lf;

        // Items
        foreach ($order->items as $item) {
            $name  = substr($item->product_name, 0, 20);
            $qty   = $item->quantity;
            $price = number_format($item->total_price, 2);
            $output .= sprintf("%-20s %2d x %7s", $name, $qty, $price) . $lf;
        }

        $output .= str_repeat('-', 32) . $lf;

        // Totals
        $output .= sprintf("%-20s %11s", "Subtotal:", '$' . number_format($order->subtotal, 2)) . $lf;
        if ($order->discount_amount > 0) {
            $output .= sprintf("%-20s %11s", "Discount:", '-$' . number_format($order->discount_amount, 2)) . $lf;
        }
        if ($order->shipping_amount > 0) {
            $output .= sprintf("%-20s %11s", "Shipping:", '$' . number_format($order->shipping_amount, 2)) . $lf;
        }

        $output .= $esc . "E" . chr(1); // Bold
        $output .= sprintf("%-20s %11s", "TOTAL:", '$' . number_format($order->total, 2)) . $lf;
        $output .= $esc . "E" . chr(0);

        $output .= str_repeat('-', 32) . $lf;
        $output .= $esc . "a" . chr(1); // center
        $output .= "Thank you for your order!" . $lf;
        $output .= "Printed: " . now()->format('d/m/Y H:i:s') . $lf;
        $output .= $lf . $lf . $lf;
        $output .= $cut;

        return $output;
    }

    /**
     * Plain-text invoice for A4/Letter printers.
     */
    protected function generateTextInvoice(Order $order, Store $store): string
    {
        $line = str_repeat('=', 60);
        $dash = str_repeat('-', 60);
        $nl   = "\r\n";

        $output = '';
        $output .= $line . $nl;
        $output .= str_pad(strtoupper($store->store_name), 60, ' ', STR_PAD_BOTH) . $nl;
        $output .= str_pad($store->full_address, 60, ' ', STR_PAD_BOTH) . $nl;
        if ($store->phone) $output .= str_pad('Tel: ' . $store->phone, 60, ' ', STR_PAD_BOTH) . $nl;
        if ($store->gst_number) $output .= str_pad('Tax ID: ' . $store->gst_number, 60, ' ', STR_PAD_BOTH) . $nl;
        $output .= $line . $nl . $nl;

        $output .= "ORDER INVOICE" . $nl;
        $output .= $dash . $nl;
        $output .= "Order Number  : " . $order->order_number . $nl;
        $output .= "Invoice Number: " . ($order->invoice_number ?? 'N/A') . $nl;
        $output .= "Date          : " . $order->created_at->format('d M Y, h:i A') . $nl;
        $output .= "Customer      : " . ($order->user->name ?? 'Guest') . $nl;
        $output .= "Payment Status: " . ucfirst($order->payment_status) . $nl;
        $output .= $dash . $nl . $nl;

        $output .= sprintf("%-30s %5s %10s %10s", "ITEM", "QTY", "PRICE", "TOTAL") . $nl;
        $output .= $dash . $nl;

        foreach ($order->items as $item) {
            $output .= sprintf(
                "%-30s %5d %10s %10s",
                substr($item->product_name, 0, 30),
                $item->quantity,
                '$' . number_format($item->unit_price, 2),
                '$' . number_format($item->total_price, 2)
            ) . $nl;
        }

        $output .= $dash . $nl;
        $output .= sprintf("%47s %10s", "Subtotal:", '$' . number_format($order->subtotal, 2)) . $nl;

        if ($order->discount_amount > 0) {
            $output .= sprintf("%47s %10s", "Discount:", '-$' . number_format($order->discount_amount, 2)) . $nl;
        }
        if ($order->tax_amount > 0) {
            $output .= sprintf("%47s %10s", "Tax:", '$' . number_format($order->tax_amount, 2)) . $nl;
        }
        if ($order->shipping_amount > 0) {
            $output .= sprintf("%47s %10s", "Shipping:", '$' . number_format($order->shipping_amount, 2)) . $nl;
        }

        $output .= $line . $nl;
        $output .= sprintf("%47s %10s", "TOTAL:", '$' . number_format($order->total, 2)) . $nl;
        $output .= $line . $nl . $nl;

        // Shipping address
        if ($order->shipping_address) {
            $addr    = $order->shipping_address;
            $output .= "SHIPPING ADDRESS:" . $nl;
            $output .= ($addr['full_name'] ?? '') . $nl;
            $output .= ($addr['address_line_1'] ?? '') . $nl;
            if (!empty($addr['address_line_2'])) $output .= $addr['address_line_2'] . $nl;
            $output .= ($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '') . ' ' . ($addr['postal_code'] ?? '') . $nl;
            $output .= 'Phone: ' . ($addr['phone'] ?? '') . $nl;
            $output .= $nl;
        }

        $output .= $dash . $nl;
        $output .= str_pad("Printed from: " . $store->store_name, 60, ' ', STR_PAD_BOTH) . $nl;
        $output .= str_pad("Printed at: " . now()->format('d M Y, h:i:s A'), 60, ' ', STR_PAD_BOTH) . $nl;
        $output .= $line . $nl;
        $output .= chr(12); // Form feed (page break)

        return $output;
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->printLog->update([
            'status'        => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
