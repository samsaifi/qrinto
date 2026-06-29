<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = $this->route('store');

        return [
            'store_name'         => 'required|string|max:255',
            'store_code'         => ['required', 'string', 'max:50', Rule::unique('stores', 'store_code')->ignore($storeId)],
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
