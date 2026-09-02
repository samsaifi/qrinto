/* ════════════════════════════════════════════════════════════════════
   QRinto Print Studio - Premium Onboarding Tour  (Driver.js engine)

   The QRinto flow spans 5 SEPARATE server-rendered pages, so this is a
   *cross-page* tour: progress is persisted in localStorage and the tour
   resumes itself on each page until the user finishes or skips.

   Requires Driver.js v1 (window.driver.js.driver) loaded before this file.
   ════════════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    if (!window.driver || !window.driver.js) {
        console.warn('[QrintoTour] Driver.js not found - tour disabled.');
        return;
    }
    var driver = window.driver.js.driver;

    /* ───────────────────────── localStorage ───────────────────────── */
    var LS = {
        done: 'qrinto_tour_completed_v1', // '1' once finished or skipped
        active: 'qrinto_tour_active_v1',    // '1' while a run is in progress
        reached: 'qrinto_tour_reached_v1',   // index of furthest page reached (within scope)
        scope: 'qrinto_tour_scope_v1'      // JSON list of pages this run covers
    };
    function lsGet(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
    function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) { } }
    function lsDel(k) { try { localStorage.removeItem(k); } catch (e) { } }

    function isDone() { return lsGet(LS.done) === '1'; }
    function isActive() { return lsGet(LS.active) === '1'; }
    function getReached() { return parseInt(lsGet(LS.reached) || '0', 10) || 0; }
    function setReached(n) { lsSet(LS.reached, String(n)); }
    function startRun(scope) {
        lsSet(LS.active, '1');
        lsSet(LS.scope, JSON.stringify(scope));
        lsDel(LS.reached);
    }
    function endRun(remember) {
        if (remember) lsSet(LS.done, '1');
        lsDel(LS.active);
        lsDel(LS.reached);
        lsDel(LS.scope);
    }

    /* ───────────────────────── Inline icons ───────────────────────── */
    var S = function (p) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
            'stroke-linecap="round" stroke-linejoin="round">' + p + '</svg>';
    };
    var ICON = {
        search: S('<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>'),
        pin: S('<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>'),
        store: S('<path d="m2 7 2-4h16l2 4"/><path d="M4 7v13h16V7"/><path d="M9 20v-6h6v6"/>'),
        grid: S('<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>'),
        ruler: S('<path d="M21.3 8.7 8.7 21.3a1 1 0 0 1-1.4 0l-4.6-4.6a1 1 0 0 1 0-1.4L15.3 2.7a1 1 0 0 1 1.4 0l4.6 4.6a1 1 0 0 1 0 1.4z"/><path d="m7.5 10.5 2 2M11 7l2 2M14.5 3.5l2 2M4 14l2 2"/>'),
        filter: S('<path d="M3 4h18l-7 8v6l-4 2v-8z"/>'),
        image: S('<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="9" cy="9" r="2"/><path d="m21 15-4.5-4.5L4 21"/>'),
        layers: S('<path d="m12 2 9 5-9 5-9-5 9-5z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>'),
        sliders: S('<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>'),
        check: S('<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="m9 11 3 3L22 4"/>'),
        tap: S('<path d="M9 11V6a2 2 0 1 1 4 0v5"/><path d="M13 11V8a2 2 0 1 1 4 0v3"/><path d="M17 11v-1a2 2 0 1 1 4 0v6a6 6 0 0 1-6 6h-2.5a5 5 0 0 1-4-2L4.5 16a2 2 0 0 1 3-2.6L9 14"/>')
    };

    /* ───────────────────────── Step blueprints ─────────────────────────
       One group per page.  `handoff:true`  → last step hands the user off to
       perform the real action (no auto-advance); the tour resumes on the next
       page.  `final:true` → last step of the whole tour (shows "Finish").
       ──────────────────────────────────────────────────────────────────── */
    var PAGES = {
        'find-store': {
            handoff: true,
            steps: [
                {
                    el: '[data-tour="store-search"]', icon: 'search',
                    title: 'Search for a store',
                    desc: 'Type a name, city, or zip code to find your nearest QRinto print location.',
                    side: 'bottom', align: 'start'
                },
                {
                    el: '[data-tour="nearby-stores"]', icon: 'pin',
                    title: 'Stores near you',
                    desc: 'Allow location access and the closest branches appear here automatically.',
                    side: 'bottom', align: 'center'
                },
                {
                    el: null, icon: 'store',
                    title: 'Pick a store to begin',
                    desc: 'A store must be selected before you can order. Tap any store to continue.',
                    align: 'center'
                }
            ]
        },
        'products': {
            handoff: true,
            steps: [
                {
                    el: '[data-tour="product-grid"]', icon: 'grid',
                    title: 'Choose what to create',
                    desc: 'Browse products like photo prints, cards, and gifts - each one is fully customizable.',
                    side: 'top', align: 'center'
                }
            ]
        },
        'sizes': {
            handoff: true,
            steps: [
                {
                    el: '[data-tour="size-list"]', icon: 'ruler',
                    title: 'Pick your size',
                    desc: 'Larger sizes cost more - the price updates with every option you choose.',
                    side: 'top', align: 'center'
                }
            ]
        },
        'templates': {
            handoff: true,
            steps: [
                {
                    el: '[data-tour="template-filters"]', icon: 'filter',
                    title: 'Filter by category',
                    desc: 'Jump straight to the style you want - birthdays, weddings, holidays, and more.',
                    side: 'bottom', align: 'start'
                },
                {
                    el: '[data-tour="template-grid"]', icon: 'image',
                    title: 'Preview & pick a template',
                    desc: 'Tap any design to open it in the editor and make it yours.',
                    side: 'top', align: 'center'
                }
            ]
        },
        'editor': {
            final: true,
            steps: [
                {
                    el: '#thumb-nav', icon: 'layers',
                    title: 'Your pages',
                    desc: 'Switch between every page of your design - front, back, and inside.',
                    side: 'bottom', align: 'center'
                },
                {
                    el: '#canvas-container', icon: 'image',
                    title: 'The design canvas',
                    desc: 'Upload photos, drag, pinch to resize, and position everything just right.',
                    side: 'bottom', align: 'center'
                },
                {
                    el: '[data-tour="editor-toolbar"]', icon: 'sliders',
                    title: 'Editing toolbar',
                    desc: 'Add photos and text, recolor elements, or remove anything from here.',
                    side: 'top', align: 'center'
                },
                {
                    el: '#submit-btn', icon: 'check',
                    title: 'Confirm & continue',
                    desc: "Happy with it? Tap here to lock in your design and head to checkout.",
                    side: 'top', align: 'end'
                }
            ]
        }
    };
    var ORDER = ['find-store', 'products', 'sizes', 'templates', 'editor'];

    // Two entry scopes: full flow, or QR entry (store already chosen → skip find-store).
    var SCOPE_FULL = ORDER.slice();
    var SCOPE_QR = ['products', 'sizes', 'templates', 'editor'];

    function getScope() {
        try {
            var s = JSON.parse(lsGet(LS.scope));
            if (Array.isArray(s) && s.length) return s;
        } catch (e) { }
        return SCOPE_FULL;
    }
    function scopeTotal(scope) {
        return scope.reduce(function (n, p) { return n + PAGES[p].steps.length; }, 0);
    }
    function scopeOffset(scope, page) {
        var s = 0, idx = scope.indexOf(page);
        for (var i = 0; i < idx; i++) s += PAGES[scope[i]].steps.length;
        return s;
    }

    /* ───────────────────── Detect the current page ─────────────────────
       /type/{slug} renders EITHER the size page or the template page, so we
       detect by DOM, not by URL. Order matters (most specific first).        */
    function detectPage() {
        if (document.querySelector('#canvas-container')) return 'editor';
        if (document.querySelector('[data-tour="template-grid"]')) return 'templates';
        if (document.querySelector('[data-tour="size-list"]')) return 'sizes';
        if (document.querySelector('[data-tour="product-grid"]')) return 'products';
        if (document.querySelector('[data-tour="store-search"]')) return 'find-store';
        return null;
    }

    function isVisible(sel) {
        if (!sel) return true; // element-less (centered) step
        var el = document.querySelector(sel);
        if (!el) return false;
        var st = getComputedStyle(el);
        if (st.display === 'none' || st.visibility === 'hidden' || st.opacity === '0') return false;
        var r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0; // works for position:fixed too
    }

    /* ───────────────────────── Tour runtime ───────────────────────── */
    var driverObj = null;
    var currentSteps = [];   // raw blueprints actually shown on this page
    var currentOffset = 0;   // global step number before this page
    var currentTotal = 0;    // total steps for the active scope
    var programmatic = false; // guards onDestroyStarted on manual teardown

    function teardown() {
        programmatic = true;
        if (driverObj) { var d = driverObj; driverObj = null; try { d.destroy(); } catch (e) { } }
        programmatic = false;
    }

    function finishOrSkip() { endRun(true); teardown(); } // remember → never auto-show again
    function pauseForHandoff(idx) { setReached(idx + 1); teardown(); } // keep run alive

    function buildPopoverExtras(popover) {
        var i = driverObj.getActiveIndex();
        var raw = currentSteps[i];
        if (!raw) return;

        // Icon chip - before the title
        if (popover.title && ICON[raw.icon]) {
            var chip = document.createElement('div');
            chip.className = 'qt-icon';
            chip.innerHTML = ICON[raw.icon];
            popover.title.parentNode.insertBefore(chip, popover.title);
        }

        // Global progress - after the description
        var cur = currentOffset + i + 1;
        var pct = Math.round((cur / currentTotal) * 100);
        var prog = document.createElement('div');
        prog.className = 'qt-progress';
        prog.innerHTML =
            '<div class="qt-progress-head">' +
            '<span class="qt-step">Step ' + cur + ' of ' + currentTotal + '</span>' +
            '<span class="qt-pct">' + pct + '%</span>' +
            '</div>' +
            '<div class="qt-progress-track"><div class="qt-progress-fill" style="width:' + pct + '%"></div></div>';
        var anchor = popover.description || popover.title;
        if (anchor && anchor.parentNode) anchor.parentNode.insertBefore(prog, anchor.nextSibling);

        // Skip link - far left of the footer
        if (popover.footer && !popover.footer.querySelector('.qt-skip')) {
            var skip = document.createElement('button');
            skip.type = 'button';
            skip.className = 'qt-skip';
            skip.textContent = 'Skip tour';
            skip.addEventListener('click', finishOrSkip);
            popover.footer.insertBefore(skip, popover.footer.firstChild);
        }
    }

    function startTour(page) {
        if (!page || !PAGES[page]) return;
        var def = PAGES[page];
        var scope = getScope();
        var idx = scope.indexOf(page);
        if (idx === -1) return; // page isn't part of this run's scope

        // Don't replay pages the user has already passed.
        if (getReached() > idx) return;

        // Keep only steps whose target is actually on screen.
        currentSteps = def.steps.filter(function (s) { return isVisible(s.el); });
        if (!currentSteps.length) { if (def.handoff) setReached(idx + 1); return; }

        currentOffset = scopeOffset(scope, page);
        currentTotal = scopeTotal(scope);
        var lastIndex = currentSteps.length - 1;

        var steps = currentSteps.map(function (s, i) {
            return {
                element: s.el || undefined,
                popover: {
                    title: s.title,
                    description: s.desc,
                    side: s.side || 'bottom',
                    align: s.align || 'center',
                    // Hide "Back" on the first step (no cross-page back-tracking)
                    showButtons: i === 0 ? ['next', 'close'] : ['previous', 'next', 'close']
                }
            };
        });

        driverObj = driver({
            showProgress: false,            // we render our own global progress
            allowClose: true,               // X / Esc / overlay = skip
            overlayColor: '#0f172a',
            overlayOpacity: 0.72,
            stagePadding: 6,
            stageRadius: 14,
            animate: true,
            smoothScroll: true,
            disableActiveInteraction: false, // let users actually click the highlighted control
            popoverClass: 'qrinto-tour',
            nextBtnText: 'Next',
            prevBtnText: 'Back',
            doneBtnText: def.final ? 'Finish' : 'Got it',
            steps: steps,
            onPopoverRender: buildPopoverExtras,
            onPrevClick: function () { driverObj.movePrevious(); },
            onNextClick: function () {
                if (driverObj.isLastStep()) {
                    if (def.final) finishOrSkip();      // end of the whole tour
                    else pauseForHandoff(idx);          // hand off to the real UI
                } else {
                    driverObj.moveNext();
                }
            },
            onCloseClick: function () { finishOrSkip(); },
            onDestroyStarted: function () {             // Esc / overlay click
                if (!programmatic) endRun(true);
                teardown();
            }
        });

        driverObj.drive();
    }

    /* ───────────────────────── Boot / resume ───────────────────────── */
    function boot() {
        var page = detectPage();
        if (!page) return;

        // Reaching the editor (any customize page) ends the tour permanently.
        if (page === 'editor') {
            if (isActive() || !isDone()) endRun(true);
            return;
        }

        if (isActive()) {
            // A run is in progress - resume it on whatever page we landed on.
            startTour(page);
        } else if (!isDone()) {
            // Brand-new visitor: auto-start at the first interactive page they hit.
            if (page === 'find-store') {
                startRun(SCOPE_FULL);     // no store yet → full flow
                startTour(page);
            } else if (page === 'products') {
                startRun(SCOPE_QR);       // store pre-selected (QR entry) → skip find-store
                startTour(page);
            }
        }
    }

    /* ───────────────────── Public API + Restart hook ───────────────── */
    window.QrintoTour = {
        start: function () {
            var p = detectPage();
            startRun(p === 'find-store' ? SCOPE_FULL : SCOPE_QR);
            if (p) startTour(p);
        },
        restart: function (findStoreUrl) {
            lsDel(LS.done);
            var p = detectPage();
            if (p === 'find-store') {
                startRun(SCOPE_FULL);
                startTour('find-store');
            } else if (p === 'products') {
                // Already past store selection (e.g. QR session) - replay from products.
                startRun(SCOPE_QR);
                startTour('products');
            } else {
                startRun(SCOPE_FULL);
                window.location.href = findStoreUrl || '/find-store';
            }
        },
        skip: function () { finishOrSkip(); },
        reset: function () { endRun(false); } // dev helper: forget everything
    };

    // Wire any "Restart Tour" trigger:  <button data-qt-restart="{find-store url}">
    function wireRestart() {
        document.querySelectorAll('[data-qt-restart]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                window.QrintoTour.restart(el.getAttribute('data-qt-restart') || el.getAttribute('href'));
            });
        });
    }

    // Let Alpine/lucide settle and async sections (e.g. nearby stores) paint first.
    function ready(fn) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
        else fn();
    }
    ready(function () {
        wireRestart();
        setTimeout(boot, 550);
    });
})();
