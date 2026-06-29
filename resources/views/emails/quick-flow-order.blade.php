<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qrinto Order</title>
</head>
<body style="margin: 0; padding: 0; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <!-- Outer Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);">
        <tr>
            <td align="center" style="padding: 32px 16px;">

                <!-- Main Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 520px; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">

                    <!-- Header Gradient Bar -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0284c7, #0ea5e9); height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Icon -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="width: 72px; height: 72px; background: linear-gradient(135deg, {{ $isAdmin ? '#d97706, #f59e0b' : '#22c55e, #16a34a' }}); border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 36px; color: #ffffff; line-height: 72px;">{{ $isAdmin ? '🔔' : '✓' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            @if($isAdmin)
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">New Order Received!</h1>
                            @else
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">Thank you for your order!</h1>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 32px 8px;">
                            @if($isAdmin)
                            <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500;">A new Quick Flow order has been placed</p>
                            @else
                            <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500;">Hello {{ $order->shipping_address['name'] ?? 'Customer' }}, we're getting your order ready!</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Order Number Badge -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 16px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #ffffff; border: 2px solid #f1f5f9; border-radius: 50px; padding: 8px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                        <span style="font-size: 13px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px;">#{{ $order->order_number }}</span>
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

                    <!-- Order Information Section -->
                    <tr>
                        <td style="padding: 24px 32px 0;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">Order Information</h2>
                        </td>
                    </tr>

                    <!-- Order Info Cards -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <!-- Product -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; width: 40%; background: #f8fafc;">Product</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $order->items->first()->product_name ?? 'N/A' }}</td>
                                </tr>
                                <!-- Type -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Type</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $order->flow_data['type_name'] ?? 'Custom' }}</td>
                                </tr>
                                <!-- Size -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Size</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $order->flow_data['size_name'] ?? 'Standard' }}</td>
                                </tr>
                                <!-- Dimensions -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Dimensions</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $order->flow_data['size_width'] ?? '0' }} x {{ $order->flow_data['size_height'] ?? '0' }}{{ $order->flow_data['size_unit'] ?? '"' }}</td>
                                </tr>
                                <!-- Quantity -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Quantity</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0284c7; border-bottom: 1px solid #f1f5f9;">{{ $order->total_quantity ?? $order->items->sum('quantity') }} Units</td>
                                </tr>
                                <!-- Payment -->
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Payment</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ strtoupper($order->payment_gateway) }} ({{ strtoupper($order->payment_status) }})</td>
                                </tr>
                                <!-- Total -->
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 15px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; background: #f8fafc;">Total</td>
                                    <td style="padding: 14px 16px; font-size: 20px; font-weight: 900; color: #0284c7; border-top: 2px solid #e2e8f0;">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Pickup Details Section -->
                    <tr>
                        <td style="padding: 28px 32px 0;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">Pickup Details</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; width: 40%; background: #f8fafc;">Name</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $order->shipping_address['name'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Email</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                                        <a href="mailto:{{ $order->guest_email ?? 'N/A' }}" style="color: #0284c7; text-decoration: none; font-weight: 600;">{{ $order->guest_email ?? 'N/A' }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #64748b; background: #f8fafc;">Phone</td>
                                    <td style="padding: 12px 16px; font-size: 14px; font-weight: 700; color: #0f172a;">
                                        <a href="tel:{{ $order->guest_phone ?? '' }}" style="color: #0284c7; text-decoration: none; font-weight: 600;">{{ $order->guest_phone ?? 'N/A' }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if($order->store)
                    <!-- Store & Pickup Location -->
                    <tr>
                        <td style="padding: 28px 32px 0;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">Store & Pickup Location</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width: 44px; vertical-align: top; padding-right: 14px;">
                                                    <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #0284c7, #0ea5e9); border-radius: 10px; text-align: center; line-height: 44px; box-shadow: 0 2px 8px rgba(2,132,199,0.25);">
                                                        <span style="font-size: 20px; color: #ffffff;">📍</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: top;">
                                                    <h3 style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #0f172a;">{{ $order->store->store_name }}</h3>
                                                    <p style="margin: 0 0 8px; font-size: 13px; color: #64748b; line-height: 1.4;">{{ $order->store->full_address }}</p>
                                                    @if($order->store->phone)
                                                    <p style="margin: 0 0 4px; font-size: 13px; color: #334155;">
                                                        <strong style="color: #475569;">Phone:</strong>
                                                        <a href="tel:{{ $order->store->phone }}" style="color: #0284c7; font-weight: 600; text-decoration: none;">{{ $order->store->phone }}</a>
                                                    </p>
                                                    @endif
                                                    @if($order->store->email)
                                                    <p style="margin: 0; font-size: 13px; color: #334155;">
                                                        <strong style="color: #475569;">Email:</strong>
                                                        <a href="mailto:{{ $order->store->email }}" style="color: #0284c7; font-weight: 600; text-decoration: none;">{{ $order->store->email }}</a>
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

                    <!-- Admin Note or Customer Message -->
                    <tr>
                        <td style="padding: 24px 32px 4px;">
                            @if($isAdmin)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; font-weight: 600;">📎 <strong>Note:</strong> The print-ready design PDF is attached to this email.</p>
                                    </td>
                                </tr>
                            </table>
                            @else
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #15803d; font-weight: 500;">📩 You will receive another update as soon as your order is ready for pickup at our branch.</p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <!-- Thank You Footer -->
                    <tr>
                        <td style="padding: 24px 32px 8px;">
                            <p style="margin: 0 0 2px; font-size: 14px; color: #64748b;">Best regards,</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">The Qrinto Team</p>
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
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">&copy; {{ date('Y') }} Qrinto Custom Printing Studio. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
