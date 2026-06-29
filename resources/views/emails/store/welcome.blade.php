<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
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

                    <!-- Welcome Icon -->
                    <tr>
                        <td align="center" style="padding: 36px 32px 12px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="width: 72px; height: 72px; background: linear-gradient(135deg, #7c3aed, #8b5cf6); border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="font-size: 36px; color: #ffffff; line-height: 72px;">🎉</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 4px;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">Welcome to {{ config('app.name') }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 32px 20px;">
                            <p style="margin: 0; font-size: 14px; color: #64748b; font-weight: 500; line-height: 1.5;">Hi {{ $user->name }}, your account has been created to manage <strong style="color: #0f172a;">{{ $user->store->store_name }}</strong></p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 32px;">
                            <div style="border-top: 1px solid #e2e8f0;"></div>
                        </td>
                    </tr>

                    <!-- Login Credentials Section -->
                    <tr>
                        <td style="padding: 24px 32px 0;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; font-weight: 800; color: #1e293b; font-style: italic; font-family: Georgia, 'Times New Roman', serif;">Your Login Credentials</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                <!-- Login URL -->
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; width: 35%; background: #f8fafc;">Login URL</td>
                                    <td style="padding: 14px 16px; font-size: 14px; border-bottom: 1px solid #f1f5f9;">
                                        <a href="{{ route('login') }}" style="color: #0284c7; font-weight: 700; text-decoration: none;">{{ route('login') }}</a>
                                    </td>
                                </tr>
                                <!-- Email -->
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 13px; font-weight: 600; color: #64748b; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">Email</td>
                                    <td style="padding: 14px 16px; font-size: 14px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #f1f5f9;">{{ $user->email }}</td>
                                </tr>
                                <!-- Password -->
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 13px; font-weight: 600; color: #64748b; background: #f8fafc;">Password</td>
                                    <td style="padding: 14px 16px; font-size: 14px; font-weight: 700; color: #0f172a;">
                                        <code style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 14px; font-family: 'Courier New', monospace; letter-spacing: 1px; color: #7c3aed;">{{ $password }}</code>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Go to Dashboard Button -->
                    <tr>
                        <td align="center" style="padding: 24px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: linear-gradient(135deg, #7c3aed, #8b5cf6); border-radius: 14px; box-shadow: 0 4px 14px rgba(124,58,237,0.35);">
                                        <a href="{{ route('login') }}" style="display: inline-block; padding: 14px 36px; font-size: 15px; font-weight: 800; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">Go to Dashboard →</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Security Note -->
                    <tr>
                        <td style="padding: 0 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; font-weight: 500;">🔒 <strong>Security Tip:</strong> We recommend changing your password after your first login.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Thank You Footer -->
                    <tr>
                        <td style="padding: 24px 32px 8px;">
                            <p style="margin: 0 0 2px; font-size: 14px; color: #64748b;">Welcome aboard,</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">The {{ config('app.name') }} Team</p>
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
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
