const CACHE_NAME = 'human-staff-v2';
const STATIC_ASSETS = [
    '/manifest-waiter.json',
    '/icons/waiter-app-icon.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    const isStaffArea = url.pathname.startsWith('/admin') || url.pathname.startsWith('/waiter');
    const isStaticAsset = url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/');

    if (!isStaffArea && !isStaticAsset) return;

    const isBuildAsset = url.pathname.startsWith('/build/');

    // Build dosyalarında once ag dene (F5'te en guncel asset), sonra cache'e dus.
    if (isBuildAsset) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // Diger staff sayfalari/ikonlar icin cache-first.
    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;

            return fetch(request).then((response) => {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                return response;
            });
        })
    );
});
