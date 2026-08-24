@extends('layouts.quick-flow')

@section('title', 'Order Confirmed — Qrinto Print Studio')
@section('header_title', 'Order Confirmed')

@push('styles')
    <style>
        .success-ring-mobile {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0ea5e9, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.4);
            animation: bounceIn 0.65s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }

            60% {
                transform: scale(1.15) rotate(5deg);
            }

            100% {
                transform: scale(1) rotate(0);
                opacity: 1;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $itemPreviews = [];
        $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];

        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            $customization = $orderItem->customization_data ?? [];
            $uploadedImages = $orderItem->uploaded_images ?? [];
            $itemPages = [];

            $noOfPages = (int) ($product->no_of_pages ?? 1);
            if ($noOfPages <= 1) {
                $activeSlots = ['frame_image'];
            } elseif ($noOfPages == 2) {
                $activeSlots = ['frame_image', 'sample_image'];
            } else {
                $activeSlots = array_slice($slots, 0, min($noOfPages, 4));
            }

            foreach ($activeSlots as $idx => $slotKey) {
                $path = $uploadedImages[$slotKey] ?? null;
                $url = null;
                if ($path) {
                    $url = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                } elseif ($product) {
                    $url = $product->{$slotKey . '_url'} ?? null;
                }
                if ($url) {
                    $itemPages[] = $url;
                }
            }

            if (empty($itemPages)) {
                if ($uploadedImages && is_array($uploadedImages)) {
                    foreach ($uploadedImages as $p) {
                        if ($p) {
                            $itemPages[] = str_starts_with($p, 'http') ? $p : asset('storage/' . $p);
                        }
                    }
                }
                if (empty($itemPages) && $product && $product->frame_image_url) {
                    $itemPages[] = $product->frame_image_url;
                }
            }

            $itemPreviews[] = [
                'id' => $orderItem->id,
                'name' => $orderItem->product_name ?? ($product->name ?? 'Custom Print'),
                'type_name' => $customization['type_name'] ?? ($product->productType->name ?? 'Print Item'),
                'size_name' => $customization['size_name'] ?? ($product->size_label ?? ''),
                'width' => $customization['size_width'] ?? '',
                'height' => $customization['size_height'] ?? '',
                'unit' => $customization['size_unit'] ?? '',
                'qty' => $orderItem->quantity,
                'price' => $orderItem->total,
                'images' => $itemPages,
                'pdf_url' => !empty($orderItem->pdf_path) ? asset('storage/' . $orderItem->pdf_path) : null,
                'preview_url' => !empty($orderItem->preview_image_path)
                    ? asset('storage/' . $orderItem->preview_image_path)
                    : (!empty($itemPages[0])
                        ? $itemPages[0]
                        : null),
            ];
        }
    @endphp

    <div class="space-y-4 pb-28 pt-1 px-6">

        {{-- Success Hero Banner --}}
        <div
            class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium text-center space-y-3 relative overflow-hidden">
            <div
                class="w-16 h-16  bg-gray-500 text-white rounded-full flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/25">
                <i data-lucide="check" class="w-8 h-8 stroke-[3]"></i>
            </div>
            <div>
                <span
                    class="text-[10px] font-black  text-gray-600 uppercase tracking-[0.2em]  bg-gray-50 px-3 py-1 rounded-full border  border-gray-200/60 inline-block mb-1.5">
                    Order Confirmed
                </span>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Order Placed Successfully!</h1>
                <p class="text-slate-500 font-medium text-xs mt-1">
                    Your custom print is queued for high-resolution processing.
                </p>
            </div>

            <div class="flex items-center justify-center gap-2 pt-1 flex-wrap">
                <span
                    class="bg-slate-900 text-white text-xs font-black px-3.5 py-1.5 rounded-xl flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="hash" class="w-3.5 h-3.5 text-mobile-400"></i>
                    <span>{{ $order->order_number }}</span>
                </span>
                @if ($order->store)
                    <span
                        class="bg-slate-100 text-slate-700 text-xs font-bold px-3.5 py-1.5 rounded-xl border border-slate-200/80 flex items-center gap-1.5">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-mobile-600"></i>
                        <span class="truncate max-w-[140px]">{{ $order->store->name ?? $order->store->store_name }}</span>
                    </span>
                @endif
            </div>

            {{-- Pickup QR (always visible; show at the counter) --}}
            <div class="pt-3 flex flex-col items-center gap-2">
                <div id="order-qr" class="w-40 h-40 bg-white rounded-2xl border border-slate-100 flex items-center justify-center p-2"></div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Show this at the counter</p>
            </div>
        </div>

        {{-- Ordered Items Design Previews Card --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium space-y-4" x-data="{ activeIndex: 0 }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-black uppercase tracking-widest text-slate-400">Ordered Products</span>
                <span
                    class="text-xs font-extrabold text-mobile-600 bg-mobile-50 px-2.5 py-0.5 rounded-full border border-mobile-200/60">
                    {{ count($itemPreviews) }} Item{{ count($itemPreviews) > 1 ? 's' : '' }}
                </span>
            </div>

            @if (count($itemPreviews) > 1)
                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    @foreach ($itemPreviews as $idx => $prevItem)
                        <button type="button" @click="activeIndex = {{ $idx }}"
                            :class="activeIndex === {{ $idx }} ?
                                'bg-mobile-500 text-white border-mobile-500 font-extrabold' :
                                'bg-slate-50 text-slate-600 border-slate-200 font-bold'"
                            class="px-3 py-1.5 rounded-xl border text-xs transition-all shrink-0 flex items-center gap-1.5 active:scale-95">
                            <span>{{ $idx + 1 }}. {{ $prevItem['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            @foreach ($itemPreviews as $idx => $prevItem)
                <div x-show="activeIndex === {{ $idx }}" x-transition class="space-y-4">
                    <div
                        class="bg-slate-50/80 border border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center text-center">
                        {{-- Fanned Card Deck Preview --}}
                        <div
                            class="fanned-card-stage relative flex items-center justify-center w-36 h-36 shrink-0 select-none py-1 px-1 my-2">
                            @if (count($prevItem['images']) > 0)
                                @foreach ($prevItem['images'] as $imgIdx => $imgUrl)
                                    @php
                                        $count = count($prevItem['images']);
                                        if ($count == 1) {
                                            $rot = 0;
                                            $tx = 0;
                                            $ty = 0;
                                        } elseif ($count == 2) {
                                            $rot = $imgIdx == 0 ? -12 : 12;
                                            $tx = $imgIdx == 0 ? -12 : 12;
                                            $ty = 2;
                                        } elseif ($count == 3) {
                                            $rot = ($imgIdx - 1) * 15;
                                            $tx = ($imgIdx - 1) * 15;
                                            $ty = abs($imgIdx - 1) * 2;
                                        } elseif ($count == 4) {
                                            $rots = [-18, -6, 6, 18];
                                            $txs = [-18, -6, 6, 18];
                                            $tys = [4, 1, 1, 4];
                                            $rot = $rots[$imgIdx];
                                            $tx = $txs[$imgIdx];
                                            $ty = $tys[$imgIdx];
                                        } else {
                                            $step = 40 / max(1, $count - 1);
                                            $rot = -20 + $imgIdx * $step;
                                            $tx = -22 + $imgIdx * (44 / max(1, $count - 1));
                                            $ty = abs($imgIdx - ($count - 1) / 2) * 2;
                                        }
                                        $zIndex = ($imgIdx + 1) * 10;
                                    @endphp
                                    <div class="fanned-card absolute top-1/2 left-1/2 rounded-xl overflow-hidden bg-white border border-white shadow-sm transition-all duration-300"
                                        style="width: {{ $count > 1 ? '72px' : '96px' }}; height: {{ $count > 1 ? '98px' : '118px' }}; margin-left: -{{ $count > 1 ? '36px' : '48px' }}; margin-top: -{{ $count > 1 ? '49px' : '59px' }}; transform: translate({{ $tx }}px, {{ $ty }}px) rotate({{ $rot }}deg); transform-origin: 50% 120%; z-index: {{ $zIndex }}; box-shadow: 0 4px 14px -2px rgba(0,0,0,0.18);">
                                        <img src="{{ $imgUrl }}" alt="Page {{ $imgIdx + 1 }}"
                                            class="w-full h-full object-cover">
                                        @if ($count > 1)
                                            <div
                                                class="absolute bottom-0.5 right-0.5 bg-slate-900/85 text-white text-[7px] font-black px-0.5 rounded">
                                                P{{ $imgIdx + 1 }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                @if ($count > 1)
                                    <div
                                        class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md backdrop-blur-xs whitespace-nowrap z-40 flex items-center gap-1 border border-slate-700/80">
                                        <i data-lucide="layers" class="w-2.5 h-2.5 text-mobile-400"></i>
                                        {{ $count }} Pages
                                    </div>
                                @endif
                            @else
                                <div
                                    class="w-24 h-32 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center text-slate-300">
                                    <i data-lucide="image" class="w-7 h-7 mb-1"></i>
                                    <span class="text-[8px] font-bold text-slate-400">Custom Design</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-2">
                            @if (!empty($prevItem['type_name']))
                                <span
                                    class="text-[9px] font-black text-mobile-600 uppercase tracking-widest block">{{ $prevItem['type_name'] }}</span>
                            @endif
                            <h3 class="font-extrabold text-sm text-slate-900 leading-tight mt-0.5">{{ $prevItem['name'] }}
                            </h3>
                            <p class="text-xs font-bold text-slate-400 mt-1">
                                Qty: {{ $prevItem['qty'] }}
                                @if (!empty($prevItem['size_name']))
                                    · {{ $prevItem['size_name'] }}
                                    @if (!empty($prevItem['width']) && !empty($prevItem['height']))
                                        ({{ $prevItem['width'] }}×{{ $prevItem['height'] }}{{ $prevItem['unit'] }})
                                    @endif
                                @endif
                            </p>
                        </div>


                    </div>
                </div>
            @endforeach
        </div>

        {{-- Fulfillment Progress Status Timeline --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium space-y-3">
            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Order Fulfillment</span>

            <div class="space-y-2.5 pt-1">
                <div class=" bg-gray-50 border  border-gray-200/80 p-3 rounded-xl flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full  bg-gray-500 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-black text-slate-900">Order Placed & Confirmed</p>
                        <p class="text-[10px] font-bold  text-gray-700">Received at store</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200/80 p-3 rounded-xl flex items-center gap-3">
                    <div
                        class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 animate-pulse">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-black text-slate-900">Pre-Press & RIP Processing</p>
                        <p class="text-[10px] font-bold text-amber-700">Preparing high-res print files</p>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-3 opacity-60">
                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-black text-slate-900">Printing & Quality Check</p>
                        <p class="text-[10px] font-bold text-slate-400">Queued for printing</p>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-3 opacity-60">
                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-black text-slate-900">Ready for Store Pickup</p>
                        <p class="text-[10px] font-bold text-slate-400">Notification will be sent</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Breakdown & Pricing Card --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium space-y-3">
            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Order Summary</span>

            <div class="space-y-2 text-xs font-bold pt-1">
                <div class="flex justify-between text-slate-500">
                    <span>Order Date:</span>
                    <span class="text-slate-900">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Payment Method:</span>
                    <span class="text-slate-900 uppercase">{{ $order->payment_method ?? 'Cash' }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Payment Status:</span>
                    <span
                        class="{{ $order->payment_status === 'paid' ? ' text-gray-600' : 'text-amber-600' }} uppercase font-extrabold">
                        {{ $order->payment_status === 'paid' ? 'Paid' : 'Pay at Counter' }}
                    </span>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                <span class="text-sm font-black text-slate-900">Total Amount</span>
                <span class="text-xl font-black text-mobile-600">
                    {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                </span>
            </div>
        </div>

        {{-- Action Links --}}
        <div class="space-y-2 pt-2">


            <a href="{{ route('flow.index') }}"
                class="w-full bg-white border-2 border-slate-100 hover:bg-slate-50 text-slate-700 font-extrabold py-3 rounded-2xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-sm no-underline">
                <i data-lucide="shopping-bag" class="w-4 h-4 text-mobile-600"></i>
                <span>Continue Shopping</span>
            </a>
        </div>

        {{-- Floating Bottom Action Bar --}}
        <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 glass border-t border-slate-100 safe-bottom z-50">
            <a href="{{ route('flow.track.order', $order->order_number) }}"
                class="w-full bg-mobile-500 hover:bg-mobile-600 text-white font-extrabold py-3.5 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base no-underline">
                <i data-lucide="map-pin" class="w-5 h-5"></i>
                <span>Track Order Status</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('qrinto_')) {
                    localStorage.removeItem(key);
                }
            });

            // Render an always-visible pickup QR of the order number.
            var holder = document.getElementById('order-qr');
            if (holder && window.QRCode) {
                new QRCode(holder, {
                    text: @json($order->order_number),
                    width: 144, height: 144,
                    colorDark: '#0f172a', colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H,
                });
            }
        });
    </script>
@endpush
