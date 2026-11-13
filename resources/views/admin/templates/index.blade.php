@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', 'Templates')

@section('content')
@php
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Design Templates</h1>
        <p class="text-sm text-surface-500">Manage ready-made templates shown in the customizer</p>
    </div>
    <a href="{{ route($rPrefix . 'templates.create', $kind !== 'photo' ? ['kind' => $kind] : []) }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Template
    </a>
</div>

{{-- Photo = customizer layouts; Card = greeting-card editor (/editor) library --}}
<div class="flex gap-2 mb-4">
    @foreach (['photo' => 'Photo templates', 'card' => 'Card templates', 'layout' => 'Card layouts', 'text_style' => 'Text styles', 'sticker_set' => 'Emojis', 'bg_palette' => 'Background colours', 'svg_art' => 'SVGs'] as $k => $label)
        <a href="{{ route($rPrefix . 'templates.index', $k !== 'photo' ? ['kind' => $k] : []) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $kind === $k ? 'bg-brand-600 text-white' : 'bg-white border border-surface-200 text-surface-600 hover:bg-surface-50' }}">
            {{ $label }} <span class="opacity-70">({{ $counts[$k] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Template</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Slug</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20 hidden sm:table-cell">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-16">Order</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                @forelse($templates as $template)
                <tr class="hover:bg-surface-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-surface-100 flex items-center justify-center flex-shrink-0">
                                @if($template->icon_type === 'upload' && $template->icon_value)
                                    <img src="{{ asset('storage/' . $template->icon_value) }}" alt="" class="w-5 h-5 object-contain">
                                @else
                                    <i data-lucide="{{ $template->icon_value ?: 'layout-template' }}" class="w-4 h-4 text-surface-500"></i>
                                @endif
                            </div>
                            <div>
                                <span class="font-semibold text-sm text-surface-800">{{ $template->name }}</span>
                                @if ($template->kind === 'svg_art')
                                    @php $svgSrc = $template->canvas_config['src'] ?? ''; @endphp
                                    <div class="text-xs text-surface-500 flex items-center gap-2">
                                        <img src="{{ preg_match('#^(https?:|data:|/)#', $svgSrc) ? $svgSrc : asset('assets/templates/' . $svgSrc) }}" alt="" class="w-8 h-8 object-contain bg-surface-50 rounded">
                                        {{ $template->canvas_config['group'] ?? '' }}
                                    </div>
                                @endif
                                @if ($template->kind === 'text_style')
                                    <div class="text-xs text-surface-500">{{ $template->canvas_config['category'] ?? '' }} · {{ count($template->canvas_config['lines'] ?? []) }} line(s)</div>
                                @endif
                                @if ($template->kind === 'sticker_set')
                                    <div class="text-base leading-6">{{ implode(' ', array_slice($template->canvas_config['items'] ?? [], 0, 12)) }}</div>
                                @endif
                                @if ($template->kind === 'bg_palette')
                                    <div class="flex gap-0.5 mt-1">
                                        @foreach (array_slice($template->canvas_config['items'] ?? [], 0, 20) as $c)
                                            <span class="w-3.5 h-3.5 rounded-sm border border-surface-200" style="background: {{ $c }}"></span>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($template->kind === 'layout')
                                    <div class="text-xs text-surface-500">{{ ['front' => 'Front', 'inside' => 'Inside', 'back' => 'Back'][$template->canvas_config['view'] ?? 'front'] ?? '' }} layout</div>
                                @endif
                                @if ($template->kind === 'card')
                                    <div class="text-xs text-surface-500">
                                        {{ $template->canvas_config['category_name'] ?? '' }}@if (!empty($template->canvas_config['style'])) · {{ $template->canvas_config['style'] }}@endif
                                        @if (!empty($template->canvas_config['featured']))<span class="ml-1 px-1.5 rounded bg-amber-100 text-amber-700 font-semibold">Featured</span>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <code class="text-xs bg-surface-100 px-2 py-0.5 rounded text-surface-600">{{ $template->slug }}</code>
                    </td>
                    <td class="px-3 py-3 text-center hidden sm:table-cell">
                        <form method="POST" action="{{ route('admin.templates.toggle', $template) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md transition
                                           {{ $template->is_active ? 'bg-accent-100 text-accent-700 hover:bg-accent-200' : 'bg-surface-200 text-surface-500 hover:bg-surface-300' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-3 py-3 text-center text-sm text-surface-500">{{ $template->sort_order }}</td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route($rPrefix . 'templates.edit', $template) }}"
                               class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route($rPrefix . 'templates.destroy', $template) }}"
                                  onsubmit="return confirm('Delete this template?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-surface-400">
                        No templates yet.
                        <a href="{{ route($rPrefix . 'templates.create', $kind !== 'photo' ? ['kind' => $kind] : []) }}" class="text-brand-600 font-medium">Add your first template</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($templates->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $templates->links() }}</div>
    @endif
</div>
@endsection
