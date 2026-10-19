// New sw.js - Updated by faris 29/2/2024

const cacheName = 'UGC-v1';
const staticAssets = [
    './',
    './index.php',
    './index.js',
];

self.addEventListener('install', async e => {
    const cache = await caches.open(cacheName);
    await cache.addAll(staticAssets);
    return self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.filter(name => name !== cacheName).map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    const req = e.request;
    const url = new URL(req.url);

    // - only cache same-origin requests
    if (url.origin === location.origin) {
        e.respondWith(cacheFirst(req));
    } else {
        // - allow other requests, such as for map tiles, to bypass the service worker
        return;
    }
});

async function cacheFirst(req) {
    const cache = await caches.open(cacheName);
    
    try {
        // - check if the request exists in the cache
        const cachedResponse = await cache.match(req);
        
        if (cachedResponse) {
            // - if the response is found in the cache, return it
            return cachedResponse;
        } else {
            // - if the response is not in the cache, fetch it from the network
            const networkResponse = await fetch(req.clone()); // clone the request - need to reclone cause of -e: FetchEvent resulted in a network error response: the promise was rejected
            
            // - check if the network response is valid
            if (networkResponse && networkResponse.ok) {
                // -clone the network response to store in the cache
                const clonedResponse = networkResponse.clone();
                
                // -put the cloned response into the cache
                await cache.put(req, clonedResponse);
            }
            
            // -return the network response
            return networkResponse;
        }
    } catch (error) {
        // - if got error occurs during caching or fetching, fallback to fetching from the network
        return fetch(req);
    }
}

async function networkAndCache(req) {
  const cache = await caches.open(cacheName);
  try {
    const fresh = await fetch(req);
    await cache.put(req, fresh.clone());
    return fresh;
  } catch (e) {
    const cached = await cache.match(req);
    return cached;
  }
}
