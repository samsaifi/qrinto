@php
    // Currency helper - reuse the app's CurrencyService if available.
$fmt = function ($amount) {
    if (class_exists(\App\Services\CurrencyService::class)) {
        return \App\Services\CurrencyService::formatWithCurrency($amount, config('app.currency', 'INR'));
        }
        return number_format((float) $amount, 2);
    };
    $num = fn($n) => number_format((int) $n);

    $obs = $observations;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Analytics - {{ $period['label'] }}</title>
</head>

<body
    style="margin:0;padding:0;background:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:680px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#0f172a;padding:24px 28px;color:#ffffff;">
                            <div
                                style="font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#94a3b8;font-weight:700;">
                                QRinto</div>
                            <div style="font-size:22px;font-weight:800;margin-top:4px;">Weekly Analytics Report</div>
                            <div style="font-size:13px;color:#cbd5e1;margin-top:6px;">Period: <strong
                                    style="color:#ffffff;">{{ $period['label'] }}</strong></div>
                        </td>
                    </tr>

                    {{-- Executive summary cards --}}
                    <tr>
                        <td style="padding:24px 28px 8px;">
                            <div
                                style="font-size:13px;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;font-weight:700;margin-bottom:12px;">
                                Executive Summary</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    @foreach ([['Total Orders', $num($summary['total_orders']), '#3b82f6'], ['Revenue', $fmt($summary['total_revenue']), '#22c55e'], ['Print Events', $num($summary['total_print_events']), '#a855f7']] as $c)
                                        <td style="padding:6px;">
                                            <div
                                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
                                                <div
                                                    style="font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;font-weight:700;">
                                                    {{ $c[0] }}</div>
                                                <div
                                                    style="font-size:20px;font-weight:800;color:{{ $c[2] }};margin-top:6px;">
                                                    {{ $c[1] }}</div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ([['Picked Up', $num($summary['total_picked_up']), '#10b981'], ['Pending', $num($summary['total_pending']), '#f59e0b'], ['Active Kiosks', $num($summary['active_kiosks']), '#0ea5e9']] as $c)
                                        <td style="padding:6px;">
                                            <div
                                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
                                                <div
                                                    style="font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;font-weight:700;">
                                                    {{ $c[0] }}</div>
                                                <div
                                                    style="font-size:20px;font-weight:800;color:{{ $c[2] }};margin-top:6px;">
                                                    {{ $c[1] }}</div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Order Analytics --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Order
                                Analytics</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                @foreach ([
        'Total Orders' => $num($orders['total_orders']),
        'New' => $num($orders['new_orders']),
        'Printing' => $num($orders['printing_orders']),
        'Ready for Pickup' => $num($orders['ready_orders']),
        'Picked Up' => $num($orders['picked_up_orders']),
        'Cancelled' => $num($orders['cancelled_orders']),
        'Pending Orders' => $num($orders['pending_orders']),
        'Paid Orders' => $num($orders['paid_orders']),
        'Gross Order Value' => $fmt($orders['total_value']),
    ] as $k => $v)
                                    <tr>
                                        <td
                                            style="padding:8px 12px;font-size:13px;color:#475569;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $k }}</td>
                                        <td
                                            style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;color:#0f172a;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $v }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Print Logs Analytics --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Print Logs
                                Analytics</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                @foreach ([
        'Total Print Events' => $num($printLogs['total_logs']),
        'Distinct Orders' => $num($printLogs['total_orders']),
        'Success' => $num($printLogs['success_count']),
        'Failed' => $num($printLogs['failed_count']),
        'Retried' => $num($printLogs['retried_count']),
        'Stage: New' => $num($printLogs['new_orders']),
        'Stage: Printing' => $num($printLogs['printing_orders']),
        'Stage: Ready' => $num($printLogs['ready_orders']),
        'Stage: Picked Up' => $num($printLogs['picked_up_orders']),
    ] as $k => $v)
                                    <tr>
                                        <td
                                            style="padding:8px 12px;font-size:13px;color:#475569;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $k }}</td>
                                        <td
                                            style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;color:#0f172a;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $v }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            @if ($printers->isNotEmpty())
                                <div
                                    style="font-size:12px;color:#64748b;margin:14px 0 6px;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">
                                    Top printers</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                    <tr style="background:#f1f5f9;">
                                        <th align="left"
                                            style="padding:8px 12px;font-size:11px;text-transform:uppercase;color:#64748b;">
                                            Printer</th>
                                        <th align="right"
                                            style="padding:8px 12px;font-size:11px;text-transform:uppercase;color:#64748b;">
                                            Events</th>
                                        <th align="right"
                                            style="padding:8px 12px;font-size:11px;text-transform:uppercase;color:#64748b;">
                                            Orders</th>
                                    </tr>
                                    @foreach ($printers->take(10) as $p)
                                        <tr>
                                            <td style="padding:8px 12px;font-size:13px;color:#0f172a;">
                                                {{ $p->printer_name }}</td>
                                            <td
                                                style="padding:8px 12px;font-size:13px;text-align:right;font-weight:700;">
                                                {{ $num($p->events) }}</td>
                                            <td style="padding:8px 12px;font-size:13px;text-align:right;color:#475569;">
                                                {{ $num($p->orders) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        </td>
                    </tr>

                    {{-- Kiosk Analytics --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Kiosk
                                Analytics</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                <tr>
                                    <td style="padding:8px 12px;font-size:13px;background:#ffffff;color:#475569;">Total
                                        Kiosks</td>
                                    <td
                                        style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;background:#ffffff;">
                                        {{ $num($kiosks['totals']['total']) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 12px;font-size:13px;background:#f8fafc;color:#475569;">Active
                                    </td>
                                    <td
                                        style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;background:#f8fafc;color:#10b981;">
                                        {{ $num($kiosks['totals']['active']) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 12px;font-size:13px;background:#ffffff;color:#475569;">
                                        Inactive</td>
                                    <td
                                        style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;background:#ffffff;color:#94a3b8;">
                                        {{ $num($kiosks['totals']['inactive']) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Store-wise --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Store-Wise
                                Breakdown</div>
                            <div style="overflow-x:auto;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;font-size:12px;">
                                    <tr style="background:#f1f5f9;">
                                        <th align="left"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Store</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Orders</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Revenue</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Prints</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            New</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Prtg</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Ready</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Picked</th>
                                    </tr>
                                    @foreach ($perStore as $s)
                                        <tr>
                                            <td style="padding:8px 10px;color:#0f172a;">
                                                {{ $s['store_name'] }}
                                                @if ($s['is_test'])
                                                    <span
                                                        style="font-size:9px;background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 4px;margin-left:4px;">TEST</span>
                                                @endif
                                            </td>
                                            <td align="right" style="padding:8px 10px;font-weight:700;">
                                                {{ $num($s['total_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;">{{ $fmt($s['revenue']) }}
                                            </td>
                                            <td align="right" style="padding:8px 10px;color:#a855f7;">
                                                {{ $num($s['print_events']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#3b82f6;">
                                                {{ $num($s['new_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#a855f7;">
                                                {{ $num($s['printing_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#f59e0b;">
                                                {{ $num($s['ready_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#10b981;">
                                                {{ $num($s['picked_up_orders']) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- Daily breakdown --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Daily
                                Breakdown</div>
                            <div style="overflow-x:auto;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;font-size:12px;">
                                    <tr style="background:#f1f5f9;">
                                        <th align="left"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Date</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Orders</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Revenue</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Prints</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            New</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Prtg</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Ready</th>
                                        <th align="right"
                                            style="padding:8px 10px;color:#64748b;text-transform:uppercase;font-size:10px;">
                                            Picked</th>
                                    </tr>
                                    @foreach ($daily as $r)
                                        <tr>
                                            <td style="padding:8px 10px;color:#0f172a;font-weight:600;">
                                                {{ \Carbon\Carbon::parse($r['date'])->format('D, M j') }}</td>
                                            <td align="right" style="padding:8px 10px;font-weight:700;">
                                                {{ $num($r['orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;">{{ $fmt($r['revenue']) }}
                                            </td>
                                            <td align="right" style="padding:8px 10px;color:#a855f7;">
                                                {{ $num($r['print_events']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#3b82f6;">
                                                {{ $num($r['new_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#a855f7;">
                                                {{ $num($r['printing_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#f59e0b;">
                                                {{ $num($r['ready_orders']) }}</td>
                                            <td align="right" style="padding:8px 10px;color:#10b981;">
                                                {{ $num($r['picked_up_orders']) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- Payments --}}
                    <tr>
                        <td style="padding:16px 28px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Payment
                                Analytics</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                @foreach ([
        'Total Revenue (paid)' => $fmt($orders['total_revenue']),
        'Paid Amount' => $fmt($orders['paid_amount']),
        'Pending Amount' => $fmt($orders['pending_amount']),
        'Online Payment Amt' => $fmt($orders['online_amount']),
        'Cash Payment Amt' => $fmt($orders['cash_amount']),
        'Paid Order Count' => $num($orders['paid_orders']),
        'Pending Order Count' => $num($orders['pending_orders']),
    ] as $k => $v)
                                    <tr>
                                        <td
                                            style="padding:8px 12px;font-size:13px;color:#475569;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $k }}</td>
                                        <td
                                            style="padding:8px 12px;font-size:13px;font-weight:700;text-align:right;color:#0f172a;background:{{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                            {{ $v }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Observations --}}
                    <tr>
                        <td style="padding:16px 28px 24px;">
                            <div style="font-size:14px;font-weight:800;color:#0f172a;margin-bottom:10px;">Important
                                Observations</div>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                <tr>
                                    <td style="padding:12px 14px;font-size:13px;color:#334155;line-height:1.6;">
                                        @if ($obs['top_store_orders'])
                                            🏆 <strong>Highest-volume store:</strong>
                                            {{ $obs['top_store_orders']['store_name'] }}
                                            ({{ $num($obs['top_store_orders']['total_orders']) }} orders)<br>
                                        @endif
                                        @if ($obs['top_store_revenue'] && ($obs['top_store_revenue']['revenue'] ?? 0) > 0)
                                            💰 <strong>Top revenue store:</strong>
                                            {{ $obs['top_store_revenue']['store_name'] }}
                                            ({{ $fmt($obs['top_store_revenue']['revenue']) }})<br>
                                        @endif
                                        @if ($obs['best_order_day'] && $obs['best_order_day']['orders'] > 0)
                                            📈 <strong>Busiest day:</strong>
                                            {{ \Carbon\Carbon::parse($obs['best_order_day']['date'])->format('D, M j') }}
                                            ({{ $num($obs['best_order_day']['orders']) }} orders)<br>
                                        @endif
                                        @if ($obs['best_revenue_day'] && $obs['best_revenue_day']['revenue'] > 0)
                                            💵 <strong>Top revenue day:</strong>
                                            {{ \Carbon\Carbon::parse($obs['best_revenue_day']['date'])->format('D, M j') }}
                                            ({{ $fmt($obs['best_revenue_day']['revenue']) }})<br>
                                        @endif
                                        @if ($obs['best_print_day'] && $obs['best_print_day']['print_events'] > 0)
                                            🖨 <strong>Most print activity:</strong>
                                            {{ \Carbon\Carbon::parse($obs['best_print_day']['date'])->format('D, M j') }}
                                            ({{ $num($obs['best_print_day']['print_events']) }} events)<br>
                                        @endif
                                        @if ($obs['top_printer'])
                                            ⚙️ <strong>Most-used printer:</strong>
                                            {{ $obs['top_printer']->printer_name }}
                                            ({{ $num($obs['top_printer']->events) }} events)<br>
                                        @endif
                                        @if ($obs['pending_orders'] > 0)
                                            ⚠️ <strong>Needs attention:</strong> {{ $num($obs['pending_orders']) }}
                                            pending orders
                                            worth {{ $fmt($obs['pending_amount']) }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="padding:20px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:11px;color:#64748b;text-align:center;line-height:1.6;">
                            Generated {{ now()->format('j M Y, H:i') }} · Period {{ $period['label'] }}<br>
                            This is an automated report from QRinto. A full Excel workbook is attached.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
