<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsActive
{
    /**
     * Kick out store admins / staff whose store was deactivated while they
     * were already logged in (or who have a "remember me" cookie).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->hasInactiveStore()) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your store is inactive. Please contact the admin to activate your store.',
            ], 403);
        }

        return redirect()->route('store.inactive');
    }
}
