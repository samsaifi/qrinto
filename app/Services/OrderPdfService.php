<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CustomerUpload;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OrderPdfService
{
    /**
     * Ensure that all items in an order have valid PDFs on disk.
     * Generates ONLY missing or invalid PDFs idempotently.
     *
     * @param Order $order
     * @return array Array of generated/existing PDF absolute file paths mapped by item ID
     */
    public function ensureOrderPdfsExist(Order $order): array
    {
        $order->loadMissing('items.product');
        $pdfPaths = [];

        foreach ($order->items as $item) {
            try {
                $path = $this->generatePdfForItem($item, $order);
                if ($path) {
                    $pdfPaths[$item->id] = $path;
                }
            } catch (\Throwable $e) {
                Log::error("OrderPdfService: Failed to generate PDF for Order #{$order->order_number}, Item #{$item->id}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
            }
        }

        return $pdfPaths;
    }

    /**
     * Generate PDF for a single OrderItem idempotently.
     * If the PDF already exists and is non-empty, returns its absolute path without regenerating.
     *
     * @param OrderItem $item
     * @param Order|null $order
     * @return string|null Absolute file path to the generated PDF
     */
    public function generatePdfForItem(OrderItem $item, ?Order $order = null): ?string
    {
        if (!$order) {
            $order = $item->order ?: Order::find($item->order_id);
        }

        if (!$order) {
            Log::error("OrderPdfService: Cannot generate PDF for Item #{$item->id} without valid parent order.");
            return null;
        }

        // 1. Idempotency Check: Verify if valid PDF already exists on disk
        if (!empty($item->pdf_path)) {
            $existingAbsPath = storage_path('app/public/' . ltrim($item->pdf_path, '/'));
            if (file_exists($existingAbsPath) && filesize($existingAbsPath) > 0) {
                return $existingAbsPath;
            }
        }

        // 2. Prepare directory
        $pdfDirectory = storage_path('app/public/orders/pdfs');
        if (!File::isDirectory($pdfDirectory)) {
            File::makeDirectory($pdfDirectory, 0755, true, true);
        }

        $pdfFilename = 'Design_' . $order->order_number . '_item_' . $item->id . '.pdf';
        $pdfAbsPath = $pdfDirectory . '/' . $pdfFilename;
        $relPath = 'orders/pdfs/' . $pdfFilename;

        // Double-check if file exists on disk even if DB field wasn't updated yet
        if (file_exists($pdfAbsPath) && filesize($pdfAbsPath) > 0) {
            if ($item->pdf_path !== $relPath) {
                $item->update(['pdf_path' => $relPath]);
            }
            return $pdfAbsPath;
        }

        // 3. Resolve Product & Page Count
        $product = $item->product ?: Product::find($item->product_id);
        $noOfPages = $product ? (int) $product->no_of_pages : 1;

        // Collect customization & upload IDs for this specific item
        $customization = (array) ($item->customization_data ?? []);
        $uploadIds = $customization['canvas_mapped_ids'] ?? ($customization['upload_ids'] ?? []);

        // Also check if uploaded_images map is stored on item
        $uploadedImages = (array) ($item->uploaded_images ?? []);

        // 4. Generate PDF based on number of pages
        if ($noOfPages === 4) {
            $pdf = $this->_generate4PagePdf($order, $item, $product, $uploadIds, $uploadedImages, $customization);
        } elseif ($noOfPages === 2) {
            $pdf = $this->_generate2PagePdf($order, $item, $product, $uploadIds, $uploadedImages, $customization);
        } else {
            $pdf = $this->_generateSinglePagePdf($order, $item, $product, $uploadIds, $uploadedImages, $customization);
        }

        if (!$pdf) {
            Log::warning("OrderPdfService: PDF rendering returned null for Item #{$item->id}");
            return null;
        }

        // 5. Save PDF to disk & update OrderItem record
        $pdf->save($pdfAbsPath);
        $item->update(['pdf_path' => $relPath]);

        return $pdfAbsPath;
    }

    /**
     * Generate 4-Page Folded Card PDF
     */
    private function _generate4PagePdf(Order $order, OrderItem $item, ?Product $product, array $uploadIds, array $uploadedImages, array $customization)
    {
        $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'landscape';

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
            $localPath = null;
            $uploadId = $uploadIds[$key] ?? null;

            // 1. Try uploaded CustomerUpload record
            if ($uploadId) {
                $upload = CustomerUpload::find($uploadId);
                if ($upload && $upload->file_path) {
                    $localPath = storage_path('app/public/' . $upload->file_path);
                    $mappedImages[$key] = $upload->file_path;
                }
            }

            // 2. Try uploaded_images array on item
            if (empty($localPath) && !empty($uploadedImages[$key])) {
                $localPath = storage_path('app/public/' . $uploadedImages[$key]);
                $mappedImages[$key] = $uploadedImages[$key];
            }

            // 3. Fallback to Product default raw original image
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
                $rotatedAbsPath = self::physicallyRotateImage($localPath, $rotation);
                $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
            } else {
                $absolutePaths[$key] = null;
            }
        }

        // Update item's uploaded_images if not already set
        if (!empty($mappedImages)) {
            $item->update(['uploaded_images' => $mappedImages]);
        }

        $flowData = $order->flow_data ?? [];
        $width = floatval($customization['width'] ?? ($flowData['size_width'] ?? 3.5));
        $height = floatval($customization['height'] ?? ($flowData['size_height'] ?? 5.0));

        if (isset($customization['dimensions'])) {
            $dims = explode('x', strtolower($customization['dimensions']));
            if (count($dims) === 2) {
                $width = floatval($dims[0]);
                $height = floatval($dims[1]);
            }
        } elseif (isset($flowData['size_dimensions'])) {
            $dims = explode('x', strtolower($flowData['size_dimensions']));
            if (count($dims) === 2) {
                $width = floatval($dims[0]);
                $height = floatval($dims[1]);
            }
        }

        $pdfWidth = min($width, $height);
        $pdfHeight = max($width, $height);

        $pdf = PDF::loadView('quick-flow.pdf.design', [
            'images' => $absolutePaths,
            'rotations' => $imageTypes,
            'width' => $pdfWidth,
            'height' => $pdfHeight,
            'orientation' => $orientation
        ]);

        $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
        return $pdf;
    }

    /**
     * Generate 2-Page PDF
     */
    private function _generate2PagePdf(Order $order, OrderItem $item, ?Product $product, array $uploadIds, array $uploadedImages, array $customization)
    {
        $orientation = ($product && $product->pdf_orientation) ? strtolower($product->pdf_orientation) : 'portrait';

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

        $mappedImages = [];
        $absolutePaths = [];

        foreach ($slots as $key) {
            $localPath = null;
            $uploadId = $uploadIds[$key] ?? null;

            if ($uploadId) {
                $upload = CustomerUpload::find($uploadId);
                if ($upload && $upload->file_path) {
                    $localPath = storage_path('app/public/' . $upload->file_path);
                    $mappedImages[$key] = $upload->file_path;
                }
            }

            if (empty($localPath) && !empty($uploadedImages[$key])) {
                $localPath = storage_path('app/public/' . $uploadedImages[$key]);
                $mappedImages[$key] = $uploadedImages[$key];
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
                $rotatedAbsPath = self::physicallyRotateImage($localPath, $rotation);
                $absolutePaths[$key] = str_replace('\\', '/', $rotatedAbsPath);
            } else {
                $absolutePaths[$key] = null;
            }
        }

        if (!empty($mappedImages)) {
            $item->update(['uploaded_images' => $mappedImages]);
        }

        $flowData = $order->flow_data ?? [];
        $width = floatval($customization['width'] ?? ($flowData['size_width'] ?? 5.0));
        $height = floatval($customization['height'] ?? ($flowData['size_height'] ?? 7.0));
        $unit = strtolower(trim($customization['unit'] ?? ($flowData['size_unit'] ?? 'inch')));

        $pdfWidth = min($width, $height);
        $pdfHeight = max($width, $height);

        // Swap width/height for 2-page layout
        $a = $pdfWidth;
        $pdfWidth = $pdfHeight;
        $pdfHeight = $a;

        $cssUnit = ($unit === 'inch') ? 'in' : $unit;

        $pdf = PDF::loadView('quick-flow.pdf.design-double', [
            'images' => $absolutePaths,
            'rotations' => $imageTypes,
            'width' => $pdfWidth,
            'height' => $pdfHeight,
            'cssUnit' => $cssUnit,
            'orientation' => $orientation,
        ]);
        $pdf->setPaper([0, 0, $pdfWidth * 72, $pdfHeight * 72]);
        return $pdf;
    }

    /**
     * Generate Single Page PDF
     */
    private function _generateSinglePagePdf(Order $order, OrderItem $item, ?Product $product, array $uploadIds, array $uploadedImages, array $customization)
    {
        $flowData = $order->flow_data ?? [];
        $widthVal = floatval($customization['width'] ?? ($flowData['size_width'] ?? 5.00));
        $heightVal = floatval($customization['height'] ?? ($flowData['size_height'] ?? 7.00));
        $unit = strtolower(trim($customization['unit'] ?? ($flowData['size_unit'] ?? 'inch')));

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

        $absolutePath = null;
        $relPath = null;

        $singleUploadId = !empty($uploadIds) ? reset($uploadIds) : null;
        if ($singleUploadId) {
            $upload = CustomerUpload::find($singleUploadId);
            if ($upload && $upload->file_path) {
                $relPath = $upload->file_path;
                $localPath = storage_path('app/public/' . $upload->file_path);
                if (file_exists($localPath)) {
                    $absolutePath = str_replace('\\', '/', $localPath);
                }
            }
        }

        if (!$absolutePath && !empty($uploadedImages)) {
            $firstImage = reset($uploadedImages);
            $relPath = $firstImage;
            $localPath = storage_path('app/public/' . $firstImage);
            if (file_exists($localPath)) {
                $absolutePath = str_replace('\\', '/', $localPath);
            }
        }

        if (!$absolutePath && !empty($customization['preview_url'])) {
            $absolutePath = $customization['preview_url'];
        }

        if ($relPath) {
            $item->update(['uploaded_images' => [$relPath]]);
        }

        $pdf = PDF::loadView('quick-flow.pdf.design-single', [
            'image' => $absolutePath,
            'width' => $pdfWidthVal,
            'height' => $pdfHeightVal,
            'cssUnit' => $cssUnit
        ]);

        $pdf->setPaper([0, 0, $pdfWidthPts, $pdfHeightPts]);
        return $pdf;
    }

    /**
     * Utility method to physically rotate image files for DomPDF rendering
     */
    public static function physicallyRotateImage($sourcePath, $rotationString)
    {
        $degrees = 0;
        if ($rotationString === 'rotate_90_minus') {
            $degrees = 90;
        } elseif ($rotationString === 'rotate_90_plus') {
            $degrees = 270;
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
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true, true);
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
