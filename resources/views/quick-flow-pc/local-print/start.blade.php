@extends('layouts.quick-flow-pc')
@section('title', 'Print on your own 931BL | Qrinto')

@section('content')
    <div class="ls">
        <main class="ls-main">
            <h1>Print on your own 931BL</h1>
            <p class="ls-lede">No store, no pickup. Your prints come out of the printer right next to you.</p>

            {{-- Two entry cards --}}
            <div class="ls-cards">
                <a href="{{ route('localprint.pdf') }}" class="ls-card main">
                    <div class="ls-card-top">
                        <span class="ls-ico solid">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                            </svg>
                        </span>
                        <span class="ls-badge">Most used</span>
                    </div>
                    <h2>Upload a file</h2>
                    <p>Already have a finished PDF or image? We check it against the paper that’s loaded and show you a
                        live preview before anything prints.</p>
                    <span class="ls-btn primary">Choose a file
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg></span>
                </a>

                <a href="{{ route('localprint.design') }}" class="ls-card">
                    <div class="ls-card-top">
                        <span class="ls-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 20h4L19 9l-4-4L4 16v4z" />
                                <path d="M13.5 6.5l4 4" />
                            </svg>
                        </span>
                    </div>
                    <h2>Design something</h2>
                    <p>No file yet? Start from a template or a blank size, then print when you’re happy with it.</p>
                    <span class="ls-btn">Open the designer
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg></span>
                </a>
            </div>

            {{-- Helper setup --}}
            <div class="ls-help">
                <span class="ls-help-ico">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5v.2M12 17h.01" />
                    </svg>
                </span>
                <div class="ls-help-txt">
                    <p class="t">Printer not showing up?</p>
                    <p>Install the PrintTrays helper on this computer so Printo can see your 931BL.</p>
                </div>
                <a href="https://qrinto.com/docs/qrinto-931bl-setup-guide.html" target="_blank" rel="noopener"
                    class="ls-btn">Set up the helper</a>
            </div>
        </main>
    </div>
@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .ls { background: #F4F6F3; color: #17201A; font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif; min-height: 100vh }
        .ls-main { max-width: 1040px; margin: 0 auto; padding: 40px 24px 56px }
        .ls h1 { margin: 0; font-size: 34px; font-weight: 800; letter-spacing: -.02em; line-height: 1.15 }
        .ls-lede { margin: 10px 0 0; font-size: 17px; color: #4C5A4E }
        .ls-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-top: 28px }
        .ls-card { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; padding: 24px; border-radius: 18px; background: #fff; border: 1px solid #E2E7E0; text-decoration: none; color: #17201A; transition: box-shadow .15s, border-color .15s }
        .ls-card:hover { border-color: #B5C0B1; box-shadow: 0 6px 20px rgba(23, 32, 26, .08) }
        .ls-card.main { border: 2px solid #2F7A3B; padding: 23px; box-shadow: 0 6px 20px rgba(23, 32, 26, .06) }
        .ls-card:focus-visible { outline: 3px solid #9FCB98; outline-offset: 2px }
        .ls-card-top { display: flex; align-items: flex-start; justify-content: space-between; width: 100%; margin-bottom: 8px }
        .ls-ico { width: 52px; height: 52px; border-radius: 12px; background: #EEF2EC; color: #2F6E35; display: flex; align-items: center; justify-content: center }
        .ls-ico.solid { background: #2F7A3B; color: #fff }
        .ls-badge { padding: 4px 10px; border-radius: 999px; background: #E3F0E1; color: #2F6E35; font-size: 12px; font-weight: 700 }
        .ls-card h2 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.01em }
        .ls-card p { margin: 0; font-size: 15px; line-height: 1.55; color: #4C5A4E }
        .ls-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 0 18px; margin-top: 10px; border-radius: 10px; border: 1px solid #D5DCD2; background: #fff; font-size: 15px; font-weight: 700; color: #17201A; text-decoration: none; white-space: nowrap }
        .ls-btn.primary { background: #1F6B2E; border-color: #1F6B2E; color: #fff }
        .ls-card:hover .ls-btn.primary { background: #185624 }
        .ls-card:hover .ls-btn:not(.primary) { border-color: #B5C0B1 }
        .ls-help { display: flex; align-items: center; gap: 14px; margin-top: 28px; padding: 18px 20px; border-radius: 18px; background: #E8ECE6; flex-wrap: wrap }
        .ls-help-ico { width: 40px; height: 40px; border-radius: 50%; background: #fff; color: #2F6E35; display: flex; align-items: center; justify-content: center; flex-shrink: 0 }
        .ls-help-txt { flex: 1 1 260px; min-width: 0 }
        .ls-help-txt p { margin: 0; font-size: 14px; color: #4C5A4E }
        .ls-help-txt .t { font-weight: 700; color: #17201A; margin-bottom: 2px }
        .ls-help .ls-btn { margin-top: 0 }
        .ls-help .ls-btn:hover { border-color: #B5C0B1 }
        @media (max-width: 600px) {
            .ls-main { padding: 24px 16px 40px }
            .ls h1 { font-size: 26px }
            .ls-lede { font-size: 15px }
        }
    </style>
@endpush
