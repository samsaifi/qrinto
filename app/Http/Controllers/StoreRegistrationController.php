<?php

namespace App\Http\Controllers;

use App\Mail\StoreRegisteredAdminMail;
use App\Mail\StoreUserWelcomeMail;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Public (no auth) store self-registration.
 * Creates the store + its store_admin user, then emails the admin and the new store user.
 */
class StoreRegistrationController extends Controller
{
    public function create()
    {
        return view('store-registration.create');
    }

    public function thankYou()
    {
        return view('store-registration.create', ['thankYou' => true]);
    }

    public function store(Request $request)
    {
        // Honeypot: bots fill the hidden field, humans never see it. Never fail
        // silently - a browser autofill could hit it too, so tell the user.
        if ($request->filled('hp_extra_info')) {
            return back()->withInput($request->except('password', 'password_confirmation', 'logo', 'hp_extra_info'))
                ->withErrors(['form' => 'Registration could not be submitted. Please try again.']);
        }

        $request->merge(['store_code' => strtoupper(trim((string) $request->store_code))]);

        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_code' => 'required|string|max:50|alpha_dash|unique:stores,store_code',
            'owner_name' => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email|unique:stores,email',
            'phone'      => 'required|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
            'address'    => 'required|string|max:1000',
            'city'       => 'required|string|max:100',
            'state'      => 'required|string|max:100',
            'zip_code'   => 'required|string|max:20',
            'country'    => 'nullable|string|max:100',
            'lat'        => 'nullable|numeric|between:-90,90',
            'lon'        => 'nullable|numeric|between:-180,180',
            'logo'       => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ], [
            'email.unique' => 'This email is already registered.',
        ]);

        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('stores/logos', 'public')
            : null;

        try {
            [$store, $user] = DB::transaction(function () use ($validated, $logoPath) {
                $store = Store::create([
                    'store_name' => $validated['store_name'],
                    'store_code' => $validated['store_code'],
                    'owner_name' => $validated['owner_name'],
                    'email'      => $validated['email'],
                    'phone'      => $validated['phone'],
                    'address'    => $validated['address'],
                    'city'       => $validated['city'],
                    'state'      => $validated['state'],
                    'zip_code'   => $validated['zip_code'],
                    // stores.country is NOT NULL (DB default 'India')
                    'country'    => filled($validated['country'] ?? null) ? $validated['country'] : 'India',
                    'lat'        => $validated['lat'] ?? null,
                    'lon'        => $validated['lon'] ?? null,
                    'logo'       => $logoPath,
                    // Self-registered stores stay inactive until an admin reviews them.
                    'is_active'  => true,
                    'is_test'    => false,
                ]);

                $user = User::create([
                    'name'      => $validated['owner_name'],
                    'email'     => $validated['email'],
                    'password'  => Hash::make($validated['password']),
                    'role'      => 'store_admin',
                    'phone'     => $validated['phone'],
                    'store_id'  => $store->id,
                    'is_active' => true,
                ]);

                return [$store, $user];
            });
        } catch (\Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            Log::error('Store registration failed: ' . $e->getMessage());

            return back()->withInput($request->except('password', 'password_confirmation', 'logo'))
                ->withErrors(['form' => 'Registration could not be completed. Please try again.']);
        }

        // 1. Admin notification
        try {
            Mail::to(config('mail.admin_address'))->send(new StoreRegisteredAdminMail($store, $user));
        } catch (\Throwable $e) {
            Log::error('Store registration admin email failed: ' . $e->getMessage());
        }

        // 2. Store user: store details + login credentials
        try {
            $user->loadMissing('store');
            Mail::to($user->email)->send(new StoreUserWelcomeMail($user, $validated['password']));
        } catch (\Throwable $e) {
            Log::error('Store registration welcome email failed for ' . $user->email . ': ' . $e->getMessage());
        }

        return redirect()->route('store-registration.thank-you')
            ->with('registered', $store->store_name)
            ->with('registered_email', $user->email);
    }
}
