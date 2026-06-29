@extends('layouts.quick-flow')

@section('title', 'Choose your size')
@section('header_title', $type->name)

@section('content')
<style>
    .category-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        background: #f8fafc;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
    }

    .header-icon svg {
        width: 100%;
        height: 100%;
        fill: #0284c7 !important;
    }

    .size-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .size-card {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 35px;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .size-card:hover {
        border-color: #0ea5e9;
        box-shadow: 0 8px 25px rgba(14, 165, 233, 0.08);
        transform: translateX(5px);
    }

    .size-info h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .size-dim {
        background: #f1f5f9;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
    }

    .size-title {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: #94a3b8;
        margin-top: 5px;
    }

    .size-price {
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .price-current {
        color: #0ea5e9;
        font-weight: 800;
        font-size: 14px;
    }

    .price-old {
        color: #cbd5e1;
        text-decoration: line-through;
        font-size: 12px;
    }

    .arrow-box {
        width: 50px;
        height: 50px;
        background: #f0f9ff;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0ea5e9;
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
        <h1 style="font-size: 28px; font-weight: 900; color: #0f172a; margin: 0;">{{ $type->name }}</h1>
        <p style="font-size: 16px; color: #64748b; font-weight: 500; margin: 0;">Choose your size</p>
    </div>
</div>

<div class="size-list">
    @if(isset($subTypes))
        @foreach($subTypes as $sub)
        <a href="{{ route('flow.category', $sub->slug) }}" class="size-card">
            <div class="size-info">
                <h3>
                    {{ $sub->name }}
                    @if($sub->width && $sub->height)
                    <span class="size-dim">{{ $sub->width }}x{{ $sub->height }}{{ $sub->unit }}</span>
                    @endif
                </h3>
                <p class="size-title">{{ $sub->title ?? 'Premium quality print' }}</p>
                
                @if($sub->price)
                <div class="size-price">
                    <span class="price-current">Starting at {{ \App\Services\CurrencyService::format($sub->price) }}</span>
                    @if($sub->old_price)
                    <span class="price-old">{{ \App\Services\CurrencyService::format($sub->old_price) }}</span>
                    @endif
                </div>
                @endif
            </div>
            <div class="arrow-box">
                <i data-lucide="chevron-right" style="width: 24px; height: 24px;"></i>
            </div>
        </a>
        @endforeach
    @endif
</div>
@endsection
