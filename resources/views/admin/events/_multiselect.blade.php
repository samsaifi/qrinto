@php
    $tagBg = match($tagColor ?? 'brand') {
        'emerald' => 'bg-emerald-50 text-emerald-700',
        default   => 'bg-brand-50 text-brand-700',
    };
    $highlightBg = match($tagColor ?? 'brand') {
        'emerald' => 'bg-emerald-50/50',
        default   => 'bg-brand-50/50',
    };
    $uid = 'ms_' . md5($fieldName . microtime());
@endphp
<div id="{{ $uid }}"
     x-data="{
        items: [],
        selected: [],
        open: false,
        search: '',

        init() {
            const el = document.getElementById('{{ $uid }}');
            this.items = JSON.parse(el.dataset.items);
            this.selected = JSON.parse(el.dataset.selected);
        },

        get filteredItems() {
            if (!this.search) return this.items;
            const q = this.search.toLowerCase();
            return this.items.filter(i => i.name.toLowerCase().includes(q));
        },

        getLabel(id) {
            const item = this.items.find(i => String(i.id) === String(id));
            return item ? item.name : '';
        },

        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) this.selected.push(id);
            else this.selected.splice(idx, 1);
        },

        toggleAll() {
            if (this.selected.length === this.items.length) this.selected = [];
            else this.selected = this.items.map(i => String(i.id));
        }
     }"
     data-items='@json($items)'
     data-selected='@json(collect($selectedIds)->map(fn($id) => (string) $id)->values())'
     class="relative">
    {{-- Trigger --}}
    <div @click="open = !open" @click.outside="open = false"
         class="w-full min-h-[42px] flex flex-wrap items-center gap-1.5 px-3 py-2 rounded-xl border border-surface-200 bg-white cursor-pointer focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 transition">
        <template x-for="id in selected" :key="id">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md {{ $tagBg }}">
                <span x-text="getLabel(id)"></span>
                <button type="button" @click.stop="toggle(id)" class="hover:opacity-70 leading-none">&times;</button>
            </span>
        </template>
        <span x-show="selected.length === 0" class="text-sm text-surface-400">{{ $placeholder }}</span>
        <i data-lucide="chevron-down" class="w-4 h-4 text-surface-400 ml-auto flex-shrink-0 transition-transform" :class="open && 'rotate-180'"></i>
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-transition.opacity.duration.150ms
         class="absolute z-50 mt-1 w-full bg-white rounded-xl border border-surface-200 shadow-lg overflow-hidden">
        <div class="p-2 border-b border-surface-100">
            <input type="text" x-model="search" placeholder="Search..." @click.stop
                   class="w-full text-sm rounded-lg border-surface-200 focus:border-brand-500 focus:ring-brand-500 py-1.5">
        </div>
        <div class="max-h-48 overflow-y-auto p-1">
            <label class="flex items-center gap-2 text-sm text-surface-700 px-2 py-1.5 rounded-lg hover:bg-surface-50 cursor-pointer border-b border-surface-100 mb-1"
                   @click.stop>
                <input type="checkbox" :checked="selected.length === items.length && items.length > 0"
                       @change="toggleAll()" class="rounded text-brand-600 focus:ring-brand-500">
                <span class="font-medium">Select All</span>
            </label>
            <template x-for="item in filteredItems" :key="item.id">
                <label class="flex items-center gap-2 text-sm text-surface-700 px-2 py-1.5 rounded-lg hover:bg-surface-50 cursor-pointer"
                       :class="selected.includes(String(item.id)) && '{{ $highlightBg }}'" @click.stop>
                    <input type="checkbox" :checked="selected.includes(String(item.id))"
                           @change="toggle(String(item.id))" class="rounded text-brand-600 focus:ring-brand-500">
                    <span x-text="item.name"></span>
                </label>
            </template>
            <p x-show="filteredItems.length === 0" class="text-sm text-surface-400 px-2 py-3 text-center">No results found.</p>
        </div>
    </div>

    {{-- Hidden inputs --}}
    <template x-for="id in selected" :key="'hidden-'+id">
        <input type="hidden" name="{{ $fieldName }}[]" :value="id">
    </template>
</div>
