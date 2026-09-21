/* Service Worker untuk Reminder Absen MagangHub PWA
 * Handle push event + notificationclick + simple caching.
 */

const CACHE_NAME = 'reminder-absen-v2'
const ASSETS = ['/', '/dashboard', '/manifest.webmanifest']

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS)).catch(() => {})
    )
    self.skipWaiting()
})

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    )
    self.clients.claim()
})

// === Push handler ===
self.addEventListener('push', (event) => {
    let data = {}
    try {
        data = event.data ? event.data.json() : {}
    } catch (e) {
        data = { title: 'Reminder', body: event.data?.text() || '' }
    }

    const title = data.title || '⏰ Reminder Absen'
    const options = {
        body: data.body || '',
        icon: data.icon || '/icons/icon-192.png',
        badge: data.badge || '/icons/icon-72.png',
        tag: data.tag || 'reminder',
        renotify: !!data.renotify,
        requireInteraction: !!data.requireInteraction,
        data: data.data || {},
        actions: (data.data?.actions) || [
            { action: 'open-dashboard', title: 'Buka Dashboard' },
            { action: 'checkin', title: '✅ Sudah Absen' },
        ],
    }

    event.waitUntil(self.registration.showNotification(title, options))
})

// === Notification click handler ===
self.addEventListener('notificationclick', (event) => {
    event.notification.close()

    const action = event.action
    const data = event.notification.data || {}
    const dashboardUrl = data.url || 'https://monev.maganghub.kemnaker.go.id/dashboard/riwayat'
    const appUrl = new URL('/dashboard', self.location.origin).toString()

    if (action === 'checkin') {
        // Kirim POST checkin ke server lewat fetch — best-effort, no blocking.
        event.waitUntil(
            fetch('/api/checkin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ source: 'button' }),
            }).catch(() => {})
        )
    }

    // Buka dashboard (external) untuk action open-dashboard, atau app PWA untuk no-action.
    const targetUrl = action === 'open-dashboard' ? dashboardUrl : appUrl
    const windowType = action === 'open-dashboard' ? '_blank' : '_self'

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((wins) => {
            // Kalau sudah ada tab dashboard eksternal terbuka, focus.
            for (const c of wins) {
                if (c.url.startsWith('https://monev.maganghub.kemnaker.go.id') && action === 'open-dashboard') {
                    return c.focus()
                }
            }
            return clients.openWindow(targetUrl)
        })
    )
})

// === Minimal fetch caching ===
// Network-first untuk SEMUA request same-origin, fallback ke cache saat offline.
// Sengaja network-first (bukan cache-first) supaya deploy asset baru gak ketahan cache basi.
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url)
    if (url.origin !== self.location.origin) return
    if (event.request.method !== 'GET') return
    // Jangan cache endpoint API — selalu live.
    if (url.pathname.startsWith('/api/')) return

    event.respondWith(
        fetch(event.request).then((resp) => {
            // Simpan copy ke cache untuk fallback offline.
            if (resp.ok) {
                const copy = resp.clone()
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy)).catch(() => {})
            }
            return resp
        }).catch(() =>
            caches.match(event.request).then((cached) =>
                cached || (event.request.mode === 'navigate' ? caches.match('/') : undefined)
            )
        )
    )
})