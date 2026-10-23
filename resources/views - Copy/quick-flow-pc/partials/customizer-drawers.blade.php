{{-- Flyout Templates Drawer --}}
<div id="templates-studio-drawer"
    class="hidden absolute left-24 top-1/2 -translate-y-1/2 w-84 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                <i data-lucide="layout-template" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Ready-Made Templates</span>
        </div>
        <button type="button" onclick="toggleTemplatesDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <p class="text-[11px] font-medium text-slate-400">Click any template to load designs onto your active canvas.</p>
    <div id="template-cat-filter" class="template-cat-filter flex flex-wrap gap-1.5 pb-1"></div>
    <div id="template-strip" class="template-strip flex flex-wrap gap-2 max-h-[360px] overflow-y-auto pr-1"></div>
</div>

{{-- Flyout Layers Drawer --}}
<div id="layers-studio-drawer"
    class="hidden absolute left-24 top-1/2 -translate-y-1/2 w-84 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg  bg-gray-100  text-gray-600 flex items-center justify-center">
                <i data-lucide="layers" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Layers Panel</span>
        </div>
        <button type="button" onclick="toggleLayersDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <p class="text-[11px] font-medium text-slate-400">Drag items to reorder stacking order on the active canvas.</p>
    <div id="layers-list" class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1"></div>
</div>

{{-- Flyout Typography Drawer --}}
@php
    $defaultFontSize = $defaultFontSize ?? 28;
    $fontSizeMin = $fontSizeMin ?? 10;
    $fontSizeMax = $fontSizeMax ?? 120;
@endphp
<div id="text-studio-drawer"
    class="hidden absolute right-24 top-1/2 -translate-y-1/2 w-80 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                <i data-lucide="type" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Typography Studio</span>
        </div>
        <button type="button" onclick="toggleTextDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <div>
        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Text Message</label>
        <div class="relative">
            <textarea id="text-input" placeholder="Type your text here..." rows="2"
                oninput="customizer.onTextInputChange(this.value)"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"></textarea>
            <button id="clear-text-btn" type="button" onclick="customizer.clearSelection()"
                class="hidden absolute right-2.5 top-2.5 text-slate-300 hover:text-slate-500">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <div>
        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Font Style</label>
        <select id="font-family-select" onchange="customizer._updateSelectedStyle('fontFamily', this.value)"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
            <option value="Inter" style="font-family: 'Inter'">Inter (Clean Sans)</option>
            <option value="Playfair Display" style="font-family: 'Playfair Display'">Playfair Display (Luxury Serif)
            </option>
            <option value="Dancing Script" style="font-family: 'Dancing Script'">Dancing Script (Cursive)</option>
            <option value="Great Vibes" style="font-family: 'Great Vibes'">Great Vibes (Elegant Script)</option>
            <option value="Pacifico" style="font-family: 'Pacifico'">Pacifico (Fun Brush)</option>
            <option value="Permanent Marker" style="font-family: 'Permanent Marker'">Permanent Marker (Bold Marker)
            </option>
            <option value="Roboto" style="font-family: 'Roboto'">Roboto (Modern)</option>
            <option value="Open Sans" style="font-family: 'Open Sans'">Open Sans (Minimal)</option>
            <option value="Poppins" style="font-family: 'Poppins'">Poppins (Geometric)</option>
            <option value="Lato" style="font-family: 'Lato'">Lato (Warm Sans)</option>
            <option value="Oswald" style="font-family: 'Oswald'">Oswald (Condensed)</option>
            <option value="Bebas Neue" style="font-family: 'Bebas Neue'">Bebas Neue (Headline)</option>
            <option value="Anton" style="font-family: 'Anton'">Anton (Impact)</option>
            <option value="Satisfy" style="font-family: 'Satisfy'">Satisfy (Signature)</option>
            <option value="Caveat" style="font-family: 'Caveat'">Caveat (Handwritten)</option>
            <option value="Lobster" style="font-family: 'Lobster'">Lobster (Vintage)</option>
            <option value="Bangers" style="font-family: 'Bangers'">Bangers (Comic)</option>
            <option value="ABeeZee" style="font-family: 'ABeeZee'">ABeeZee</option>
            <option value="Alfa Slab One" style="font-family: 'Alfa Slab One'">Alfa Slab One</option>
            <option value="Lora" style="font-family: 'Lora'">Lora (Book Serif)</option>
            <option value="Crimson Pro" style="font-family: 'Crimson Pro'">Crimson Pro (Classic)</option>
            <option value="Cinzel" style="font-family: 'Cinzel'">Cinzel (Roman)</option>
            <option value="Alex Brush" style="font-family: 'Alex Brush'">Alex Brush (Calligraphy)</option>
            <option value="Courgette" style="font-family: 'Courgette'">Courgette (Casual Script)</option>
            <option value="Sacramento" style="font-family: 'Sacramento'">Sacramento (Thin Script)</option>
            <option value="Indie Flower" style="font-family: 'Indie Flower'">Indie Flower (Handwritten)</option>
            <option value="Amatic SC" style="font-family: 'Amatic SC'">Amatic SC (Narrow Hand)</option>
            <option value="Shadows Into Light" style="font-family: 'Shadows Into Light'">Shadows Into Light</option>
            <option value="Righteous" style="font-family: 'Righteous'">Righteous (Retro)</option>
            <option value="Bungee" style="font-family: 'Bungee'">Bungee (Block)</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Size</label>
            <select id="font-size-select" onchange="customizer._updateSelectedStyle('fontSize', parseInt(this.value))"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
                @for ($i = $fontSizeMin; $i <= $fontSizeMax; $i += 2)
                    <option value="{{ $i }}" {{ $i == $defaultFontSize ? 'selected' : '' }}>
                        {{ $i }}px</option>
                @endfor
            </select>
        </div>
        <div>
            <label
                class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Alignment</label>
            <select id="text-align-select" onchange="customizer._updateSelectedStyle('textAlign', this.value)"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
                <option value="center">Center</option>
                <option value="left">Left</option>
                <option value="right">Right</option>
                <option value="justify">Justify</option>
            </select>
        </div>
    </div>

    <div class="pt-2">
        <button type="button" id="add-text-btn" onclick="customizer.addText()"
            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs py-3 rounded-xl shadow-lg shadow-purple-500/20 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Text to Canvas
        </button>
        <div id="editing-badge"
            class="hidden w-full bg-purple-50 border border-purple-200 text-purple-700 font-extrabold text-xs py-2.5 rounded-xl text-center">
            Editing Selected Text
        </div>
    </div>
