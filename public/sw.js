/**
 * SwiftKudi Service Worker — Web Push notifications
 * Registered at the root scope so it covers all pages.
 */

const CACHE_NAME = 'swiftkudi-v1';
const APP_ICON   = '/favicon.svg';
const DEFAULT_BADGE = '/favicon.ico';

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
        icon:    data.icon    || APP_ICON,
        badge:   data.badge   || DEFAULT_BADGE,
        tag:     data.tag     || 'swiftkudi-push',
        renotify: true,
        data: {
            url: data.url || '/',
        },
        actions: data.actions || [],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ─── Notification click ──────────────────────────────────────────────────────
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : '/';

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
