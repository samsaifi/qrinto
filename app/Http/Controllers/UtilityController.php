<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UtilityController extends Controller
{
    public function generateThumbnails()
    {
        $sourceDir = 'products';
        $thumbDir = 'thumbnails/products';
        
        // Scan public storage (storage/app/public/products)
        $files = Storage::disk('public')->allFiles($sourceDir);
        $thumbnails = [];

        $manager = new ImageManager(new Driver());

        foreach ($files as $file) {
            // Only process images
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            $thumbPath = $thumbDir . '/' . $file;
            $fullThumbPath = storage_path('app/public/' . $thumbPath);
            $fullSourcePath = storage_path('app/public/' . $file);
            
            // Check if thumbnail exists
            if (!Storage::disk('public')->exists($thumbPath)) {
                // Ensure sub-directory exists in thumbnails folder
                $dir = dirname($fullThumbPath);
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }

                try {
                    // Create thumbnail using Intervention Image
                    $img = $manager->read($fullSourcePath);
                    $img->scale(width: 300); // Scale to 300px width, maintaining aspect ratio
                    $img->save($fullThumbPath);
                } catch (\Exception $e) {
                    \Log::error("Thumbnail creation failed for {$file}: " . $e->getMessage());
                    continue;
                }
            }
            
            $thumbnails[] = [
                'original_url' => asset('storage/' . $file),
                'thumbnail_url' => asset('storage/' . $thumbPath),
                'path' => $file,
                'name' => basename($file)
            ];
        }

        return view('utility.thumbnails', compact('thumbnails'));
    }
}
