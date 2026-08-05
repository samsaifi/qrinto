{{-- Flyout Templates Drawer --}}
<div id="templates-studio-drawer"
    class="hidden absolute left-24 top-1/2 -translate-y-1/2 w-84 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
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
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
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
@php $defaultFontSize = $defaultFontSize ?? 28; $fontSizeMin = $fontSizeMin ?? 10; $fontSizeMax = $fontSizeMax ?? 120; @endphp
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
            <option value="Playfair Display" style="font-family: 'Playfair Display'">Playfair Display (Luxury Serif)</option>
            <option value="Dancing Script" style="font-family: 'Dancing Script'">Dancing Script (Cursive)</option>
            <option value="Great Vibes" style="font-family: 'Great Vibes'">Great Vibes (Elegant Script)</option>
            <option value="Pacifico" style="font-family: 'Pacifico'">Pacifico (Fun Brush)</option>
            <option value="Permanent Marker" style="font-family: 'Permanent Marker'">Permanent Marker (Bold Marker)</option>
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
                    <option value="{{ $i }}" {{ $i == $defaultFontSize ? 'selected' : '' }}>{{ $i }}px</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Alignment</label>
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
        <div id="editing-badge" class="hidden w-full bg-purple-50 border border-purple-200 text-purple-700 font-extrabold text-xs py-2.5 rounded-xl text-center">
            Editing Selected Text
        </div>
    </div>
</div>
