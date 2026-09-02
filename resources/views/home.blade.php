@extends('layouts.app')

@section('title', 'Custom Photo Printing & Wall Art')
@section('meta_description', 'Transform your photos into stunning acrylic, canvas, and poster wall art. Customize size, material, and framing options for the perfect personalized print.')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-br from-surface-900 via-surface-800 to-brand-900">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 py-20 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="animate-slide-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-sm text-brand-300 mb-6">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    Premium Custom Prints
                </div>
                <h1 class="font-display font-extrabold text-4xl lg:text-6xl text-white leading-tight mb-6">
                    Turn Your Photos Into
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-accent-400">
                        Stunning Art
                    </span>
                </h1>
                <p class="text-lg text-surface-300 leading-relaxed mb-8 max-w-xl">
                    Choose from premium acrylic, canvas, and poster prints. Customize every detail - size, material, frame, and more. Made with love, delivered to your door.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-semibold rounded-2xl hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-500/25 hover:shadow-2xl hover:shadow-brand-500/40 transition-all duration-300 transform hover:-translate-y-1 text-center">
                        <i data-lucide="palette" class="w-5 h-5"></i> Explore Products
                    </a>
                    <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-2xl border border-white/20 hover:bg-white/20 hover:shadow-lg transition-all duration-300 text-center">
                        <i data-lucide="trending-up" class="w-5 h-5"></i> Best Sellers
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="flex flex-wrap items-center gap-8 mt-12 pt-8 border-t border-white/10">
                    <div class="flex items-center gap-2 text-sm text-surface-300">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                            <i data-lucide="award" class="w-5 h-5 text-brand-400"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Premium Quality</p>
                            <p class="text-xs text-surface-400">HD Printing</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-surface-300">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                            <i data-lucide="truck" class="w-5 h-5 text-accent-400"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Free Delivery</p>
                            <p class="text-xs text-surface-400">On $999+</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-surface-300">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5 text-blue-400"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Secure Pay</p>
                            <p class="text-xs text-surface-400">100% Safe</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Image Showcase -->
            <div class="hidden lg:block relative">
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-brand-500/20 to-accent-500/20 rounded-3xl blur-2xl"></div>
                    <div class="relative grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/10 animate-float">
                                <div class="aspect-[3/4] rounded-xl bg-gradient-to-br from-brand-400/30 to-brand-600/30 flex items-center justify-center">
                                    <i data-lucide="image" class="w-16 h-16 text-white/40"></i>
                                </div>
                                <p class="text-xs text-white/60 mt-2 text-center">Acrylic Print</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/10" style="animation: float 6s ease-in-out 1s infinite">
                                <div class="aspect-square rounded-xl bg-gradient-to-br from-accent-400/30 to-accent-600/30 flex items-center justify-center">
                                    <i data-lucide="frame" class="w-12 h-12 text-white/40"></i>
                                </div>
                                <p class="text-xs text-white/60 mt-2 text-center">Framed Photo</p>
                            </div>
                        </div>
                        <div class="space-y-4 mt-8">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/10" style="animation: float 6s ease-in-out 2s infinite">
                                <div class="aspect-square rounded-xl bg-gradient-to-br from-purple-400/30 to-purple-600/30 flex items-center justify-center">
                                    <i data-lucide="grid-2x2" class="w-12 h-12 text-white/40"></i>
                                </div>
                                <p class="text-xs text-white/60 mt-2 text-center">Photo Collage</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/10" style="animation: float 6s ease-in-out 0.5s infinite">
                                <div class="aspect-[4/3] rounded-xl bg-gradient-to-br from-blue-400/30 to-blue-600/30 flex items-center justify-center">
                                    <i data-lucide="layout" class="w-12 h-12 text-white/40"></i>
                                </div>
                                <p class="text-xs text-white/60 mt-2 text-center">Canvas Print</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
@if($categories->count() > 0)
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="font-display font-bold text-3xl lg:text-4xl text-surface-900 mb-4">Shop by Category</h2>
            <p class="text-surface-500 max-w-2xl mx-auto">Discover our curated collection of custom print categories</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
               class="group relative overflow-hidden rounded-2xl bg-white border border-surface-100 shadow-card hover:shadow-xl transition-all duration-500 transform hover:-translate-y-1">
                <div class="aspect-[4/3] bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center overflow-hidden">
                    @if($category->image)
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-brand-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="image" class="w-8 h-8 text-brand-500"></i>
                    </div>
                    @endif
                </div>
                <div class="p-4 text-center">
                    <h3 class="font-display font-semibold text-surface-900 group-hover:text-brand-600 transition-colors">{{ $category->name }}</h3>
                    <p class="text-xs text-surface-400 mt-1">{{ $category->products->count() }} Products</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif



<!-- How It Works -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="font-display font-bold text-3xl lg:text-4xl text-surface-900 mb-4">How It Works</h2>
            <p class="text-surface-500 max-w-2xl mx-auto">Create your perfect custom print in 4 simple steps</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach([
                ['icon' => 'search', 'title' => 'Choose Product', 'desc' => 'Browse our collection of premium print types and styles', 'color' => 'brand'],
                ['icon' => 'upload', 'title' => 'Upload Photos', 'desc' => 'Upload your high-quality photos directly to our platform', 'color' => 'blue'],
                ['icon' => 'sliders', 'title' => 'Customize', 'desc' => 'Select size, material, frame and other options', 'color' => 'purple'],
                ['icon' => 'truck', 'title' => 'Get Delivered', 'desc' => 'Secure payment & fast delivery to your doorstep', 'color' => 'accent'],
            ] as $index => $step)
            <div class="relative group">
                <div class="bg-white rounded-2xl p-8 border border-white shadow-sm hover:shadow-xl hover:shadow-brand-500/10 transition-all duration-500 transform hover:-translate-y-1 h-full">
                    <div class="w-14 h-14 rounded-2xl bg-{{ $step['color'] }}-100 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="{{ $step['icon'] }}" class="w-7 h-7 text-{{ $step['color'] }}-600"></i>
                    </div>
                    <div class="absolute top-8 right-8 text-5xl font-display font-bold text-surface-100 group-hover:text-brand-100 transition-colors">{{ $index + 1 }}</div>
                    <h3 class="font-display font-semibold text-lg text-surface-900 mb-2">{{ $step['title'] }}</h3>
                    <p class="text-sm text-surface-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>



<!-- CTA Section -->
<section class="py-20">
    <div class="max-w-5xl mx-auto px-4">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-600 to-brand-800 p-12 lg:p-16 text-center">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-accent-500/20 rounded-full blur-3xl"></div>
            <div class="relative">
                <h2 class="font-display font-bold text-3xl lg:text-4xl text-white mb-4">Ready to Create Something Amazing?</h2>
                <p class="text-brand-200 text-lg mb-8 max-w-2xl mx-auto">Start customizing your perfect print today. Upload your photos and see the magic happen.</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-brand-700 font-bold rounded-2xl hover:bg-brand-50 shadow-xl hover:shadow-2xl hover:shadow-brand-500/30 transition-all duration-300 transform hover:-translate-y-1">
                    <i data-lucide="sparkles" class="w-5 h-5"></i> Start Creating Now
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
