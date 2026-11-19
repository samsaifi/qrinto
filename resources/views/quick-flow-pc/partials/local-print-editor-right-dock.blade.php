{{-- Local-print editor right dock (own-design sizes + business card; replaces
     customizer-right-dock in quick-flow-pc.customize). Same tools and the ids the
     editor JS relies on (upload-tool-label, photo-upload-input, upload-text,
     text-dock-trigger, shapes-dock-trigger, color-tool, text-color-input,
     qr-dock-trigger). Continue goes through customizer.lpContinue()
     (partials/local-print-editor-ui):
       - own-design sizes: the existing composite upload + checkout-form POST to
         localprint.preview (same fields as customizer-right-dock);
       - business card: partials/business-card-editor saves both sides.
     Expects $lpUi, $product. --}}
<div class="cust-right-dock lp-rdock absolute right-1 lg:right-4 z-30" role="toolbar" aria-label="Design tools">

    <label for="photo-upload-input" id="upload-tool-label" class="lp-rtool" title="Upload Photo">
        <span class="lp-rtool-ico"><i data-lucide="image-plus" class="w-[18px] h-[18px]" aria-hidden="true"></i></span>
        <span id="upload-text" class="lp-rtool-label">Photo</span>
    </label>
    <input type="file" onchange="customizer.handleFileUpload(this)" class="hidden" id="photo-upload-input" accept="image/*">

    <button type="button" id="text-dock-trigger" class="lp-rtool" onclick="toggleTextDrawer()" title="Add Custom Text">
        <span class="lp-rtool-ico"><i data-lucide="type" class="w-[18px] h-[18px]" aria-hidden="true"></i></span>
        <span class="lp-rtool-label">Text</span>
    </button>

    <button type="button" id="shapes-dock-trigger" class="lp-rtool" onclick="toggleShapesDrawer()" title="Shapes & Stickers">
        <span class="lp-rtool-ico"><i data-lucide="shapes" class="w-[18px] h-[18px]" aria-hidden="true"></i></span>
        <span class="lp-rtool-label">Shapes</span>
    </button>

    <div id="color-tool" class="lp-rtool" title="Background & Colors">
        <span class="lp-rtool-ico relative overflow-hidden">
            <i data-lucide="palette" class="w-[18px] h-[18px]" aria-hidden="true"></i>
            <input type="color" id="text-color-input" aria-label="Color" oninput="customizer._updateSelectedStyle('fill', this.value)"
                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
        </span>
        <span class="lp-rtool-label">Color</span>
    </div>

    <button type="button" id="qr-dock-trigger" class="lp-rtool" onclick="toggleQrDrawer()" title="Generate QR Code">
        <span class="lp-rtool-ico"><i data-lucide="qr-code" class="w-[18px] h-[18px]" aria-hidden="true"></i></span>
        <span class="lp-rtool-label">QR code</span>
    </button>

    <button type="button" id="lp-continue-btn" class="lp-rtool lp-rtool-go" onclick="customizer.lpContinue()"
        title="{{ $lpUi['businessCard'] ? 'Save both sides and continue to sheet layout' : ($lpUi['cartMode'] ? 'Continue to order' : 'Continue to preview and print') }}">
        <span class="lp-rtool-ico"><i data-lucide="arrow-right" class="w-[18px] h-[18px]" aria-hidden="true"></i></span>
        <span class="lp-rtool-label">Continue</span>
    </button>

    @unless ($lpUi['businessCard'])
        {{-- Same submission as customizer-right-dock (read by customizer-base submitAllCanvases). --}}
        <form action="{{ $lpUi['cartMode'] ? route('flow.cart.add') : route('localprint.preview') }}" method="POST" id="checkout-form" class="hidden">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="upload_ids" id="upload_ids_field">
            @unless ($lpUi['cartMode'])
            @php $fd = session('quick_flow_data', []); @endphp
            <input type="hidden" name="size_width" value="{{ $fd['size_width'] ?? '' }}">
            <input type="hidden" name="size_height" value="{{ $fd['size_height'] ?? '' }}">
            <input type="hidden" name="size_unit" value="{{ $fd['size_unit'] ?? 'in' }}">
            <input type="hidden" name="orientation" value="{{ $fd['orientation'] ?? 'portrait' }}">
            @endunless
        </form>
    @endunless
</div>

<style>
    .lp-rdock { display: grid; grid-template-columns: repeat(2, 60px); gap: 10px; padding: 12px; border-radius: 16px; background: #FFFFFF; border: 1px solid #E2E7E0; box-shadow: 0 1px 3px rgba(23, 32, 26, .06) }
    .lp-rtool { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 8px 0; border: none; border-radius: 12px; background: transparent; cursor: pointer; font-family: inherit; color: #17201A }
    .lp-rtool:hover { background: #F0F3EE }
    .lp-rtool:focus-visible, .lp-rtool:focus-within { outline: 2px solid #2F7A3B; outline-offset: 2px }
    .lp-rtool-ico { width: 40px; height: 40px; border-radius: 50%; border: 1px solid #E2E7E0; background: #FFFFFF; display: flex; align-items: center; justify-content: center; color: #2F6E35 }
    .lp-rtool-label { font-size: 12px; font-weight: 600; line-height: 1.2; white-space: nowrap }
    .lp-rtool-go .lp-rtool-ico { background: #1F6B2E; border-color: #1F6B2E; color: #FFFFFF }
    .lp-rtool-go .lp-rtool-label { font-weight: 700 }
    .lp-rtool-go:disabled { cursor: progress; opacity: .8 }
    .lp-rtool-go.saving .lp-rtool-ico svg { animation: lpSpin 1s linear infinite }
    @keyframes lpSpin { to { transform: rotate(360deg) } }
</style>
