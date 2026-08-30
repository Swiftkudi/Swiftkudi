/**
 * SwiftKudi Service Worker — Web Push notifications
 * Registered at the root scope so it covers all pages.
 */

// ─── Push event ──────────────────────────────────────────────────────────────
self.addEventListener('push', function (event) {
    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (_) {
            data = { title: 'SwiftKudi', body: event.data.text() };
        }
    }

    const title   = data.title  || 'SwiftKudi';
    const options = {
        body:    data.body    || 'You have a new notification.',
        icon:    data.icon    || '/favicon.svg',
        tag:     data.tag     || 'swiftkudi-' + Date.now(),
        requireInteraction: false,
        data: {
            url: data.url || '/dashboard',
        },
    };


    event.waitUntil(
self.registration.showNotification(title, options)    );
});

// ─── Notification click ──────────────────────────────────────────────────────
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const rawTarget = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/';
    let targetUrl = '/';
    try {
        const parsed = new URL(rawTarget, self.location.origin);
        if (parsed.origin === self.location.origin) {
            targetUrl = parsed.pathname + parsed.search + parsed.hash;
        }
    } catch (_) {}

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // Focus existing tab if already open
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            // Otherwise open a new tab
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

// ─── Install & Activate (minimal caching) ────────────────────────────────────
self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(clients.claim());
});
