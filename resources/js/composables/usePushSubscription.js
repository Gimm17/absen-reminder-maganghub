import { ref } from 'vue'

/**
 * Subscribe browser untuk Web Push dgn VAPID public key.
 * Returns { subscribe(), unsubscribe(), permission }.
 */
export function usePushSubscription(vapidPublicKey) {
    const permission = ref(typeof Notification !== 'undefined' ? Notification.permission : 'default')

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
        const raw = window.atob(base64)
        return Uint8Array.from([...raw].map(c => c.charCodeAt(0)))
    }

    async function subscribe() {
        if (! ('serviceWorker' in navigator)) throw new Error('Service Worker tidak didukung.')
        if (! ('PushManager' in window)) throw new Error('Push API tidak didukung di browser ini.')
        if (! vapidPublicKey) throw new Error('VAPID public key belum dimuat.')

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