<style>
    /* ── Hero Header & Workspace ── */
    .hero-cust-gradient {
        background: #FEF5F1;
    }
    .hero-glass-card {
        background: rgba(254, 245, 241, 0.95);
        box-shadow: 0 20px 40px -15px rgba(214, 95, 50, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
    }
    @keyframes spinSlow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spinSlow 12s linear infinite;
    }
    .hero-cust-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(236,72,153,0.04) 1px, transparent 0);
        background-size: 32px 32px;
    }
    .hero-blob-1 {
        position: absolute; top: -60px; right: 15%; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
        border-radius: 50%; filter: blur(40px); pointer-events: none;
    }
    .hero-blob-2 {
        position: absolute; bottom: -40px; right: 5%; width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(249, 168, 212, 0.2) 0%, transparent 70%);
        border-radius: 50%; filter: blur(30px); pointer-events: none;
    }
    .hero-blob-3 {
        position: absolute; top: 20%; right: 35%; width: 80px; height: 80px;
        background: rgba(236, 72, 153, 0.15); border-radius: 50%; filter: blur(10px); pointer-events: none;
    }
    .hero-dots {
        position: absolute; top: 10%; right: 3%; width: 80px; height: 80px;
        background-image: radial-gradient(circle, rgba(236,72,153,0.2) 2px, transparent 2px);
        background-size: 10px 10px; border-radius: 50%; pointer-events: none;
    }

    .cust-page { display: grid; grid-template-columns: 1fr 420px; gap: 32px; align-items: start; }
    @media (max-width: 1024px) { .cust-page { grid-template-columns: 1fr; } }
    .cust-breadcrumb a { transition: color 0.2s ease; }

    /* Canvas */
    .canvas-wrapper { position: relative; background: #fff; border-radius: 1rem; overflow: hidden; touch-action: none; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 8px 24px -4px rgba(0,0,0,0.06); }
    .canvas-hidden { display: none !important; }
    .canvas-disabled-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.05); z-index: 200; pointer-events: none; border-radius: 1rem; }
    .canvas-wrapper:has(.canvas-disabled-overlay) { cursor: not-allowed; }
    .canvas-container { z-index: 100; touch-action: none; }
    .hidden { display: none !important; }

    /* Ready-made Template Strip & Category Filter */
    .template-strip {
        display: flex; gap: 8px; overflow-x: auto; padding: 6px 4px 2px;
        -webkit-overflow-scrolling: touch; scrollbar-width: none; flex-wrap: wrap;
    }
    .template-strip::-webkit-scrollbar { display: none; }
    .template-chip {
        flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 9999px; background: #f1f5f9;
        border: 1px solid #e2e8f0; font-size: 12px; font-weight: 700;
        color: #334155; cursor: pointer; transition: all .2s; white-space: nowrap;
    }
    .template-chip:hover { background: #e2e8f0; }
    .template-chip:active { transform: scale(.95); }
    .template-chip i { width: 14px; height: 14px; color: #D65F32; }
    .template-cat-filter {
        display: flex; gap: 6px; overflow-x: auto; padding: 4px 4px 2px;
        scrollbar-width: none; -webkit-overflow-scrolling: touch;
    }
    .template-cat-filter::-webkit-scrollbar { display: none; }
    .template-cat-chip {
        flex: 0 0 auto; padding: 5px 13px; border-radius: 9999px; font-size: 11px;
        font-weight: 700; border: 1.5px solid #e2e8f0; background: #f8fafc;
        color: #64748b; cursor: pointer; transition: all .2s; white-space: nowrap;
    }
    .template-cat-chip:hover { background: #e2e8f0; }
    .template-cat-chip.active { background: #D65F32; border-color: #D65F32; color: #fff; }
</style>
