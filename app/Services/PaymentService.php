<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected string $gateway;

    public function __construct()
    {
        $this->gateway = config('services.payment_gateway', 'razorpay');
    }

    /**
     * Create a payment order with the configured gateway.
     */
    public function createPaymentOrder(Order $order): array
    {
        return match($this->gateway) {
            'razorpay' => $this->createRazorpayOrder($order),
            'stripe' => $this->createStripeIntent($order),
            'paypal' => $this->createPayPalOrder($order),
            default => throw new \Exception('Unsupported payment gateway: ' . $this->gateway),
        };
    }

    /**
     * Verify payment callback/webhook.
     */
    public function verifyPayment(array $data): bool
    {
        return match($this->gateway) {
            'razorpay' => $this->verifyRazorpayPayment($data),
            'stripe' => $this->verifyStripePayment($data),
            'paypal' => $this->verifyPayPalPayment($data),
            default => false,
        };
    }

    protected function createRazorpayOrder(Order $order): array
    {
        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $razorpayOrder = $api->order->create([
            'receipt' => $order->order_number,
            'amount' => $order->total * 100, // cents
            'currency' => CurrencyService::getCode(),
        ]);

        // Create payment record
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => 'razorpay',
            'gateway_order_id' => $razorpayOrder['id'],
            'amount' => $order->total,
            'currency' => CurrencyService::getCode(),
            'status' => 'pending',
        ]);

        return [
            'gateway' => 'razorpay',
            'order_id' => $razorpayOrder['id'],
            'amount' => $order->total * 100,
            'currency' => CurrencyService::getCode(),
            'key' => config('services.razorpay.key'),
            'name' => config('app.name'),
            'description' => 'Order ' . $order->order_number,
        ];
    }

    protected function verifyRazorpayPayment(array $data): bool
    {
        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Update payment record
            $payment = Payment::where('gateway_order_id', $data['razorpay_order_id'])->first();
            if ($payment) {
                $payment->update([
                    'gateway_payment_id' => $data['razorpay_payment_id'],
                    'gateway_signature' => $data['razorpay_signature'],
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                $payment->order->update([
                    'payment_id' => $data['razorpay_payment_id'],
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'confirmed',
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Razorpay verification failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function createStripeIntent(Order $order): array
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $intent = \Stripe\PaymentIntent::create([
            'amount' => $order->total * 100, // cents
            'currency' => strtolower(CurrencyService::getCode()),
            'metadata' => [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
            ],
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => 'stripe',
            'gateway_order_id' => $intent->id,
            'amount' => $order->total,
            'currency' => CurrencyService::getCode(),
            'status' => 'pending',
        ]);

        return [
            'gateway' => 'stripe',
            'client_secret' => $intent->client_secret,
            'amount' => $order->total * 100,
            'key' => config('services.stripe.key'),
        ];
    }

    protected function verifyStripePayment(array $data): bool
    {
        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $intent = \Stripe\PaymentIntent::retrieve($data['payment_intent']);

            if ($intent->status === 'succeeded') {
                $payment = Payment::where('gateway_order_id', $intent->id)->first();
                if ($payment) {
                    $payment->update([
                        'gateway_payment_id' => $intent->id,
                        'status' => 'completed',
                        'completed_at' => now(),
                        'gateway_response' => $intent->toArray(),
                    ]);

                    $payment->order->update([
                        'payment_id' => $intent->id,
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'confirmed',
                    ]);
                }
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Stripe verification failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function createPayPalOrder(Order $order): array
    {
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'gateway' => 'paypal',
            'gateway_order_id' => 'pending_' . $order->order_number,
            'amount' => $order->total,
            'currency' => CurrencyService::getCode(),
            'status' => 'pending',
        ]);

        return [
            'gateway' => 'paypal',
            'client_id' => config('services.paypal.client_id'),
            'amount' => $order->total,
            'currency' => CurrencyService::getCode(),
            'order_id' => $order->id,
        ];
    }

    protected function verifyPayPalPayment(array $data): bool
    {
        try {
            $clientId = config('services.paypal.client_id');
            $secret = config('services.paypal.secret');
            $mode = config('services.paypal.mode', 'sandbox');
            
            $baseUrl = $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
            
            // Get Access Token
            $authResponse = \Illuminate\Support\Facades\Http::asForm()->withBasicAuth($clientId, $secret)
                ->post($baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);
                
            if (!$authResponse->successful()) {
                Log::error('PayPal Auth Failed: ' . $authResponse->body());
                return false;
            }
            
            $token = $authResponse->json('access_token');
            
            // Capture Order
            $captureResponse = \Illuminate\Support\Facades\Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($baseUrl . '/v2/checkout/orders/' . $data['paypal_order_id'] . '/capture');
                
            if ($captureResponse->successful()) {
                $responseArr = $captureResponse->json();
                if (isset($responseArr['status']) && $responseArr['status'] === 'COMPLETED') {
                    $payment = Payment::where('order_id', $data['order_id'])
                        ->where('gateway', 'paypal')
                        ->first();
                        
                    if ($payment) {
                        $payment->update([
                            'gateway_order_id' => $data['paypal_order_id'],
                            'gateway_payment_id' => $responseArr['purchase_units'][0]['payments']['captures'][0]['id'] ?? $data['paypal_order_id'],
                            'status' => 'completed',
                            'completed_at' => now(),
                            'gateway_response' => $responseArr,
                        ]);

                        $payment->order->update([
                            'payment_id' => $payment->gateway_payment_id,
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'status' => 'confirmed',
                        ]);
                    }
                    return true;
                }
            }
            Log::error('PayPal Capture failed: ' . $captureResponse->body());
            return false;
        } catch (\Exception $e) {
            Log::error('PayPal verification exception: ' . $e->getMessage());
            return false;
        }
    }
}
