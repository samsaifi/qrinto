<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>404 · Page Not Found — Qrinto Print Studio</title>

    <link rel="apple-touch-icon" href="/logo/Qrinto-logo-med.png">
    <meta name="theme-color" content="#0ea5e9">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap"
        rel="stylesheet">

    <style>
        *,
        *::before,
        *::after { box-sizing: border-box; }

        :root {
            --brand: #0ea5e9;
            --brand-600: #0284c7;
            --brand-700: #0369a1;
            --ink: #0f172a;
            --muted: #64748b;
            --faint: #94a3b8;
            --line: #eef2f7;
        }

        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            color: var(--ink);
            background: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile-first centered shell, mirrors the app's max-w-md container */
        .shell {
            position: relative;
            max-width: 28rem;
            margin: 0 auto;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(2, 132, 199, .12);
            overflow: hidden;
        }

        /* Soft brand glow behind the hero */
        .glow {
            position: absolute;
            top: -160px;
            left: 50%;
            transform: translateX(-50%);
            width: 460px;
            height: 460px;
            border-radius: 50%;
            background: radial-gradient(circle at center, rgba(14, 165, 233, .18), rgba(14, 165, 233, 0) 70%);
            pointer-events: none;
        }

        /* Header — same blue treatment as the flow layout */
        .topbar {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
        }
        .logo-badge {
            width: 40px;
            height: 40px;
            background: var(--brand-600);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px -6px rgba(2, 132, 199, .5);
        }
        .logo-badge span {
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 22px;
            line-height: 1;
        }
        .logo-text { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-text b { font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
        .logo-text small {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: var(--faint);
            margin-top: 2px;
        }

        /* Hero content */
        .main {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px 28px 8px;
        }

        .icon-orb {
            width: 96px;
            height: 96px;
            border-radius: 30px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1.5px solid rgba(14, 165, 233, .14);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-600);
            margin-bottom: 22px;
            box-shadow: 0 18px 40px -16px rgba(2, 132, 199, .35);
            animation: float 4.5s ease-in-out infinite;
        }
        .icon-orb svg { width: 44px; height: 44px; stroke-width: 1.9; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-9px); }
        }

        .code {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: clamp(72px, 26vw, 104px);
            line-height: .92;
            letter-spacing: -.04em;
            margin: 0 0 6px;
            background: linear-gradient(135deg, var(--brand) 0%, #38bdf8 45%, #818cf8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.01em;
            margin: 0 0 8px;
        }
        .lead {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.6;
            color: var(--muted);
            max-width: 19rem;
            margin: 0 auto;
        }

        /* Actions */
        .actions {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 28px 28px 8px;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 15px 20px;
            border-radius: 18px;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -.01em;
            text-decoration: none;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        }
        .btn svg { width: 18px; height: 18px; stroke-width: 2.2; }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--brand-600));
            box-shadow: 0 12px 26px -10px rgba(2, 132, 199, .6);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 30px -10px rgba(2, 132, 199, .65); }
        .btn-primary:active { transform: translateY(0) scale(.99); }
        .btn-ghost {
            color: var(--brand-600);
            background: #fff;
            border-color: var(--line);
        }
        .btn-ghost:hover { background: #f8fafc; border-color: #d8e3ef; }

        /* Footer helper links */
        .foot {
            position: relative;
            z-index: 2;
            padding: 22px 28px calc(26px + env(safe-area-inset-bottom, 0px));
            text-align: center;
        }
        .foot .links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-bottom: 14px;
        }
        .foot a {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--muted);
            text-decoration: none;
            transition: color .18s ease;
        }
        .foot a:hover { color: var(--brand-600); }
        .foot .sep { width: 4px; height: 4px; border-radius: 50%; background: #cbd5e1; }
        .foot .mark {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .28em;
            color: #cbd5e1;
        }

        @media (prefers-reduced-motion: reduce) {
            .icon-orb { animation: none; }
        }
    </style>
</head>

<body>
    <div class="shell">
        <div class="glow"></div>

        <!-- Branding -->
        <header class="topbar">
            <a href="{{ url('/') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                <span class="logo-badge"><span>Q</span></span>
                <span class="logo-text">
                    <b>Qrinto</b>
                    <small>Print Studio</small>
                </span>
            </a>
        </header>

        <!-- Hero -->
        <main class="main">
            <div class="icon-orb" aria-hidden="true">
                <!-- map-pin-off (lost / store not found) -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5.43 5.43A8.06 8.06 0 0 0 4 10c0 6 8 12 8 12a29.94 29.94 0 0 0 5-5" />
                    <path d="M19.18 13.52A8.66 8.66 0 0 0 20 10a8 8 0 0 0-13.95-5.39" />
                    <path d="M9.13 9.13A2.74 2.74 0 0 0 9 10a3 3 0 0 0 3 3 2.74 2.74 0 0 0 .87-.13" />
                    <path d="m2 2 20 20" />
                </svg>
            </div>

            <p class="code">404</p>
            <h1>This page took a wrong turn</h1>
            <p class="lead">
                We couldn't find what you were looking for. It may have moved, expired, or the store link is no longer
                active.
            </p>
        </main>

        <!-- Actions -->
        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">
                <!-- home -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <path d="M9 22V12h6v10" />
                </svg>
                Back to Home
            </a>
            <a class="btn btn-ghost" href="{{ url('/find-store') }}">
                <!-- search -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                Find a Store
            </a>
        </div>

        <!-- Footer -->
        <footer class="foot">
            <div class="links">
                <a href="{{ url('/track') }}">Track an Order</a>
                <span class="sep"></span>
                <a href="{{ url('/custom-print') }}">Custom Print</a>
            </div>
            <p class="mark">Qrinto Custom Studio</p>
        </footer>
    </div>
</body>

</html>
