{{--
    Printer status strip for the 931BL.

    The live 931BL state and per-tray media come from the local print helper
    (Section 6). Until that helper reports in, this strip degrades gracefully:
    it shows an "unknown" state rather than a fake "online" badge, honouring the
    design rule that internal states never appear when they're not real.
--}}
@php
    // When the helper is wired up it will populate $printerStatus; for now it is
    // intentionally absent so the strip renders its graceful fallback.
    $printerStatus = $printerStatus ?? null;
@endphp

<div class="mb-5 flex items-center gap-4 px-4 py-3 rounded-2xl bg-white border border-surface-200/70 shadow-xs">
    <div class="flex items-center gap-2.5">
        <span class="relative flex h-2.5 w-2.5">
            @if ($printerStatus && ($printerStatus['online'] ?? false))
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            @else
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-surface-300"></span>
            @endif
        </span>
        <div>
            <p class="text-xs font-bold text-surface-900 leading-tight">Noritsu 931BL</p>
            <p class="text-[11px] text-surface-500 leading-tight">
                {{ $printerStatus['online'] ?? false ? 'Ready' : 'Status unavailable — helper not connected' }}
            </p>
        </div>
    </div>

    @if ($printerStatus && !empty($printerStatus['trays']))
        <div class="flex items-center gap-2 flex-wrap border-l border-surface-200 pl-4">
            @foreach ($printerStatus['trays'] as $tray)
                <span class="px-2 py-1 rounded-lg bg-surface-50 border border-surface-200 text-[11px] font-medium text-surface-700">
                    {{ $tray['name'] }}<span class="text-surface-400"> · {{ $tray['media'] ?? '—' }}</span>
                </span>
            @endforeach
        </div>
    @else
        <div class="border-l border-surface-200 pl-4">
            <p class="text-[11px] text-surface-400">Per-tray media will appear here once the print helper is connected.</p>
        </div>
    @endif
</div>
