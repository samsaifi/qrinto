{{-- Size page: the Business card card's Upload button picks a PDF (page 1 front,
     page 2 back) or one or two PNG / JPG images, reads them in the browser with
     public/js/business-card-pdf.js, hands them to the editor (sessionStorage)
     and redirects there. This partial only carries the shared styles + script. --}}

@push('styles')
    <style>
        /* Size cards: darker border on hover, selected card outlined in the brand green. */
        .lp-size-card {
            cursor: pointer
        }

        .lp-size-card:hover {
            border-color: #A9B3A6 !important
        }

        .lp-size-card.lp-size-card-on {
            border-color: #1F6B2E !important;
            box-shadow: 0 0 0 1px #1F6B2E, 0 4px 14px rgba(31, 107, 46, .12) !important
        }

        .bcu {
            width: 100%;
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-family: 'Figtree', system-ui, sans-serif;
            color: #17201A;
            text-align: left
        }

        .bcu-banner {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 14px;
            background: #E3F0E1;
            color: #1D4D22;
            font-size: 14px;
            line-height: 1.45
        }

        .bcu-banner svg {
            flex-shrink: 0;
            margin-top: 1px
        }

        .bcu-box {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding: 14px 16px;
            border-radius: 14px;
            background: #FFFFFF;
            border: 1px solid #E2E7E0
        }

        .bcu-text {
            flex: 1 1 260px;
            font-size: 14px;
            line-height: 1.45
        }

        .bcu-text strong {
            display: block;
            font-weight: 700
        }

        .bcu-text span {
            color: #4C5A4E
        }

        .bcu-actions {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .bcu-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid #C7CFC4;
            background: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            color: #17201A
        }

        .bcu-btn:hover {
            border-color: #1F6B2E
        }

        .bcu-btn.is-busy {
            cursor: progress;
            opacity: .8
        }

        .bcu-btn:focus-within {
            outline: 3px solid #9FCB98;
            outline-offset: 2px
        }

        .bcu-input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            overflow: hidden
        }

        .bcu-link {
            border: none;
            background: none;
            font-size: 13px;
            font-weight: 700;
            color: #8A2018;
            cursor: pointer;
            padding: 6px
        }

        .bcu-spin {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2.5px solid #C7CFC4;
            border-top-color: #1F6B2E;
            box-sizing: border-box;
            animation: bcuSpin 1s linear infinite
        }

        @keyframes bcuSpin {
            to {
                transform: rotate(360deg)
            }
        }

        .bcu [hidden] {
            display: none !important
        }

        .bcu-pick { position: relative; cursor: pointer }
        .bcu-pick.is-busy { cursor: progress; opacity: .8 }
        .bcu-pick:focus-within { outline: 3px solid #9FCB98; outline-offset: 2px }

        .bcu-error {
            margin: 0;
            padding: 10px 14px;
            border-radius: 12px;
            background: #FDECEA;
            color: #8A2018;
            font-size: 13px;
            line-height: 1.45
        }

        .bcu-note {
            margin: 0;
            padding: 10px 14px;
            border-radius: 12px;
            background: #FFF6E5;
            color: #6B4A00;
            font-size: 13px;
            line-height: 1.45
        }

        .bcu-go {
            display: flex;
            justify-content: flex-end
        }

        .bcu-design {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 50px;
            padding: 0 22px;
            border-radius: 12px;
            background: #1F6B2E;
            color: #FFFFFF !important;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none
        }

        .bcu-design:hover {
            background: #18572A
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/business-card-pdf.js') }}?v={{ filemtime(public_path('js/business-card-pdf.js')) }}"></script>
@endpush
