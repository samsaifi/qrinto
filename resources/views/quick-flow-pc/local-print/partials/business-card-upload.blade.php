{{-- Size page, Business card selected (shown under the custom-size preview):
     upload finished artwork and/or go to
     the editor. A PDF (page 1 front, page 2 back if any) or one or two
     PNG / JPG images (front, then back) are checked and rendered in the browser
     by public/js/business-card-pdf.js and handed to the editor, which places
     them on Front (and Back). Runs inside the size page's x-data (selectedCard). --}}
<section class="bcu" x-show="selectedCard === 'business-card'" x-cloak x-transition.opacity
    aria-label="Business card file upload" x-data="{
        busy: false,
        error: '',
        using: '',
        hasBack: false,
        note: '',
        init() {
            const s = window.BusinessCardPdf && BusinessCardPdf.stored();
            if (s) { this.show(s); uploadOpen = true; }
        },
        show(res) {
            this.using = BusinessCardPdf.describe(res, 'front');
            this.hasBack = res.pages.length === 2;
            this.note = res.note || '';
        },
        async pick(e) {
            const files = e.target.files ? Array.from(e.target.files) : [];
            e.target.value = '';
            if (!files.length) return;
            this.busy = true;
            this.error = '';
            try {
                const res = await BusinessCardPdf.read(files);
                if (!BusinessCardPdf.store(res)) throw new BusinessCardPdf.UploadError('Your browser could not keep this file. Close other tabs and try again.');
                this.show(res);
            } catch (err) {
                this.error = BusinessCardPdf.message(err);
            } finally {
                this.busy = false;
            }
        },
        remove() {
            BusinessCardPdf.clear();
            this.using = '';
            this.error = '';
            this.note = '';
        },
    }">
    <div class="bcu-banner">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <path d="M7 10h6M7 14h4" />
        </svg>
        <span><strong>That’s a business card.</strong> After you design it, we’ll offer to print 10 on a 7 × 10 in sheet
            or 12 on Letter or A4, with the backs lined up for cutting.</span>
    </div>
    {{-- Upload box opens from the card's Upload button (uploadOpen on the size page). --}}
    <div class="bcu-box" x-show="uploadOpen">
        <span class="bcu-text">
            <strong x-text="using ? using : 'Already have it designed?'"></strong>
            <span
                x-text="using
                ? (hasBack ? 'Front and back are loaded. Open the editor to check them or add more.' : 'The front is loaded. Add the back in the editor.')
                : 'Upload a PDF (page 1 is the front, page 2 the back) or one or two PNG/JPG images (front, then back).'"></span>
        </span>
        <span class="bcu-actions">
            <button type="button" class="bcu-link" :hidden="!using || busy" @click="remove()">Remove</button>
            <label class="bcu-btn" :class="busy && 'is-busy'">
                <span class="bcu-spin" x-show="busy" aria-hidden="true"></span>
                <svg x-show="!busy" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                </svg>
                <span x-text="busy ? 'Reading file…' : (using ? 'Replace file' : 'Upload file')"></span>
                <input type="file" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple
                    class="bcu-input" :disabled="busy" @change="pick($event)">
            </label>
        </span>
    </div>
    <p class="bcu-error" role="alert" :hidden="!uploadOpen || !error" x-text="error"></p>
    <p class="bcu-note" role="status" :hidden="!uploadOpen || !note || !using || !!error" x-text="note"></p>
    <div class="bcu-go">
        <a class="bcu-design"
            href="{{ route('localprint.customizer', ['size' => '3.5x2', 'orientation' => 'landscape', 'title' => 'business-card', 'fresh' => 1]) }}">
            Design 3.5 in × 2 in
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </a>
    </div>
</section>

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
