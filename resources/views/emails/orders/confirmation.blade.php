<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>

<body
    style="margin: 0; padding: 0; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <!-- Outer Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);">
        <tr>
            <td align="center" style="padding: 32px 16px;">

                <!-- Main Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 520px; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">

                    <!-- Header Gradient Bar -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #0284c7, #0ea5e9); height: 6px; font-size: 0; line-height: 0;">
                            &nbsp;</td>
                    </tr>

                    <!-- Success Icon -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center"
                                        style="width: 72px; height: 72px; background: linear-gradient(135deg, #0ea5e9, #16a34a); border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 36px; color: #ffffff; line-height: 72px;">✓</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            <h1
                                style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                                Order Confirmed!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 32px 8px;">
                            <p style="margin: 0; font-size: 14px; color: #0ea5e9; font-weight: 500;">Thank you for your
                                order. We're getting it ready!</p>
                        </td>
                    </tr>

                    <!-- Order Number Badge -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td
                                        style="background: #ffffff; border: 2px solid #f1f5f9; border-radius: 50px; padding: 8px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                        <span
                                            style="font-size: 13px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px;">#{{ $order->order_number }}</span>
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
                                                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: center;">
                                                    <p
                                                        style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                                                        Status</p>
                                                    <p
                                                        style="margin: 0; font-size: 14px; font-weight: 700; color: #15803d;">
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
                                                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: center;">
                                                    <p
                                                        style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                                                        Payment</p>
                                                    <p
                                                        style="margin: 0; font-size: 14px; font-weight: 700; color: {{ $order->payment_status === 'paid' ? '#15803d' : '#b45309' }};">
                                                        {{ ucfirst($order->payment_status) }}</p>
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
                                        style="background: linear-gradient(135deg, #0284c7, #0ea5e9); border-radius: 14px; box-shadow: 0 4px 14px rgba(2,132,199,0.35);">
                                        <a href="{{ route('flow.track.order', ['orderNumber' => $order->order_number]) }}"
                                            style="display: inline-block; padding: 14px 36px; font-size: 15px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Track
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
                                style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">
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
                                        style="background: linear-gradient(135deg, #0284c7, #0ea5e9); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: left; width: 55%;">
                                        Item</td>
                                    <td
                                        style="background: linear-gradient(135deg, #0284c7, #0ea5e9); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: center; width: 20%;">
                                        Qty</td>
                                    <td
                                        style="background: linear-gradient(135deg, #0284c7, #0ea5e9); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: right; width: 25%;">
                                        Price</td>
                                </tr>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td
                                            style="padding: 14px; font-size: 14px; color: #334155; font-weight: 500; border-bottom: 1px solid #f1f5f9; text-align: left;">
                                            {{ $item->product_name ?? 'Custom Print' }}</td>
                                        <td
                                            style="padding: 14px; font-size: 14px; color: #0284c7; font-weight: 600; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                            {{ $item->quantity }}</td>
                                        <td
                                            style="padding: 14px; font-size: 14px; color: #334155; font-weight: 500; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                            {{ \App\Services\CurrencyService::formatWithCurrency($item->total_price, $order->currency) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- Total Row -->
                                <tr>
                                    <td colspan="2"
                                        style="padding: 14px; font-size: 15px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; text-align: left;">
                                        Total</td>
                                    <td
                                        style="padding: 14px; font-size: 18px; font-weight: 900; color: #0284c7; border-top: 2px solid #e2e8f0; text-align: right;">
                                        {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($order->store)
                        <!-- Store & Pickup Location -->
                        <tr>
                            <td style="padding: 28px 32px 0;">
                                <h2
                                    style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">
                                    Store & Pickup Location</h2>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 32px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="width: 44px; vertical-align: top; padding-right: 14px;">
                                                        <div
                                                            style="width: 44px; height: 44px; background: linear-gradient(135deg, #0284c7, #0ea5e9); border-radius: 10px; text-align: center; line-height: 44px; box-shadow: 0 2px 8px rgba(2,132,199,0.25);">
                                                            <span style="font-size: 20px; color: #ffffff;">📍</span>
                                                        </div>
                                                    </td>
                                                    <td style="vertical-align: top;">
                                                        <h3
                                                            style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #0f172a;">
                                                            {{ $order->store->store_name }}</h3>
                                                        <p
                                                            style="margin: 0 0 8px; font-size: 13px; color: #0ea5e9; line-height: 1.4;">
                                                            {{ $order->store->full_address }}</p>
                                                        @if ($order->store->phone)
                                                            <p
                                                                style="margin: 0 0 4px; font-size: 13px; color: #334155;">
                                                                <strong style="color: #475569;">Phone:</strong>
                                                                <a href="tel:{{ $order->store->phone }}"
                                                                    style="color: #0284c7; font-weight: 600; text-decoration: none;">{{ $order->store->phone }}</a>
                                                            </p>
                                                        @endif
                                                        @if ($order->store->email)
                                                            <p style="margin: 0; font-size: 13px; color: #334155;">
                                                                <strong style="color: #475569;">Email:</strong>
                                                                <a href="mailto:{{ $order->store->email }}"
                                                                    style="color: #0284c7; font-weight: 600; text-decoration: none;">{{ $order->store->email }}</a>
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
                        <td style="padding: 28px 32px 8px;">
                            <p style="margin: 0 0 2px; font-size: 14px; color: #0ea5e9;">Thanks for choosing Qrinto,
                            </p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">The Qrinto Team
                            </p>
                        </td>
                    </tr>

                    <!-- Bottom Padding -->
                    <tr>
                        <td style="padding: 20px 0; font-size: 0; line-height: 0;">&nbsp;</td>
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
