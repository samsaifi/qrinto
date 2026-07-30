@extends('layouts.admin')
@section('title', isset($paperType) ? 'Edit Paper Type' : 'Create Paper Type')

@section('content')
<div class="max-w-2xl">
    <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">{{ isset($paperType) ? 'Edit Paper Type' : 'Create Paper Type' }}</h1>

    <form action="{{ isset($paperType) ? route('admin.paper-types.update', $paperType) : route('admin.paper-types.store') }}"
          method="POST" class="space-y-6">
        @csrf
        @if(isset($paperType)) @method('PUT') @endif

        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $paperType->title ?? '') }}" required
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-surface-700">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paperType->is_active ?? true) ? 'checked' : '' }}
                       class="rounded text-brand-600 focus:ring-brand-500"> Active
            </label>

            @if(isset($paperType) && $paperType->user)
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Created By</label>
                <p class="text-sm text-surface-600">{{ $paperType->user->name }}</p>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Stores</label>
                @if(auth()->user()->isAdmin())
                    @error('stores') <p class="text-xs text-red-500 mb-1">{{ $message }}</p> @enderror
                    @include('admin.paper-types._multiselect', [
                        'items' => $stores->map(fn($s) => ['id' => $s->id, 'name' => $s->store_name]),
                        'selectedIds' => old('stores', isset($paperType) ? $paperType->stores->pluck('id')->map(fn($id) => (string) $id)->toArray() : []),
                        'fieldName' => 'stores',
                        'placeholder' => 'Select stores...',
                        'tagColor' => 'emerald',
                    ])
                @else
                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-emerald-50 text-emerald-700">
                        {{ auth()->user()->store->store_name ?? 'Your Store' }}
                    </span>
                    <p class="text-xs text-surface-400 mt-1">Paper type will be assigned to your store automatically.</p>
                @endif
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                {{ isset($paperType) ? 'Update' : 'Create' }} Paper Type
            </button>
            <a href="{{ route('admin.paper-types.index') }}" class="px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
