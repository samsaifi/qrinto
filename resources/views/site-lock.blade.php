<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Site Locked | Qrinto</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .lock-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .lock-icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .lock-icon svg {
            width: 28px;
            height: 28px;
            stroke: #64748b;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #287d3c;
            box-shadow: 0 0 0 3px rgba(40,125,60,0.1);
        }
        .error-msg {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            text-align: left;
        }
        .submit-btn {
            width: 100%;
            padding: 13px;
            background: #287d3c;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .submit-btn:hover {
            background: #1e6b31;
        }
        .logo {
            margin-bottom: 32px;
        }
        .logo img {
            height: 36px;
        }
        @media (max-width: 480px) {
            .lock-card { padding: 36px 24px; }
        }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="logo">
            <img src="{{ asset('images/svg-logo/logo-full.svg') }}" alt="Qrinto" onerror="this.style.display='none'">
        </div>

        <div class="lock-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <h1>Site is Locked</h1>
        <p class="subtitle">Enter the password to access this site.</p>

        @if ($errors->has('password'))
            <div class="error-msg">{{ $errors->first('password') }}</div>
        @endif

        <form method="POST" action="{{ route('site-lock.verify') }}">
            @csrf
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter site password" autofocus required>
            </div>
            <button type="submit" class="submit-btn">Unlock</button>
        </form>
    </div>
</body>
</html>
