<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Create Your Custom Print')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,700;1,700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-tap-highlight-color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
    @stack('styles')
</head>

<body class="antialiased select-none ">
     
        <header  class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
			<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
				<div class="flex h-16 items-center justify-between gap-4"> 
					<a href="#" class="flex items-center gap-2.5 shrink-0 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
						<span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white font-extrabold text-base shadow-sm shadow-brand-600/30">
						Q
						</span>
						<div class="flex flex-col">
							<span class="text-md font-extrabold tracking-tight text-brand-600">Qrinto</span>
							<span class="text-xs   tracking-tight text-slate-900">Print Studio</span> 
						</div>
					</a> 
					<!-- Desktop Navigation (centered) -->
					@include('layouts.pc.nav')
					<!-- Right side actions (desktop) -->
					
				</div>
			</div>
		</header>
        <!-- Main Content -->
        <main class="  px-10 py-4    ">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>

        </main> 
        @include('helper.footer') 
       
    <script>
        // Init Lucide icons
        lucide.createIcons();
        window.__currency = @json(\App\Services\CurrencyService::toArray());
        window.__price = function(amount, decimals) {
            decimals = decimals !== undefined ? decimals : 2;
            var converted = parseFloat(amount) * (window.__currency.rate || 1);
            return window.__currency.symbol + converted.toFixed(decimals);
        };
    </script>
    @stack('scripts')
</body>

</html>