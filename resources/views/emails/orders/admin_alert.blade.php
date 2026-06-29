<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order - Admin Alert</title>
</head>
<body style="margin: 0; padding: 0; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <!-- Outer Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);">
        <tr>
            <td align="center" style="padding: 32px 16px;">

                <!-- Main Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 520px; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">

                    <!-- Header Gradient Bar (Orange/Amber for Admin) -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #d97706, #f59e0b); height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Alert Icon -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="width: 72px; height: 72px; background: linear-gradient(135deg, #d97706, #f59e0b); border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 36px; color: #ffffff; line-height: 72px;">🔔</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">New Order Received</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 32px 8px;">
                            <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500;">A new order has been placed in the system</p>
                        </td>
                    </tr>

                    <!-- Order Summary Cards -->
                    <tr>
                        <td style="padding: 16px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Order Number -->
                                    <td width="33%" style="padding-right: 4px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 10px; text-align: center;">
                                                    <p style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px;">Order</p>
                                                    <p style="margin: 0; font-size: 12px; font-weight: 800; color: #92400e; word-break: break-all;">{{ $order->order_number }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Total -->
                                    <td width="33%" style="padding: 0 4px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 10px; text-align: center;">
                                                    <p style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px;">Total</p>
                                                    <p style="margin: 0; font-size: 14px; font-weight: 800; color: #15803d;">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- Customer -->
                                    <td width="33%" style="padding-left: 4px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 10px; text-align: center;">
                                                    <p style="margin: 0 0 2px; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px;">Customer</p>
                                                    <p style="margin: 0; font-size: 12px; font-weight: 700; color: #334155; overflow: hidden; text-overflow: ellipsis;">{{ $order->shipping_address['name'] ?? $order->user->name ?? 'Guest' }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if($order->store)
                    <!-- Store Info Card -->
                    <tr>
                        <td style="padding: 8px 32px 4px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 18px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width: 36px; vertical-align: top; padding-right: 12px;">
                                                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #0284c7, #0ea5e9); border-radius: 8px; text-align: center; line-height: 36px;">
                                                        <span style="font-size: 16px;">🏪</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <p style="margin: 0 0 2px; font-size: 14px; font-weight: 700; color: #0f172a;">{{ $order->store->store_name }}</p>
                                                    <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 1.4;">{{ $order->store->full_address }}</p>
                                                    @if($order->store->phone)
                                                    <p style="margin: 4px 0 0; font-size: 12px; color: #334155;"><strong>Phone:</strong> {{ $order->store->phone }}</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td style="padding: 8px 32px 4px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; text-align: center;">
                                        <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: 500;">No store assigned</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- View Order Button -->
                    <tr>
                        <td align="center" style="padding: 20px 32px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #d97706, #f59e0b); border-radius: 14px; box-shadow: 0 4px 14px rgba(217,119,6,0.35);">
                                        <a href="{{ route('admin.orders.show', $order) }}" style="display: inline-block; padding: 14px 36px; font-size: 15px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">View Order Details →</a>
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
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">Order Details</h2>
                        </td>
                    </tr>

                    <!-- Order Table -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #d97706, #f59e0b); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: left; width: 55%;">Item</td>
                                    <td style="background: linear-gradient(135deg, #d97706, #f59e0b); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: center; width: 20%;">Qty</td>
                                    <td style="background: linear-gradient(135deg, #d97706, #f59e0b); padding: 10px 14px; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1.2px; text-align: right; width: 25%;">Price</td>
                                </tr>
                                @foreach($order->items as $item)
                                <tr>
                                    <td style="padding: 14px; font-size: 14px; color: #334155; font-weight: 500; border-bottom: 1px solid #f1f5f9; text-align: left;">{{ $item->product_name ?? 'Custom Print' }}</td>
                                    <td style="padding: 14px; font-size: 14px; color: #d97706; font-weight: 600; border-bottom: 1px solid #f1f5f9; text-align: center;">{{ $item->quantity }}</td>
                                    <td style="padding: 14px; font-size: 14px; color: #334155; font-weight: 500; border-bottom: 1px solid #f1f5f9; text-align: right;">{{ \App\Services\CurrencyService::formatWithCurrency($item->total_price, $order->currency) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" style="padding: 14px; font-size: 15px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; text-align: left;">Total</td>
                                    <td style="padding: 14px; font-size: 18px; font-weight: 900; color: #d97706; border-top: 2px solid #e2e8f0; text-align: right;">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 28px 32px 8px;">
                            <p style="margin: 0 0 2px; font-size: 14px; color: #64748b;">Admin Notification,</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 0; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                </table>

                <!-- Footer Text -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 520px;">
                    <tr>
                        <td align="center" style="padding: 20px 32px;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">This is an automated admin notification from {{ config('app.name') }}.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
