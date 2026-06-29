<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider, \App\Services\CartService $cartService)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where($provider . '_id', $socialUser->getId())->first();

            if (!$user) {
                // Check if user with this email already exists
                $user = User::where('email', $socialUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        $provider . '_id' => $socialUser->getId(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $socialUser->getName() ?? 'User',
                        'email' => $socialUser->getEmail(),
                        $provider . '_id' => $socialUser->getId(),
                        'password' => null,
                        'role' => 'customer',
                        'is_active' => true,
                    ]);
                }
            }

            $oldSessionId = request()->session()->getId();

            Auth::login($user);

            $cartService->mergeGuestCart($oldSessionId);

            return redirect()->intended(route('dashboard'));
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to login using ' . ucfirst($provider) . '. Please try again.']);
        }
    }
}
