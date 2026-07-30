@extends('layouts.admin')
@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content')
<div class="max-w-2xl">
    <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h1>

    <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
        @csrf
        @if(isset($category)) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Parent Category</label>
                <select name="parent_id" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">None (Top Level)</option>
                    @foreach($parentCategories as $parent)
                    @if(!isset($category) || $parent->id !== $category->id)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                    @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $category->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Image</label>
                <input type="file" name="image" accept="image/*" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-brand-100 file:text-brand-700 file:font-semibold cursor-pointer">
                @if(isset($category) && $category->image)
                <img src="{{ $category->image_url }}" class="w-20 h-20 rounded-lg object-cover mt-2">
                @endif
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                       class="rounded text-brand-600 focus:ring-brand-500"> Active
            </label>

            @if(isset($category) && $category->user)
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Created By</label>
                <p class="text-sm text-surface-600">{{ $category->user->name }}</p>
            </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                {{ isset($category) ? 'Update' : 'Create' }} Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
