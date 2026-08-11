<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UploadApiController extends Controller
{
    /**
     * Upload customer photo or asset
     */
    public function uploadPhoto(Request $request)
    {
        $fileKey = $request->hasFile('image') ? 'image' : ($request->hasFile('photo') ? 'photo' : 'file');
        
        $validator = Validator::make($request->all(), [
            $fileKey => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file($fileKey);
        $path = $file->store('customer_uploads', 'public');
        $fullUrl = asset('storage/' . $path);

        $upload = CustomerUpload::create([
            'user_id'       => auth()->id(),
            'session_id'    => session()->getId(),
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_type'     => $file->getClientMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Image uploaded successfully.',
            'data'      => [
                'upload_id'     => $upload->id,
                'original_name' => $upload->original_name,
                'file_path'     => $path,
                'url'           => $fullUrl,
                'image_url'     => $fullUrl,
            ],
        ], 201);
    }

    /**
     * Upload composite canvas export image
     */
    public function uploadComposite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'composite_image' => 'required|string', // Data URL base64 string
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $dataUrl = $request->input('composite_image');
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
            $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
            $type = strtolower($type[1]);
            $data = base64_decode($data);
            if ($data === false) {
                return response()->json(['success' => false, 'message' => 'Base64 decode failed.'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid data URL format.'], 400);
        }

        $filename = 'composites/' . uniqid('comp_', true) . '.' . $type;
        Storage::disk('public')->put($filename, $data);
        $fullUrl = asset('storage/' . $filename);

        $upload = CustomerUpload::create([
            'user_id'       => auth()->id(),
            'session_id'    => session()->getId(),
            'original_name' => basename($filename),
            'file_path'     => $filename,
            'file_type'     => 'image/' . $type,
            'file_size'     => strlen($data),
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'upload_id' => $upload->id,
                'url'       => $fullUrl,
            ],
        ], 201);
    }

    /**
     * Delete upload
     */
    public function destroy($id)
    {
        $upload = CustomerUpload::find($id);
        if (!$upload) {
            return response()->json(['success' => false, 'message' => 'Upload not found.'], 404);
        }

        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }
        $upload->delete();

        return response()->json([
            'success' => true,
            'message' => 'Upload deleted successfully.',
        ]);
    }
}
