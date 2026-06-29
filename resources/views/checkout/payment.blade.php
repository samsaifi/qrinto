@extends('layouts.app')
@section('title', 'Payment - Order ' . $order->order_number)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-3xl border border-surface-100 shadow-premium p-8 lg:p-12">
        <div class="w-16 h-16 rounded-2xl bg-brand-100 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="credit-card" class="w-8 h-8 text-brand-600"></i>
        </div>

        <h1 class="font-display font-bold text-2xl text-surface-900 mb-2">Complete Payment</h1>
        <p class="text-surface-500 mb-1">Order: <span class="font-mono font-bold">{{ $order->order_number }}</span></p>
        <p class="text-3xl font-bold text-brand-600 mb-8">${{ number_format($order->total, 2) }}</p>

        @if($paymentData)
            @if($paymentData['gateway'] === 'razorpay')
            <button id="pay-btn" class="w-full px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-2xl shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5"></i> Pay with Razorpay
            </button>
            @push('scripts')
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                document.getElementById('pay-btn').addEventListener('click', function() {
                    const options = {
                        key: '{{ $paymentData["key"] }}',
                        amount: {{ $paymentData["amount"] }},
                        currency: '{{ $paymentData["currency"] }}',
                        name: '{{ $paymentData["name"] }}',
                        description: '{{ $paymentData["description"] }}',
                        order_id: '{{ $paymentData["order_id"] }}',
                        handler: function(response) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("checkout.verifyPayment") }}';
                            const fields = {
                                '_token': '{{ csrf_token() }}',
                                'razorpay_order_id': response.razorpay_order_id,
                                'razorpay_payment_id': response.razorpay_payment_id,
                                'razorpay_signature': response.razorpay_signature,
                                'order_id': '{{ $order->id }}',
                            };
                            for (const [key, value] of Object.entries(fields)) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = value;
                                form.appendChild(input);
                            }
                            document.body.appendChild(form);
                            form.submit();
                        },
                        prefill: {
                            name: '{{ auth()->user()?->name ?? ($order->shipping_address["full_name"] ?? "") }}',
                            email: '{{ auth()->user()?->email ?? $order->guest_email ?? "" }}',
                        },
                        theme: { color: '#db2777' },
                    };
                    const rzp = new Razorpay(options);
                    rzp.open();
                });
            </script>
            @endpush
            @endif
            
            @if($paymentData['gateway'] === 'paypal')
            <div id="paypal-button-container" class="mt-4 min-h-[150px]"></div>
            <div id="payment-status-message" class="hidden mt-4 p-4 rounded-xl text-sm"></div>

            @push('scripts')
            <script src="https://www.paypal.com/sdk/js?client-id={{ $paymentData['client_id'] }}&currency={{ $paymentData['currency'] }}&disable-funding=credit"></script>
            <script>
                const statusMsg = document.getElementById('payment-status-message');
                
                function showMessage(msg, isError = false) {
                    statusMsg.textContent = msg;
                    statusMsg.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-green-50', 'text-green-700');
                    statusMsg.classList.add(isError ? 'bg-red-50' : 'bg-green-50');
                    statusMsg.classList.add(isError ? 'text-red-700' : 'text-green-700');
                }

                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'gold',
                        shape: 'rect',
                        label: 'pay'
                    },
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: '{{ number_format($paymentData["amount"], 2, ".", "") }}'
                                },
                                description: 'Order #{{ $order->order_number }}'
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        showMessage('Verifying your payment... please wait.');
                        return fetch('{{ route("checkout.verifyPayment") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_id: '{{ $paymentData["order_id"] }}',
                                paypal_order_id: data.orderID
                            })
                        }).then(function(res) {
                            return res.json();
                        }).then(function(details) {
                            if (details.success) {
                                showMessage('Payment successful! Redirecting...');
                                window.location.href = details.redirect_url;
                            } else {
                                showMessage('Payment verification failed. Please try again.', true);
                            }
                        }).catch(function(err) {
                            showMessage('An error occurred during verification.', true);
                            console.error(err);
                        });
                    },
                    onError: function(err) {
                        showMessage('PayPal checkout failed. Please try again.', true);
                        console.error(err);
                    }
                }).render('#paypal-button-container');
            </script>
            @endpush
            @endif
        @else
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700 mb-6">
            Payment gateway is not configured. Your order has been placed and will be processed as cash on delivery.
        </div>
        <a href="{{ route('orders.confirmation', $order) }}" class="w-full inline-block px-8 py-4 bg-brand-600 text-white font-bold rounded-2xl hover:bg-brand-700 transition shadow-xl">
            Continue to Order Confirmation
        </a>
        @endif

        <div class="mt-8 flex items-center justify-center gap-4 grayscale opacity-50">
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="h-5">
            <div class="w-px h-4 bg-surface-200"></div>
            <i data-lucide="shield-check" class="w-5 h-5"></i>
            <span class="text-xs font-medium uppercase tracking-wider">Secure Payment</span>
        </div>

        <p class="text-xs text-surface-400 mt-6">🔒 All transactions are secure and encrypted</p>
    </div>
</div>
@endsection
