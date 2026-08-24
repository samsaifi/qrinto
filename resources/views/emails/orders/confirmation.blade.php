<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>

<body
    style="margin: 0; padding: 0; background: #fafcf9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <!-- Outer Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background: #fafcf9;">
        <tr>
            <td align="center" style="padding: 32px 16px;">

                <!-- Main Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 520px; background: #ffffff; border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 24px rgba(0,0,0,0.04);">

                    <!-- Header Brand Green Bar -->
                    <tr>
                        <td style="background: #287d3c; height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Success Icon -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center"
                                        style="width: 64px; height: 64px; background: #eaf3ea; border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 28px; color: #287d3c; line-height: 64px;">✓</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            @php
                                $rawCustName = $order->shipping_address['name'] ?? ($order->user->name ?? ($order->billing_address['name'] ?? 'Customer'));
                                $custName = ucwords(strtolower(trim($rawCustName)));
                            @endphp
                            <h1
                                style="margin: 0; font-size: 24px; font-weight: 800; color: #112419; letter-spacing: -0.5px;">
                                Order Confirmed, {{ $custName }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 32px 8px;">
                            <p style="margin: 0; font-size: 14px; color: #287d3c; font-weight: 600;">Usually ready the same day. An email goes out the moment it is ready.</p>
                        </td>
                    </tr>

                    <!-- Order Number Badge -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="background: #f2f7f2; border: 1px solid #d1e7d4; border-radius: 50px; padding: 8px 20px;">
                                        @php
                                            $code = $order->order_number;
                                            $prefix = strlen($code) > 5 ? substr($code, 0, -5) : '';
                                            $last5 = strlen($code) > 5 ? substr($code, -5) : $code;
                                        @endphp
                                        <span
                                            style="font-size: 14px; font-weight: 600; color: #112419; letter-spacing: 0.5px;">Order #{{ $prefix }}<strong style="font-weight: 900; color: #287d3c;">{{ $last5 }}</strong></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Quick Info Cards -->
                    <tr>
                        <td style="padding: 16px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Status -->
                                    <td width="50%" style="padding-right: 6px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td
                                                    style="background: #fafcf9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: center;">
                                                    <p
                                                        style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">
                                                        Status</p>
                                                    <p
                                                        style="margin: 0; font-size: 14px; font-weight: 700; color: #287d3c;">
                                                        {{ ucfirst($order->status) }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Payment -->
                                    <td width="50%" style="padding-left: 6px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td
                                                    style="background: #fafcf9; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: center;">
                                                    <p
                                                        style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">
                                                        Payment</p>
                                                    <p
                                                        style="margin: 0; font-size: 12px; font-weight: 700; color: {{ $order->payment_status === 'paid' ? '#287d3c' : '#334155' }};">
                                                        {{ $order->payment_status === 'paid' ? 'Paid online' : 'Pay at the counter when you pick up' }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Track Order Button -->
                    <tr>
                        <td align="center" style="padding: 16px 32px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="background: #287d3c; border-radius: 14px;">
                                        <a href="{{ route('flow.track.order', ['orderNumber' => $order->order_number]) }}"
                                            style="display: inline-block; padding: 14px 36px; font-size: 14px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Track
                                            Order Progress →</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <div style="border-top: 1px solid #e2e8f0;"></div>
                        </td>
                    </tr>

                    <!-- Order Details Section -->
                    <tr>
                        <td style="padding: 24px 32px 0;">
                            <h2
                                style="margin: 0 0 16px; font-size: 16px; font-weight: 800; color: #112419;">
                                Order Details</h2>
                        </td>
                    </tr>

                    <!-- Order Table -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <!-- Table Header -->
                                <tr>
                                    <td
                                        style="background: #287d3c; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: left; width: 55%;">
                                        Item</td>
                                    <td
                                        style="background: #287d3c; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: center; width: 20%;">
                                        Qty</td>
                                    <td
                                        style="background: #287d3c; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: right; width: 25%;">
                                        Price</td>
                                </tr>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td
                                            style="padding: 14px; font-size: 13px; color: #334155; font-weight: 600; border-bottom: 1px solid #f1f5f9; text-align: left;">
                                            {{ $item->product_name ?? 'Custom Print' }}</td>
                                        <td
                                            style="padding: 14px; font-size: 13px; color: #287d3c; font-weight: 700; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                            {{ $item->quantity }}</td>
                                        <td
                                            style="padding: 14px; font-size: 13px; color: #334155; font-weight: 600; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                            {{ \App\Services\CurrencyService::formatWithCurrency($item->total_price ?? ($item->unit_price * $item->quantity), $order->currency) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Total Row -->
                                <tr>
                                    <td colspan="2"
                                        style="padding: 14px; font-size: 14px; font-weight: 700; color: #112419; border-top: 2px solid #e2e8f0; text-align: left;">
                                        {{ $order->payment_status === 'paid' ? 'Total Paid' : 'Total Due at Pickup' }}</td>
                                    <td
                                        style="padding: 14px; font-size: 16px; font-weight: 900; color: #287d3c; border-top: 2px solid #e2e8f0; text-align: right;">
                                        {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($order->store)
                        <!-- Store & Pickup Location -->
                        <tr>
                            <td style="padding: 24px 32px 0;">
                                <h2
                                    style="margin: 0 0 12px; font-size: 16px; font-weight: 800; color: #112419;">
                                    Pickup Store Location</h2>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 32px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fafcf9;">
                                    <tr>
                                        <td style="padding: 16px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="width: 40px; vertical-align: top; padding-right: 12px;">
                                                        <div
                                                            style="width: 40px; height: 40px; background: #287d3c; border-radius: 10px; text-align: center; line-height: 40px;">
                                                            <span style="font-size: 18px; color: #ffffff;">📍</span>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <h3
                                                            style="margin: 0 0 4px; font-size: 15px; font-weight: 800; color: #112419;">
                                                            {{ $order->store->store_name }}</h3>
                                                        <p
                                                            style="margin: 0 0 6px; font-size: 12px; color: #475569; line-height: 1.4;">
                                                            {{ $order->store->address ?? ($order->store->city ?? 'Local Store') }}</p>
                                                        @if ($order->store->opening_time && $order->store->closing_time)
                                                            <p style="margin: 0 0 4px; font-size: 12px; color: #64748b;">
                                                                <strong>Store Hours:</strong> {{ $order->store->opening_time }} - {{ $order->store->closing_time }}
                                                            </p>
                                                        @endif
                                                        @if ($order->store->phone)
                                                            <p
                                                                style="margin: 0 0 2px; font-size: 12px; color: #334155;">
                                                                <strong style="color: #475569;">Phone:</strong>
                                                                <a href="tel:{{ $order->store->phone }}"
                                                                    style="color: #287d3c; font-weight: 700; text-decoration: none;">{{ $order->store->phone }}</a>
                                                            </p>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <!-- Thank You Footer -->
                    <tr>
                        <td style="padding: 24px 32px 8px;">
                            <p style="margin: 0 0 2px; font-size: 13px; color: #287d3c; font-weight: 600;">Thanks for choosing Qrinto,
                            </p>
                            <p style="margin: 0; font-size: 14px; font-weight: 800; color: #112419;">The Qrinto Team
                            </p>
                        </td>
                    </tr>

                    <!-- Bottom Padding -->
                    <tr>
                        <td style="padding: 16px 0; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                </table>

                <!-- Footer Text -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 520px;">
                    <tr>
                        <td align="center" style="padding: 20px 32px;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">&copy;
                                {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>

</html>
