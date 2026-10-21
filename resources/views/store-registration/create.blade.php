<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Store Registration - {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-surface-50">
    <div class="max-w-4xl mx-auto px-4 py-10">

        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-block mb-4">
                <img src="{{ asset('images/svg-logo/Q-only.svg') }}" alt="{{ config('app.name') }}" class="w-12 h-12 mx-auto">
            </a>
            @if (empty($thankYou))
                <h1 class="font-display font-bold text-2xl text-surface-900">Register Your Store</h1>
                <p class="text-sm text-surface-500 mt-1">Fill in your store details. Your login details will be emailed to you.</p>
            @endif
        </div>

        @if (!empty($thankYou))
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-8 text-center">
                <div class="w-14 h-14 rounded-full bg-accent-50 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check" class="w-7 h-7 text-accent-600"></i>
                </div>
                <h2 class="font-display font-semibold text-lg text-surface-900">Thank you for registering!</h2>
                @if (session('registered'))
                    <p class="text-sm text-surface-600 mt-2">
                        <strong>{{ session('registered') }}</strong> has been registered. Login details have been sent to
                        <strong>{{ session('registered_email') }}</strong>.
                    </p>
                @else
                    <p class="text-sm text-surface-600 mt-2">Your store has been registered. Login details have been sent to your email.</p>
                @endif
                <p class="text-sm text-surface-500 mt-1">Our team will review and activate your store shortly.</p>
                <a href="{{ route('login') }}" class="inline-block mt-6 px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition">
                    Go to Login
                </a>
            </div>
        @else

        <form action="{{ route('store-registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="storeRegistrationForm">
            @csrf

            {{-- Honeypot (hidden from humans) --}}
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label>Leave this empty <input type="text" name="hp_extra_info" value="" tabindex="-1" autocomplete="new-password"></label>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700" role="alert" id="formErrors">
                    <p class="font-semibold mb-1">Please fix the following:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Store Information -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="store" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Store Information</h2>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Name <span class="text-red-500">*</span></label>
                        <input type="text" name="store_name" value="{{ old('store_name') }}" required maxlength="255"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="e.g. PrintPro Downtown">
                        @error('store_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Code <span class="text-red-500">*</span></label>
                        <input type="text" name="store_code" value="{{ old('store_code') }}" required maxlength="50"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 font-mono uppercase"
                            placeholder="e.g. PP-DT-001">
                        @error('store_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Owner Name <span class="text-red-500">*</span></label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required maxlength="255"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Full name of owner">
                        @error('owner_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="store@example.com">
                        <p class="text-xs text-surface-400 mt-1">This will be your login email.</p>
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="20"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="(555) 123-4567">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="hidden sm:block"></div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Minimum 8 characters">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Re-enter password">
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="map-pin" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Address</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="2" required maxlength="1000"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Street, building, area">{{ old('address') }}</textarea>
                        @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">City <span class="text-red-500">*</span></label>
                            <input type="text" name="city" value="{{ old('city') }}" required maxlength="100"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">State <span class="text-red-500">*</span></label>
                            <input type="text" name="state" value="{{ old('state') }}" required maxlength="100"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">ZIP Code <span class="text-red-500">*</span></label>
                            <input type="text" name="zip_code" value="{{ old('zip_code') }}" required maxlength="20"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('zip_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Country</label>
                            <input type="text" name="country" value="{{ old('country', 'India') }}" maxlength="100"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Latitude</label>
                            <input type="text" inputmode="decimal" name="lat" id="lat" value="{{ old('lat') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('lat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Longitude</label>
                            <input type="text" inputmode="decimal" name="lon" id="lon" value="{{ old('lon') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('lon') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2 flex items-end">
                            <button type="button" onclick="useMyLocation(this)"
                                class="w-full sm:w-auto px-4 py-2.5 bg-surface-100 text-surface-700 text-sm font-semibold rounded-xl hover:bg-surface-200 transition flex items-center justify-center gap-2">
                                <i data-lucide="crosshair" class="w-4 h-4"></i>
                                Use my current location
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logo -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="image" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Store Logo</h2>
                </div>
                <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer"
                    onclick="document.getElementById('logo_input').click()">
                    <div id="logoPlaceholder" class="py-2">
                        <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-surface-300 mb-1"></i>
                        <p class="text-xs text-surface-500">Click to upload (JPG, PNG, WEBP or SVG, max 2 MB)</p>
                    </div>
                    <div id="logoPreview" class="hidden">
                        <img src="" alt="Logo preview" class="mx-auto max-h-20 rounded-lg mb-2">
                        <p class="text-xs text-surface-500">Click to replace</p>
                    </div>
                </div>
                <input type="file" name="logo" id="logo_input" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden"
                    onchange="previewLogo(this)">
                <p id="logoClientError" class="hidden text-xs text-red-500 mt-1"></p>
                @error('logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" id="registerBtn"
                class="w-full px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition">
                Register Store
            </button>
            <p class="text-center text-sm text-surface-500">
                Already registered? <a href="{{ route('login') }}" class="text-brand-600 font-semibold">Log in</a>
            </p>
        </form>
        @endif
    </div>

    <script>
        lucide.createIcons();

        const formErrors = document.getElementById('formErrors');
        if (formErrors) formErrors.scrollIntoView({ block: 'center' });

        function previewLogo(input) {
            const preview = document.getElementById('logoPreview');
            const placeholder = document.getElementById('logoPlaceholder');
            const err = document.getElementById('logoClientError');
            err.classList.add('hidden');
            if (input.files && input.files[0] && input.files[0].size > 2 * 1024 * 1024) {
                err.textContent = 'Logo must be 2 MB or smaller.';
                err.classList.remove('hidden');
                input.value = '';
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
                return;
            }
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function useMyLocation(btn) {
            if (!navigator.geolocation) return;
            btn.disabled = true;
            navigator.geolocation.getCurrentPosition(function (pos) {
                document.getElementById('lat').value = pos.coords.latitude.toFixed(7);
                document.getElementById('lon').value = pos.coords.longitude.toFixed(7);
                btn.disabled = false;
            }, function () { btn.disabled = false; });
        }

        const regForm = document.getElementById('storeRegistrationForm');
        if (regForm) {
            regForm.addEventListener('submit', function () {
                const btn = document.getElementById('registerBtn');
                btn.disabled = true;
                btn.textContent = 'Registering…';
            });
        }
    </script>
</body>

</html>
