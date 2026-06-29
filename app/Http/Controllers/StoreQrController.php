<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreQrController extends Controller
{
    /**
     * Handle QR code scan: set store in session and redirect to products.
     * URL: /store/{store_code}
     */
    public function scan(string $storeCode)
    {
        $store = Store::where('store_code', $storeCode)
            ->where('is_active', true)
            ->firstOrFail();

        // Set the active store in session
        session([
            'active_store_id'   => $store->id,
            'active_store_name' => $store->store_name,
            'active_store_code' => $store->store_code,
        ]);

        return redirect()->route('flow.index');
    }

    /**
     * Show a full-page QR code for a store (printable / displayable).
     * URL: /store/{store_code}/qr
     */
    public function show(string $storeCode)
    {
        $store = Store::where('store_code', $storeCode)->firstOrFail();

        $scanUrl = url('/store/' . $store->store_code);

        return view('stores.qr', compact('store', 'scanUrl'));
    }
}
