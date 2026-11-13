<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsSet
{
    /**
     * Allow the request only when a store has been selected (via QR scan,
     * which sets `active_store_id` in the session) and that store is still active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $storeId = $request->session()->get('active_store_id');

        $isValid = $storeId && Store::where('id', $storeId)->where('is_active', true)->exists();

        if ($isValid) {
            return $next($request);
        }

        $request->session()->forget(['active_store_id', 'active_store_name', 'active_store_code']);

        $message = 'Please scan a store QR code to continue.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect('/find-store')->with('error', $message);
    }
}
