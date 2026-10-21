<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Store Registration</title>
</head>

<body
    style="margin: 0; padding: 0; background: #f0f9ff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    @php
        $rows = array_filter([
            'Store Name' => $store->store_name,
            'Store Code' => $store->store_code,
            'Owner'      => $store->owner_name,
            'Email'      => $store->email,
            'Phone'      => $store->phone,
            'Address'    => implode(', ', array_filter([
                $store->address, $store->city, $store->state, $store->zip_code, $store->country,
            ])),
            'Location'   => ($store->lat && $store->lon) ? $store->lat . ', ' . $store->lon : null,
            'Login User' => $user->email . ' (store admin)',
            'Status'     => $store->is_active ? 'Active' : 'Inactive - pending review',
        ]);
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f0f9ff;">
        <tr>
            <td align="center" style="padding: 32px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width: 560px; background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06);">

                    <tr>
                        <td style="background: linear-gradient(135deg, #0284c7, #0ea5e9); height: 6px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 32px 32px 4px;">
                            @if ($store->logo)
                                <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->store_name }}"
                                    style="max-height: 72px; max-width: 200px; border-radius: 12px; margin-bottom: 12px;">
                            @endif
                            <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">New Store Registration</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 6px 32px 20px;">
                            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                                <strong style="color: #0f172a;">{{ $store->store_name }}</strong> registered from the
                                public store registration form on {{ $store->created_at?->format('d M Y, h:i A') }}.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                @foreach ($rows as $label => $value)
                                    <tr>
                                        <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #0ea5e9; width: 32%; background: #f8fafc; {{ $loop->last ? '' : 'border-bottom: 1px solid #f1f5f9;' }}">
                                            {{ $label }}</td>
                                        <td style="padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0f172a; {{ $loop->last ? '' : 'border-bottom: 1px solid #f1f5f9;' }}">
                                            {{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 24px 32px 32px;">
                            <a href="{{ route('admin.stores.edit', $store->id) }}"
                                style="display: inline-block; padding: 13px 32px; font-size: 15px; font-weight: 800; color: #ffffff; text-decoration: none; background: #0284c7; border-radius: 14px;">
                                Review &amp; Activate Store →</a>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px;">
                    <tr>
                        <td align="center" style="padding: 20px 32px;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>
