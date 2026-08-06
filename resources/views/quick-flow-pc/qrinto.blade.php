@extends('layouts.quick-flow-pc')

@section('title', 'Custom Print Studio — Qrinto')
@section('header_title', 'Custom Print')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .shimmer-cta {
        position: relative;
        overflow: hidden;
    }
    .shimmer-cta::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 50%;
        height: 200%;
        background: linear-gradient(60deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(25deg);
        transition: all 0.75s ease;
    }
    .shimmer-cta:hover::after {
        left: 140%;
    }

    .ambient-bg {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(236, 72, 153, 0.05) 0px, transparent 50%),
            radial-gradient(circle at 50% 50%, rgba(241, 245, 249, 0.5) 0px, transparent 100%);
    }

    .paypal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 80;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .paypal-sheet {
        width: 100%;
        max-width: 480px;
        background: #fff;
        border-radius: 2rem;
        padding: 2rem;
        max-height: 88vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.3);
        animation: modalIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalIn {
        from { transform: scale(0.94); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .processing-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
    }
    .processing-overlay .spinner {
        width: 56px;
        height: 56px;
        border: 4px solid #e2e8f0;
        border-top: 4px solid #ec4899;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .success-check {
        width: 76px;
        height: 76px;
        background: #22c55e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.4);
        animation: popScale 0.45s cubic-bezier(0.175,0.885,0.32,1.275);
    }
    @keyframes popScale {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="ambient-bg min-h-screen py-8 -mt-6 font-sans text-slate-900" x-data="qrintoFlow()">
    
    {{-- Fullscreen Processing Overlay --}}
    <div x-show="isProcessing" 
         class="processing-overlay" 
         style="display: none;" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100">
        <template x-if="!paymentSuccess">
            <div class="text-center">
                <div class="spinner mx-auto mb-6"></div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Processing Payment</h3>
                <p class="text-slate-500 font-semibold text-sm mt-1.5">Please wait while we confirm your custom print order...</p>
            </div>
        </template>
        <template x-if="paymentSuccess">
            <div class="text-center">
                <div class="success-check mx-auto mb-6">
                    <i data-lucide="check" class="w-10 h-10 text-white"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Order Confirmed!</h3>
                <p class="text-slate-500 font-semibold text-sm mt-1.5">Redirecting to your order confirmation details...</p>
            </div>
        </template>
    </div>

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb Navigation & Interactive Step Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-900 font-bold">Quick Custom Print</span>
            </nav>

            {{-- Floating 3-Step Indicator Bar --}}
            <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 text-xs font-bold" :class="currentStep >= 1 ? (currentStep > 1 ? 'text-emerald-600' : 'text-brand-600 font-black bg-brand-50 px-3 py-1 rounded-full border border-brand-200/60') : 'text-slate-400'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="currentStep > 1 ? 'bg-emerald-100 text-emerald-600 font-bold' : (currentStep === 1 ? 'bg-brand-600 text-white font-black' : 'bg-slate-100 text-slate-400')">
                        <template x-if="currentStep > 1"><span>✓</span></template>
                        <template x-if="currentStep <= 1"><span>1</span></template>
                    </span>
                    <span>Upload Design</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>

                <div class="flex items-center gap-1.5 text-xs font-bold" :class="currentStep >= 2 ? (currentStep > 2 ? 'text-emerald-600' : 'text-brand-600 font-black bg-brand-50 px-3 py-1 rounded-full border border-brand-200/60') : 'text-slate-400'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="currentStep > 2 ? 'bg-emerald-100 text-emerald-600 font-bold' : (currentStep === 2 ? 'bg-brand-600 text-white font-black' : 'bg-slate-100 text-slate-400')">
                        <template x-if="currentStep > 2"><span>✓</span></template>
                        <template x-if="currentStep <= 2"><span>2</span></template>
                    </span>
                    <span>Dimensions & Size</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>

                <div class="flex items-center gap-1.5 text-xs font-bold" :class="currentStep === 3 ? 'text-brand-600 font-black bg-brand-50 px-3 py-1 rounded-full border border-brand-200/60' : 'text-slate-400'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="currentStep === 3 ? 'bg-brand-600 text-white font-black' : 'bg-slate-100 text-slate-400'">3</span>
                    <span>Review & Pay</span>
                </div>
            </div>
        </div>

        {{-- Main Page Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-slate-200/70 shadow-xs">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-brand-50 border border-brand-100 text-brand-600 text-xs font-extrabold mb-1">
                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i> Direct Upload Print Service
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Direct Custom Print Studio</h1>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold text-slate-500 bg-slate-100/80 px-4 py-2 rounded-2xl border border-slate-200/60">
                <i data-lucide="sparkles" class="w-4 h-4 text-brand-600"></i>
                <span>High-Resolution Photo & Document Printing</span>
            </div>
        </div>

        {{-- STEP 1: UPLOAD DESIGN --}}
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" class="max-w-2xl mx-auto">
            <div class="glass-card border border-slate-200/90 rounded-3xl p-6 sm:p-10 text-center shadow-xl shadow-slate-200/40 relative overflow-hidden">
                <div class="w-16 h-16 bg-brand-50 border border-brand-100 rounded-3xl flex items-center justify-center text-brand-600 mx-auto mb-5 shadow-2xs">
                    <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                </div>

                <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-1.5">Upload Your Image or Design</h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                    Upload your high-resolution artwork or photo (JPG, PNG, or WebP up to 10MB). We will print it exactly as provided with true colors!
                </p>

                <div class="relative group"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)">

                    <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleFileSelect($event)">

                    <div @click="$refs.fileInput.click()"
                        :class="[
                            isDragging ? 'border-brand-500 bg-brand-50/50 scale-[1.01]' : 'border-slate-300/80 bg-slate-50/50 hover:bg-white hover:border-brand-400',
                            previewUrl ? 'p-0 border-solid overflow-hidden bg-slate-900' : 'p-10 border-dashed'
                         ]"
                        class="border-2 rounded-3xl transition-all duration-300 cursor-pointer relative min-h-[280px] flex items-center justify-center shadow-inner">

                        <template x-if="!previewUrl">
                            <div class="space-y-3">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mx-auto text-slate-400 group-hover:text-brand-600 group-hover:scale-110 transition-all shadow-2xs border border-slate-200/80">
                                    <i data-lucide="plus" class="w-7 h-7"></i>
                                </div>
                                <div>
                                    <p class="font-extrabold text-slate-900 text-base">Click to browse or drag & drop</p>
                                    <p class="text-xs text-slate-400 font-semibold mt-1">Supports JPG, PNG, WEBP (Max 10MB)</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="w-full h-[320px] relative group/img">
                                <img :src="previewUrl" class="w-full h-full object-contain rounded-3xl">
                                <div class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-[11px] font-black px-3 py-1 rounded-xl shadow-md flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-400"></i> Ready for Print
                                </div>
                                <button type="button" @click.stop="removeFile()"
                                    class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur-md shadow-xl text-red-500 rounded-2xl flex items-center justify-center transition-all hover:bg-red-50 hover:scale-110 active:scale-95 z-20 border border-slate-200/80"
                                    title="Remove uploaded image">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Uploading Progress Bar --}}
                    <div x-show="isUploading" class="absolute inset-0 bg-white/95 backdrop-blur-md rounded-3xl flex flex-col items-center justify-center z-30">
                        <div class="w-12 h-12 border-4 border-slate-200 border-t-brand-600 rounded-full animate-spin mb-4"></div>
                        <p class="font-black text-slate-900 text-sm" x-text="`Uploading Image... ${uploadProgress}%`"></p>
                    </div>
                </div>

                <div class="pt-8">
                    <button @click="currentStep = 2"
                        :disabled="!uploadId"
                        :class="!uploadId ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'shimmer-cta bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-brand-600 hover:to-indigo-600 text-white shadow-xl shadow-slate-900/20 active:scale-[0.99] cursor-pointer'"
                        class="w-full py-4 rounded-2xl font-black text-base transition-all duration-300 flex items-center justify-center gap-2">
                        <span>Continue to Select Sizes</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- STEP 2: SIZE & CATEGORY SELECTION --}}
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" class="max-w-4xl mx-auto space-y-6">
            <div class="text-center max-w-xl mx-auto mb-6">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight" x-text="!selectedParent ? 'Select Print Category' : 'Choose Dimensions'"></h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1" x-text="!selectedParent ? 'Pick the style of product you want to print.' : `Select custom paper dimensions for your ${selectedParent.name}.`"></p>
            </div>

            {{-- Category Grid --}}
            <div x-show="!selectedParent" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <template x-for="parent in productTypes" :key="parent.id">
                    <button @click="selectedParent = parent"
                        class="glass-card flex items-center justify-between p-6 rounded-3xl border border-slate-200/90 hover:border-brand-400 transition-all duration-300 group shadow-2xs hover:shadow-xl text-left active:scale-[0.98]">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-100 text-slate-500 group-hover:bg-brand-50 group-hover:text-brand-600 rounded-2xl flex items-center justify-center transition-colors overflow-hidden shrink-0 border border-slate-200/80">
                                <template x-if="parent.icon_svg">
                                    <div class="w-6 h-6 fill-current" x-html="parent.icon_svg"></div>
                                </template>
                                <template x-if="!parent.icon_svg">
                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                </template>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-base group-hover:text-brand-600 transition-colors" x-text="parent.name"></h3>
                                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mt-0.5" x-text="parent.title || 'Various Sizes'"></p>
                            </div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand-600 group-hover:text-white transition-all shrink-0">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </div>
                    </button>
                </template>
            </div>

            {{-- Subtype Sizes Grid --}}
            <div x-show="selectedParent" class="space-y-5">
                <button @click="selectedParent = null; selectedSize = null" 
                    class="inline-flex items-center gap-2 text-xs font-black text-brand-600 bg-brand-50 hover:bg-brand-100 px-3.5 py-2 rounded-xl border border-brand-200/60 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Categories
                </button>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="size in selectedParent?.subtypes || []" :key="size.id">
                        <button @click="selectedSize = { ...size, label: `${selectedParent.name}: ${size.name}` }"
                            :class="selectedSize?.id === size.id ? 'border-brand-500 bg-brand-50/70 shadow-md ring-2 ring-brand-100' : 'border-slate-200/90 bg-white hover:border-brand-300'"
                            class="glass-card flex items-center justify-between p-5 rounded-3xl border-2 transition-all duration-200 group text-left">
                            <div class="flex items-center gap-4">
                                <div :class="selectedSize?.id === size.id ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500'"
                                    class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors shrink-0">
                                    <i data-lucide="maximize" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 text-base" x-text="size.name"></h4>
                                    <span class="inline-block text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded mt-1" x-text="size.dimensions"></span>
                                    <template x-if="size.title">
                                        <p class="text-[10px] font-semibold text-slate-400 mt-1" x-text="size.title"></p>
                                    </template>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-black text-lg text-slate-900" x-text="__price(size.price)"></p>
                                <template x-if="size.popular">
                                    <span class="text-[9px] font-black bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md uppercase tracking-wider">Popular</span>
                                </template>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <div class="pt-6 flex gap-4">
                <button @click="currentStep = 1" class="flex-1 py-4 rounded-2xl bg-white border border-slate-200 text-slate-700 font-extrabold text-sm hover:bg-slate-50 transition-colors">
                    Back to Upload
                </button>
                <button @click="currentStep = 3"
                    :disabled="!selectedSize"
                    :class="!selectedSize ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'shimmer-cta bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-brand-600 hover:to-indigo-600 text-white shadow-xl shadow-slate-900/20 active:scale-[0.99] cursor-pointer'"
                    class="flex-[2] py-4 rounded-2xl font-black text-base transition-all duration-300 flex items-center justify-center gap-2">
                    <span>Review Order</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- STEP 3: FINAL REVIEW & CHECKOUT GRID --}}
        <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" class="max-w-[1360px] mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                {{-- Left Column: Product & Pickup Info --}}
                <div class="lg:col-span-7 space-y-6">
                    
                    {{-- Order Item Specifications Card --}}
                    <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-200/80 mb-5">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                <i data-lucide="package" class="w-4.5 h-4.5"></i>
                            </div>
                            <h2 class="text-base font-black text-slate-900 tracking-tight">Print Item Specifications</h2>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-5">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-slate-900 shrink-0 border border-slate-200 shadow-sm relative">
                                <img :src="previewUrl" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-1 bg-slate-900/80 backdrop-blur-xs text-white text-[9px] font-black px-1.5 py-0.5 rounded">HD PRINT</span>
                            </div>
                            <div class="flex-1 text-center sm:text-left min-w-0">
                                <h3 class="font-black text-slate-900 text-lg" x-text="selectedSize?.label"></h3>
                                <div class="flex items-center justify-center sm:justify-start gap-2 mt-1.5">
                                    <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/80" x-text="selectedSize?.dimensions"></span>
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60">High Resolution</span>
                                </div>
                                <div class="mt-3 flex items-center justify-center sm:justify-start gap-3">
                                    <span class="text-xs font-bold text-slate-400 uppercase">Quantity:</span>
                                    <div class="inline-flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/80">
                                        <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-7 h-7 rounded-lg bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-2xs active:scale-95">−</button>
                                        <span class="w-10 text-center font-black text-slate-900 text-sm" x-text="quantity"></span>
                                        <button type="button" @click="quantity = Math.min(10, quantity + 1)" class="w-7 h-7 rounded-lg bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-2xs active:scale-95">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pickup Contact Details Form --}}
                    <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm">
                        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-200/80">
                            <div class="w-11 h-11 bg-brand-50 border border-brand-100 rounded-2xl flex items-center justify-center text-brand-600 flex-shrink-0 shadow-2xs">
                                <i data-lucide="user-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 tracking-tight">Pickup Information</h3>
                                <p class="text-xs text-slate-500 font-medium">Details of the person collecting this print order</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">Full Name <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                        <i data-lucide="user" class="w-5 h-5"></i>
                                    </div>
                                    <input type="text" x-model="pickupName" placeholder="Enter your full name"
                                        class="w-full bg-white border-2 border-slate-200/90 focus:border-brand-500 rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-sm shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">Email Address <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                        <i data-lucide="mail" class="w-5 h-5"></i>
                                    </div>
                                    <input type="email" x-model="pickupEmail" placeholder="name@example.com"
                                        class="w-full bg-white border-2 border-slate-200/90 focus:border-brand-500 rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-sm shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                        <i data-lucide="phone" class="w-5 h-5"></i>
                                    </div>
                                    <input type="tel" x-model="contactNumber" placeholder="Phone number (e.g. 555-123-4567)"
                                        class="w-full bg-white border-2 border-slate-200/90 focus:border-brand-500 rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-sm shadow-2xs">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Payment Summary Sidebar (Hero Card) --}}
                <div class="lg:col-span-5 sticky top-24 space-y-6">
                    <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-2xl shadow-slate-200/50 relative overflow-hidden space-y-6">
                        
                        {{-- Top Multi-Color Gradient Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-500 via-indigo-500 to-purple-600"></div>

                        {{-- Summary Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Payment Summary</h3>
                            <span class="inline-flex items-center gap-1 text-[11px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i> Encrypted
                            </span>
                        </div>

                        {{-- Price Breakdown --}}
                        <div class="space-y-3 pb-5 border-b border-slate-200/80">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-semibold">Unit Price</span>
                                <span class="font-extrabold text-slate-800" x-text="__price(selectedSize?.price)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-semibold">Quantity</span>
                                <span class="font-extrabold text-slate-800" x-text="quantity"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-semibold">Setup & Printing</span>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">FREE Included</span>
                            </div>
                        </div>

                        {{-- Dark Luxury Grand Total Card --}}
                        <div class="bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-brand-500/20 rounded-full blur-xl pointer-events-none"></div>
                            <div class="flex justify-between items-baseline mb-1 relative z-10">
                                <span class="text-sm font-bold text-slate-300">Total Amount</span>
                                <span class="text-3xl font-black text-white tracking-tight" x-text="__price(selectedSize?.price * quantity)"></span>
                            </div>
                            <p class="text-[11px] font-medium text-slate-400 text-right relative z-10">Includes taxes & setup</p>
                        </div>

                        {{-- Checkout Action Buttons --}}
                        <div class="space-y-3 pt-1">
                            <button type="button" 
                                    @click="openPaypal()"
                                    :disabled="isProcessing || !isValid"
                                    class="shimmer-cta w-full bg-gradient-to-r from-brand-600 via-indigo-600 to-brand-700 hover:from-brand-500 hover:to-indigo-500 disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black py-4 rounded-2xl shadow-xl shadow-brand-500/25 disabled:shadow-none transition-all active:scale-[0.99] flex items-center justify-center gap-3 text-base cursor-pointer">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                                <span x-text="isValid ? 'Pay Now — ' + __price(selectedSize?.price * quantity) : 'Complete Pickup Info'"></span>
                                <i data-lucide="arrow-right" class="w-5 h-5" x-show="isValid"></i>
                            </button>

                            <button type="button" 
                                    @click="processCheckout('cash')"
                                    :disabled="isProcessing || !isValid"
                                    class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-800 font-extrabold py-3.5 rounded-2xl shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-sm cursor-pointer">
                                <i data-lucide="banknote" class="w-5 h-5 text-emerald-600"></i>
                                <span>Pay by Cash at Store Counter</span>
                            </button>

                            <button @click="currentStep = 2" 
                                    :disabled="isProcessing"
                                    class="w-full pt-2 text-center text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                <i data-lucide="arrow-left" class="w-3.5 h-3.5 inline-block mr-1"></i> Back to Dimensions
                            </button>
                        </div>

                        {{-- Security & Trust Highlights --}}
                        <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                <span>Print Guarantee</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="truck" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                <span>Store Pickup</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                <span>SSL Security</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="headphones" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                                <span>Store Support</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- PayPal Modal Teleport --}}
    <template x-teleport="body">
        <div x-cloak>
            <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                <div class="paypal-sheet" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Pay with PayPal</h3>
                                <p class="text-xs text-slate-400 font-semibold">Instant & secure 256-Bit transaction</p>
                            </div>
                        </div>
                        <button @click="showPaypal = false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-4 mb-5 flex items-center justify-between shadow-lg">
                        <div>
                            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Amount to Pay</p>
                            <p class="text-2xl font-black text-white" x-text="__price(selectedSize?.price * quantity)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Pickup For</p>
                            <p class="text-sm font-bold text-slate-200 truncate max-w-[140px]" x-text="pickupName || 'Guest'"></p>
                        </div>
                    </div>

                    <div id="paypal-button-container" class="mb-2"></div>

                    <p class="text-center text-xs text-slate-400 font-medium mt-4 flex items-center justify-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500"></i>
                        Payments are processed securely by PayPal
                    </p>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ \App\Services\CurrencyService::getCode() }}"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('qrintoFlow', () => ({
            currentStep: 1,
            isDragging: false,
            isUploading: false,
            uploadProgress: 0,
            previewUrl: '',
            uploadId: null,
            productTypes: @json($productTypes),
            selectedParent: null,
            selectedSize: null,
            quantity: 1,
            pickupName: '',
            pickupEmail: '',
            contactNumber: '',
            isProcessing: false,
            processingMode: '',
            paymentSuccess: false,
            showPaypal: false,
            paypalRendered: false,

            get isValid() {
                return this.uploadId && this.selectedSize && this.pickupName && this.pickupEmail && this.contactNumber;
            },

            init() {
                this.loadState();

                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });

                this.$watch('currentStep', (val) => {
                    this.saveState();
                    if (val === 3) {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });

                this.$watch('selectedParent', () => {
                    this.saveState();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });

                this.$watch('selectedSize', () => this.saveState());
                this.$watch('quantity', () => this.saveState());
                this.$watch('pickupName', () => this.saveState());
                this.$watch('pickupEmail', () => this.saveState());
                this.$watch('contactNumber', () => this.saveState());

                this.$watch('previewUrl', () => {
                    this.saveState();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });
            },

            saveState() {
                const state = {
                    uploadId: this.uploadId,
                    previewUrl: this.previewUrl,
                    selectedParentId: this.selectedParent ? this.selectedParent.id : null,
                    selectedSizeId: this.selectedSize ? this.selectedSize.id : null,
                    quantity: this.quantity,
                    pickupName: this.pickupName,
                    pickupEmail: this.pickupEmail,
                    contactNumber: this.contactNumber,
                    currentStep: this.currentStep
                };
                localStorage.setItem('qrinto_custom_print_state', JSON.stringify(state));
            },

            loadState() {
                try {
                    const saved = localStorage.getItem('qrinto_custom_print_state');
                    if (saved) {
                        const state = JSON.parse(saved);
                        this.uploadId = state.uploadId;
                        this.previewUrl = state.previewUrl;
                        this.quantity = state.quantity || 1;
                        this.pickupName = state.pickupName || '';
                        this.pickupEmail = state.pickupEmail || '';
                        this.contactNumber = state.contactNumber || '';
                        this.currentStep = state.currentStep || 1;

                        if (state.selectedParentId) {
                            this.selectedParent = this.productTypes.find(p => p.id === state.selectedParentId);
                            if (this.selectedParent && state.selectedSizeId) {
                                this.selectedSize = this.selectedParent.subtypes.find(s => s.id === state.selectedSizeId);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error loading saved state:', e);
                }
            },

            clearState() {
                localStorage.removeItem('qrinto_custom_print_state');
            },

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) this.uploadFile(file);
            },

            handleDrop(e) {
                this.isDragging = false;
                const file = e.dataTransfer.files[0];
                if (file) this.uploadFile(file);
            },

            uploadFile(file) {
                if (!file.type.startsWith('image/')) {
                    alert('Please upload an image file.');
                    return;
                }

                this.isUploading = true;
                this.uploadProgress = 0;

                const formData = new FormData();
                formData.append('image', file);

                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        this.uploadId = data.upload.id;
                        this.previewUrl = data.upload.url;
                        this.isUploading = false;
                    } else {
                        alert('Upload failed');
                        this.isUploading = false;
                    }
                });

                xhr.open('POST', '{{ route("qrinto.upload") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.send(formData);
            },

            removeFile() {
                this.uploadId = null;
                this.previewUrl = '';
                this.$refs.fileInput.value = '';
            },

            async processCheckout(mode) {
                if (!this.isValid) return;

                this.isProcessing = true;
                this.processingMode = mode;
                this.paymentSuccess = false;

                try {
                    const response = await fetch('{{ route("flow-pc.qrinto.checkout.cash") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            upload_id: this.uploadId,
                            size_id: this.selectedSize.id,
                            quantity: this.quantity,
                            pickup_name: this.pickupName,
                            pickup_email: this.pickupEmail,
                            contact_number: this.contactNumber
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.paymentSuccess = true;
                        this.clearState();
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1500);
                    } else {
                        alert(data.error || data.message || 'Checkout failed');
                        this.isProcessing = false;
                    }
                } catch (e) {
                    console.error(e);
                    alert('An error occurred while connecting to server.');
                    this.isProcessing = false;
                }
            },

            openPaypal() {
                if (!this.isValid) return;
                this.showPaypal = true;

                this.$nextTick(() => {
                    if (!this.paypalRendered) {
                        this.initPaypal();
                        this.paypalRendered = true;
                    }
                    setTimeout(() => {
                        if (window.lucide) lucide.createIcons();
                    }, 200);
                });
            },

            initPaypal() {
                if (!document.getElementById('paypal-button-container')) return;

                paypal.Buttons({
                    createOrder: async (data, actions) => {
                        const response = await fetch('{{ route("flow-pc.qrinto.paypal.create") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                size_id: this.selectedSize.id,
                                quantity: this.quantity
                            })
                        });
                        const order = await response.json();
                        return order.id;
                    },
                    onApprove: async (data, actions) => {
                        this.showPaypal = false;
                        this.isProcessing = true;
                        this.processingMode = 'paypal';
                        this.paymentSuccess = false;

                        const response = await fetch('{{ route("flow-pc.qrinto.paypal.capture") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                paypal_order_id: data.orderID,
                                upload_id: this.uploadId,
                                size_id: this.selectedSize.id,
                                quantity: this.quantity,
                                pickup_name: this.pickupName,
                                pickup_email: this.pickupEmail,
                                contact_number: this.contactNumber
                            })
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.paymentSuccess = true;
                            this.clearState();
                            this.$nextTick(() => {
                                if (window.lucide) lucide.createIcons();
                            });
                            setTimeout(() => {
                                window.location.href = result.redirect_url;
                            }, 1500);
                        } else {
                            alert(result.error || result.message || 'Payment capture failed');
                            this.isProcessing = false;
                        }
                    }
                }).render('#paypal-button-container');
            }
        }));
    });
</script>
@endpush