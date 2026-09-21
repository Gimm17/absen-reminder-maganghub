/* Service Worker — Reminder Absen MagangHub PWA
 *
 * PENTING soal suara: Notification API tidak menyediakan opsi `sound`.
 * Suara selalu suara notifikasi sistem. Yang bisa diatur hanya `vibrate`
 * (pola getar) dan `silent`. Jangan tambahkan `sound:` — diabaikan diam-diam.
 */

const CACHE_NAME = 'reminder-absen-v3'
const ASSETS = ['/', '/dashboard', '/manifest.webmanifest']

const DEFAULT_BADGE = '/icons/icon-96.png'
const DEFAULT_ICON = '/icons/icon-192.png'

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
        data = { title: 'Reminder Absen', body: event.data ? event.data.text() : '' }
    }

    const title = data.title || 'Reminder Absen MagangHub'

    const options = {
        body: data.body || '',
        icon: data.icon || DEFAULT_ICON,
        badge: data.badge || DEFAULT_BADGE,
        tag: data.tag || 'reminder-absen',
        renotify: data.renotify !== false,
        requireInteraction: !!data.requireInteraction,
        silent: data.silent === true,
        timestamp: data.timestamp || Date.now(),
        data: data.data || {},
        actions: (data.data && data.data.actions) || [
            { action: 'checkin', title: '✅ Sudah Absen' },
            { action: 'open-dashboard', title: 'Buka Portal' },
        ],
    }

    // vibrate tidak boleh ada bersamaan dengan silent: true (TypeError).
    if (! options.silent && Array.isArray(data.vibrate) && data.vibrate.length) {
        options.vibrate = data.vibrate
    }

    // image opsional — hanya kalau diisi.
    if (data.image) options.image = data.image

    event.waitUntil(self.registration.showNotification(title, options))
})

// === Notification click handler ===
self.addEventListener('notificationclick', (event) => {
    event.notification.close()

    const action = event.action
    const data = event.notification.data || {}
    const dashboardUrl = data.url
        || 'https://monev.maganghub.kemnaker.go.id/dashboard/riwayat'
    const appUrl = new URL('/dashboard', self.location.origin).toString()

    // "Sudah Absen" -> catat, lalu buka app supaya user lihat status terbaru.
    if (action === 'checkin') {
        event.waitUntil(markCheckedInAndOpen(appUrl, data.token))
        return
    }

    // "Buka Portal" -> tab baru ke dashboard resmi.
    if (action === 'open-dashboard') {
        event.waitUntil(openOrFocus(dashboardUrl, true))
        return
    }

    // Tap body notifikasi -> buka app PWA.
    event.waitUntil(openOrFocus(appUrl, false))
})

/**
 * Catat absen lewat API, lalu fokus/buka app.
 * SW tidak punya akses localStorage, jadi identitas dikirim lewat
 * device_token yg disertakan server di dalam payload push.
 */
async function markCheckedInAndOpen(appUrl, token) {
    if (! token) {
        // Tidak ada token (mis. push lama) — tetap buka app, user catat manual.
        return openOrFocus(appUrl, false)
    }

    try {
        await fetch('/api/checkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ device_token: token, source: 'notification' }),
        })
    } catch (e) {
        // Best-effort — user tetap dibawa ke app untuk konfirmasi manual.
    }
    return openOrFocus(appUrl, false)
}

/** Fokus tab yg cocok kalau ada, kalau tidak buka baru. */
async function openOrFocus(url, preferNewTab) {
    const wins = await clients.matchAll({ type: 'window', includeUncontrolled: true })

    const origin = new URL(url).origin
    for (const c of wins) {
        if (c.url.startsWith(origin) && 'focus' in c) {
            return c.focus()
        }
    }

    return clients.openWindow(url)
}

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