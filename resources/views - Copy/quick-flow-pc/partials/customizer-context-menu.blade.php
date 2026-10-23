{{-- Desktop Right-Click Context Menu --}}
<div id="customizer-context-menu"
    class="hidden fixed z-50 w-52 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-2xl p-1.5 shadow-2xl space-y-0.5 text-xs font-semibold text-slate-700">
    <button type="button" onclick="customizer.duplicateSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-slate-100 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="copy" class="w-4 h-4 text-slate-500"></i> Duplicate</span>
        <span class="text-[10px] text-slate-400 font-mono">Ctrl+D</span>
    </button>
    <button type="button" onclick="customizer.groupSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-indigo-50 text-indigo-700 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="folder-plus" class="w-4 h-4 text-indigo-600"></i>
            Group</span>
        <span class="text-[10px] text-slate-400 font-mono">Ctrl+G</span>
    </button>
    <button type="button" onclick="customizer.ungroupSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-amber-50 text-amber-700 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="folder-minus" class="w-4 h-4 text-amber-600"></i>
            Ungroup</span>
        <span class="text-[10px] text-slate-400 font-mono">Ctrl+Shift+G</span>
    </button>
    <button type="button" onclick="customizer.enterCropMode(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-brand-50 text-brand-700 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="crop" class="w-4 h-4 text-pink-600"></i> Crop Image</span>
    </button>

    <div class="h-px bg-slate-100 my-1"></div>

    <button type="button" onclick="customizer.bringToFrontSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-slate-100 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="arrow-up-to-line" class="w-4 h-4 text-slate-500"></i>
            Bring to Front</span>
    </button>
    <button type="button" onclick="customizer.sendToBackSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-slate-100 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="arrow-down-to-line" class="w-4 h-4 text-slate-500"></i>
            Send to Back</span>
    </button>

    <div class="h-px bg-slate-100 my-1"></div>

    <button type="button" onclick="customizer.toggleLockSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-amber-50 text-amber-700 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4 text-amber-600"></i> Lock /
            Unlock</span>
    </button>
    <button type="button" onclick="customizer.toggleHideSelected(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-slate-100 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="eye-off" class="w-4 h-4 text-slate-500"></i> Hide /
            Show</span>
    </button>

    <div class="h-px bg-slate-100 my-1"></div>

    <button type="button" onclick="customizer.handleRemove(); hideContextMenu();"
        class="w-full flex items-center justify-between px-3 py-2 hover:bg-red-50 text-red-600 rounded-xl transition-all">
        <span class="flex items-center gap-2"><i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i> Delete</span>
        <span class="text-[10px] text-red-400 font-mono">Del</span>
    </button>
</div>

<script>
    function hideContextMenu() {
        const menu = document.getElementById('customizer-context-menu');
        if (menu) menu.classList.add('hidden');
    }

    document.addEventListener('click', hideContextMenu);
</script>
