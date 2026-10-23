@extends('layouts.app')
@section('title', 'Profile & Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        @include('customer.partials.sidebar')

        <div class="flex-1 space-y-8">
            <h1 class="font-display font-bold text-2xl text-surface-900">Profile & Addresses</h1>

            <!-- Profile Form -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <h2 class="font-display font-semibold text-lg text-surface-900 mb-5 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-brand-500"></i> Personal Information
                </h2>
                <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Addresses -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="font-display font-semibold text-lg text-surface-900 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-brand-500"></i> Saved Addresses
                    </h2>
                    <button onclick="document.getElementById('new-address-form').classList.toggle('hidden')"
                            class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                        <i data-lucide="plus" class="w-4 h-4"></i> Add New
                    </button>
                </div>

                <!-- Existing Addresses -->
                @if($addresses->count() > 0)
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    @foreach($addresses as $address)
                    <div class="p-4 rounded-xl border border-surface-200 relative group">
                        @if($address->is_default)
                        <span class="absolute top-2 right-2 px-2 py-0.5 bg-brand-100 text-brand-700 text-xs font-bold rounded">Default</span>
                        @endif
                        <p class="font-semibold text-surface-800">{{ $address->full_name }}</p>
                        <p class="text-sm text-surface-500 mt-1">{{ $address->full_address }}</p>
                        <p class="text-sm text-surface-500">Phone: {{ $address->phone }}</p>
                        <form action="{{ route('customer.address.delete', $address) }}" method="POST" class="mt-3"
                              onsubmit="return confirm('Delete this address?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- New Address Form -->
                <div id="new-address-form" class="{{ $addresses->count() > 0 ? 'hidden' : '' }}">
                    <form action="{{ route('customer.address.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">Full Name *</label>
                                <input type="text" name="full_name" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">Phone *</label>
                                <input type="tel" name="phone" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div class="sm:col-span-2"><label class="block text-sm font-medium text-surface-700 mb-1">Address Line 1 *</label>
                                <input type="text" name="address_line_1" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div class="sm:col-span-2"><label class="block text-sm font-medium text-surface-700 mb-1">Address Line 2</label>
                                <input type="text" name="address_line_2" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">City *</label>
                                <input type="text" name="city" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">State *</label>
                                <input type="text" name="state" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">PIN Code *</label>
                                <input type="text" name="postal_code" required maxlength="6" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"></div>
                            <div><label class="block text-sm font-medium text-surface-700 mb-1">Label</label>
                                <select name="label" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                    <option value="home">Home</option><option value="work">Work</option><option value="other">Other</option>
                                </select></div>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-surface-600">
                            <input type="checkbox" name="is_default" value="1" class="text-brand-600 rounded focus:ring-brand-500"> Set as default address
                        </label>
                        <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                            Save Address
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
