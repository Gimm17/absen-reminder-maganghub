import { ref } from 'vue'

/**
 * Subscribe browser untuk Web Push dgn VAPID public key.
 *
 * Param bisa berupa:
 *   - string key langsung, atau
 *   - getter function () => key  ← pakai ini kalau key datang async (store/reactive),
 *     supaya nilainya dibaca saat subscribe() dipanggil, bukan saat composable dibuat.
 *
 * Returns { subscribe(), unsubscribe(), permission }.
 */
export function usePushSubscription(vapidPublicKeyOrGetter) {
    const permission = ref(typeof Notification !== 'undefined' ? Notification.permission : 'default')

    const getKey = typeof vapidPublicKeyOrGetter === 'function'
        ? vapidPublicKeyOrGetter
        : () => vapidPublicKeyOrGetter

    /** Coba beberapa kali — key bisa belum termuat saat user klik cepat. */
    async function waitForKey(attempts = 10, delayMs = 200) {
        for (let i = 0; i < attempts; i++) {
            const k = getKey()
            if (k) return k
            await new Promise(r => setTimeout(r, delayMs))
        }
        return ''
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
        const raw = window.atob(base64)
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)))
    }

    async function subscribe() {
        if (! ('serviceWorker' in navigator)) throw new Error('Service Worker tidak didukung.')
        if (! ('PushManager' in window)) throw new Error('Push API tidak didukung di browser ini.')

        const vapidPublicKey = await waitForKey()
        if (! vapidPublicKey) throw new Error('VAPID public key belum dimuat. Coba refresh halaman.')

        const perm = await Notification.requestPermission()
        permission.value = perm
        if (perm !== 'granted') throw new Error('Izin notifikasi ditolak.')

        const registration = await navigator.serviceWorker.ready
        let subscription = await registration.pushManager.getSubscription()

        if (! subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            })
        }

        return subscription.toJSON()
    }

    async function unsubscribe() {
        const registration = await navigator.serviceWorker.ready
        const sub = await registration.pushManager.getSubscription()
        if (sub) await sub.unsubscribe()
    }

    return { subscribe, unsubscribe, permission }
}