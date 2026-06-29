@extends('layouts.app')

@section('title', 'Products - Custom Photo Prints')
@section('meta_description', 'Browse our collection of custom photo prints. Acrylic wall photos, canvas prints, poster prints and more with personalized customization options.')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span class="text-surface-800 font-medium">Products</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-72 flex-shrink-0" x-data="{ filtersOpen: false }">
            <button @click="filtersOpen = !filtersOpen" class="lg:hidden w-full flex items-center justify-between px-5 py-3 bg-white rounded-xl border border-surface-200 shadow-card mb-4">
                <span class="font-semibold text-surface-800">Filters</span>
                <i data-lucide="sliders" class="w-5 h-5 text-surface-500"></i>
            </button>

            <div :class="filtersOpen ? 'block' : 'hidden'" class="lg:block">
                <form action="{{ route('products.index') }}" method="GET" class="space-y-6">
                    <!-- Categories -->
                    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                        <h3 class="font-display font-semibold text-surface-800 mb-4 flex items-center gap-2">
                            <i data-lucide="grid-2x2" class="w-4 h-4 text-brand-500"></i> Categories
                        </h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-50 cursor-pointer transition">
                                <input type="radio" onchange="this.form.submit()" name="category" value="" {{ !request('category') ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                                <span class="text-sm text-surface-800 font-medium">All Categories</span>
                            </label>
                            
                            @foreach($categories as $category)
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-50 cursor-pointer transition">
                                    <input type="radio" onchange="this.form.submit()" name="category" value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                                    <span class="text-sm font-bold text-surface-800">{{ $category->name }}</span>
                                </label>
                                
                                @if(isset($category->children) && $category->children->count() > 0)
                                <div class="pl-7 space-y-2 mt-2">
                                    @foreach($category->children as $child)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-50 cursor-pointer transition">
                                        <input type="radio" onchange="this.form.submit()" name="category" value="{{ $child->slug }}" {{ request('category') == $child->slug ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                                        <span class="text-sm text-surface-600">{{ $child->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Print Type -->
                    <!-- <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                        <h3 class="font-display font-semibold text-surface-800 mb-4 flex items-center gap-2">
                            <i data-lucide="printer" class="w-4 h-4 text-brand-500"></i> Print Type
                        </h3>
                        <div class="space-y-2">
                            @foreach(['acrylic' => 'Acrylic', 'canvas' => 'Canvas', 'poster' => 'Poster'] as $key => $label)
                            <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-surface-50 cursor-pointer transition">
                                <input type="radio" onchange="this.form.submit()" name="print_type" value="{{ $key }}" {{ request('print_type') == $key ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                                <span class="text-sm text-surface-600">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div> -->

                    <!-- Price Range -->
                    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                        <h3 class="font-display font-semibold text-surface-800 mb-4 flex items-center gap-2">
                            <i data-lucide="dollar-sign" class="w-4 h-4 text-brand-500"></i> Price Range
                        </h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-surface-500 mb-1 block">Min</label>
                                <input type="number" name="min_price" value="{{ request('min_price') }}"
                                       placeholder="$0" class="w-full rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="text-xs text-surface-500 mb-1 block">Max</label>
                                <input type="number" name="max_price" value="{{ request('max_price') }}"
                                       placeholder="$9999" class="w-full rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['category', 'print_type', 'min_price', 'max_price']))
                    <a href="{{ route('products.index') }}" class="block text-center text-sm text-surface-500 hover:text-brand-600 transition">
                        Clear All Filters
                    </a>
                    @endif
                </form>
            </div>
        </aside>

        <!-- Products Grid -->
        <div class="flex-1">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="font-display font-bold text-2xl lg:text-3xl text-surface-900">
                        @if(request('category'))
                            {{ ucfirst(str_replace('-', ' ', request('category'))) }} Products
                        @else
                            All Products
                        @endif
                    </h1>
                    <p class="text-sm text-surface-500 mt-1">{{ $products->total() }} products found</p>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-sm text-surface-500">Sort:</label>
                    <select onchange="window.location.href = this.value" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 pr-10">
                        @foreach(['latest' => 'Newest', 'price_low' => 'Price: Low to High', 'price_high' => 'Price: High to Low', 'popular' => 'Most Popular', 'name' => 'Name A-Z'] as $key => $label)
                        <option value="{{ route('products.index', array_merge(request()->except('sort', 'page'), ['sort' => $key])) }}"
                                {{ request('sort', 'latest') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Custom Print Card -->
                <div class="group flex flex-col h-full bg-white rounded-2xl border border-surface-100 shadow-sm hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-500 transform hover:-translate-y-1 overflow-hidden">
                    <a href="{{ route('custom-print.index') }}" class="block">
                        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800">
                            <!-- Animated Background Pattern -->
                            <div class="absolute inset-0 opacity-10">
                                <div class="absolute top-6 left-6 w-20 h-20 border-2 border-white rounded-2xl rotate-12 group-hover:rotate-45 transition-transform duration-700"></div>
                                <div class="absolute bottom-10 right-10 w-16 h-16 border-2 border-white rounded-full group-hover:scale-125 transition-transform duration-700"></div>
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 border border-white rounded-3xl rotate-45 group-hover:rotate-90 transition-transform duration-1000"></div>
                            </div>

                            <!-- Center Content -->
                            <div class="relative z-10 flex flex-col items-center justify-center h-full text-white p-6">
                                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-500">
                                    <i data-lucide="upload" class="w-10 h-10"></i>
                                </div>
                                <h3 class="font-display font-bold text-xl mb-2 tracking-tight">Upload Your Design</h3>
                                <p class="text-sm text-white/80 text-center leading-relaxed">Already have a design? Upload it and we'll print it for you!</p>
                            </div>

                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                    </a>

                    <div class="p-4 flex-1 flex flex-col">
                        <span class="text-xs font-medium text-brand-600 uppercase tracking-wider">Custom Print</span>
                        <h3 class="font-display font-semibold text-surface-900 mt-1 group-hover:text-brand-600 transition-colors line-clamp-2">
                            <a href="{{ route('custom-print.index') }}">Upload & Print Your Design</a>
                        </h3>
                        <div class="flex items-center gap-2 mt-3 mb-4">
                            <span class="text-lg font-bold text-surface-900">From $20</span>
                            <span class="text-xs text-surface-400 bg-surface-100 px-2 py-0.5 rounded-full">Multiple sizes</span>
                        </div>
                        <a href="{{ route('custom-print.index') }}"
                           class="mt-auto w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-semibold rounded-xl hover:from-brand-700 hover:to-brand-800 hover:shadow-lg hover:shadow-brand-500/25 transition-all duration-300">
                            <i data-lucide="upload" class="w-4 h-4"></i> Custom Print
                        </a>
                    </div>
                </div>

                @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $products->links() }}
            </div>
            @else
            <div class="text-center py-20 bg-white rounded-2xl border border-surface-100">
                <i data-lucide="package-x" class="w-16 h-16 text-surface-300 mx-auto mb-4"></i>
                <h3 class="font-display font-semibold text-xl text-surface-700 mb-2">No products found</h3>
                <p class="text-surface-500 mb-6">Try adjusting your filters or search criteria.</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Clear Filters
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
