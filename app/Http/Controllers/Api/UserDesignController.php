<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDesign;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserDesignController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'design_data' => 'required|array',
            'preview_image' => 'required|string', // base64
        ]);

        try {
            // Process the base64 preview image
            $imageData = $request->preview_image;
            $extension = 'png';
            if (preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $imageData, $matches)) {
                $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
                $imageData = preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $imageData);
            }
            $imageData = str_replace(' ', '+', $imageData);
            $imageBinary = base64_decode($imageData);

            $randomStr = Str::random(40);
            $fileName = 'designs/' . $randomStr . '.' . $extension;
            Storage::disk('public')->put($fileName, $imageBinary);

            // Save the bulky array to a JSON file to avoid MySQL's max_allowed_packet limit
            $jsonFileName = 'designs/' . $randomStr . '.json';
            Storage::disk('public')->put($jsonFileName, json_encode($request->design_data));

            $session_id = session()->getId();

            $design = UserDesign::create([
                'user_id' => auth()->check() ? auth()->id() : null,
                'session_id' => $session_id,
                'product_id' => $request->product_id,
                'design_data' => ['file_path' => $jsonFileName], // Store reference map
                'preview_image_path' => $fileName,
            ]);

            return response()->json([
                'success' => true,
                'design_id' => $design->id,
                'preview_url' => asset('storage/' . $fileName),
                'message' => 'Design saved successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Design save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save design: ' . $e->getMessage()
            ], 500);
        }
    }
}
