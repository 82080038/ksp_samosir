// Service Worker for KSP Samosir
// Implements caching strategies for optimal performance

const CACHE_NAME = 'ksp-v1.0.0';
const STATIC_CACHE = 'ksp-static-v1.0.0';
const DYNAMIC_CACHE = 'ksp-dynamic-v1.0.0';

// Resources to cache immediately
const STATIC_ASSETS = [
    '/',
    '/ksp_samosir/public/assets/css/style-blue.min.css',
    '/ksp_samosir/public/assets/js/ksp-ajax.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(error => {
                console.error('Service Worker: Failed to cache static assets', error);
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                        console.log('Service Worker: Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests and external domains
    if (request.method !== 'GET' || !url.origin.includes('ksp_samosir')) {
        return;
    }

    // API requests - Network first, then cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Static assets - Cache first, then network
    if (isStaticAsset(request.url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // HTML pages - Network first, then cache
    if (request.headers.get('Accept').includes('text/html')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Images - Cache first with fallback
    if (request.destination === 'image') {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Default - Network first
    event.respondWith(networkFirst(request));
});

// Cache-first strategy for static assets
function cacheFirst(request) {
    return caches.match(request)
        .then(response => {
            if (response) {
                return response;
            }

            return fetch(request)
                .then(response => {
                    // Don't cache non-successful responses
                    if (!response.ok) {
                        return response;
                    }

                    // Clone the response for caching
                    const responseClone = response.clone();

                    caches.open(DYNAMIC_CACHE)
                        .then(cache => {
                            cache.put(request, responseClone);
                        });

                    return response;
                })
                .catch(() => {
                    // Return offline fallback if available
                    if (request.destination === 'document') {
                        return caches.match('/offline.html');
                    }
                });
        });
}

// Network-first strategy for dynamic content
function networkFirst(request) {
    return fetch(request)
        .then(response => {
            // Clone the response for caching
            const responseClone = response.clone();

            // Cache successful responses
            if (response.ok) {
                caches.open(DYNAMIC_CACHE)
                    .then(cache => {
                        cache.put(request, responseClone);
                    });
            }

            return response;
        })
        .catch(() => {
            // Return cached version if network fails
            return caches.match(request)
                .then(response => {
                    if (response) {
                        return response;
                    }

                    // Return offline page for navigation requests
                    if (request.mode === 'navigate') {
                        return caches.match('/offline.html');
                    }
                });
        });
}

// Check if request is for static assets
function isStaticAsset(url) {
    const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.woff', '.woff2'];
    return staticExtensions.some(ext => url.includes(ext));
}

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

// Push notifications
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();

        const options = {
            body: data.body,
            icon: '/ksp_samosir/public/assets/images/icon-192x192.png',
            badge: '/ksp_samosir/public/assets/images/badge-72x72.png',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: data.primaryKey
            },
            actions: [
                {
                    action: 'view',
                    title: 'View Details',
                    icon: '/ksp_samosir/public/assets/images/view-action.png'
                },
                {
                    action: 'dismiss',
                    title: 'Dismiss',
                    icon: '/ksp_samosir/public/assets/images/dismiss-action.png'
                }
            ]
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        // Open the app and navigate to relevant page
        event.waitUntil(
            clients.openWindow('/ksp_samosir/dashboard')
        );
    } else if (event.action === 'dismiss') {
        // Just dismiss the notification
        return;
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.openWindow('/ksp_samosir/dashboard')
        );
    }
});

// Background sync implementation
function doBackgroundSync() {
    return Promise.all([
        // Sync offline forms
        syncOfflineForms(),
        // Sync offline actions
        syncOfflineActions(),
        // Update cached data
        updateCachedData()
    ]);
}

function syncOfflineForms() {
    // Implementation for syncing offline form submissions
    return Promise.resolve();
}

function syncOfflineActions() {
    // Implementation for syncing offline user actions
    return Promise.resolve();
}

function updateCachedData() {
    // Update cached reference data
    return Promise.resolve();
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', event => {
    if (event.tag === 'content-sync') {
        event.waitUntil(updateContent());
    }
});

function updateContent() {
    // Update cached content periodically
    return caches.open(DYNAMIC_CACHE)
        .then(cache => {
            // Update frequently changing content
            return cache.addAll([
                '/api/notifications',
                '/api/updates'
            ]);
        });
}

// Message handling for communication with main thread
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.keys().then(names => {
            names.forEach(name => {
                caches.delete(name);
            });
        });
        event.ports[0].postMessage({ success: true });
    }
});
