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
    public function printPage(Order $order, Request $request, $itemParam = null)
    {
        $order->load(['items.product.category', 'items.product.productType', 'store']);

        $targetItemId = $itemParam ?: $request->query('item_id');
        $item = null;
        $designImages = [];

        if ($targetItemId) {
            $item = $order->items->firstWhere('id', (int)$targetItemId);
        }
        if (!$item) {
            $item = $order->items->first();
        }

        if ($item) {
            // 1. Collect all uploaded images
            if (!empty($item->uploaded_images) && is_array($item->uploaded_images)) {
                foreach ($item->uploaded_images as $path) {
                    if (is_string($path) && trim($path) !== '') {
                        $designImages[] = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                    }
                }
            }

            // 2. Collect from customization_data preview_urls / preview_url
            if (empty($designImages)) {
                if (!empty($item->customization_data['preview_urls']) && is_array($item->customization_data['preview_urls'])) {
                    foreach ($item->customization_data['preview_urls'] as $url) {
                        if (is_string($url) && trim($url) !== '') {
                            $designImages[] = $url;
                        }
                    }
                } elseif (!empty($item->customization_data['preview_url'])) {
                    $designImages[] = $item->customization_data['preview_url'];
                }
            }

            // 3. Fallback to pdf_path or sample_image_url
            if (empty($designImages)) {
                if (!empty($item->pdf_path)) {
                    $designImages[] = asset('storage/' . $item->pdf_path);
                } elseif (!empty($item->product?->sample_image_url)) {
                    $designImages[] = $item->product->sample_image_url;
                } elseif (!empty($item->product?->frame_image_url)) {
                    $designImages[] = $item->product->frame_image_url;
                }
            }
        }

        $designUrl = !empty($designImages) ? $designImages[0] : null;

        // Full detailed list of every installed printer/driver with its connection status.
        $systemPrinters = $this->getSystemPrinters();

        // Plain name list (kept for backward compatibility with existing JS).
        $systemPrinterNames = array_map(fn($p) => $p['name'], $systemPrinters);

        $noritsuPrinters = array_values(array_filter($systemPrinterNames, function ($name) {
            return preg_match('/noritsu\s*931bl/i', $name);
        }));
        $hasNoritsuPrinter = count($noritsuPrinters) > 0;

        return view('quick-flow.print', compact('order', 'item', 'designUrl', 'designImages', 'hasNoritsuPrinter', 'noritsuPrinters', 'systemPrinters'));
    }

    /**
     * Directly send print job to specified Windows printer spooler.
     */
    public function sendDirectPrint(Request $request, Order $order)
    {
        $request->validate([
            'printer_name' => 'required|string',
            'item_id'      => 'nullable|integer',
        ]);

        $printerName = trim($request->input('printer_name'));
        $itemId = $request->input('item_id');

        $order->load(['items.product', 'store']);
        $item = $itemId ? $order->items->firstWhere('id', $itemId) : $order->items->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Order item not found.'], 404);
        }

        $filePath = null;
        if (!empty($item->pdf_path) && file_exists(storage_path('app/public/' . $item->pdf_path))) {
            $filePath = storage_path('app/public/' . $item->pdf_path);
        }

        if (!$filePath && !empty($item->uploaded_images) && is_array($item->uploaded_images)) {
            foreach ($item->uploaded_images as $img) {
                if (is_string($img) && file_exists(storage_path('app/public/' . $img))) {
                    $filePath = storage_path('app/public/' . $img);
                    break;
                }
            }
        }

        if (!$filePath && $item->product) {
            $sample = $item->product->getRawOriginal('sample_image');
            if ($sample && file_exists(storage_path('app/public/' . $sample))) {
                $filePath = storage_path('app/public/' . $sample);
            }
        }

        $printedLocally = false;
        $errorMsg = null;

        if ($filePath && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $escapedPrinter = addslashes($printerName);
                $escapedFile = addslashes($filePath);

                $psScript = <<<PS
Add-Type -AssemblyName System.Drawing
\$doc = New-Object System.Drawing.Printing.PrintDocument
\$doc.PrinterSettings.PrinterName = '{$escapedPrinter}'
\$img = [System.Drawing.Image]::FromFile('{$escapedFile}')
\$doc.add_PrintPage({
    param(\$sender, \$e)
    \$e.Graphics.DrawImage(\$img, 0, 0, \$e.PageBounds.Width, \$e.PageBounds.Height)
})
\$doc.Print()
\$img.Dispose()
PS;

                $tempPsFile = storage_path('app/temp_print_' . time() . '.ps1');
                file_put_contents($tempPsFile, $psScript);

                $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -File \"{$tempPsFile}\"";
                @exec($cmd, $output, $returnCode);
                @unlink($tempPsFile);

                if ($returnCode === 0) {
                    $printedLocally = true;
                } else {
                    $errorMsg = implode(" ", $output);
                }
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
            }
        }

        \App\Models\PrintLog::create([
            'order_id' => $order->id,
            'store_id' => $order->store_id ?? 1,
            'user_id'  => auth()->id(),
            'status'   => $printedLocally ? 'printed' : 'queued',
        ]);

        return response()->json([
            'success'      => true,
            'message'      => "Sent print job directly to {$printerName}",
            'printer_name' => $printerName,
            'printed'      => $printedLocally,
            'error'        => $errorMsg,
        ]);
    }

    /**
     * Fetch every installed printer/driver from the Windows spooler, including
     * whether each one is actually connected (online) or not.
     *
     * @return array<int, array{name:string, driver:string, port:string, connected:bool, status:string}>
     */
    private function getSystemPrinters(): array
    {
        $printers = [];

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return $printers;
        }

        try {
            // We can't rely on WorkOffline alone: it stays false for a driver even
            // when no physical printer is attached, and virtual printers are always
            // "online". So we actually probe device presence:
            //   - virtual printers (OneNote/XPS/PDF/Fax) -> never a real connection
            //   - USB/local ports    -> must appear in Get-PnpDevice -PresentOnly
            //   - TCP/IP ports       -> host must answer a ping
            //   - anything else      -> fall back to !WorkOffline
            $psScript = <<<'PS'
$ErrorActionPreference = 'SilentlyContinue'
$present = @(Get-PnpDevice -Class Printer -PresentOnly | Select-Object -ExpandProperty FriendlyName)
$ports = @{}
foreach ($pt in Get-PrinterPort) { $ports[$pt.Name] = $pt }
$out = foreach ($p in Get-CimInstance Win32_Printer) {
    $port = [string]$p.PortName
    $type = 'physical'
    $connected = $false
    if ($p.Name -match 'OneNote|Microsoft XPS|Microsoft Print to PDF|Fax|Send To' -or $port -match '^(PORTPROMPT|nul|FILE|XPSPort|SHRFAX|Microsoft\.)') {
        $type = 'virtual'
        $connected = $false
    }
    elseif ($port -match '^(USB|DOT4|LPT|COM|WSD)') {
        $type = 'usb'
        foreach ($d in $present) {
            if ($d -and ($d -eq $p.Name -or $p.Name -like "*$d*" -or $d -like "*$($p.Name)*")) { $connected = $true; break }
        }
    }
    elseif ($ports[$port] -and $ports[$port].PrinterHostAddress) {
        $type = 'network'
        $connected = [bool](Test-Connection -ComputerName $ports[$port].PrinterHostAddress -Count 1 -Quiet)
    }
    else {
        # Custom / vendor monitor ports (e.g. the Noritsu "Noritsu 931-BL" port)
        # exist whether or not hardware is attached and WorkOffline is unreliable,
        # so we cannot positively verify a connection here. Only trust it when the
        # matching device is actually present in PnP; otherwise report not connected.
        $type = 'custom'
        $connected = $false
        foreach ($d in $present) {
            if ($d -and ($d -eq $p.Name -or $p.Name -like "*$d*" -or $d -like "*$($p.Name)*")) { $connected = $true; break }
        }
    }
    [PSCustomObject]@{ Name = $p.Name; DriverName = $p.DriverName; PortName = $port; Connected = $connected; Type = $type }
}
$out | ConvertTo-Json -Compress
PS;

            $tempPsFile = storage_path('app/detect_printers_' . getmypid() . '.ps1');
            file_put_contents($tempPsFile, $psScript);
            @exec('powershell -NoProfile -ExecutionPolicy Bypass -File "' . $tempPsFile . '"', $output);
            @unlink($tempPsFile);

            $json = trim(implode('', $output));

            if ($json !== '') {
                $decoded = json_decode($json, true);

                // A single printer decodes to an assoc array, multiple to a list.
                if (is_array($decoded)) {
                    if (isset($decoded['Name'])) {
                        $decoded = [$decoded];
                    }

                    foreach ($decoded as $p) {
                        $name = trim($p['Name'] ?? '');
                        if ($name === '') {
                            continue;
                        }

                        $type = $p['Type'] ?? 'physical';
                        $connected = filter_var($p['Connected'] ?? false, FILTER_VALIDATE_BOOLEAN);

                        if ($connected) {
                            $status = 'Connected';
                        } elseif ($type === 'virtual') {
                            $status = 'Driver only';
                        } else {
                            $status = 'Not connected';
                        }

                        $printers[] = [
                            'name'      => $name,
                            'driver'    => trim($p['DriverName'] ?? ''),
                            'port'      => trim($p['PortName'] ?? ''),
                            'type'      => $type,
                            'connected' => $connected,
                            'status'    => $status,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fall through to the name-only fallback below.
        }

        // Fallback: if the CIM query returned nothing, at least list names.
        if (empty($printers)) {
            try {
                $fallback = [];
                @exec('powershell -NoProfile -Command "Get-Printer | Select-Object -ExpandProperty Name"', $fallback);
                foreach (array_filter(array_map('trim', $fallback)) as $name) {
                    $printers[] = [
                        'name'      => $name,
                        'driver'    => '',
                        'port'      => '',
                        'type'      => 'unknown',
                        'connected' => false,
                        'status'    => 'Unknown',
                    ];
                }
            } catch (\Throwable $e) {
                // give up
            }
        }

        return $printers;
    }
}
