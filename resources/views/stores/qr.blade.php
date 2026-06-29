<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Code - {{ $store->store_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #f8fafc;
        }

        .qr-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 2rem;
            padding: 3rem;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }

        .store-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            background: linear-gradient(135deg, #e11d73, #be185d);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.5rem;
        }

        .store-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #f8fafc, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .store-code {
            font-family: monospace;
            font-size: 0.875rem;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        .qr-container {
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: inline-block;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .qr-container canvas {
            display: block;
            width: 280px !important;
            height: 280px !important;
        }

        .scan-instruction {
            font-size: 1rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 0.5rem;
        }

        .scan-description {
            font-size: 0.813rem;
            color: #64748b;
            line-height: 1.6;
        }

        .scan-url {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.625rem 1.25rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.75rem;
            font-family: monospace;
            font-size: 0.75rem;
            color: #94a3b8;
            word-break: break-all;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #e11d73, #be185d);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(225, 29, 115, 0.4);
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }

            .qr-card {
                max-width: 100%;
                width: 100%;
                box-shadow: none;
                border: none;
                border-radius: 0;
                background: #ffffff !important;
                backdrop-filter: none;
                padding: 2rem 3rem;
            }

            .store-badge {
                background: #0f172a !important;
                color: #ffffff !important;
                font-size: 0.8rem;
                padding: 0.5rem 1.5rem;
            }

            .store-name {
                -webkit-text-fill-color: #0f172a !important;
                color: #0f172a !important;
                background: none !important;
                font-size: 2.25rem;
                word-break: break-word;
                overflow-wrap: break-word;
            }

            .store-code {
                color: #475569 !important;
                font-size: 1rem;
            }

            .qr-container {
                border: 3px solid #0f172a;
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: none;
                margin-bottom: 1.5rem;
            }

            .qr-container img,
            .qr-container canvas {
                width: 320px !important;
                height: 320px !important;
            }

            .scan-instruction {
                color: #0f172a !important;
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }

            .scan-description {
                color: #475569 !important;
                font-size: 0.95rem;
            }

            .scan-url {
                background: #f1f5f9 !important;
                border: 1px solid #cbd5e1 !important;
                color: #334155 !important;
                font-size: 0.85rem;
                padding: 0.5rem 1rem;
            }

            .print-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="store-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M12 12v5"/></svg>
            In-Store QR Code
        </div>

        <h1 class="store-name">{{ $store->store_name }}</h1>
        <p class="store-code">Store #{{ $store->store_code }}</p>

        <div class="qr-container">
            <div id="qrcode"></div>
        </div>

        <p class="scan-instruction">📱 Scan to Start Ordering</p>
        <p class="scan-description">
            Point your phone camera at the QR code to browse products,<br>
            customize your order, and pay — all from your device.
        </p>

        <div class="scan-url">{{ $scanUrl }}</div>

        <br>
        <button class="print-btn" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print QR Code
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: '{{ $scanUrl }}',
            width: 280,
            height: 280,
            colorDark: '#0f172a',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });
    </script>
</body>
</html>
