<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Design - {{ $order->order_number }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Reset ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Screen-only UI (hidden during print) ─── */
        .screen-only {
            width: 100%;
            max-width: 520px;
            padding: 1.5rem;
        }

        .print-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .print-header h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
        }
        .print-header p {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .preview-card {
            background: #fff;
            border-radius: 1.25rem;
            border: 2px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
        }

        .preview-image-wrap {
            background: #f8fafc;
            padding: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .preview-image-wrap img {
            max-width: 100%;
            max-height: 50vh;
            border-radius: 0.75rem;
            object-fit: contain;
        }

        .preview-info {
            padding: 1rem 1.25rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-info .order-num {
            font-weight: 800;
            font-size: 0.875rem;
            color: #334155;
        }
        .preview-info .store-name {
            font-weight: 600;
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Buttons */
        .btn-row {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            border: none;
            border-radius: 1rem;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn:active { transform: scale(0.97); }

        .btn-print {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 6px 20px -6px rgba(99, 102, 241, 0.5);
        }
        .btn-print:hover { background: linear-gradient(135deg, #4f46e5, #4338ca); }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        .btn-back:hover { background: #e2e8f0; }

        /* Icon SVGs inline */
        .icon {
            width: 20px;
            height: 20px;
            display: inline-block;
        }

        /* ── Print Styles ─────────────────────── */
        @media print {
            .screen-only { display: none !important; }

            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            @page {
                margin: 0;
                size: auto;
            }

            .print-area {
                display: flex !important;
                width: 100vw;
                height: 100vh;
                justify-content: center;
                align-items: center;
                page-break-inside: avoid;
            }

            .print-area img {
                max-width: 100%;
                max-height: 100vh;
                object-fit: contain;
            }
        }

        /* Hidden by default on screen */
        .print-area {
            display: none;
        }
    </style>
</head>
<body>

    {{-- ── Screen UI: Preview + Print Button ─── --}}
    <div class="screen-only">
        <div class="print-header">
            <h1>🖨️ Print Your Design</h1>
            <p>Order {{ $order->order_number }}</p>
        </div>

        <div class="preview-card">
            <div class="preview-image-wrap">
                @if($designUrl)
                    <img src="{{ $designUrl }}" alt="Custom Design Preview">
                @else
                    <p style="color: #94a3b8; font-size: 0.875rem;">No design image found</p>
                @endif
            </div>
            <div class="preview-info">
                <div>
                    <div class="order-num">#{{ $order->order_number }}</div>
                    <div class="store-name">{{ $order->store?->store_name ?? 'Custom Print' }}</div>
                </div>
                <div style="font-weight: 800; color: #0ea5e9; font-size: 1.125rem;">
                    {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                </div>
            </div>
        </div>

        <div class="btn-row">
            <a href="{{ route('flow.confirmation', $order->id) }}" class="btn btn-back">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <button onclick="window.print()" class="btn btn-print" id="printBtn">
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12h.008v.008h-.008V12zm-2.25 0h.008v.008h-.008V12z"/></svg>
                Print Now
            </button>
        </div>
    </div>

    {{-- ── Print-only: Full-page image ─── --}}
    <div class="print-area">
        @if($designUrl)
            <img src="{{ $designUrl }}" alt="Custom Design">
        @endif
    </div>

</body>
</html>
