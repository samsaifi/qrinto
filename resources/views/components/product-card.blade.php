<!-- Product Card Component -->
<div class="group flex flex-col h-full bg-white rounded-2xl border border-surface-100 shadow-sm hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-500 transform hover:-translate-y-1 overflow-hidden">
    <a href="{{ route('noritsu.editor', $product) }}" class="block">
        <div class="relative aspect-square overflow-hidden bg-surface-100">
            @if($product->featured_image_url)
            <img src="{{ $product->featured_image_url }}" alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
            @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100 transition-transform duration-700 group-hover:scale-105">
                <i data-lucide="image" class="w-16 h-16 text-brand-300 drop-shadow-sm"></i>
            </div>
            @endif

            <!-- Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-2">
                @if($product->is_featured)
                <span class="px-2.5 py-1 bg-brand-600 text-white text-xs font-bold rounded-lg shadow-lg">Featured</span>
                @endif
                @if($product->discount_percent)
                <span class="px-2.5 py-1 bg-accent-600 text-white text-xs font-bold rounded-lg shadow-lg">-{{ $product->discount_percent }}%</span>
                @endif
            </div>

            <!-- Quick Customize overlay -->
            <div class="absolute inset-0 bg-brand-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                <span class="inline-flex items-center gap-1 px-3 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl shadow-lg">
                    <i data-lucide="palette" class="w-3 h-3"></i> Customize
                </span>
            </div>
        </div>
    </a>

    <div class="p-4 flex-1 flex flex-col">
        @if($product->category)
        <span class="text-xs font-medium text-brand-600 uppercase tracking-wider">{{ $product->category->name }}</span>
        @endif
        <h3 class="font-display font-semibold text-surface-900 mt-1 group-hover:text-brand-600 transition-colors line-clamp-2">
            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <div class="flex items-center gap-2 mt-3 mb-4">
            <span class="text-lg font-bold text-surface-900">${{ number_format($product->base_price, 0) }}</span>
            @if($product->compare_price)
            <span class="text-sm text-surface-400 line-through">${{ number_format($product->compare_price, 0) }}</span>
            @endif
        </div>
        <a href="{{ route('noritsu.editor', $product) }}"
           class="mt-auto w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-surface-900 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 hover:shadow-lg hover:shadow-brand-500/25 transition-all duration-300">
            <i data-lucide="sparkles" class="w-4 h-4"></i> Customize Now
        </a>
    </div>
</div>
