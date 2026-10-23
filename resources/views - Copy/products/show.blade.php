@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name . ' - Custom Print')
@section('meta_description', $product->meta_description ?: $product->short_description)

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ $product->name }}",
    "description": "{{ $product->short_description }}",
    "image": "{{ $product->featured_image_url }}",
    "offers": {
        "@type": "Offer",
        "price": "{{ $product->base_price }}",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock"
    }
}
</script>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <a href="{{ route('products.index') }}" class="hover:text-brand-600 transition">Products</a>
        @if($product->category)
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-brand-600 transition">{{ $product->category->name }}</a>
        @endif
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span class="text-surface-800 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-12">
        <!-- Image Gallery -->
        <div x-data="{ activeImage: 0 }" class="space-y-4">
            <div class="aspect-square rounded-2xl overflow-hidden bg-surface-100 border border-surface-200 shadow-card">
                @if($product->images->count() > 0)
                @foreach($product->images as $index => $image)
                <img x-show="activeImage === {{ $index }}" src="{{ $image->url }}" alt="{{ $image->alt_text ?: $product->name }}"
                     class="w-full h-full object-cover" x-transition>
                @endforeach
                @elseif($product->featured_image_url)
                <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                    <i data-lucide="image" class="w-24 h-24 text-brand-300"></i>
                </div>
                @endif
            </div>

            @if($product->images->count() > 1)
            <div class="flex gap-3 overflow-x-auto pb-2">
                @foreach($product->images as $index => $image)
                <button @click="activeImage = {{ $index }}"
                        :class="activeImage === {{ $index }} ? 'ring-2 ring-brand-500 ring-offset-2' : 'ring-1 ring-surface-200'"
                        class="flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden transition-all">
                    <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Product Info -->
        <div>
            @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
               class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700 mb-3">
                {{ $product->category->name }}
            </a>
            @endif

            <h1 class="font-display font-bold text-3xl lg:text-4xl text-surface-900 mb-4">{{ $product->name }}</h1>

            @if($product->short_description)
            <p class="text-surface-600 text-lg leading-relaxed mb-6">{{ $product->short_description }}</p>
            @endif

            <!-- Price -->
            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-surface-100">
                <span class="text-3xl font-bold text-surface-900">${{ number_format($product->base_price, 0) }}</span>
                @if($product->compare_price)
                <span class="text-lg text-surface-400 line-through">${{ number_format($product->compare_price, 0) }}</span>
                <span class="px-3 py-1 bg-accent-100 text-accent-700 text-sm font-bold rounded-lg">Save {{ $product->discount_percent }}%</span>
                @endif
                <span class="text-sm text-surface-500">Starting price</span>
            </div>

            <!-- Options Preview -->
            @if($product->optionGroups->count() > 0)
            <div class="space-y-6 mb-8">
                @foreach($product->optionGroups as $group)
                <div>
                    <h3 class="text-sm font-semibold text-surface-700 mb-3 flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-brand-500"></i>
                        {{ $group->name }}
                        @if($group->is_required) <span class="text-xs text-red-500">*Required</span> @endif
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($group->values->where('is_active', true)->take(6) as $value)
                        <span class="px-4 py-2 rounded-xl bg-surface-100 text-sm text-surface-600 border border-surface-200">
                            {{ $value->label }}
                            @if($value->price_modifier != 0)
                            <span class="text-xs text-brand-600 font-medium">({{ $value->formatted_price_modifier }})</span>
                            @endif
                        </span>
                        @endforeach
                        @if($group->values->where('is_active', true)->count() > 6)
                        <span class="px-4 py-2 rounded-xl bg-surface-50 text-sm text-surface-400">+{{ $group->values->where('is_active', true)->count() - 6 }} more</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- CTA -->
            <a href="{{ route('products.customize', $product) }}"
               class="w-full inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-lg font-bold rounded-2xl hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-200 transition-all transform hover:-translate-y-0.5">
                <i data-lucide="sparkles" class="w-5 h-5"></i> Customize & Order
            </a>

            <!-- Features -->
            <div class="grid grid-cols-2 gap-4 mt-8">
                @foreach([
                    ['icon' => 'shield-check', 'text' => 'Quality Guaranteed'],
                    ['icon' => 'truck', 'text' => 'Free Shipping $999+'],
                    ['icon' => 'refresh-cw', 'text' => 'Easy Returns'],
                    ['icon' => 'headphones', 'text' => '24/7 Support'],
                ] as $feature)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-50">
                    <i data-lucide="{{ $feature['icon'] }}" class="w-5 h-5 text-accent-600 flex-shrink-0"></i>
                    <span class="text-sm text-surface-600">{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($product->description)
    <div class="mt-16 bg-white rounded-2xl border border-surface-100 shadow-card p-8 lg:p-12">
        <h2 class="font-display font-bold text-2xl text-surface-900 mb-6">Product Details</h2>
        <div class="prose prose-brand max-w-none text-surface-600">
            {!! nl2br(e($product->description)) !!}
        </div>
    </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-16">
        <h2 class="font-display font-bold text-2xl text-surface-900 mb-8">You May Also Like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            @include('components.product-card', ['product' => $related])
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
