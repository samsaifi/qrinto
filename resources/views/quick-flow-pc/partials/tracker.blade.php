@php
    $stages = [
        ['key' => 'received', 'label' => 'Order received', 'icon' => 'clock'],
        ['key' => 'printing', 'label' => 'Printing', 'icon' => 'printer'],
        ['key' => 'ready', 'label' => 'Ready for pickup', 'icon' => 'package-check'],
        ['key' => 'completed', 'label' => 'Picked up', 'icon' => 'check-circle-2'],
    ];

    // Derive the active stage from the shared order state machine so the
    // tracker, the store queue, and emails always agree.
    $activeStageIndex = match ($order->queue_stage) {
        \App\Models\Order::STAGE_DONE     => 3,
        \App\Models\Order::STAGE_READY    => 2,
        \App\Models\Order::STAGE_PRINTING => 1,
        default                            => 0,
    };
@endphp

<div class="py-2 px-1">
    <div class="space-y-6 relative before:absolute before:left-3.5 before:top-4 before:bottom-4 before:w-0.5 before:bg-slate-200">
        @foreach ($stages as $index => $stage)
            @php
                $isDone = $index <= $activeStageIndex;
                $isCurrent = $index === $activeStageIndex;
            @endphp
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 border-2 transition-all
                    {{ $isDone ? 'bg-[#287d3c] border-[#287d3c] text-white shadow-sm' : 'bg-white border-slate-200 text-slate-400' }}">
                    @if ($isDone)
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <span class="text-[10px] font-bold">{{ $index + 1 }}</span>
                    @endif
                </div>

                <div class="pt-0.5">
                    <h4 class="text-xs font-extrabold {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">
                        {{ $stage['label'] }}
                    </h4>
                    <p class="text-[11px] {{ $isCurrent ? 'text-[#287d3c] font-bold' : 'text-slate-400 font-normal' }} mt-0.5">
                        @if ($isCurrent)
                            Current Status
                        @elseif ($index < $activeStageIndex)
                            Completed
                        @else
                            Upcoming
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
