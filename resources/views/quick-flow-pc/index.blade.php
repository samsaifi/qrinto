@extends('layouts.quick-flow-pc')

@section('title', 'Qrinto — Custom Print Studio | Personalized Photo Cards & Business Stationery')
@section('meta_description', 'Design and print custom greeting cards, photo prints, business cards, and personalized stationery online with Qrinto. Instant customization and fast store pickup.')
@section('meta_keywords', 'custom print studio, photo greeting cards, personalized birthday cards, business cards, photo printing, Qrinto, print shop near me')
@section('header_title', 'Create')

@section('json_ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Qrinto Print Studio",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('images/logo.png') }}",
  "sameAs": []
}
</script>
@endsection

@push('styles')
    <!-- Swiper CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        .hero-pattern-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(rgba(148, 163, 184, 0.28) 1.2px, transparent 1.2px),
                linear-gradient(to right, rgba(241, 245, 249, 0.7) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(241, 245, 249, 0.7) 1px, transparent 1px);
            background-size: 24px 24px, 48px 48px, 48px 48px;
        }

        .hero-gradient-overlay {
            background: radial-gradient(circle at 85% 20%, rgba(111, 182, 58, 0.08) 0%, rgba(255, 255, 255, 0) 55%),
                radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.06) 0%, rgba(255, 255, 255, 0) 50%);
        }

        /* ===== Colorful Animated Hero Background ===== */
        .hero-vibrant-bg {
            background: linear-gradient(-45deg, #0f172a, #1e1b4b, #31104b, #4c0519, #B94F28, #1e1b4b);
            background-size: 400% 400%;
            animation: gradientFlow 15s ease infinite;
        }

        @keyframes gradientFlow {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Hero Geometric Mesh Overlay */
        .hero-mesh-overlay {
            background-image:
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 1px, transparent 0),
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px, 40px 40px, 40px 40px;
        }

        /* Animated Ambient Blobs */
        .hero-orb-1 {
            position: absolute;
            top: -12%;
            left: 5%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.35) 0%, rgba(168, 85, 247, 0.15) 50%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            animation: floatOrb1 14s ease-in-out infinite alternate;
        }

        .hero-orb-2 {
            position: absolute;
            bottom: -15%;
            right: 2%;
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.35) 0%, rgba(59, 130, 246, 0.15) 50%, transparent 70%);
            border-radius: 50%;
            filter: blur(70px);
            animation: floatOrb2 16s ease-in-out infinite alternate;
        }

        .hero-orb-3 {
            position: absolute;
            top: 25%;
            right: 35%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.25) 0%, rgba(234, 88, 12, 0.1) 50%, transparent 70%);
            border-radius: 50%;
            filter: blur(50px);
            animation: floatOrb3 12s ease-in-out infinite alternate;
        }

        @keyframes floatOrb1 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(45px, 35px) scale(1.15);
            }
        }

        @keyframes floatOrb2 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(-40px, -45px) scale(1.2);
            }
        }

        @keyframes floatOrb3 {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(30px, -30px) scale(0.9);
            }
        }

        /* Floating Editing Tool Badges */
        .floating-tool-1 {
            animation: floatBadge 5s ease-in-out infinite;
        }

        .template-card-premium {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .template-card-premium:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        .template-card-premium:hover .tpl-img {
            transform: scale(1.06);
        }

        .floating-tool-2 {
            animation: floatBadge 6s ease-in-out infinite 0.8s;
        }

        .floating-tool-3 {
            animation: floatBadge 7s ease-in-out infinite 1.6s;
        }

        .floating-tool-4 {
            animation: floatBadge 6.5s ease-in-out infinite 2.2s;
        }

        @keyframes floatBadge {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(1.5deg);
            }
        }

        /* Glassmorphism Cards & Toolbars */
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-card-hover {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card-hover:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.35);
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        /* SVG Tool Icon Animations */
        .tool-icon-box {
            position: relative;
            transition: all 0.3s ease;
        }

        .tool-icon-box:hover {
            transform: scale(1.15) rotate(5deg);
        }

        .svg-pulse {
            animation: svgPulse 2s ease-in-out infinite;
        }

        @keyframes svgPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.85;
            }
        }

        .svg-rotate-slow {
            animation: svgRotate 12s linear infinite;
        }

        @keyframes svgRotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ===== Swiper Slider Styling ===== */
        .hero-swiper {
            width: 100%;
            border-radius: 1.5rem;
            overflow: hidden;
        }

        .hero-swiper .swiper-slide {
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .hero-swiper .swiper-slide-active {
            opacity: 1;
        }

        .hero-swiper-pagination .swiper-pagination-bullet {
            background: rgba(255, 255, 255, 0.35);
            opacity: 1;
            width: 9px;
            height: 9px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0 4px !important;
        }

        .hero-swiper-pagination .swiper-pagination-bullet-active {
            background: #ec4899;
            width: 32px;
            border-radius: 999px;
            box-shadow: 0 0 16px rgba(236, 72, 153, 0.9);
        }

        .swiper-nav-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            z-index: 20;
        }

        .swiper-nav-btn:hover {
            background: rgba(236, 72, 153, 0.8);
            border-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.12);
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.4);
        }

        .category-swiper .swiper-slide {
            height: auto;
        }

        /* General UI Styling */
        .product-card {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -16px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(236, 72, 153, 0.12);
        }

        .product-card:hover .product-icon {
            transform: scale(1.1);
        }

        .product-card:hover .product-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .product-card.disabled-card {
            opacity: 0.45;
            filter: grayscale(1);
            pointer-events: none;
        }

        .product-icon {
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-icon svg {
            width: 100%;
            height: 100%;
            fill: currentColor !important;
        }

        .product-arrow {
            opacity: 0;
            transform: translateX(-8px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-connector {
            position: relative;
        }

        .step-connector::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -1.5rem;
            width: 2rem;
            height: 2px;
            background: repeating-linear-gradient(90deg, #c7d2fe 0, #c7d2fe 4px, transparent 4px, transparent 8px);
            transform: translateY(-50%);
        }

        .step-connector:last-child::after {
            display: none;
        }

        .step-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.08);
        }

        .feature-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.06);
        }

        .review-card-home {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .review-card-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.06);
        }

        .fade-in {
            animation: fadeIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .fade-in-d1 {
            animation-delay: 0.1s;
        }

        .fade-in-d2 {
            animation-delay: 0.2s;
        }

        .fade-in-d3 {
            animation-delay: 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    <div class=" ">

        {{-- ===== SECTION 1: HERO HEADER (WHITE PATTERN BACKGROUND HERO SECTION) ===== --}}
        <section
            class="hero-pattern-bg relative w-full px-6 lg:px-12 pt-8 sm:pt-10 pb-6 sm:pb-8 border-b border-slate-200/80 overflow-hidden">
            <!-- Ambient Soft Radial Glow -->
            <div class="absolute inset-0 pointer-events-none hero-gradient-overlay"></div>

            <!-- Giant Background Watermark Text "Quick Print" (Bottom Right) -->
            <div
                class="absolute right-4 sm:right-10 bottom-2 text-[140px] sm:text-[200px] font-black text-slate-200/40 select-none pointer-events-none tracking-tighter leading-none z-0">
                Quick Print
            </div>

            <div class="max-w-[1400px] mx-auto w-full relative z-10">
                {{-- Top Navigation / Breadcrumbs --}}
                <div class="flex items-center justify-between gap-3 mb-6">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <span class="text-slate-900 font-extrabold flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-brand-600"></i>
                            <span>Home</span>
                        </span>
                    </nav>
                </div>

                <div class="grid grid-cols-12 gap-6 lg:gap-10 items-center">
                    {{-- Left Column: Copy & CTAs --}}
                    <div class="col-span-12 lg:col-span-6">
                        <!-- Badge Tag -->
                        <div class="mb-3">
                            <span
                                class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 border border-brand-200/80 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest shadow-2xs">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-600"></i>
                                NEXT-GEN INTERACTIVE PRINT STUDIO
                            </span>
                        </div>

                        <!-- Heading -->
                        <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 leading-tight tracking-tight">
                            Turn your memories into <span class="text-brand-600 italic"
                                style="font-family: 'Playfair Display', serif;">extraordinary prints.</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-sm sm:text-base text-slate-600 font-medium mt-3 leading-relaxed max-w-2xl">
                            Unleash your creativity with our real-time design studio. Transform photos into custom business
                            cards, greeting cards, marketing materials, and personalized stationery.
                        </p>

                        <!-- CTA Action Buttons -->
                        <div class="flex flex-wrap items-center gap-3 mt-5">
                            <a href="{{ route('flow-pc.find-store') }}"
                                class="bg-brand-600 hover:bg-brand-700 text-white font-extrabold px-6 py-2.5 rounded-xl transition-all duration-200 active:scale-95 flex items-center gap-2 shadow-md text-xs sm:text-sm">
                                <i data-lucide="store" class="w-4 h-4 text-white"></i>
                                <span>Start Designing Now</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: HOW IT WORKS (6 STEPS IN ONE HORIZONTAL ROW IN BRAND COLOR) --}}
                    <div class="col-span-12 lg:col-span-6">
                        <div class="relative pt-2">
                            {{-- Top Title Header --}}
                            <div class="flex items-center justify-between pb-3 mb-6 border-b border-slate-200/80">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                                    <h3 class="text-base font-black text-slate-900 tracking-tight">How It Works</h3>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200/80 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                    6 Steps Flow
                                </span>
                            </div>

                            {{-- 6 Steps in ONE HORIZONTAL ROW --}}
                            <div class="grid grid-cols-6 gap-1 relative">
                                {{-- Brand Green Background Connecting Line across all steps --}}
                                <div class="absolute top-3 inset-x-4 h-0.5 bg-brand-500/80 -z-0"></div>

                                {{-- Step 1 --}}
                                <a href="{{ route('flow-pc.find-store') }}"
                                    class="relative z-10 flex flex-col items-center text-center group cursor-pointer">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs group-hover:scale-110 group-hover:bg-brand-700 transition-all duration-200">
                                        1
                                    </div>
                                    <h4
                                        class="text-[11px] font-extrabold text-slate-900 group-hover:text-brand-600 transition-colors leading-tight mt-2.5">
                                        Select Store
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Branch</p>
                                </a>

                                {{-- Step 2 --}}
                                <a href="#products"
                                    class="relative z-10 flex flex-col items-center text-center group cursor-pointer">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs group-hover:scale-110 group-hover:bg-brand-700 transition-all duration-200">
                                        2
                                    </div>
                                    <h4
                                        class="text-[11px] font-extrabold text-slate-900 group-hover:text-brand-600 transition-colors leading-tight mt-2.5">
                                        Select Card
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Product</p>
                                </a>

                                {{-- Step 3 --}}
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs">
                                        3
                                    </div>
                                    <h4 class="text-[11px] font-extrabold text-slate-900 leading-tight mt-2.5">
                                        Select Size
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Dimensions
                                    </p>
                                </div>

                                {{-- Step 4 --}}
                                <a href="{{ url('/pc/templates') }}"
                                    class="relative z-10 flex flex-col items-center text-center group cursor-pointer">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs group-hover:scale-110 group-hover:bg-brand-700 transition-all duration-200">
                                        4
                                    </div>
                                    <h4
                                        class="text-[11px] font-extrabold text-slate-900 group-hover:text-brand-600 transition-colors leading-tight mt-2.5">
                                        Select Template
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Preset</p>
                                </a>

                                {{-- Step 5 --}}
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs">
                                        5
                                    </div>
                                    <h4 class="text-[11px] font-extrabold text-slate-900 leading-tight mt-2.5">
                                        Customize
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Design</p>
                                </div>

                                {{-- Step 6 --}}
                                <div class="relative z-10 flex flex-col items-center text-center">
                                    <div
                                        class="w-6 h-6 rounded-full bg-brand-600 text-white text-[11px] font-black flex items-center justify-center ring-4 ring-brand-100 shadow-2xs">
                                        6
                                    </div>
                                    <h4 class="text-[11px] font-extrabold text-brand-900 leading-tight mt-2.5">
                                        Checkout
                                    </h4>
                                    <p class="text-[9.5px] font-semibold text-brand-700 hidden sm:block mt-0.5">Order</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== SECTION 2: SHOP BY CATEGORY (PRODUCT TYPES) ===== --}}
        <section id="products"
            class="w-full bg-white pt-4 sm:pt-4 pb-12 px-6 sm:px-10 border-y border-slate-100/80 scroll-mt-20">
            <div class="max-w-[1400px] mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-6">
                    <div class="max-w-2xl">
                        <!-- Main Heading with Gradient Accent & Animated Vector Underline SVG -->
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                            Select a <span class="text-slate-950 italic relative inline-block"
                                style="font-family: 'Playfair Display', serif;">
                                Product Type
                                <svg class="absolute -bottom-2 left-0 w-full h-3 text-pink-500/60" viewBox="0 0 200 12"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 9C50 3 150 3 198 9" stroke="currentColor" stroke-width="4"
                                        stroke-linecap="round" stroke-dasharray="200" stroke-dashoffset="0"
                                        class="animate-pulse" />
                                </svg>
                            </span>
                        </h2>

                        <!-- Subtitle with Animated Mouse Pointer Icon -->
                        <p class="text-base sm:text-lg text-slate-500 mt-4 leading-relaxed flex items-center gap-2.5">
                            <span
                                class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-brand-100 text-brand-600 shrink-0 shadow-xs">
                                <i data-lucide="mouse-pointer-click" class="w-3.5 h-3.5 animate-bounce"></i>
                            </span>
                            <span>Please select one product type below to launch your interactive print studio.</span>
                        </p>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center gap-3 shrink-0">
                        <button
                            class="swiper-nav-btn category-swiper-prev !static !w-12 !h-12 !bg-slate-50 hover:!bg-brand-600 !text-slate-800 hover:!text-white !border-slate-200/90 shadow-md hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <button
                            class="swiper-nav-btn category-swiper-next !static !w-12 !h-12 !bg-slate-50 hover:!bg-brand-600 !text-slate-800 hover:!text-white !border-slate-200/90 shadow-md hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                @php
                    $cardThemes = [
                        [
                            'border' => 'border-pink-200/90 hover:border-brand-hover hover:shadow-brand-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-pink-500/15 via-rose-500/10 to-purple-500/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-hover',
                            'titleHover' => 'group-hover:text-brand-hover',
                            'accent' => 'pink',
                        ],
                        [
                            'border' => 'border-brand-200/90 hover:border-brand-500 hover:shadow-brand-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-brand-500/15 via-brand-400/10 to-brand-600/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-600',
                            'titleHover' => 'group-hover:text-brand-600',
                            'accent' => 'brand',
                        ],
                        [
                            'border' => 'border-purple-200/90 hover:border-purple-500 hover:shadow-purple-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-purple-500/15 via-brand-500/10 to-violet-500/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-600',
                            'titleHover' => 'group-hover:text-purple-600',
                            'accent' => 'purple',
                        ],
                        [
                            'border' => 'border-amber-200/90 hover:border-amber-500 hover:shadow-amber-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-amber-500/15 via-orange-500/10 to-yellow-500/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-600',
                            'titleHover' => 'group-hover:text-amber-600',
                            'accent' => 'amber',
                        ],
                        [
                            'border' => 'border-emerald-200/90 hover:border-emerald-500 hover:shadow-emerald-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-emerald-500/15 via-teal-500/10 to-green-500/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-600',
                            'titleHover' => 'group-hover:text-emerald-600',
                            'accent' => 'emerald',
                        ],
                        [
                            'border' => 'border-fuchsia-200/90 hover:border-fuchsia-500 hover:shadow-fuchsia-500/10',
                            'bg' => 'bg-white',
                            'imgBg' => 'from-fuchsia-500/15 via-pink-500/10 to-rose-500/20',
                            'badge' => 'bg-brand-500 text-white shadow-md shadow-brand-500/30',
                            'btn' => 'bg-brand-500 text-white hover:bg-brand-600',
                            'titleHover' => 'group-hover:text-fuchsia-600',
                            'accent' => 'fuchsia',
                        ],
                    ];
                @endphp

                <!-- CATEGORY SWIPER CAROUSEL -->
                <div class="swiper category-swiper overflow-visible py-4">
                    <div class="swiper-wrapper">
                        @foreach ($productTypes as $index => $type)
                            @php
                                $theme = $cardThemes[$index % count($cardThemes)];
                                $slug = strtolower($type->slug ?? $type->name);
                            @endphp

                            <div class="swiper-slide">
                                @if ($type->is_active)
                                    <a href="{{ route('flow-pc.category', $type->slug) }}"
                                        class="product-card bg-gradient-to-b {{ $theme['bg'] }} border {{ $theme['border'] }} rounded-[32px] overflow-hidden group block h-full shadow-sm hover:shadow-2xl transition-all duration-300 relative flex flex-col justify-between">

                                        <!-- Top Product Image Container -->
                                        <div
                                            class="h-44 sm:h-48 w-full bg-gradient-to-br {{ $theme['imgBg'] }} relative p-4 flex items-center justify-center overflow-hidden border-b border-slate-100">

                                            <!-- Floating Icon Badge -->
                                            <div
                                                class="absolute top-3.5 left-3.5 z-20 w-11 h-11 {{ $theme['badge'] }} rounded-2xl flex items-center justify-center p-2.5 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                                                @if ($type->icon_svg)
                                                    {!! $type->icon_svg !!}
                                                @else
                                                    <i data-lucide="package" class="w-6 h-6 text-white"></i>
                                                @endif
                                            </div>

                                            <!-- Category Status Tag -->
                                            <span
                                                class="absolute top-3.5 right-3.5 z-20 bg-white/90 backdrop-blur-md text-slate-800 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border border-slate-200/80 flex flex-row shadow-xs">
                                                Studio Active
                                            </span>

                                            <!-- Embedded Product Mockup Image / Visual -->
                                            <div
                                                class="w-full h-full flex items-center justify-center p-2 group-hover:scale-105 transition-transform duration-500">
                                                @if (Str::contains($slug, ['card', 'greeting']))
                                                    <!-- Greeting Cards Illustration Image -->
                                                    <div
                                                        class="w-36 h-28 bg-white rounded-xl shadow-xl border border-pink-200 p-3 transform -rotate-3 group-hover:rotate-0 transition-all flex flex-col justify-between">
                                                        <div
                                                            class="w-6 h-6 rounded-full bg-pink-100 flex items-center justify-center text-pink-500">
                                                            <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <div class="space-y-1.5">
                                                            <div
                                                                class="h-2.5 w-3/4 bg-gradient-to-r from-pink-400 to-purple-400 rounded">
                                                            </div>
                                                            <div class="h-2 w-1/2 bg-slate-200 rounded"></div>
                                                        </div>
                                                    </div>
                                                @elseif (Str::contains($slug, ['magnet', 'fridge']))
                                                    <!-- Magnet Illustration Image -->
                                                    <div
                                                        class="w-28 h-28 bg-white rounded-2xl shadow-xl border-2 border-brand-300 p-2 transform rotate-2 group-hover:rotate-0 transition-all flex items-center justify-center relative">
                                                        <div
                                                            class="w-full h-full rounded-xl bg-gradient-to-tr from-brand-500 to-brand-600 flex items-center justify-center text-white font-black text-xs shadow-inner">
                                                            <i data-lucide="magnet"
                                                                class="w-9 h-9 text-white animate-pulse"></i>
                                                        </div>
                                                    </div>
                                                @elseif (Str::contains($slug, ['photo', 'print']))
                                                    <!-- Photo Prints Stack Illustration Image -->
                                                    <div class="relative w-36 h-28">
                                                        <div
                                                            class="absolute inset-0 bg-white rounded-xl shadow-md border border-slate-200 transform -rotate-6">
                                                        </div>
                                                        <div
                                                            class="absolute inset-0 bg-white rounded-xl shadow-lg border border-slate-200 transform rotate-3">
                                                        </div>
                                                        <div
                                                            class="absolute inset-0 bg-gradient-to-tr from-purple-600 to-brand-600 rounded-xl shadow-xl p-2.5 text-white flex flex-col justify-between border border-white/20">
                                                            <div class="flex justify-between items-center">
                                                                <i data-lucide="image"
                                                                    class="w-4 h-4 text-purple-200"></i>
                                                                <span
                                                                    class="text-[9px] font-black bg-black/30 px-1.5 py-0.5 rounded">HD
                                                                    Print</span>
                                                            </div>
                                                            <div
                                                                class="h-10 rounded-lg bg-white/20 backdrop-blur-xs flex items-center justify-center text-[10px] font-extrabold">
                                                                Glossy Photo Paper</div>
                                                        </div>
                                                    </div>
                                                @elseif (Str::contains($slug, ['book', 'album']))
                                                    <!-- Photo Book Illustration Image -->
                                                    <div
                                                        class="w-40 h-28 bg-slate-900 rounded-xl shadow-2xl border border-purple-400/40 p-2 flex items-center justify-between transform -rotate-1 group-hover:rotate-0 transition-all">
                                                        <div
                                                            class="w-1/2 h-full bg-slate-800 rounded-l p-1.5 flex flex-col justify-between border-r border-purple-400/30">
                                                            <div class="h-2 w-full bg-purple-500/40 rounded"></div>
                                                            <i data-lucide="book-open"
                                                                class="w-5 h-5 text-purple-400 mx-auto"></i>
                                                        </div>
                                                        <div
                                                            class="w-1/2 h-full bg-slate-800 rounded-r p-1.5 flex flex-col justify-between">
                                                            <div class="h-2 w-3/4 bg-pink-500/40 rounded"></div>
                                                            <i data-lucide="sparkles"
                                                                class="w-5 h-5 text-pink-400 mx-auto"></i>
                                                        </div>
                                                    </div>
                                                @elseif (Str::contains($slug, ['calendar']))
                                                    <!-- Calendar Illustration Image -->
                                                    <div
                                                        class="w-32 h-28 bg-white rounded-xl shadow-xl border border-amber-300 p-2 transform rotate-2 group-hover:rotate-0 transition-all flex flex-col justify-between">
                                                        <div
                                                            class="flex items-center justify-between border-b border-amber-100 pb-1">
                                                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                                            <span class="text-[9px] font-black text-slate-800">2026</span>
                                                        </div>
                                                        <div class="grid grid-cols-4 gap-1 p-1">
                                                            <div class="h-3 bg-amber-100 rounded"></div>
                                                            <div class="h-3 bg-amber-100 rounded"></div>
                                                            <div class="h-3 bg-amber-100 rounded"></div>
                                                            <div
                                                                class="h-3 bg-amber-500 rounded text-white text-[7px] font-bold flex items-center justify-center">
                                                                15</div>
                                                        </div>
                                                    </div>
                                                @elseif (Str::contains($slug, ['canvas', 'wall', 'frame']))
                                                    <!-- Canvas Frame Illustration Image -->
                                                    <div
                                                        class="w-36 h-28 bg-amber-900/20 rounded-xl shadow-2xl p-1.5 border border-amber-700/30">
                                                        <div
                                                            class="w-full h-full bg-gradient-to-tr from-brand-600 via-purple-600 to-pink-500 rounded-lg shadow-inner p-2 flex flex-col justify-between text-white border border-white/20">
                                                            <span
                                                                class="text-[9px] font-black bg-black/40 px-2 py-0.5 rounded w-max">Gallery
                                                                Canvas</span>
                                                            <i data-lucide="frame"
                                                                class="w-7 h-7 text-white/80 mx-auto"></i>
                                                        </div>
                                                    </div>
                                                @else
                                                    <!-- Default Product Studio Illustration Image -->
                                                    <div
                                                        class="w-32 h-28 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 flex flex-col items-center justify-center transform group-hover:scale-105 transition-all">
                                                        <div
                                                            class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-2">
                                                            <i data-lucide="package" class="w-7 h-7"></i>
                                                        </div>
                                                        <span class="text-[10px] font-black text-slate-700">Custom
                                                            Studio</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Card Body & Action Footer -->
                                        <div class="p-6 text-left flex-1 flex flex-col justify-between">
                                            <div>
                                                <h3
                                                    class="font-black text-slate-900 text-lg sm:text-xl {{ $theme['titleHover'] }} transition-colors">
                                                    {{ $type->name }}
                                                </h3>
                                                @if ($type->title)
                                                    <p
                                                        class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed line-clamp-2">
                                                        {{ $type->title }}
                                                    </p>
                                                @else
                                                    <p class="text-xs text-slate-400 mt-1.5">Custom premium printing studio
                                                        options</p>
                                                @endif
                                            </div>

                                            <!-- Footer Action Button -->
                                            <div
                                                class="mt-6 pt-4 border-t border-slate-100/80 flex items-center justify-between">
                                                <span
                                                    class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Fast
                                                    Pickup</span>
                                                <span
                                                    class="inline-flex items-center gap-1.5 text-xs font-black {{ $theme['btn'] }} px-4 py-2 rounded-xl transition-all shadow-md group-hover:scale-105">
                                                    Create now <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </span>
                                            </div>
                                        </div>

                                    </a>
                                @else
                                    <!-- Disabled/Paused Card -->
                                    <div
                                        class="product-card disabled-card bg-slate-50 border border-slate-200 rounded-[32px] overflow-hidden text-left relative h-full flex flex-col justify-between">
                                        <span
                                            class="absolute top-4 right-4 z-20 bg-slate-200 text-slate-500 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full">Paused</span>
                                        <div class="h-44 w-full bg-slate-100 flex items-center justify-center p-4">
                                            <div
                                                class="w-16 h-16 bg-slate-200 rounded-2xl flex items-center justify-center text-slate-400">
                                                <i data-lucide="package" class="w-8 h-8"></i>
                                            </div>
                                        </div>
                                        <div class="p-6">
                                            <h3 class="font-bold text-slate-800 text-lg">{{ $type->name }}</h3>
                                            @if ($type->title)
                                                <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $type->title }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
        </section>




        {{-- ===== SECTION 4: WHY CHOOSE US (FULL-WIDTH GRAY-100 RICH UI PRESENTATION) ===== --}}
        <section class="w-full bg-gray-100 py-24 px-6 sm:px-10 border-b border-slate-200/80 relative overflow-hidden">
            <!-- Decorative Background Subtle Ambient Blobs -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="max-w-[1400px] mx-auto relative z-10">
                <div class="grid grid-cols-12 gap-12 lg:gap-16 items-center">

                    <!-- Left Column: Branding Copy & High Impact CTA -->
                    <div class="col-span-12 lg:col-span-5">
                        <span
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-50 via-purple-50 to-pink-50 border border-brand-200/80 text-brand-700 text-xs font-black px-4 py-2 rounded-full mb-4 shadow-xs uppercase tracking-widest">
                            <i data-lucide="shield-check" class="w-4 h-4 text-brand-600 animate-pulse"></i>
                            Craftsmanship & Precision Engine
                        </span>

                        <h2
                            class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 tracking-tight leading-tight">
                            Why choose <br>
                            <span class="text-slate-950 italic relative inline-block"
                                style="font-family: 'Playfair Display', serif;">
                                Qrinto Print Studio?
                                <svg class="absolute -bottom-2 left-0 w-full h-3 text-brand-500/40" viewBox="0 0 200 12"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 9C50 3 150 3 198 9" stroke="currentColor" stroke-width="4"
                                        stroke-linecap="round" class="animate-pulse" />
                                </svg>
                            </span>
                        </h2>

                        <p class="text-base sm:text-lg text-slate-600 mt-5 leading-relaxed font-medium">
                            We combine cutting-edge precision printing technology with artisan hand-crafted finishing to
                            deliver museum-grade prints that exceed all expectations.
                        </p>

                        <!-- CTA Button -->
                        <div class="mt-8">
                            <a href="{{ route('flow-pc.find-store') }}"
                                class="inline-flex items-center gap-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold px-8 py-4 rounded-2xl shadow-xl shadow-brand-500/25 transition-all duration-300 hover:scale-105 active:scale-95 border border-brand-400/30">
                                <span>Find a Store Near You</span>
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>
                        </div>

                        <!-- Quick Trust Indicators -->
                        <div
                            class="mt-10 pt-8 border-t border-slate-100 flex flex-wrap gap-4 text-xs font-bold text-slate-600">
                            <div
                                class="flex items-center gap-2 bg-slate-50 border border-slate-200/80 flex flex-row px-3.5 py-1.5 rounded-full shadow-xs">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                                <span>100% Quality Guaranteed</span>
                            </div>
                            <div
                                class="flex items-center gap-2 bg-slate-50 border border-slate-200/80 flex flex-row px-3.5 py-1.5 rounded-full shadow-xs">
                                <i data-lucide="truck" class="w-4 h-4 text-brand-500"></i>
                                <span>Same-Day Local Pickup</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: 4 Luxury Feature Cards Grid -->
                    <div class="col-span-12 lg:col-span-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @php
                                $features = [
                                    [
                                        'icon' => 'award',
                                        'title' => 'Museum Quality',
                                        'desc' =>
                                            'Archival-grade canvas, fine art papers, and 12-color archival inks for vibrant life.',
                                        'badge' => 'Archival Grade',
                                        'cardBg' => 'from-pink-50/70 via-white to-white',
                                        'border' =>
                                            'border-pink-200/90 hover:border-brand-hover hover:shadow-brand-500/10',
                                        'iconBox' => 'bg-brand-500 text-white shadow-lg shadow-brand-500/30',
                                        'titleColor' => 'group-hover:text-brand-hover',
                                    ],
                                    [
                                        'icon' => 'zap',
                                        'title' => 'Fast 48hr Turnaround',
                                        'desc' =>
                                            'Most orders printed & ready within 48 hours. Same-day pickup at select branches.',
                                        'badge' => 'Express Speed',
                                        'cardBg' => 'from-amber-50/70 via-white to-white',
                                        'border' =>
                                            'border-amber-200/90 hover:border-amber-500 hover:shadow-amber-500/10',
                                        'iconBox' => 'bg-brand-500 text-white shadow-lg shadow-brand-500/30',
                                        'titleColor' => 'group-hover:text-amber-600',
                                    ],
                                    [
                                        'icon' => 'palette',
                                        'title' => 'Interactive Studio',
                                        'desc' =>
                                            'Powerful vector design editor with real-time text styles, layers, and filters.',
                                        'badge' => 'Vector Suite 2.0',
                                        'cardBg' => 'from-purple-50/70 via-white to-white',
                                        'border' =>
                                            'border-purple-200/90 hover:border-purple-500 hover:shadow-purple-500/10',
                                        'iconBox' => 'bg-brand-500 text-white shadow-lg shadow-brand-500/30',
                                        'titleColor' => 'group-hover:text-purple-600',
                                    ],
                                    [
                                        'icon' => 'shield-check',
                                        'title' => '100% Satisfaction',
                                        'desc' =>
                                            'Not completely in love with your print? We will reprint or issue a full refund.',
                                        'badge' => 'Risk-Free Guarantee',
                                        'cardBg' => 'from-emerald-50/70 via-white to-white',
                                        'border' =>
                                            'border-emerald-200/90 hover:border-emerald-500 hover:shadow-emerald-500/10',
                                        'iconBox' => 'bg-brand-500 text-white shadow-lg shadow-brand-500/30',
                                        'titleColor' => 'group-hover:text-emerald-600',
                                    ],
                                ];
                            @endphp

                            @foreach ($features as $feature)
                                <div
                                    class="bg-gradient-to-b {{ $feature['cardBg'] }} border {{ $feature['border'] }} rounded-[32px] p-8 shadow-sm hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group flex flex-col justify-between relative overflow-hidden">
                                    <div>
                                        <div class="flex items-center justify-between mb-6">
                                            <div
                                                class="w-14 h-14 {{ $feature['iconBox'] }} rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                                <i data-lucide="{{ $feature['icon'] }}" class="w-7 h-7"></i>
                                            </div>
                                            <span
                                                class="text-[10px] font-black uppercase tracking-wider text-slate-500 bg-white border border-slate-200/80 flex flex-row px-2.5 py-1 rounded-full shadow-xs">
                                                {{ $feature['badge'] }}
                                            </span>
                                        </div>

                                        <h4
                                            class="font-black text-slate-900 text-lg sm:text-xl mb-2.5 {{ $feature['titleColor'] }} transition-colors">
                                            {{ $feature['title'] }}
                                        </h4>
                                        <p class="text-sm text-slate-500 leading-relaxed font-medium">
                                            {{ $feature['desc'] }}
                                        </p>
                                    </div>

                                    <div
                                        class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-black text-slate-400 group-hover:text-slate-700 transition-colors">
                                        <span>Learn more</span>
                                        <i data-lucide="chevron-right"
                                            class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ===== SECTION 5: CUSTOMER REVIEWS (PREMIUM SWIPER SLIDER) ===== --}}
        <section class="w-full bg-white py-10 px-6 sm:px-10 border-b border-slate-100/80 relative overflow-hidden">
            <!-- Decorative Ambient Glow Background Orbs -->
            <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-purple-500/5 rounded-full blur-3xl pointer-events-none">
            </div>

            <div class="max-w-[1400px] mx-auto relative z-10">
                <!-- Section Header with Title & Navigation Arrows -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-6">
                    <div class="max-w-2xl">
                        <h2
                            class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                            What Our <span class="text-slate-950 italic relative inline-block"
                                style="font-family: 'Playfair Display', serif;">
                                Customers Say
                                <svg class="absolute -bottom-2 left-0 w-full h-3 text-purple-500/40" viewBox="0 0 200 12"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 9C50 3 150 3 198 9" stroke="currentColor" stroke-width="4"
                                        stroke-linecap="round" class="animate-pulse" />
                                </svg>
                            </span>
                        </h2>
                        <p class="text-base sm:text-lg text-slate-500 mt-4 leading-relaxed font-medium">
                            Trusted by over 50,000+ creators, wedding planners, and businesses nationwide.
                        </p>
                    </div>

                    <!-- Swiper Navigation Buttons -->
                    <div class="flex items-center gap-3 shrink-0">
                        <button
                            class="swiper-nav-btn reviews-swiper-prev !static !w-12 !h-12 !bg-slate-50 hover:!bg-brand-600 !text-slate-800 hover:!text-white !border-slate-200 shadow-md hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <button
                            class="swiper-nav-btn reviews-swiper-next !static !w-12 !h-12 !bg-slate-50 hover:!bg-brand-600 !text-slate-800 hover:!text-white !border-slate-200 shadow-md hover:scale-105 active:scale-95 transition-all">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Swiper Slider Carousel Container -->
                <div class="swiper reviews-swiper overflow-visible py-4">
                    <div class="swiper-wrapper">
                        @php
                            $reviews = [
                                [
                                    'name' => 'Sarah Mitchell',
                                    'initials' => 'SM',
                                    'avatarBg' => 'from-brand-500 to-brand-700',
                                    'product' => 'Custom Stretched Canvas (24x36)',
                                    'location' => 'Austin, TX',
                                    'rating' => '5.0',
                                    'text' =>
                                        'The canvas print quality blew me away. Colors are vibrant and true to the original photo. Arrived in perfect condition within two days. Already ordered three more for our gallery wall!',
                                ],
                                [
                                    'name' => 'James Thornton',
                                    'initials' => 'JT',
                                    'avatarBg' => 'from-purple-600 to-pink-600',
                                    'product' => 'Hardcover Wedding Photobook',
                                    'location' => 'Seattle, WA',
                                    'rating' => '5.0',
                                    'text' =>
                                        'Created a 100-page photo book of our wedding and it turned out absolutely stunning. The archival paper quality is fantastic and the lay-flat binding feels super premium.',
                                ],
                                [
                                    'name' => 'Amara Osei',
                                    'initials' => 'AO',
                                    'avatarBg' => 'from-pink-500 to-rose-600',
                                    'product' => 'Custom Merch & Apparel',
                                    'location' => 'Chicago, IL',
                                    'rating' => '5.0',
                                    'text' =>
                                        'The design studio is incredibly intuitive. I uploaded my vector artwork and created custom merchandise in under five minutes. The print sharpness exceeded my expectations.',
                                ],
                                [
                                    'name' => 'David Sterling',
                                    'initials' => 'DS',
                                    'avatarBg' => 'from-emerald-500 to-teal-600',
                                    'product' => 'Acrylic Wall Art Prints',
                                    'location' => 'Miami, FL',
                                    'rating' => '5.0',
                                    'text' =>
                                        'Ordered acrylic prints for our design agency office. The clarity and glossy depth are world-class. Fast store pickup option saved us before our big product launch event!',
                                ],
                                [
                                    'name' => 'Elena Rostova',
                                    'initials' => 'ER',
                                    'avatarBg' => 'from-amber-500 to-orange-600',
                                    'product' => 'Custom Holiday Cards & Envelopes',
                                    'location' => 'New York, NY',
                                    'rating' => '5.0',
                                    'text' =>
                                        'Qrinto print quality is unbeatable! The paper stock is heavy and crisp. Customer service helped adjust my photo resolution before printing. 10/10 experience!',
                                ],
                            ];
                        @endphp

                        @foreach ($reviews as $review)
                            <div class="swiper-slide h-auto">
                                <div
                                    class="bg-slate-50/90 border border-slate-200/90 rounded-[32px] p-8 shadow-sm hover:shadow-2xl transition-all duration-300 relative flex flex-col justify-between h-full group">
                                    <!-- Decorative Large Quote Mark -->
                                    <div
                                        class="absolute top-6 right-7 text-slate-200 font-serif text-7xl select-none leading-none pointer-events-none group-hover:text-brand-100 transition-colors">
                                        “
                                    </div>

                                    <div>
                                        <!-- Rating Stars & Product Tag -->
                                        <div class="flex items-center justify-between gap-2 mb-5 relative z-10">
                                            <div class="flex items-center gap-1">
                                                @for ($s = 0; $s < 5; $s++)
                                                    <i data-lucide="star"
                                                        class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                                @endfor
                                                <span
                                                    class="text-xs font-black text-slate-700 ml-1.5">{{ $review['rating'] }}</span>
                                            </div>
                                            <span
                                                class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500 bg-white px-2.5 py-1 rounded-full border border-slate-200/60">
                                                Verified Order
                                            </span>
                                        </div>

                                        <!-- Purchased Item Pill -->
                                        <div class="mb-4">
                                            <span
                                                class="text-xs font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-lg border border-brand-100 inline-block">
                                                <i data-lucide="package" class="w-3.5 h-3.5 inline mr-1 -mt-0.5"></i>
                                                {{ $review['product'] }}
                                            </span>
                                        </div>

                                        <!-- Review Copy -->
                                        <p class="text-slate-600 leading-relaxed text-sm font-medium relative z-10">
                                            "{{ $review['text'] }}"
                                        </p>
                                    </div>

                                    <!-- Customer Profile Footer -->
                                    <div
                                        class="mt-8 pt-5 border-t border-slate-200/80 flex items-center justify-between relative z-10">
                                        <div class="flex items-center gap-3.5">
                                            <div
                                                class="w-12 h-12 bg-gradient-to-br {{ $review['avatarBg'] }} text-white rounded-full flex items-center justify-center text-xs font-black shadow-md shrink-0">
                                                {{ $review['initials'] }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900">{{ $review['name'] }}</p>
                                                <p class="text-xs text-slate-400 font-medium">{{ $review['location'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                            Verified Buyer
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== SECTION 6: CTA BANNER (CLEAN ELEGANT MINIMALIST) ===== --}}
        <section class="w-full bg-gray-100 py-10 px-6 sm:px-10 border-t border-slate-200/80 text-center">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                    Ready to create something <br class="hidden sm:inline">
                    <span class="text-slate-950 italic" style="font-family: 'Playfair Display', serif;">
                        extraordinary?
                    </span>
                </h2>

                <p class="text-base sm:text-lg text-slate-500 mt-4 font-medium max-w-xl mx-auto">
                    Start designing your custom print project in minutes with our intuitive online editor.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-4 mt-8">
                    <a href="#products"
                        class="bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm px-8 py-3.5 rounded-xl transition-all shadow-md hover:scale-105 active:scale-95 flex items-center gap-2">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        <span>Start Designing Now</span>
                    </a>
                    <a href="{{ route('flow-pc.find-store') }}"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-extrabold text-sm px-7 py-3.5 rounded-xl transition-all flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-brand-600"></i>
                        <span>Find a Store</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('scripts')
    <!-- Swiper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Hero Showcase Swiper
            if (typeof Swiper !== 'undefined') {
                const heroSwiper = new Swiper('.hero-swiper', {
                    loop: true,
                    speed: 700,
                    autoplay: {
                        delay: 4500,
                        disableOnInteraction: false,
                    },
                    effect: 'slide',
                    pagination: {
                        el: '.hero-swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.hero-swiper-next',
                        prevEl: '.hero-swiper-prev',
                    },
                });

                // Initialize Category Swiper Carousel
                const categorySwiper = new Swiper('.category-swiper', {
                    slidesPerView: 1.5,
                    spaceBetween: 16,
                    loop: false,
                    breakpoints: {
                        480: {
                            slidesPerView: 2,
                            spaceBetween: 16
                        },
                        640: {
                            slidesPerView: 3,
                            spaceBetween: 20
                        },
                        1024: {
                            slidesPerView: 4,
                            spaceBetween: 24
                        },
                        1280: {
                            slidesPerView: 5,
                            spaceBetween: 24
                        }
                    },
                    navigation: {
                        nextEl: '.category-swiper-next',
                        prevEl: '.category-swiper-prev',
                    },
                });

                // Initialize Customer Reviews Swiper Carousel
                const reviewsSwiper = new Swiper('.reviews-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    loop: true,
                    breakpoints: {
                        768: {
                            slidesPerView: 3,
                            spaceBetween: 24
                        },
                        1280: {
                            slidesPerView: 3,
                            spaceBetween: 28
                        }
                    },
                    navigation: {
                        nextEl: '.reviews-swiper-next',
                        prevEl: '.reviews-swiper-prev',
                    },
                });
            }

            // Refresh Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
