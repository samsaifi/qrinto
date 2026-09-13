@if ($action)
    @php
        $btnClass =
            $stage === \App\Models\Order::STAGE_READY
                ? 'bg-white text-slate-800 border border-slate-200 hover:bg-slate-50'
                : 'bg-[#287d3c] hover:bg-emerald-800 text-white';
    @endphp
    <div class="text-right">
        @if ($stage === \App\Models\Order::STAGE_NEW)
            <button type="button" data-pt-print data-order-id="{{ $order->id }}"
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
            <button type="button" data-pt-print data-order-id="{{ $order->id }}"
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
    <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold border border-slate-200">
        Delivered
    </span>
@endif

@if ($stage !== \App\Models\Order::STAGE_NEW)
    <form method="POST" action="{{ route($rPrefix . 'orders.undoStatus', $order) }}">
        @csrf
        <button type="submit"
            class="text-[13px] font-medium text-slate-400 hover:text-slate-700 underline underline-offset-2 transition">Undo</button>
    </form>
@endif