</div>

{{-- Flyout Shapes & Elements Drawer --}}
<div id="shapes-studio-drawer"
    class="hidden absolute right-24 top-1/2 -translate-y-1/2 w-80 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                <i data-lucide="shapes" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Shapes Library</span>
        </div>
        <button type="button" onclick="toggleShapesDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <p class="text-[11px] font-medium text-slate-400">Click any shape to add to your design canvas.</p>
    <div class="grid grid-cols-3 gap-2.5">
        <button type="button" onclick="customizer.addShape('rectangle')"
            class="p-3 bg-slate-50 border border-slate-200 hover:border-brand-500 rounded-xl flex flex-col items-center gap-1.5 hover:bg-brand-50 transition-all">
            <div class="w-8 h-6 bg-brand-500 rounded-xs"></div>
            <span class="text-[10px] font-bold text-slate-700">Rectangle</span>
        </button>
        <button type="button" onclick="customizer.addShape('circle')"
            class="p-3 bg-slate-50 border border-slate-200 hover:border-brand-500 rounded-xl flex flex-col items-center gap-1.5 hover:bg-brand-50 transition-all">
            <div class="w-7 h-7 bg-brand-500 rounded-full"></div>
            <span class="text-[10px] font-bold text-slate-700">Circle</span>
        </button>
        <button type="button" onclick="customizer.addShape('triangle')"
            class="p-3 bg-slate-50 border border-slate-200 hover:border-brand-500 rounded-xl flex flex-col items-center gap-1.5 hover:bg-brand-50 transition-all">
            <div
                class="w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-b-[24px] border-b-brand-500">
            </div>
            <span class="text-[10px] font-bold text-slate-700">Triangle</span>
        </button>
        <button type="button" onclick="customizer.addShape('star')"
            class="p-3 bg-slate-50 border border-slate-200 hover:border-brand-500 rounded-xl flex flex-col items-center gap-1.5 hover:bg-brand-50 transition-all">
            <i data-lucide="star" class="w-6 h-6 text-brand-500 fill-brand-500"></i>
            <span class="text-[10px] font-bold text-slate-700">Star</span>
        </button>
        <button type="button" onclick="customizer.addShape('heart')"
            class="p-3 bg-slate-50 border border-slate-200 hover:border-brand-500 rounded-xl flex flex-col items-center gap-1.5 hover:bg-brand-50 transition-all">
            <i data-lucide="heart" class="w-6 h-6 text-brand-500 fill-brand-500"></i>
            <span class="text-[10px] font-bold text-slate-700">Heart</span>
        </button>
    </div>

    {{-- Shape Style & Fill Controls --}}
    <div class="pt-3 border-t border-slate-100 space-y-3">
        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider">Customize Selected
            Shape</label>

        <div class="grid grid-cols-2 gap-2">
            {{-- 1. Change Color --}}
            <label
                class="relative w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                <i data-lucide="palette" class="w-3.5 h-3.5 text-brand-600"></i> Fill Color
                <input type="color" id="shape-color-input" oninput="customizer.setShapeColor(this.value)"
                    class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
            </label>

            {{-- 2. Remove Solid Fill (Transparent) --}}
            <button type="button" onclick="customizer.removeShapeFill()"
                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all">
                <i data-lucide="ban" class="w-3.5 h-3.5 text-red-500"></i> No Fill
            </button>
        </div>

        {{-- Shape Border (Active toggle + Color + Thickness) --}}
        <div class="pt-2 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Shape Border</span>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" id="shape-lib-border-show"
                        onchange="customizer.setShapeBorderShow(this.checked)"
                        class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 cursor-pointer">
                    <span class="text-[11px] font-bold text-slate-700">Active</span>
                </label>
            </div>
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5">
                <input type="color" id="shape-lib-border-color" value="#6FB63A"
                    oninput="document.getElementById('shape-lib-border-color-val').innerText = this.value; customizer.setShapeBorderColor(this.value);"
                    class="w-6 h-6 rounded-lg border-0 cursor-pointer p-0 bg-transparent shrink-0">
                <span id="shape-lib-border-color-val"
                    class="text-[11px] font-bold text-slate-700 uppercase truncate">#6FB63A</span>
                <input type="range" id="shape-lib-border-width" min="0" max="50" value="3"
                    step="1"
                    oninput="document.getElementById('shape-lib-border-width-val').innerText = this.value + 'px'; customizer.setShapeBorderWidth(this.value);"
                    class="flex-1 h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand-600">
                <span id="shape-lib-border-width-val"
                    class="text-[11px] font-extrabold text-brand-600 shrink-0 w-9 text-right">3px</span>
            </div>
        </div>

        {{-- 3. Add Image onto Shape --}}
        <div>
            <label for="shape-img-fill-input"
                class="w-full bg-brand-50 hover:bg-brand-100 border border-brand-200 text-brand-700 font-extrabold text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 cursor-pointer transition-all shadow-2xs">
                <i data-lucide="image-plus" class="w-4 h-4 text-brand-600"></i> Fill Image into Shape
            </label>
            <input type="file" id="shape-img-fill-input" accept="image/*"
                onchange="customizer.fillShapeWithImage(this)" class="hidden">
        </div>

        {{-- 4. Reposition Image Inside Shape --}}
        <div id="reposition-shape-btn-wrap" class="hidden">
            <button type="button" onclick="customizer.enterImageRepositionMode()"
                class="w-full bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 font-extrabold text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all shadow-2xs">
                <i data-lucide="move" class="w-4 h-4 text-purple-600"></i> Reposition Image Inside Shape
            </button>
        </div>
    </div>
