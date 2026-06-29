<?php

namespace App\Http\Controllers;

use App\Models\CustomerUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max
        ]);

        $file = $request->file('image');
        $path = $file->store('customer-uploads/' . date('Y/m'), 'public');

        // Get image dimensions
        $imageInfo = getimagesize($file->getRealPath());
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        $upload = CustomerUpload::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);

        return response()->json([
            'success' => true,
            'upload' => [
                'id' => $upload->id,
                'url' => $upload->url,
                'thumbnail_url' => $upload->thumbnail_url,
                'original_name' => $upload->original_name,
                'width' => $upload->width,
                'height' => $upload->height,
            ],
        ]);
    }

    public function destroy(CustomerUpload $upload)
    {
        if (auth()->id() !== $upload->user_id && $upload->session_id !== session()->getId()) {
            abort(403);
        }

        Storage::disk('public')->delete($upload->file_path);
        if ($upload->thumbnail_path) {
            Storage::disk('public')->delete($upload->thumbnail_path);
        }

        $upload->delete();

        return response()->json(['success' => true]);
    }
}
