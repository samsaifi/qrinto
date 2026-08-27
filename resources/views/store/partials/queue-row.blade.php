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
$sizeDimDisplay = ($sizeW && $sizeH) ? ($sizeW . ' × ' . $sizeH . ' ' . $sizeUnit) : null;
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
        $trayHint = $match['label'] . ' · ' . ($mediaShort[$match['media']] ?? ($match['media'] ?? '—'));
        }
    }
@endphp

<div
    class="bg-white border border-slate-200/80 rounded-2xl px-5 py-4 flex items-center gap-5 flex-wrap {{ $isDone ? 'opacity-70' : '' }}">
    {{-- Code --}}
    <div class="w-[120px] shrink-0">
        <a class="mono text-[11px] text-slate-400 hover:text-[#287d3c] transition block leading-tight"
            title="{{ $order->order_number }}">
            {{ $prefix }}<br><strong class="text-slate-900 font-extrabold text-[13px]">{{ $last5 }}</strong>
        </a>
    </div>

    {{-- Items + customer --}}
    <div class="flex-1 min-w-[180px]">
        @foreach ($order->items as $item)
            <p class="font-bold text-slate-900 text-[15px] leading-snug">
                {{ $item->quantity }} × {{ $item->product_name ?? ($item->product->name ?? 'Item') }}
            </p>
        @endforeach
        <p class="text-[13px] text-slate-500 mt-0.5">{{ $name }} · {{ $verb }}
            {{ $time->format('g:i A') }}</p>
        <p class="mono text-[12px] text-slate-400 mt-0.5">{{ $sizeDimDisplay ?? '—' }}</p>
    </div>

    {{-- Payment tag --}}
    <div class="shrink-0">
        @if ($order->payment_status === 'paid')
            <span
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">Paid
                online</span>
        @else
            <span
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 whitespace-nowrap">
                Collect {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }} at
                pickup
            </span>
        @endif
    </div>

    {{-- Action + secondary --}}
    <div class="shrink-0 flex items-center gap-4">
        @if ($action)
            @php
                // Ready → "Picked up" is a neutral action (the customer is
                // right there); everything upstream is the brand-primary CTA.
                $btnClass =
                    $stage === \App\Models\Order::STAGE_READY
                        ? 'bg-white text-slate-800 border border-slate-200 hover:bg-slate-50'
                        : 'bg-[#287d3c] hover:bg-emerald-800 text-white';
            @endphp
            <div class="text-right">
                @if ($stage === \App\Models\Order::STAGE_NEW)
                    {{-- New → "Print on 931BL": route through QZ Tray, then advance --}}
                    <button type="button" data-qz-print data-order-id="{{ $order->id }}"
                        data-prepare-url="{{ route('storepanel.orders.preparePrint', $order) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition active:scale-[0.98] {{ $btnClass }}">
                        <i data-lucide="{{ $action['icon'] }}" class="w-4 h-4"></i>
                        {{ $action['label'] }}
                    </button>
                @else
                    <form method="POST" action="{{ route($rPrefix . 'orders.advance', $order) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition active:scale-[0.98] {{ $btnClass }}">
                            <i data-lucide="{{ $action['icon'] }}" class="w-4 h-4"></i>
                            {{ $action['label'] }}
                        </button>
                    </form>
                @endif
                @if ($stage === \App\Models\Order::STAGE_NEW && $trayHint)
                    <p class="mono text-[11px] text-slate-400 mt-1.5">{{ $trayHint }}</p>
                @endif
                @if ($stage === \App\Models\Order::STAGE_PRINTING)
                    <button type="button" data-qz-print data-order-id="{{ $order->id }}"
                        data-prepare-url="{{ route('storepanel.orders.preparePrint', $order) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition active:scale-[0.98] bg-white text-slate-800 border border-slate-200 hover:bg-slate-50 mt-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Again Print on 931BL
                    </button>
                    <a href="{{ route($rPrefix . 'orders.downloadPdf', $order) }}"
                        class="inline-flex items-center gap-1 mono text-[11px] text-slate-500 hover:text-[#287d3c] mt-1.5">
                        <i data-lucide="download" class="w-3 h-3"></i> Download PDF
                    </a>
                @endif
            </div>
        @elseif ($isDone)
            {{-- Terminal state: show a neutral "Done" chip in the action slot --}}
            <span
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold border border-slate-200">
                Done
            </span>
        @endif

        @if ($stage !== \App\Models\Order::STAGE_NEW)
            <form method="POST" action="{{ route($rPrefix . 'orders.undoStatus', $order) }}">
                @csrf
                <button type="submit"
                    class="text-[13px] font-medium text-slate-400 hover:text-slate-700 underline underline-offset-2 transition">Undo</button>
            </form>
        @endif
    </div>
</div>
