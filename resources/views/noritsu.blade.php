<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#ffffff">
    <meta name="format-detection" content="telephone=no">
    <title>Noritsu Print</title>

    @vite(['resources/css/app.css', 'resources/js/noritsu.jsx'])

    <style>
        /* ── Mobile-first global reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; -webkit-text-size-adjust: 100%; }
        body {
            height: 100%; margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
            background: #f8fafc; color: #111;
            overscroll-behavior: none;
        }
        #noritsu-app { height: 100%; min-height: 100vh; min-height: 100dvh; }

        /* ── Safe-area insets (iPhone notch / nav bar) ── */
        .safe-top { padding-top: env(safe-area-inset-top, 0px); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }

        /* ── Touch-optimized defaults ── */
        button, a { -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
        input, select, textarea { font-size: 16px; /* prevent zoom on iOS */ }

        /* ── Animations ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 0; height: 0; }
    </style>
</head>
<body>
    <div id="noritsu-app"></div>
</body>
</html>
