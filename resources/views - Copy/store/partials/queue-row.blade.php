@php
    $code = $order->order_number;
    $prefix = strlen($code) > 5 ? substr($code, 0, -5) : '';
    $last5 = strlen($code) > 5 ? substr($code, -5) : $code;

    $name = ucwords(strtolower(trim($order->shipping_address['name'] ?? ($order->user->name ?? 'Guest'))));
    $stage = $order->queue_stage;
    $action = $order->forward_action;

    $verb = match ($stage) {
        \App\Models\Order::STAGE_READY => 'ready',
        \App\Models\Order::STAGE_DONE => 'picked up',
        default => 'placed',
    };
    $time = $order->updated_at && $stage !== \App\Models\Order::STAGE_NEW ? $order->updated_at : $order->created_at;
    $isDone = $stage === \App\Models\Order::STAGE_DONE;

    $flow = $order->flow_data ?? [];
    $sizeW = $flow['size_width'] ?? null;
    $sizeH = $flow['size_height'] ?? null;
    $sizeUnit = $flow['size_unit'] ?? 'inch';
    $sizeDimDisplay = $sizeW && $sizeH ? $sizeW . ' × ' . $sizeH . ' ' . $sizeUnit : null;
    $orderSize = $flow['size_slug'] ?? ($flow['size_dimensions'] ?? null);
    if ($orderSize) {
        $orderSize = str_replace([' ', '×'], ['', 'x'], strtolower($orderSize));
    }
    $trayHint = null;
    if ($stage === \App\Models\Order::STAGE_NEW && $store) {
        $mediaShort = [
            'plain' => 'plain',
            'cardstock' => 'cardstock',
            'cardstock_scored' => 'cardstock',
            'photo_glossy' => 'glossy',
            'photo_lustre' => 'lustre',
            'photo_matte' => 'matte',
            'film' => 'film',
            'envelopes' => 'envelopes',
            'labels' => 'labels',
            'magnets' => 'magnets',
        ];
        $match = $store->matchTrayForSize($orderSize);
        if ($match) {
            $trayHint = $match['label'] . ' · ' . ($mediaShort[$match['media']] ?? ($match['media'] ?? '-'));
        }
    }
@endphp

<div class="bg-white border border-slate-200/80 rounded-2xl px-4 py-3 md:px-5 md:py-4 {{ $isDone ? 'opacity-70' : '' }}">
    {{-- Desktop: horizontal row --}}
    <div class="hidden md:flex items-center gap-5 flex-wrap">
        <div class="w-[120px] shrink-0">
            <a class="mono text-[11px] text-slate-400 hover:text-[#287d3c] transition block leading-tight"
                title="{{ $order->order_number }}">
                {{ $prefix }}<br><strong class="text-slate-900 font-extrabold text-[13px]">{{ $last5 }}</strong>
            </a>
        </div>

        <div class="flex-1 min-w-[180px]">
            @foreach ($order->items as $item)
                <p class="font-bold text-slate-900 text-[15px] leading-snug">
                    {{ $item->quantity }} × {{ $item->product_name ?? ($item->product->name ?? 'Item') }}
                </p>
            @endforeach
            <p class="text-[13px] text-slate-500 mt-0.5">{{ $name }} · {{ $verb }}
                {{ $time->format('g:i A') }}</p>
            <p class="mono text-[12px] text-slate-400 mt-0.5">{{ $sizeDimDisplay ?? '-' }}</p>
        </div>

        <div class="shrink-0">
            @if ($order->payment_status === 'paid')
                <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">Paid online</span>
            @else
                <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                    Collect {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }} at pickup
                </span>
            @endif
        </div>

        <div class="shrink-0 flex items-center gap-4">
            @include('store.partials._queue-row-actions', ['order' => $order, 'stage' => $stage, 'action' => $action, 'isDone' => $isDone, 'rPrefix' => $rPrefix, 'trayHint' => $trayHint])
        </div>
    </div>

    {{-- Mobile: vertical stack --}}
    <div class="md:hidden flex flex-col gap-2.5">
        {{-- Top row: order code + payment tag --}}
        <div class="flex items-center justify-between gap-3">
            <span class="mono text-[11px] text-slate-400 leading-tight">
                {{ $prefix }}<strong class="text-slate-900 font-extrabold text-[13px]">{{ $last5 }}</strong>
            </span>
            @if ($order->payment_status === 'paid')
                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">Paid online</span>
            @else
                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                    Collect {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }} at pickup
                </span>
            @endif
        </div>

        {{-- Items --}}
        <div>
            @foreach ($order->items as $item)
                <p class="font-bold text-slate-900 text-[14px] leading-snug">
                    {{ $item->quantity }} × {{ $item->product_name ?? ($item->product->name ?? 'Item') }}
                </p>
            @endforeach
            <p class="text-[12px] text-slate-500 mt-0.5">{{ $name }} · {{ $verb }} {{ $time->format('g:i A') }}</p>
            @if ($sizeDimDisplay)
                <p class="mono text-[11px] text-slate-400 mt-0.5">{{ $sizeDimDisplay }}</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 flex-wrap">
            @include('store.partials._queue-row-actions', ['order' => $order, 'stage' => $stage, 'action' => $action, 'isDone' => $isDone, 'rPrefix' => $rPrefix, 'trayHint' => $trayHint])
        </div>
    </div>
</div>
