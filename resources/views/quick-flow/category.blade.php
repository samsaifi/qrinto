@extends('layouts.quick-flow')

@section('title', 'Choose your size')
@section('header_title', $type->name)

@section('content')
<style>
    .category-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
    }

    .header-icon {
        width: 64px;
        height: 64px;
        background: #f0f9ff;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        flex-shrink: 0;
    }

    .header-icon svg {
        width: 100%;
        height: 100%;
        fill: #0284c7 !important;
    }

    /* ── Size groups ─────────────────────────────── */
    .size-groups {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .size-group {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 24px;
        padding: 18px 18px 8px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }

    .size-group-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        padding: 0 4px 4px;
    }

    .size-group-name {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .size-group-dim {
        color: #0284c7;
        background: #f0f9ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        padding: 4px 10px;
        border-radius: 999px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* ── Variant rows (Folded / Flat …) ──────────── */
    .variant-row {
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        padding: 14px 12px;
        border-radius: 16px;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .variant-row + .variant-row {
        border-top: 1px solid #f4f6f9;
    }

    .variant-row:hover {
        background: #f8fbfe;
    }

    .variant-row:active {
        transform: scale(0.99);
    }

    .variant-glyph {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        background: #f0f9ff;
        color: #0ea5e9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .variant-body {
        flex: 1;
        min-width: 0;
    }

    .variant-title {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .variant-price {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 2px;
    }

    .price-current {
        color: #0ea5e9;
        font-weight: 700;
        font-size: 13px;
    }

    .price-old {
        color: #cbd5e1;
        text-decoration: line-through;
        font-size: 12px;
    }

    .variant-arrow {
        color: #cbd5e1;
        flex-shrink: 0;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .variant-row:hover .variant-arrow {
        color: #0ea5e9;
        transform: translateX(3px);
    }
</style>

<div class="category-header">
    <a href="javascript:history.back()" class="w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="header-icon">
         @if($type->icon_svg)
            {!! $type->icon_svg !!}
        @else
            <i data-lucide="package" style="width: 32px; height: 32px; color: #0ea5e9;"></i>
        @endif
    </div>
    <div>
        <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; margin: 0;">{{ $type->name }}</h1>
        <p style="font-size: 15px; color: #64748b; font-weight: 500; margin: 0;">Choose your size</p>
    </div>
</div>

<div class="size-groups" data-tour="size-list">
    @if(isset($subTypes))
        @foreach($subTypes->groupBy('name') as $groupName => $variants)
        @php($first = $variants->first())
        <div class="size-group">
            <div class="size-group-head">
                <h2 class="size-group-name">{{ $groupName }}</h2>
                @if($first->width && $first->height)
                <span class="size-group-dim">{{ $first->width }} × {{ $first->height }} {{ $first->unit }}</span>
                @endif
            </div>

            @foreach($variants as $sub)
            <a href="{{ route('flow.category', $sub->slug) }}" class="variant-row">
                <div class="variant-glyph">
                    <i data-lucide="{{ \Illuminate\Support\Str::contains(strtolower($sub->title ?? ''), 'flat') ? 'square' : 'book-open' }}" style="width: 20px; height: 20px;"></i>
                </div>
                <div class="variant-body">
                    <p class="variant-title">{{ $sub->title ?? 'Standard' }}</p>
                    @if($sub->price)
                    <div class="variant-price">
                        <span class="price-current">Starting at {{ \App\Services\CurrencyService::format($sub->price) }}</span>
                        @if($sub->old_price)
                        <span class="price-old">{{ \App\Services\CurrencyService::format($sub->old_price) }}</span>
                        @endif
                    </div>
                    @endif
                </div>
                <i data-lucide="chevron-right" class="variant-arrow" style="width: 22px; height: 22px;"></i>
            </a>
            @endforeach
        </div>
        @endforeach
    @endif
</div>
@endsection
