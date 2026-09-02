// Qrinto Service Worker - offline shell caching only
const CACHE = 'qrinto-v1';
const SHELL = [
    '/',
    '/manifest.json',
    '/logo/Qrinto-logo-small.png',
    '/logo/Qrinto-logo-med.png',
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Network-first strategy - always fresh content, fallback to cache
self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    const url = new URL(e.request.url);
    // Only handle same-origin requests
    if (url.origin !== location.origin) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                // Cache successful responses for shell assets
                if (res.ok && SHELL.includes(url.pathname)) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            })
            .catch(() => caches.match(e.request))
    );
});
