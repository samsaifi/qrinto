{{-- Right Floating Vertical Studio Dock --}}
{{-- Expects: $multiUpload (bool, default true) --}}
@php $multiUpload = $multiUpload ?? true; @endphp

<div
    class="absolute right-1 lg:right-4 top-1/2 -translate-y-1/2 grid grid-cols-2 gap-x-2 gap-y-3 justify-items-center items-start shrink-0 z-30 py-3 px-2 bg-white/50 backdrop-blur-sm rounded-3xl border border-slate-200/60 shadow-sm">

    {{-- 1. Photo Tool --}}
    <label for="photo-upload-input" id="upload-tool-label" class="group flex flex-col items-center gap-1.5 cursor-pointer"
        title="Upload Photo">
        <div id="upload-icon-bg"
            class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-pink-500 group-hover:bg-brand-hover group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i id="upload-icon" data-lucide="image-plus" class="w-5 h-5"></i>
        </div>
        <span id="upload-text"
            class="text-xs font-bold text-slate-600 group-hover:text-brand-hover transition-colors">Photo</span>
    </label>
    <input type="file" onchange="customizer.handleFileUpload(this)" class="hidden" id="photo-upload-input"
        accept="image/*" {{ $multiUpload ? 'multiple' : '' }}>

    {{-- 2. + Text Tool --}}
    <button type="button" id="text-dock-trigger" onclick="toggleTextDrawer()"
        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Add Custom Text">
        <div id="text-dock-btn"
            class="w-12 h-12 rounded-full bg-purple-100/90 border border-purple-200/90 shadow-xl shadow-purple-500/10 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="type" class="w-5 h-5"></i>
        </div>
        <span class="text-xs font-bold text-purple-600 group-hover:text-purple-700 transition-colors">+ Text</span>
    </button>

    {{-- 3. Shapes & Stickers Tool --}}
    <button type="button" id="shapes-dock-trigger" onclick="toggleShapesDrawer()"
        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Shapes & Stickers">
        <div
            class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="shapes" class="w-5 h-5"></i>
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-brand-600 transition-colors">Shapes</span>
    </button>

    {{-- 4. Color & Background Tool --}}
    <div id="color-tool" class="group flex flex-col items-center gap-1.5 cursor-pointer relative"
        title="Background & Colors">
        <div
            class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-110 transition-all duration-200 relative overflow-hidden">
            <i data-lucide="palette" class="w-5 h-5"></i>
            <input type="color" id="text-color-input" oninput="customizer._updateSelectedStyle('fill', this.value)"
                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-brand-600 transition-colors">Color</span>
    </div>

    {{-- 5. QR Code Tool --}}
    <button type="button" id="qr-dock-trigger" onclick="toggleQrDrawer()"
        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Generate QR Code">
        <div
            class="w-12 h-12 rounded-full bg-white border border-slate-200/90 shadow-xl shadow-slate-300/40 flex items-center justify-center text-slate-700 group-hover:bg-slate-900 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="qr-code" class="w-5 h-5"></i>
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-slate-900 transition-colors">QR Code</span>
    </button>

    {{-- 6. Continue to order (single design per order — replaces "Add to cart") --}}
    <form action="{{ route('flow.cart.add') }}" method="POST" id="checkout-form" class="flex justify-center">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="upload_ids" id="upload_ids_field">
        <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
            class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Continue to order">
            <div
                class="w-12 h-12 rounded-full bg-brand-600 text-white shadow-2xl shadow-brand-600/30 flex items-center justify-center group-hover:bg-brand-700 transition-all duration-200">
                <i data-lucide="arrow-right" class="w-5 h-5 text-white"></i>
            </div>
            <span class="text-xs font-black text-slate-900">Continue</span>
        </button>
    </form>
</div>
