<div class="bg-white border-2 border-slate-50 rounded-[2rem] p-6 shadow-premium text-left mt-6">
    <h3 class="text-[17px] font-extrabold text-slate-800 mb-6 font-display">Order Progress</h3>
    
    @php
        $statusArray = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        
        // Map alternative statuses to base timeline steps
        $mappedStatus = $order->status;
        if ($mappedStatus === 'printing') {
            $mappedStatus = 'processing';
        } elseif ($mappedStatus === 'delivered_store') {
            $mappedStatus = 'delivered';
        }
        
        $currentIndex = array_search($mappedStatus, $statusArray);
        if ($currentIndex === false) {
            $currentIndex = 0; // Default or cancelled
        }
    @endphp

    <div class="relative flex justify-between items-center w-full">
        <!-- Connecting Line Base -->
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-200 rounded-full z-0"></div>
        
        <!-- Connecting Line Progress -->
        @php
            $progressWidth = ($currentIndex / (count($statusArray) - 1)) * 100;
        @endphp
        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-500 rounded-full z-0 transition-all duration-500 ease-in-out" style="width: {{ $progressWidth }}%"></div>
        
        <!-- Nodes -->
        @foreach($statusArray as $index => $step)
            @php
                $isCompleted = $index < $currentIndex;
                $isCurrent = $index === $currentIndex;
                $isFuture = $index > $currentIndex;
            @endphp
            <div class="relative z-10 flex flex-col items-center group">
                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $isCompleted || $isCurrent ? 'bg-brand-500 text-white shadow-lg shadow-brand-100 scale-110' : 'bg-slate-100 border-4 border-white text-slate-400' }}">
                    @if($isCompleted)
                        <i data-lucide="check" class="w-5 h-5"></i>
                    @else
                        <span class="text-sm font-bold">{{ $index + 1 }}</span>
                    @endif
                </div>
                <div class="absolute top-12 whitespace-nowrap">
                    <span class="text-[11px] font-bold {{ $isCompleted || $isCurrent ? 'text-brand-600' : 'text-slate-400' }} tracking-wide">{{ ucfirst($step) }}</span>
                </div>
            </div>
        @endforeach
    </div>
    <div class="h-10"></div> <!-- Spacer for absolute positioning labels -->
</div>
