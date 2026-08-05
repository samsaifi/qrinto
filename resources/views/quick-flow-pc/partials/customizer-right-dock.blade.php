{{-- Right Floating Vertical Studio Dock --}}
{{-- Expects: $multiUpload (bool, default true) --}}
@php $multiUpload = $multiUpload ?? true; @endphp

<div class="absolute right-2 lg:right-6 top-1/2 -translate-y-1/2 flex flex-col items-center gap-6 shrink-0 z-30 py-4 px-2">

    {{-- 1. Photo Tool --}}
    <label for="photo-upload-input" id="upload-tool-label" class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Upload Photo">
        <div id="upload-icon-bg"
            class="w-14 h-14 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-pink-500 group-hover:bg-pink-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i id="upload-icon" data-lucide="image-plus" class="w-6 h-6"></i>
        </div>
        <span id="upload-text" class="text-xs font-bold text-slate-600 group-hover:text-pink-600 transition-colors">Photo</span>
    </label>
    <input type="file" onchange="customizer.handleFileUpload(this)" class="hidden"
        id="photo-upload-input" accept="image/*" {{ $multiUpload ? 'multiple' : '' }}>

    {{-- 2. + Text Tool --}}
    <button type="button" id="text-dock-trigger" onclick="toggleTextDrawer()"
        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Add Custom Text">
        <div id="text-dock-btn"
            class="w-14 h-14 rounded-full bg-purple-100/90 border border-purple-200/90 shadow-xl shadow-purple-500/10 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="type" class="w-6 h-6"></i>
        </div>
        <span class="text-xs font-bold text-purple-600 group-hover:text-purple-700 transition-colors">+ Text</span>
    </button>

    {{-- 3. Color Tool --}}
    <div id="color-tool" class="group flex flex-col items-center gap-1.5 cursor-pointer relative" title="Text Color">
        <div
            class="w-14 h-14 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:scale-110 transition-all duration-200 relative overflow-hidden">
            <i data-lucide="palette" class="w-6 h-6"></i>
            <input type="color" id="text-color-input" oninput="customizer._updateSelectedStyle('fill', this.value)"
                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors">Color</span>
    </div>

    {{-- 4. Fonts Tool --}}
    <button type="button" id="fonts-dock-trigger" onclick="toggleTextDrawer('font')"
        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Select Font">
        <div
            class="w-14 h-14 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-sky-500 group-hover:bg-sky-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="whole-word" class="w-6 h-6"></i>
        </div>
        <span class="text-xs font-bold text-slate-600 group-hover:text-sky-600 transition-colors">Fonts</span>
    </button>

    {{-- 5. Delete Tool --}}
    <button type="button" id="remove-btn" onclick="customizer.handleRemove()"
        class="hidden group flex flex-col items-center gap-1.5 cursor-pointer" title="Remove Item">
        <div
            class="w-14 h-14 rounded-full bg-red-50 border border-red-200 shadow-xl shadow-red-500/10 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <span class="text-xs font-bold text-red-600" id="remove-btn-text">Remove</span>
    </button>

    {{-- 6. Add to Cart Button --}}
    <form action="{{ route('flow-pc.cart.add') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="upload_ids" id="upload_ids_field">
        <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
            class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Add To Cart">
            <div
                class="w-14 h-14 rounded-full bg-white text-pink-500 shadow-2xl shadow-slate-900/40 flex items-center justify-center group-hover:bg-pink-100">
                <i data-lucide="save" class="w-6 h-6 text-pink-500"></i>
            </div>
            <span class="text-xs font-black text-slate-900">Add to cart</span>
        </button>
    </form>
</div>
