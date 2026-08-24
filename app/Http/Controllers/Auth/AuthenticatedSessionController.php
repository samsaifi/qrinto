<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, \App\Services\CartService $cartService): RedirectResponse
    {
        $oldSessionId = $request->session()->getId();

        $request->authenticate();

        $cartService->mergeGuestCart($oldSessionId);

        $user = Auth::user();
        session()->forget('url.intended');

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif (in_array($user->role, ['store_admin', 'storeadmin', 'staff'])) {
            // Store admins and staff land on the standalone store panel.
            return redirect()->route('storepanel.orders');
        }

        return redirect()->route('customer.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
