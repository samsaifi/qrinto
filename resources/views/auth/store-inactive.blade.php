<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store Inactive - {{ config('app.name', 'Qrinto') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: #111827;
        }
        .card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .35);
        }
        .icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fee2e2;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon svg { width: 38px; height: 38px; color: #dc2626; }
        h1 { font-size: 22px; margin-bottom: 12px; }
        p { font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 28px; }
        .btn {
            display: inline-block;
            padding: 11px 26px;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .btn:hover { background: #374151; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1>Store Inactive</h1>
        <p>Your store is inactive. Please contact the admin to activate your store.</p>
        <a href="{{ route('login') }}" class="btn">Back to Login</a>
    </div>
</body>
</html>
