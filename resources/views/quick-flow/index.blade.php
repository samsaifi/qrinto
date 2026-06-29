@extends('layouts.quick-flow')

@section('title', 'What would you like to create?')
@section('header_title', 'Create')

@section('content')
<style>
    .selection-grid {
        display: grid;
        grid-template-columns: 47% 47%;
        gap: 20px;
        padding-top: 20px;
    }

    .flow-card {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 60px;
        padding: 40px 20px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .flow-card:hover {
        border-color: #0ea5e9;
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.1);
        transform: translateY(-2px);
    }

    .flow-card.active-link {
        border-color: #0ea5e9;
    }

    .icon-box {
        width: 100px;
        height: 100px;
        background: #f8fafc;
        border-radius: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
        padding: 20px;
    }

    .icon-box svg {
        width: 100%;
        height: 100%;
        fill: #0284c7 !important;
    }

    .card-name {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.2;
    }

    .card-title {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        color: #94a3b8;
        margin-top: 8px;
        font-weight: 500;
    }

    /* Disabled State */
    .flow-card.disabled {
        opacity: 0.4;
        filter: grayscale(1);
        pointer-events: none;
        background: #fafafa;
        border-color: #f1f5f9;
    }

    .paused-badge {
        position: absolute;
        top: 25px;
        right: 25px;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 640px) {
        .flow-card {
            border-radius: 40px;
            padding: 30px 15px;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 25px;
        }

        .card-name {
            font-size: 20px;
        }

        .card-title {
            font-size: 14px;
        }
    }
</style>
@php
    $activeStore = session()->has('active_store_id') ? \App\Models\Store::find(session('active_store_id')) : null;
@endphp
<div class="px-5 pt-5 pb-4  ">

  {{-- Store pill badge --}}
  <div class="inline-flex items-center gap-2 bg-brand-100 border border-brand-200 rounded-full pl-1.5 pr-3 py-1 mb-5">
    <img src="{{ asset('/storage/') . '/' . $activeStore->logo ?? asset('images/default-logo.png') }}"
           alt="{{ $activeStore->store_name }}"
           class="w-6 h-6 rounded-full object-cover border border-slate-200">
    <span class="text-[13px] font-medium text-brand-800">{{ $activeStore->store_name }}</span>
  </div>

  {{-- Headline --}}
  <h1 class="text-[28px] font-black leading-tight text-black mb-1.5">
    What would you like to<br>
    <span class="text-brand-500">create?</span>
  </h1>

  {{-- Subtitle --}}
  <p class="text-[13px] text-black">
    Printed on-site in minutes - pick a product below.
  </p>

</div>

<div class="selection-grid">
    @foreach($productTypes as $type)
    @if($type->is_active)
    <a href="{{ route('flow.category', $type->slug) }}" class="flow-card">
        <div class="icon-box">
            @if($type->icon_svg)
            {!! $type->icon_svg !!}
            @else
            <i data-lucide="package" style="width: 40px; height: 40px; color: #0ea5e9;"></i>
            @endif
        </div>
        <h3 class="card-name">{{ $type->name }}</h3>
        @if($type->title)
        <p class="card-title">{{ $type->title }}</p>
        @endif
    </a>
    @else
    <div class="flow-card disabled">
        <div class="paused-badge">PAUSED</div>
        <div class="icon-box">
            @if($type->icon_svg)
            {!! $type->icon_svg !!}
            @else
            <i data-lucide="package" style="width: 40px; height: 40px; color: #cbd5e1;"></i>
            @endif
        </div>
        <h3 class="card-name">{{ $type->name }}</h3>
        @if($type->title)
        <p class="card-title">{{ $type->title }}</p>
        @endif
    </div>
    @endif
    @endforeach
</div>
@endsection