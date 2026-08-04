@php
    $statusArray = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

    $mappedStatus = $order->status;
    if ($mappedStatus === 'printing') {
        $mappedStatus = 'processing';
    } elseif ($mappedStatus === 'delivered_store') {
        $mappedStatus = 'delivered';
    }

    $currentIndex = array_search($mappedStatus, $statusArray);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    $progressWidth = ($currentIndex / (count($statusArray) - 1)) * 100;

    $icons = [
        'pending' => 'clock',
        'confirmed' => 'check-circle',
        'processing' => 'printer',
        'shipped' => 'truck',
        'delivered' => 'package-check',
    ];
@endphp

<div class="relative flex justify-between items-start w-full pt-2">
    <div class="absolute left-5 right-5 top-[22px] h-[3px] bg-slate-100 rounded-full z-0"></div>
    <div class="absolute left-5 top-[22px] h-[3px] bg-brand-500 rounded-full z-0 transition-all duration-700 ease-out" style="width: calc({{ $progressWidth }}% - 10px)"></div>

    @foreach($statusArray as $index => $step)
        @php
            $isCompleted = $index < $currentIndex;
            $isCurrent = $index === $currentIndex;
        @endphp
        <div class="relative z-10 flex flex-col items-center" style="width: 20%">
            <div class="w-11 h-11 rounded-full flex items-center justify-center transition-all duration-300
                {{ $isCompleted ? 'bg-brand-500 text-white shadow-md shadow-brand-200' : '' }}
                {{ $isCurrent ? 'bg-brand-600 text-white shadow-lg shadow-brand-200 ring-4 ring-brand-100' : '' }}
                {{ !$isCompleted && !$isCurrent ? 'bg-white text-slate-400 border-2 border-slate-200' : '' }}">
                @if($isCompleted)
                    <i data-lucide="check" class="w-5 h-5"></i>
                @else
                    <i data-lucide="{{ $icons[$step] ?? 'circle' }}" class="w-4.5 h-4.5"></i>
                @endif
            </div>
            <span class="mt-2.5 text-[11px] font-bold {{ $isCompleted || $isCurrent ? 'text-brand-600' : 'text-slate-400' }} tracking-wide text-center">
                {{ ucfirst($step) }}
            </span>
        </div>
    @endforeach
</div>
