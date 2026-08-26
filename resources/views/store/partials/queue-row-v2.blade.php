@php
    $code = $order->order_number;
    $codeParts = explode('-', $code);

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
    $orderSize = $flow['size_dimensions'] ?? null;
    if ($orderSize) {
        $orderSize = str_replace([' ', '×'], ['', 'x'], strtolower($orderSize));
    }
    $trayHint = null;
    if ($stage === \App\Models\Order::STAGE_NEW && $store) {
        $mediaShort = ['plain'=>'plain','cardstock'=>'cardstock','cardstock_scored'=>'cardstock','photo_glossy'=>'glossy','photo_lustre'=>'lustre','photo_matte'=>'matte','film'=>'film','envelopes'=>'envelopes','labels'=>'labels','magnets'=>'magnets'];
        $match = $store->matchTrayForSize($orderSize);
        if ($match) {
            $trayHint = $match['label'] . ' · ' . ($mediaShort[$match['media']] ?? ($match['media'] ?? '—'));
        }
    }

    $journeyStages = ['new' => 'New', 'printing' => 'Printing', 'ready' => 'Ready', 'done' => 'Picked up'];
    $stageKeys = array_keys($journeyStages);
    $currentIdx = array_search($stage, $stageKeys);
    if ($currentIdx === false) $currentIdx = count($stageKeys) - 1;
@endphp

<div class="v2-card {{ $isDone ? 'is-done' : '' }} {{ $stage === 'printing' ? 'is-printing' : '' }}"
     id="v2-card-{{ $order->id }}"
     data-prepare-url="{{ route('storepanel.orders.preparePrint', $order) }}">
    {{-- Order ID --}}
    <div class="v2-col-id">
        @foreach ($codeParts as $part)
            @if ($loop->last)
                <strong class="text-slate-900 font-extrabold text-[13px]">{{ $part }}</strong>
            @else
                {{ $part }}<br>
            @endif
        @endforeach
    </div>

    {{-- Items + customer + journey --}}
    <div class="v2-col-main">
        @foreach ($order->items as $item)
            <p class="v2-title">{{ $item->quantity }} × {{ $item->product_name ?? ($item->product->name ?? 'Item') }}</p>
        @endforeach
        <p class="v2-sub">{{ $name }} · {{ $verb }} {{ $time->format('g:i A') }}</p>

        {{-- Journey stepper --}}
        <div class="v2-journey">
            @foreach ($journeyStages as $sKey => $sLabel)
                @php
                    $sIdx = array_search($sKey, $stageKeys);
                    $state = $sIdx < $currentIdx ? 'done' : ($sIdx === $currentIdx ? 'current' : '');
                    $lineDone = $sIdx <= $currentIdx ? 'done' : '';
                @endphp
                @if (!$loop->first)
                    <div class="v2-j-line {{ $lineDone }}"></div>
                @endif
                <div class="v2-j-step {{ $state }}">
                    <div class="v2-j-dot">
                        @if ($sIdx < $currentIdx)
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                        @endif
                    </div>
                    <span class="v2-j-label">{{ $sLabel }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Payment pill --}}
    <div class="v2-col-price">
        @if ($order->payment_status === 'paid')
            <span class="v2-pill-paid">Paid online</span>
        @else
            <span class="v2-pill">Collect {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }} at pickup</span>
        @endif
    </div>

    {{-- Action column --}}
    <div class="v2-col-action">
        @if ($action)
            @if ($stage === \App\Models\Order::STAGE_NEW)
                <button type="button" data-qz-print data-order-id="{{ $order->id }}"
                    data-prepare-url="{{ route('storepanel.orders.preparePrint', $order) }}"
                    class="v2-btn-primary">
                    <i data-lucide="{{ $action['icon'] }}" class="w-4 h-4"></i>
                    {{ $action['label'] }}
                </button>
                @if ($trayHint)
                    <p class="mono text-[11px] text-slate-400">{{ $trayHint }}</p>
                @endif
            @elseif ($stage === \App\Models\Order::STAGE_READY)
                <form method="POST" action="{{ route($rPrefix . 'orders.advance', $order) }}">
                    @csrf
                    <button type="submit" class="v2-btn-neutral">
                        <i data-lucide="{{ $action['icon'] }}" class="w-4 h-4"></i>
                        {{ $action['label'] }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route($rPrefix . 'orders.advance', $order) }}">
                    @csrf
                    <button type="submit" class="v2-btn-primary">
                        <i data-lucide="{{ $action['icon'] }}" class="w-4 h-4"></i>
                        {{ $action['label'] }}
                    </button>
                </form>
            @endif
        @elseif ($isDone)
            <span class="v2-btn-done">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                Done
            </span>
        @endif

        <div class="v2-link-row">
            @if ($stage === \App\Models\Order::STAGE_PRINTING)
                <a href="{{ route($rPrefix . 'orders.downloadPdf', $order) }}" class="v2-link">
                    <i data-lucide="download" class="w-3 h-3"></i> Download PDF
                </a>
            @endif
            @if ($stage !== \App\Models\Order::STAGE_NEW)
                <form method="POST" action="{{ route($rPrefix . 'orders.undoStatus', $order) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="v2-undo">Undo</button>
                </form>
            @endif
        </div>
    </div>
</div>