</div>

{{-- Flyout QR Generator Drawer --}}
<div id="qr-studio-drawer"
    class="hidden absolute right-24 top-1/2 -translate-y-1/2 w-80 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg  bg-gray-100  text-gray-600 flex items-center justify-center">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">QR Code Studio</span>
        </div>
        <button type="button" onclick="toggleQrDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <div>
        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Website URL /
            Contact Data</label>
        <input type="text" id="qr-input-text" placeholder="https://example.com"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus: border-gray-500 outline-none">
    </div>

    <button type="button" onclick="customizer.addQrCode(document.getElementById('qr-input-text').value)"
        class="w-full  bg-gray-600 hover: bg-gray-700 text-white font-extrabold text-xs py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
        <i data-lucide="qr-code" class="w-4 h-4"></i> Add QR Code to Canvas
    </button>
</div>

{{-- Flyout Image Shape Mask Drawer --}}
<div id="shape-mask-drawer"
    class="hidden absolute right-24 top-1/2 -translate-y-1/2 w-80 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg  bg-gray-100  text-gray-600 flex items-center justify-center">
                <i data-lucide="shapes" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Image Shape Mask</span>
        </div>
        <button type="button" onclick="toggleShapeMaskDrawer()" class="text-slate-400 hover:text-slate-600 p-1">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="grid grid-cols-4 gap-2 max-h-72 overflow-y-auto custom-scrollbar p-1">
        <button type="button" data-shape="rect" onclick="customizer.applyShapeMaskToSelectedImage('rect');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div class="w-8 h-8 rounded bg-slate-700 group-hover: bg-gray-600 transition-colors"></div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Rectangle</span>
        </button>

        <button type="button" data-shape="rounded-rect"
            onclick="customizer.applyShapeMaskToSelectedImage('rounded-rect');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div class="w-8 h-8 rounded-lg bg-slate-700 group-hover: bg-gray-600 transition-colors"></div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Rounded</span>
        </button>

        <button type="button" data-shape="circle" onclick="customizer.applyShapeMaskToSelectedImage('circle');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div class="w-8 h-8 rounded-full bg-slate-700 group-hover: bg-gray-600 transition-colors"></div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Circle</span>
        </button>

        <button type="button" data-shape="oval" onclick="customizer.applyShapeMaskToSelectedImage('oval');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div class="w-8 h-6 rounded-full bg-slate-700 group-hover: bg-gray-600 transition-colors my-1"></div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Oval</span>
        </button>

        <button type="button" data-shape="triangle" onclick="customizer.applyShapeMaskToSelectedImage('triangle');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div
                class="w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-b-[24px] border-b-slate-700 group-hover:border-b-emerald-600 my-0.5">
            </div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Triangle</span>
        </button>

        <button type="button" data-shape="diamond" onclick="customizer.applyShapeMaskToSelectedImage('diamond');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <div class="w-6 h-6 rotate-45 bg-slate-700 group-hover: bg-gray-600 transition-colors my-1"></div>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Diamond</span>
        </button>

        <button type="button" data-shape="star" onclick="customizer.applyShapeMaskToSelectedImage('star');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <i data-lucide="star" class="w-7 h-7 text-slate-700 group-hover: text-gray-600 fill-current my-0.5"></i>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Star</span>
        </button>

        <button type="button" data-shape="heart" onclick="customizer.applyShapeMaskToSelectedImage('heart');"
            class="shape-mask-btn relative flex flex-col items-center gap-1 p-2 rounded-xl bg-slate-50 hover: bg-gray-50 border border-slate-200 hover: border-gray-300 transition-all group cursor-pointer">
            <i data-lucide="heart" class="w-7 h-7 text-slate-700 group-hover: text-gray-600 fill-current my-0.5"></i>
            <span class="text-[10px] font-bold text-slate-600 group-hover: text-gray-700">Heart</span>
        </button>
    </div>

    {{-- Shape Border Controls Section --}}
    <div class="pt-3 border-t border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Shape Border</span>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="shape-border-show-checkbox"
                    onchange="customizer.setShapeBorderShow(this.checked)"
                    class="w-4 h-4 rounded  text-gray-600 focus:ring-emerald-500 cursor-pointer">
                <span class="text-xs font-bold text-slate-700">Show Border</span>
            </label>
        </div>

        <div id="shape-border-options-wrap" class="space-y-2.5">
            {{-- Border Color & Style --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-extrabold text-slate-500 uppercase">Color</span>
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5">
                        <input type="color" id="shape-border-color-input" value="#378ADD"
                            oninput="document.getElementById('shape-border-color-val').innerText = this.value; customizer.setShapeBorderColor(this.value);"
                            class="w-6 h-6 rounded-lg border-0 cursor-pointer p-0 bg-transparent">
                        <span id="shape-border-color-val"
                            class="text-[11px] font-bold text-slate-700 uppercase truncate">#378ADD</span>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-extrabold text-slate-500 uppercase">Style</span>
                    <select id="shape-border-style-select" onchange="customizer.setShapeBorderStyle(this.value)"
                        class="text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-700 outline-none cursor-pointer">
                        <option value="solid">Solid</option>
                        <option value="dashed">Dashed</option>
                        <option value="dotted">Dotted</option>
                    </select>
                </div>
            </div>

            {{-- Border Width Slider --}}
            <div class="space-y-1">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span>Thickness</span>
                    <span id="shape-border-width-val" class="font-extrabold  text-gray-600">3px</span>
                </div>
                <input type="range" id="shape-border-width-slider" min="0" max="50" value="3"
                    step="1"
                    oninput="document.getElementById('shape-border-width-val').innerText = this.value + 'px'; customizer.setShapeBorderWidth(this.value);"
                    class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
            </div>
        </div>
    </div>

    <div class="pt-2 border-t border-slate-100">
        <button type="button" onclick="customizer.removeShapeMaskFromImage(); toggleShapeMaskDrawer();"
            class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-extrabold text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer">
            <i data-lucide="ban" class="w-3.5 h-3.5"></i> Remove Shape Mask
        </button>
    </div>
</div>
