@extends('layouts.admin')
@section('title', isset($store) ? 'Edit Store' : 'Create Store')
@php
  
    $isAdmin = auth()->user()->isAdmin();
@endphp
@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3">
         @if($isAdmin)
        <a href="{{ route('admin.stores.index') }}" class="p-2 rounded-xl hover:bg-surface-100 text-surface-500 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        @else
        <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl hover:bg-surface-100 text-surface-500 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        @endif
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">{{ isset($store) ? (auth()->user()->isStoreAdmin() || auth()->user()->isAdmin() ? 'Edit Store' : 'Store Details') : 'Create Store' }}</h1>
            <p class="text-sm text-surface-500">{{ isset($store) ? 'View and manage store configuration' : 'Add a new printing shop' }}</p>
        </div>
    </div>
</div>

<form action="{{ isset($store) ? route('admin.stores.update', $store->id) : route('admin.stores.store') }}"
    method="POST" enctype="multipart/form-data" class="space-y-6" id="storeForm">
    @csrf
    @if(isset($store)) @method('PUT') @endif

    @php
    $isStaff = auth()->user()->isStaff() && !auth()->user()->isStoreAdmin() && !auth()->user()->isAdmin();
    $readonlyAtts = $isStaff ? 'readonly disabled' : '';
    @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Store Information -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="store" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Store Information</h2>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Name <span class="text-red-500">*</span></label>
                        <input type="text" name="store_name" value="{{ old('store_name', $store->store_name ?? '') }}" required
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="e.g. PrintPro Downtown">
                        @error('store_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Code <span class="text-red-500">*</span></label>
                        <input type="text" name="store_code" value="{{ old('store_code', $store->store_code ?? '') }}" required
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 font-mono uppercase" {!! $readonlyAtts !!}
                            placeholder="e.g. PP-DT-001" maxlength="50">
                        @error('store_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Owner Name <span class="text-red-500">*</span></label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $store->owner_name ?? '') }}" required
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="Full name of owner">
                        @error('owner_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Email</label>
                        <input type="email" name="email" value="{{ old('email', $store->email ?? '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="store@example.com">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $store->phone ?? '') }}" required
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="(555) 123-4567">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Admin Password</label>
                        <input type="password" name="password"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="{{ isset($store) ? 'Leave blank to keep current' : 'Current email used as login' }}">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                        <textarea name="address" rows="2" required
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="Full street address">{{ old('address', $store->address ?? '') }}</textarea>
                        @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">City <span class="text-red-500">*</span></label>
                            <input type="text" name="city" value="{{ old('city', $store->city ?? '') }}" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">State <span class="text-red-500">*</span></label>
                            <input type="text" name="state" value="{{ old('state', $store->state ?? '') }}" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('state') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">ZIP Code <span class="text-red-500">*</span></label>
                            <input type="text" name="zip_code" value="{{ old('zip_code', $store->zip_code ?? '') }}" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('zip_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Country</label>
                            <input type="text" name="country" value="{{ old('country', $store->country ?? 'USA') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('country') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Latitude</label>
                            <input type="text" name="lat" value="{{ old('lat', $store->lat ?? '') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('lat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Longitude</label>
                            <input type="text" name="lon" value="{{ old('lon', $store->lon ?? '') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                            @error('lon') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>


          
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

            <!-- Actions -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <h2 class="font-display font-semibold text-lg mb-5">Publish</h2>
                @if(!$isStaff)
                <label class="flex items-center gap-3 cursor-pointer mb-4">
                    <div class="relative">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $store->is_active ?? true) ? 'checked' : '' }}
                            class="sr-only peer" id="activeToggle">
                        <div class="w-11 h-6 bg-surface-300 rounded-full peer peer-checked:bg-brand-600 transition-colors"></div>
                        <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                    <span class="text-sm text-surface-700 font-medium">Store is Active</span>
                </label>
                <button type="submit" class="w-full px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <span class="flex items-center justify-center gap-2">
                        <i data-lucide="{{ isset($store) ? 'save' : 'plus-circle' }}" class="w-4 h-4"></i>
                        {{ isset($store) ? 'Update Store' : 'Create Store' }}
                    </span>
                </button>
                @else
                <div class="p-4 bg-surface-50 rounded-xl border border-surface-100 flex items-center gap-3 mb-4">
                    <div class="w-2 h-2 rounded-full {{ ($store->is_active ?? true) ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="text-sm font-medium text-surface-700">Store is {{ ($store->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                </div>
                @endif
                <a href="{{ auth()->user()->isAdmin() ? route('admin.stores.index') : route('admin.dashboard') }}" class="block w-full mt-3 px-5 py-3 text-center bg-surface-100 text-surface-700 font-semibold rounded-xl hover:bg-surface-200 transition">
                    {{ auth()->user()->isAdmin() ? 'Cancel' : 'Back to Dashboard' }}
                </a>
            </div>

            <!-- Schedule -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="clock" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Schedule</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Opening Time</label>
                        <input type="time" name="opening_time"
                            value="{{ old('opening_time', isset($store) && $store->opening_time ? \Carbon\Carbon::parse($store->opening_time)->format('H:i') : '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Closing Time</label>
                        <input type="time" name="closing_time"
                            value="{{ old('closing_time', isset($store) && $store->closing_time ? \Carbon\Carbon::parse($store->closing_time)->format('H:i') : '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}>
                    </div>
                </div>
            </div>

            <!-- GST & Logo -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <div class="flex items-center gap-2 mb-5">
                    <i data-lucide="file-text" class="w-5 h-5 text-brand-600"></i>
                    <h2 class="font-display font-semibold text-lg">Other Details</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Tax ID (EIN)</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number', $store->gst_number ?? '') }}"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" {!! $readonlyAtts !!}
                            placeholder="e.g. 12-3456789">
                        @error('gst_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Store Logo</label>
                        @if(!$isStaff)
                        <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer"
                            onclick="document.getElementById('logo_input').click()">
                            @if(isset($store) && $store->logo)
                            <img src="{{ Storage::url($store->logo) }}" class="mx-auto max-h-20 rounded-lg mb-2">
                            <p class="text-xs text-surface-500">Click to replace</p>
                            @else
                            <div class="py-2">
                                <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-surface-300 mb-1"></i>
                                <p class="text-xs text-surface-500">Click to upload</p>
                            </div>
                            @endif
                        </div>
                        <input type="file" name="logo" id="logo_input" accept="image/*" class="hidden"
                            onchange="previewLogo(this)">
                        <div id="logoPreview" class="hidden mt-2 text-center">
                            <img src="" class="mx-auto max-h-20 rounded-lg">
                            <p class="text-xs text-accent-600 mt-1 font-medium">✓ New logo selected</p>
                        </div>
                        @else
                        @if(isset($store) && $store->logo)
                        <div class="p-2 bg-surface-50 rounded-xl border border-surface-100 text-center">
                            <img src="{{ Storage::url($store->logo) }}" class="mx-auto max-h-32 rounded-lg">
                        </div>
                        @else
                        <p class="text-xs text-surface-400 italic">No logo uploaded</p>
                        @endif
                        @endif
                        @error('logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" {!! $readonlyAtts !!}
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Additional notes...">{{ old('notes', $store->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@if(isset($store) && !$isStaff)
<!-- Store Users Section -->
<div class="mt-8 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="font-display font-bold text-2xl text-surface-900">Linked Users</h2>
        <button type="button" onclick="openUserModal()"
            class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add User
        </button>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-100">
                @forelse($store->users as $user)
                <tr class="hover:bg-surface-50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-surface-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $user->role === 'store_admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ $user->role === 'store_admin' ? 'Store Admin' : ($user->role === 'admin' ? 'Super Admin' : 'Staff') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-600' : 'bg-red-600' }}"></span>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">

                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="editUser({{ $user }})" class="text-surface-400 hover:text-brand-600 transition">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.stores.users.destroy', [$store->id, $user->id]) }}" method="POST" onsubmit="return confirm('Remove this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-surface-400 hover:text-red-600 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-surface-400 italic">No users linked to this store yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="fixed inset-0 z-50 hidden bg-surface-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-300" id="userModalContent">
        <div class="p-6 border-b border-surface-100 flex items-center justify-between">
            <h3 class="text-xl font-display font-bold text-surface-900" id="modalTitle">Add Store User</h3>
            <button type="button" onclick="closeUserModal()" class="text-surface-400 hover:text-surface-900 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form action="{{ route('admin.stores.users.store', $store->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="userInputId">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-surface-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="userName" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-surface-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="userEmail" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="userRole" required class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="staff">Staff</option>
                        <option value="store_admin">Store Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="userPhone" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Password <span id="pwdReq" class="text-red-500">*</span></label>
                    <input type="password" name="password" id="userPass" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="userPassConf" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div class="col-span-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" id="userIsActive" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-200 rounded-full peer peer-checked:bg-brand-600 transition-colors"></div>
                            <div class="absolute left-[2px] top-[2px] w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-surface-700 font-medium">Account is Active</span>
                    </label>
                </div>
            </div>
            <div class="pt-4 flex gap-3">
                <button type="submit" class="flex-1 bg-brand-600 text-white font-bold py-3 rounded-xl hover:bg-brand-700 transition">Save User</button>
                <button type="button" onclick="closeUserModal()" class="flex-1 bg-surface-100 text-surface-700 font-bold py-3 rounded-xl hover:bg-surface-200 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.classList.remove('hidden');
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }



    function openUserModal() {
        const modal = document.getElementById('userModal');
        const content = document.getElementById('userModalContent');
        document.getElementById('modalTitle').innerText = 'Add Store User';
        document.getElementById('userInputId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPhone').value = '';
        document.getElementById('userRole').value = 'staff';
        document.getElementById('pwdReq').style.display = 'inline';
        document.getElementById('userPass').required = true;
        document.getElementById('userIsActive').checked = true;

        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function editUser(user) {
        const modal = document.getElementById('userModal');
        const content = document.getElementById('userModalContent');
        document.getElementById('modalTitle').innerText = 'Edit Store User';
        document.getElementById('userInputId').value = user.id;
        document.getElementById('userName').value = user.name;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userPhone').value = user.phone || '';
        document.getElementById('userRole').value = user.role;
        document.getElementById('pwdReq').style.display = 'none';
        document.getElementById('userPass').required = false;
        document.getElementById('userIsActive').checked = !!user.is_active;

        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function closeUserModal() {
        const modal = document.getElementById('userModal');
        const content = document.getElementById('userModalContent');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush