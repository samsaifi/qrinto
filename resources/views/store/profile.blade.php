@extends('layouts.store')
@section('title', 'Store details')

@php
    $input = 'w-full rounded-lg border-slate-200 text-sm py-2 focus:border-[#287d3c] focus:ring-[#287d3c]';
    $label = 'block text-xs font-medium text-slate-600 mb-1';
    $card = 'bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 md:p-5';
    $cardTitle = 'flex items-center gap-2 mb-3';
    $err = 'text-[11px] text-red-500 mt-0.5';
    $logoUrl = $store->logo ? asset('storage/' . $store->logo) : null;
@endphp

@section('content')
    <form action="{{ route('storepanel.profile.update') }}" method="POST" enctype="multipart/form-data"
        id="storeProfileForm">
        @csrf
        @method('PUT')

        {{-- Title + actions on one row so the whole form fits a single screen --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h1 class="font-display font-bold text-2xl text-slate-900 tracking-tight">Store details</h1>
                <p class="text-slate-500 text-sm">Update your store information, address and logo.</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('storepanel.orders') }}"
                    class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" id="saveBtn"
                    class="px-5 py-2 rounded-xl bg-[#287d3c] text-white text-sm font-semibold hover:bg-[#1f6630] disabled:opacity-70 transition">
                    Save changes
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800"
                role="status">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 px-4 py-2.5 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700" role="alert">
                Please fix the highlighted fields below.
                @error('form') <span class="block">{{ $message }}</span> @enderror
            </div>
        @endif

        <div class="grid lg:grid-cols-2 gap-4">
            {{-- Store Information --}}
            <div class="{{ $card }}">
                <div class="{{ $cardTitle }}">
                    <i data-lucide="store" class="w-4 h-4 text-[#287d3c]"></i>
                    <h2 class="font-display font-semibold text-base text-slate-900">Store Information</h2>
                </div>
                <div class="grid grid-cols-2 gap-x-3 gap-y-3">
                    <div>
                        <label class="{{ $label }}">Store Name <span class="text-red-500">*</span></label>
                        <input type="text" name="store_name" value="{{ old('store_name', $store->store_name) }}" required
                            maxlength="255" class="{{ $input }}" placeholder="e.g. PrintPro Downtown">
                        @error('store_name') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" title="Changing this changes your store QR link">Store Code <span
                                class="text-red-500">*</span> <span class="text-slate-400 font-normal">· QR link</span></label>
                        <input type="text" name="store_code" value="{{ old('store_code', $store->store_code) }}" required
                            maxlength="50" class="{{ $input }} font-mono uppercase" placeholder="e.g. PP-DT-001">
                        @error('store_code') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Owner Name <span class="text-red-500">*</span></label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $store->owner_name) }}" required
                            maxlength="255" class="{{ $input }}" placeholder="Full name of owner">
                        @error('owner_name') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Store Email <span class="text-red-500">*</span> <span
                                class="text-slate-400 font-normal">· login</span></label>
                        <input type="email" name="email" value="{{ old('email', $store->email ?: auth()->user()->email) }}"
                            required maxlength="255" autocomplete="email" class="{{ $input }}"
                            placeholder="store@example.com">
                        @error('email') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" required maxlength="20"
                            class="{{ $input }}" placeholder="(555) 123-4567">
                        @error('phone') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="hidden sm:block"></div>
                    <div>
                        <label class="{{ $label }}">New Password</label>
                        <input type="password" name="password" minlength="8" autocomplete="new-password"
                            class="{{ $input }}" placeholder="Leave blank to keep">
                        @error('password') <p class="{{ $err }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Confirm Password</label>
                        <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password"
                            class="{{ $input }}" placeholder="Re-enter new password">
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Address --}}
                <div class="{{ $card }}">
                    <div class="{{ $cardTitle }}">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#287d3c]"></i>
                        <h2 class="font-display font-semibold text-base text-slate-900">Address</h2>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="{{ $label }}">Street Address <span class="text-red-500">*</span></label>
                            <input type="text" name="address" value="{{ old('address', $store->address) }}" required
                                maxlength="1000" class="{{ $input }}" placeholder="Street, building, area">
                            @error('address') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="{{ $label }}">City <span class="text-red-500">*</span></label>
                                <input type="text" name="city" value="{{ old('city', $store->city) }}" required
                                    maxlength="100" class="{{ $input }}">
                                @error('city') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">State <span class="text-red-500">*</span></label>
                                <input type="text" name="state" value="{{ old('state', $store->state) }}" required
                                    maxlength="100" class="{{ $input }}">
                                @error('state') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">ZIP Code <span class="text-red-500">*</span></label>
                                <input type="text" name="zip_code" value="{{ old('zip_code', $store->zip_code) }}"
                                    required maxlength="20" class="{{ $input }}">
                                @error('zip_code') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">Country</label>
                                <input type="text" name="country"
                                    value="{{ old('country', $store->country ?: 'India') }}" maxlength="100"
                                    class="{{ $input }}">
                                @error('country') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="{{ $label }}">Latitude</label>
                                <input type="text" inputmode="decimal" name="lat" id="lat"
                                    value="{{ old('lat', $store->lat) }}" class="{{ $input }}">
                                @error('lat') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $label }}">Longitude</label>
                                <input type="text" inputmode="decimal" name="lon" id="lon"
                                    value="{{ old('lon', $store->lon) }}" class="{{ $input }}">
                                @error('lon') <p class="{{ $err }}">{{ $message }}</p> @enderror
                            </div>
                            <div class="col-span-2 flex items-end">
                                <button type="button" onclick="useMyLocation(this)"
                                    class="w-full px-3 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 disabled:opacity-60 transition flex items-center justify-center gap-2">
                                    <i data-lucide="crosshair" class="w-4 h-4"></i>
                                    Use my current location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logo (compact row: preview + upload) --}}
                <div class="{{ $card }}">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 shrink-0 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                            <img id="logoImg" src="{{ $logoUrl }}" alt="Store logo"
                                class="max-w-full max-h-full object-contain {{ $logoUrl ? '' : 'hidden' }}">
                            <i id="logoIcon" data-lucide="image" class="w-6 h-6 text-slate-300 {{ $logoUrl ? 'hidden' : '' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="font-display font-semibold text-base text-slate-900">Store Logo</h2>
                            <p class="text-xs text-slate-500">JPG, PNG, WEBP or SVG, max 2 MB</p>
                            <p id="logoClientError" class="hidden {{ $err }}"></p>
                            @error('logo') <p class="{{ $err }}">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" onclick="document.getElementById('logo_input').click()"
                            class="shrink-0 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed border-slate-300 text-sm font-semibold text-slate-700 hover:border-[#287d3c] hover:text-[#287d3c] transition">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                            {{ $logoUrl ? 'Replace' : 'Upload' }}
                        </button>
                    </div>
                    <input type="file" name="logo" id="logo_input"
                        accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" onchange="previewLogo(this)">
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('storeProfileForm').addEventListener('submit', function () {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.textContent = 'Saving…';
        });

        function previewLogo(input) {
            const err = document.getElementById('logoClientError');
            err.classList.add('hidden');
            if (input.files && input.files[0] && input.files[0].size > 2 * 1024 * 1024) {
                err.textContent = 'Logo must be 2 MB or smaller.';
                err.classList.remove('hidden');
                input.value = '';
                return;
            }
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('logoImg');
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    document.getElementById('logoIcon')?.classList.add('hidden');
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
    </script>
@endpush
