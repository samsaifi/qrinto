<?php

namespace App\Http\Controllers\Api\Noritsu;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * List all active stores for pickup.
     */
    public function index(Request $request): JsonResponse
    {
        $stores = Store::where('is_active', true)
            ->get(['id', 'name', 'address', 'city', 'state', 'zip', 'phone']);

        return response()->json($stores);
    }

    /**
     * Get details of a specific store.
     */
    public function show($id): JsonResponse
    {
        $store = Store::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail(['id', 'name', 'address', 'city', 'state', 'zip', 'phone', 'email']);

        return response()->json($store);
    }
}
