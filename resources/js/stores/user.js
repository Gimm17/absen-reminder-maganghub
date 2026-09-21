import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
    state: () => ({
        userId: null,
        name: '',
        email: '',
        phone: '',
        isAdmin: false,
        checkedInToday: false,
        checkinTime: null,
        vapidPublicKey: '',
        permission: typeof Notification !== 'undefined' ? Notification.permission : 'default',
    }),
    actions: {
        setUserId(id) { this.userId = id; localStorage.setItem('reminder_absen_user_id', id) },
        clear() { this.userId = null; localStorage.removeItem('reminder_absen_user_id') },
        async loadVapidKey() {
            const r = await fetch('/api/vapid-public-key')
            const j = await r.json()
            this.vapidPublicKey = j.publicKey
        },
        async refreshToday() {
            if (! this.userId) return
            try {
                const r = await fetch(`/api/checkin/today?user_id=${this.userId}`)
                const j = await r.json()
                this.checkedInToday = j.has_checked_in
                this.checkinTime = j.checkin?.reported_at ?? null
            } catch (e) {
                console.warn('refreshToday failed', e)
            }
        },
    },
})