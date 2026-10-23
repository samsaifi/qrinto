<style>
    /* ── Mobile customizer overrides ── */
    @media (max-width: 767px) {
        /* Hide floating docks — replaced by fixed bottom bar */
        .cust-left-dock, .cust-right-dock { display: none !important; }

        /* Hide heavy toolbar sections on mobile */
        .cust-toolbar-align, .cust-toolbar-rotate, .cust-toolbar-zoom { display: none !important; }

        /* Breadcrumb: show compact mobile version */
        .cust-breadcrumb-desktop { display: none !important; }
        .cust-breadcrumb-mobile { display: flex !important; }

        /* Workspace: minimal padding, room for bottom bar */
        .cust-workspace { padding: 4px 6px 100px 6px !important; min-height: 100vh !important; min-height: 100dvh !important; }
        .cust-workspace-inner { min-height: auto !important; }

        /* Toolbar: compact inline row */
        .cust-toolbar {
            padding: 4px 6px !important;
            border-radius: 12px !important;
            gap: 1px !important;
            justify-content: center !important;
            max-width: 100% !important;
            overflow-x: auto !important;
        }
        .cust-toolbar button, .cust-toolbar a {
            padding: 5px !important;
            min-width: 32px !important;
        }
        .cust-toolbar button svg, .cust-toolbar a svg,
        .cust-toolbar button i, .cust-toolbar a i {
            width: 16px !important;
            height: 16px !important;
        }

        /* Canvas: fill width */
        .canvas-wrapper {
            border-radius: 10px !important;
            max-width: calc(100vw - 12px) !important;
            margin: 0 auto !important;
        }

        /* Canvas stage: tighter vertical */
        #canvas-stage { padding-top: 4px !important; padding-bottom: 4px !important; }

        /* Header: compact on mobile */
        header .flex.h-20 { height: 48px !important; }
        header img.h-10, header img.sm\:h-12 { height: 28px !important; }

        /* Customizer app: fill screen */
        #customizer-app { min-height: 100vh !important; min-height: 100dvh !important; }

        /* Mobile page switcher */
        .cust-mobile-pages { display: flex !important; gap: 6px; padding: 4px 8px; justify-content: center; flex-wrap: wrap; }
    }

    /* Mobile page switcher buttons */
    .cust-mobile-page-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 6px 14px; border-radius: 9999px;
        background: #f8fafc; border: 1.5px solid #e2e8f0;
        font-size: 11px; font-weight: 700; color: #64748b;
        cursor: pointer; transition: all .15s; position: relative;
    }
    .cust-mobile-page-btn.active {
        background: #287d3c; border-color: #287d3c; color: #fff;
        box-shadow: 0 2px 8px rgba(40,125,60,0.25);
    }
    .cust-mobile-page-btn.active svg { stroke: #fff; }
    @media (min-width: 768px) {
        .cust-mobile-pages { display: none !important; }
    }
    @media (min-width: 768px) {
        .cust-breadcrumb-mobile { display: none !important; }
        .cust-breadcrumb-desktop { display: flex !important; }
    }

    /* ── Toolbar tooltip (fixed pill, never clipped by overflow) ── */
    #tt-pop {
        position: fixed;
        z-index: 9999;
        background: #0f172a;
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        line-height: 1;
        padding: 6px 8px;
        border-radius: 8px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity .12s ease;
        box-shadow: 0 8px 20px -6px rgba(0, 0, 0, .4);
        transform: translateX(-50%);
    }

    #tt-pop.show {
        opacity: 1;
    }

    #tt-pop::before {
        content: "";
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-bottom-color: #0f172a;
    }

    /* ── Shape Mask Library: active (applied) shape indicator ── */
    .shape-mask-btn.shape-mask-active {
        border-color: #10b981;
        /* emerald-500 - matches drawer accent */
        border-width: 2px;
        background-color: #ecfdf5;
        /* emerald-50 */
        box-shadow: 0 6px 16px -4px rgba(16, 185, 129, 0.45);
        transform: translateY(-1px);
    }

    .shape-mask-btn.shape-mask-active>div,
    .shape-mask-btn.shape-mask-active i[data-lucide] {
        color: #059669;
        background-color: #059669;
        /* solid-fill thumbnails (rect/circle/etc.) turn accent-green when active */
    }

    .shape-mask-btn.shape-mask-active .shape-mask-check>div,
    .shape-mask-btn.shape-mask-active .shape-mask-check i[data-lucide] {
        background-color: transparent;
        color: #fff;
    }

    .shape-mask-btn.shape-mask-active>span {
        color: #047857 !important;
    }

    /* ── Hero Header & Workspace ── */
    .hero-cust-gradient {
        background: #FEF5F1;
    }

    .hero-glass-card {
        background: rgba(254, 245, 241, 0.95);
        box-shadow: 0 20px 40px -15px rgba(214, 95, 50, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
    }

    @keyframes spinSlow {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin-slow {
        animation: spinSlow 12s linear infinite;
    }

    .hero-cust-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(236, 72, 153, 0.04) 1px, transparent 0);
        background-size: 32px 32px;
    }

    .hero-blob-1 {
        position: absolute;
        top: -60px;
        right: 15%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
        pointer-events: none;
    }

    .hero-blob-2 {
        position: absolute;
        bottom: -40px;
        right: 5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(249, 168, 212, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(30px);
        pointer-events: none;
    }

    .hero-blob-3 {
        position: absolute;
        top: 20%;
        right: 35%;
        width: 80px;
        height: 80px;
        background: rgba(236, 72, 153, 0.15);
        border-radius: 50%;
        filter: blur(10px);
        pointer-events: none;
    }

    .hero-dots {
        position: absolute;
        top: 10%;
        right: 3%;
        width: 80px;
        height: 80px;
        background-image: radial-gradient(circle, rgba(236, 72, 153, 0.2) 2px, transparent 2px);
        background-size: 10px 10px;
        border-radius: 50%;
        pointer-events: none;
    }

    .cust-page {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .cust-page {
            grid-template-columns: 1fr;
        }
    }

    .cust-breadcrumb a {
        transition: color 0.2s ease;
    }

    /* Canvas */
    .canvas-wrapper {
        position: relative;
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
        touch-action: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
    }

    .canvas-hidden {
        display: none !important;
    }

    .canvas-disabled-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.05);
        z-index: 200;
        pointer-events: none;
        border-radius: 1rem;
    }

    .canvas-wrapper:has(.canvas-disabled-overlay) {
        cursor: not-allowed;
    }

    .canvas-container {
        z-index: 100;
        touch-action: none;
    }

    .hidden {
        display: none !important;
    }

    /* Ready-made Template Strip & Category Filter */
    .template-strip {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 6px 4px 2px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        flex-wrap: wrap;
    }

    .template-strip::-webkit-scrollbar {
        display: none;
    }

    .template-chip {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 9999px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
    }

    .template-chip:hover {
        background: #e2e8f0;
    }

    .template-chip:active {
        transform: scale(.95);
    }

    .template-chip i {
        width: 14px;
        height: 14px;
        color: var(--color-brand-500)
    }

    .template-cat-filter {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 4px 4px 2px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    .template-cat-filter::-webkit-scrollbar {
        display: none;
    }

    .template-cat-chip {
        flex: 0 0 auto;
        padding: 5px 13px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #0ea5e9;
        cursor: pointer;
        transition: all .2s;
        white-space: nowrap;
    }

    .template-cat-chip:hover {
        background: #e2e8f0;
    }

    .template-cat-chip.active {
        background: var(--color-brand-500);
        border-color: var(--color-brand-500);
        color: #fff;
    }
</style>
