/**
 * Local-print audit logging (client side).
 *
 * Shared by /print/pdf/check and /print/own-design/…/preview. Wraps the
 * existing pp.print() call so every attempt is recorded server-side WITHOUT
 * changing the print payload or output.
 *
 * Usage inside a page's doPrint():
 *
 *   const job = await LPLog.start(this.buildLogPayload(cfg));
 *   try {
 *     const resp = await pp.print(printer, b64, cfg);
 *     await LPLog.finish(job, { ok: true, response: resp });
 *   } catch (e) {
 *     await LPLog.finish(job, { ok: false, error: e.message });
 *     throw e;
 *   }
 *
 * Both calls fail SOFT: a logging error never blocks or breaks the print.
 */
(function () {
    function csrf() {
        return (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    }

    async function post(url, body) {
        const r = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body || {}),
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return await r.json();
    }

    // Base URLs. The app can live under a sub-path (e.g. /qrinto), so a plain
    // "/print/log/start" would resolve to the wrong host root. The page sets
    // window.LPLOG_ROUTES from Laravel's route() helper; fall back to relative.
    function startUrl() {
        return (window.LPLOG_ROUTES && window.LPLOG_ROUTES.start) || 'print/log/start';
    }
    function completeUrl(id) {
        var base = (window.LPLOG_ROUTES && window.LPLOG_ROUTES.completeBase) || 'print/log';
        return base.replace(/\/$/, '') + '/' + encodeURIComponent(id) + '/complete';
    }
    function fileUrl(id) {
        var base = (window.LPLOG_ROUTES && window.LPLOG_ROUTES.completeBase) || 'print/log';
        return base.replace(/\/$/, '') + '/' + encodeURIComponent(id) + '/file';
    }
    function b64ToBlob(b64, type) {
        var clean = String(b64 || '').replace(/^data:[^;]+;base64,/, '');
        var bin = atob(clean);
        var bytes = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
        return new Blob([bytes], { type: type || 'application/pdf' });
    }

    window.LPLog = {
        // Create the log row. Returns { id, t0 } — a token passed back to finish().
        // Never throws: on failure returns { id: null } so printing continues.
        async start(payload) {
            const t0 = (window.performance && performance.now) ? performance.now() : Date.now();
            try {
                const json = await post(startUrl(), payload);
                return { id: (json && json.id) || null, t0: t0 };
            } catch (e) {
                console.info('[local-print-log] start failed (non-fatal):', e && e.message);
                return { id: null, t0: t0 };
            }
        },

        // Save the exact print-ready PDF (base64) to this log row. Sent as
        // MULTIPART (binary) so a large PDF never trips the web server's body-size
        // limit (HTTP 413). Never throws — a save error must not break the print.
        async attachPdf(job, base64) {
            if (!job || !job.id || !base64) return;
            try {
                var fd = new FormData();
                fd.append('pdf', b64ToBlob(base64, 'application/pdf'), 'print.pdf');
                await fetch(fileUrl(job.id), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });
            } catch (e) {
                console.info('[local-print-log] attachPdf failed (non-fatal):', e && e.message);
            }
        },

        // Update the same row with the outcome. `job` is what start() returned.
        // result: { ok, error?, response?, job_id? }. Never throws.
        async finish(job, result) {
            if (!job || !job.id) return;
            const now = (window.performance && performance.now) ? performance.now() : Date.now();
            const duration = Math.max(0, Math.round(now - (job.t0 || now)));
            try {
                await post(completeUrl(job.id), {
                    ok: !!(result && result.ok),
                    error: (result && result.error) ? String(result.error) : null,
                    response: (result && result.response !== undefined) ? result.response : null,
                    job_id: (result && result.job_id) ? String(result.job_id) : null,
                    duration_ms: duration,
                });
            } catch (e) {
                console.info('[local-print-log] finish failed (non-fatal):', e && e.message);
            }
        },
    };
})();
