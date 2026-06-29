<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth is handled by middleware
    }

    public function rules(): array
    {
        return [
            'store_name'         => 'required|string|max:255',
            'store_code'         => 'required|string|max:50|unique:stores,store_code',
            'owner_name'         => 'required|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'required|string|max:20',
            'address'            => 'required|string',
            'city'               => 'required|string|max:100',
            'state'              => 'required|string|max:100',
            'zip_code'           => 'required|string|max:20',
            'country'            => 'nullable|string|max:100',
            'lat'                => 'nullable|numeric',
            'lon'                => 'nullable|numeric',

            'is_active'          => 'nullable|boolean',
            'opening_time'       => 'nullable|date_format:H:i',
            'closing_time'       => 'nullable|date_format:H:i',
            'gst_number'         => 'nullable|string|max:50',
            'logo'               => 'nullable|image|max:2048',
            'notes'              => 'nullable|string',
            'password'           => 'nullable|string|min:6',
        ];
    }
}
