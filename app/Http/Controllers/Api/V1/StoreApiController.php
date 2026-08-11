<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreApiController extends Controller
{
    /**
     * List all active stores
     */
    public function index(Request $request)
    {
        $query = Store::active();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('zip_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $stores = $query->get()->map(function ($store) {
            return [
                'id'            => $store->id,
                'store_name'    => $store->store_name,
                'store_code'    => $store->store_code,
                'phone'         => $store->phone,
                'email'         => $store->email,
                'address'       => $store->address,
                'city'          => $store->city,
                'state'         => $store->state,
                'zip_code'      => $store->zip_code,
                'full_address'  => $store->full_address,
                'lat'           => (float) $store->lat,
                'lon'           => (float) $store->lon,
                'opening_time'  => $store->opening_time ? $store->opening_time->format('H:i') : null,
                'closing_time'  => $store->closing_time ? $store->closing_time->format('H:i') : null,
                'logo_url'      => $store->logo ? asset('storage/' . $store->logo) : null,
                'qr_url'        => route('store.scan', $store->store_code ?? $store->id),
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $stores->count(),
            'data'    => $stores,
        ]);
    }

    /**
     * Store details
     */
    public function show($idOrCode)
    {
        $store = Store::where('id', $idOrCode)
            ->orWhere('store_code', $idOrCode)
            ->first();

        if (!$store || !$store->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $store->id,
                'store_name'    => $store->store_name,
                'store_code'    => $store->store_code,
                'owner_name'    => $store->owner_name,
                'phone'         => $store->phone,
                'email'         => $store->email,
                'address'       => $store->address,
                'city'          => $store->city,
                'state'         => $store->state,
                'zip_code'      => $store->zip_code,
                'country'       => $store->country,
                'full_address'  => $store->full_address,
                'lat'           => (float) $store->lat,
                'lon'           => (float) $store->lon,
                'opening_time'  => $store->opening_time ? $store->opening_time->format('H:i') : null,
                'closing_time'  => $store->closing_time ? $store->closing_time->format('H:i') : null,
                'logo_url'      => $store->logo ? asset('storage/' . $store->logo) : null,
                'qr_url'        => route('store.scan', $store->store_code ?? $store->id),
            ],
        ]);
    }

    /**
     * Nearest Store Locator by Latitude & Longitude
     */
    public function nearest(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng', $request->input('lon'));

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide lat and lng query parameters.',
            ], 422);
        }

        $stores = Store::active()->whereNotNull('lat')->whereNotNull('lon')->get();

        $sorted = $stores->map(function ($store) use ($lat, $lng) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($store->lat - $lat);
            $dLon = deg2rad($store->lon - $lng);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($lat)) * cos(deg2rad($store->lat)) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = round($earthRadius * $c, 2);

            $storeArray = [
                'id'            => $store->id,
                'store_name'    => $store->store_name,
                'store_code'    => $store->store_code,
                'phone'         => $store->phone,
                'address'       => $store->address,
                'city'          => $store->city,
                'full_address'  => $store->full_address,
                'lat'           => (float) $store->lat,
                'lon'           => (float) $store->lon,
                'distance_km'   => $distance,
            ];
            return $storeArray;
        })->sortBy('distance_km')->values();

        return response()->json([
            'success' => true,
            'nearest_store' => $sorted->first(),
            'data'          => $sorted,
        ]);
    }
}
