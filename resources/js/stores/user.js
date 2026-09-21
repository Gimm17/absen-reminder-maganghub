import { defineStore } from 'pinia'

/** Slot reminder — satu sumber kebenaran untuk label, jam, dan ikon. */
export const SLOTS = [
    {
        key: 'slot-1',
        time: '16.30',
        short: 'Sore',
        title: 'Pengingat Sore',
        desc: 'Kesiapan laporan harian',
        icon: 'wb_twilight',
        flag: 'notify_slot_1',
        tone: 'amber',
    },
    {
        key: 'slot-2',
        time: '20.30',
        short: 'Malam',
        title: 'Pengingat Malam',
        desc: 'Evaluasi aktivitas magang',
        icon: 'bedtime',
        flag: 'notify_slot_2',
        tone: 'brand',
    },
    {
        key: 'slot-3',
        time: '23.00',
        short: 'Peringatan Terakhir',
        title: 'Peringatan Terakhir',
        desc: '1 jam sebelum penutupan',
        icon: 'notification_important',
        flag: 'notify_slot_3',
        tone: 'rose',
    },
]

/** Jam slot dalam menit sejak 00.00, untuk hitung status timeline. */
const SLOT_MINUTES = [16 * 60 + 30, 20 * 60 + 30, 23 * 60]

export const useUserStore = defineStore('user', {
    state: () => ({
        userId: null,
        name: '',
        email: '',
        isAdmin: false,
        checkedInToday: false,
        checkinTime: null,
        history: [],
        week: { checked: 0, expected: 0, rate: 0 },
        slots: ['slot-1', 'slot-2', 'slot-3'],
        vapidPublicKey: '',
        permission: typeof Notification !== 'undefined' ? Notification.permission : 'default',
        loading: false,
        /** Detik WITA saat komponen terakhir dimuat — dipakai countdown. */
        nowMinutes: 0,
    }),

    getters: {
        /** Waktu sekarang dalam menit (WITA), reaktif via nowMinutes. */
        now: (s) => s.nowMinutes,

        /** Status tiap slot: sent | next | upcoming. */
        timeline() {
            const now = this.now
            let nextAssigned = false

            return SLOTS.map((slot, i) => {
                const at = SLOT_MINUTES[i]
                const enabled = this.slots.includes(slot.key)
                let state

                if (! enabled) {
                    state = 'off'
                } else if (now >= at) {
                    state = 'sent'
                } else if (! nextAssigned) {
                    state = 'next'
                    nextAssigned = true
                } else {
                    state = 'upcoming'
                }

                let eta = null
                if (state === 'next') {
                    const diff = at - now
                    eta = diff < 60
                        ? `${diff} mnt`
                        : `${Math.floor(diff / 60)} jam ${diff % 60 ? (diff % 60) + ' mnt' : ''}`.trim()
                }

                return { ...slot, at, state, eta }
            })
        },

        /** Jumlah slot aktif, untuk badge header. */
        activeSlotCount() {
            return this.slots.length
        },
    },

    actions: {
        setUserId(id) {
            this.userId = id
            localStorage.setItem('reminder_absen_user_id', id)
        },

        clear() {
            this.userId = null
            localStorage.removeItem('reminder_absen_user_id')
        },

        /** Update jam sekarang (WITA) — dipanggil interval tiap 30 detik. */
        tick() {
            const nowUtc = new Date()
            const wita = new Date(nowUtc.getTime() + (8 * 60 + nowUtc.getTimezoneOffset()) * 60000)
            this.nowMinutes = wita.getHours() * 60 + wita.getMinutes()
        },

        async loadVapidKey() {
            const r = await fetch('/api/vapid-public-key')
            const j = await r.json()
            this.vapidPublicKey = j.publicKey
        },

        async loadProfile() {
            if (! this.userId) return
            try {
                const r = await fetch(`/api/user?user_id=${this.userId}`)
                if (! r.ok) return
                const j = await r.json()
                this.name = j.name ?? this.name
                this.email = j.email ?? this.email
                this.slots = j.slots ?? this.slots
            } catch (e) {
                console.warn('loadProfile failed', e)
            }
        },

        async refreshToday() {
            if (! this.userId) return
            this.loading = true
            try {
                const [todayRes, histRes] = await Promise.all([
                    fetch(`/api/checkin/today?user_id=${this.userId}`),
                    fetch(`/api/checkin/history?user_id=${this.userId}&days=5`),
                ])

                if (todayRes.ok) {
                    const j = await todayRes.json()
                    this.checkedInToday = j.has_checked_in
                    this.checkinTime = j.checkin?.reported_at ?? null
                }

                if (histRes.ok) {
                    const h = await histRes.json()
                    this.history = h.history ?? []
                    this.week = h.week ?? this.week
                }
            } catch (e) {
                console.warn('refreshToday failed', e)
            } finally {
                this.loading = false
            }
        },

        async updateEmail(email) {
            const r = await fetch('/api/user/email', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: this.userId, email }),
            })
            const j = await r.json()
            if (! r.ok) {
                throw new Error(j.message || j.error || 'Gagal menyimpan email.')
            }
            this.email = j.email
            return j
        },
    },
})