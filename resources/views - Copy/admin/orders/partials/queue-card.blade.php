@php
    $code = $order->order_number;
    $prefix = strlen($code) > 5 ? substr($code, 0, -5) : '';
    $last5 = strlen($code) > 5 ? substr($code, -5) : $code;

    $name = ucwords(strtolower(trim($order->shipping_address['name'] ?? ($order->user->name ?? 'Guest'))));
    $action = $order->forward_action;
    $stage = $order->queue_stage;

    // Best-effort target-tray hint for the New card. Until the local helper
    // reports fitted trays (Section 6), fall back to the order's media size.
    $flow = $order->flow_data ?? [];
    $mediaHint = $flow['size_dimensions'] ?? ($order->items->first()->product->name ?? null);
@endphp

<div class="bg-white rounded-xl border border-surface-200/80 shadow-2xs p-3.5 space-y-3">
    {{-- Header: code + payment tag --}}
    <div class="flex items-start justify-between gap-2">
        <a href="{{ route($rPrefix . 'orders.show', $order) }}"
            class="font-mono text-xs text-surface-500 hover:text-brand-600 transition" title="{{ $order->order_number }}">
            {{ $prefix }}<strong class="font-black text-surface-950">{{ $last5 }}</strong>
        </a>
        @if ($order->payment_status === 'paid')
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                Paid online
            </span>
        @else
            <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                Collect {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }} at pickup
            </span>
        @endif
    </div>

    {{-- Items + quantity --}}
    <div class="space-y-1">
        @foreach ($order->items as $item)
            <div class="flex items-center justify-between text-xs">
                <span class="text-surface-700 font-medium truncate max-w-[170px]"
                    title="{{ $item->product_name ?? ($item->product->name ?? 'Item') }}">
                    {{ $item->product_name ?? ($item->product->name ?? 'Item') }}
                </span>
                <span class="font-mono text-surface-500 shrink-0">×{{ $item->quantity }}</span>
            </div>
        @endforeach
    </div>

    {{-- Customer + time --}}
    <div class="flex items-center justify-between text-[11px] text-surface-500">
        <span class="font-semibold text-surface-700 truncate max-w-[150px]" title="{{ $name }}">{{ $name }}</span>
        <span class="whitespace-nowrap">{{ $order->created_at->format('d M · H:i') }}</span>
    </div>

    {{-- Single state action --}}
    @if ($action)
        <form method="POST" action="{{ route($rPrefix . 'orders.advance', $order) }}">
            @csrf
            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-brand-600 text-white text-xs font-bold hover:bg-brand-700 transition active:scale-[0.99]">
                <i data-lucide="{{ $action['icon'] }}" class="w-3.5 h-3.5"></i>
                {{ $action['label'] }}
            </button>
        </form>
        @if ($stage === \App\Models\Order::STAGE_NEW && $mediaHint)
            <p class="text-[10px] text-surface-400 text-center -mt-1">
                Target tray: MP tray · {{ $mediaHint }}
            </p>
        @endif
    @endif

    {{-- Download the print-ready PDF while the order is being printed --}}
    @if ($stage === \App\Models\Order::STAGE_PRINTING)
        <a href="{{ route($rPrefix . 'orders.downloadPdf', $order) }}"
            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-surface-200 text-surface-700 text-xs font-bold hover:bg-surface-50 transition">
            <i data-lucide="download" class="w-3.5 h-3.5"></i>
            Download PDF
        </a>
    @endif

    {{-- Undo (every card except New) --}}
    @if ($stage !== \App\Models\Order::STAGE_NEW)
        <form method="POST" action="{{ route($rPrefix . 'orders.undoStatus', $order) }}" class="text-center">
            @csrf
            <button type="submit"
                class="text-[11px] font-medium text-surface-400 hover:text-surface-700 transition inline-flex items-center gap-1">
                <i data-lucide="undo-2" class="w-3 h-3"></i> Undo
            </button>
        </form>
    @endif
</div>
