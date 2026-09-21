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

/**
 * WITA = UTC+8, offset TETAP. Jangan pakai getTimezoneOffset() device —
 * itu membuat hasilnya bergantung timezone mesin, bukan WITA.
 */
const WITA_OFFSET_SECONDS = 8 * 3600
const SECONDS_PER_DAY = 86400

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
        /** Detik epoch ASLI (UTC) — satu sumber waktu untuk seluruh app. */
        nowSeconds: 0,
    }),

    getters: {
        /**
         * Detik sejak 00.00 WITA (0–86399).
         * Epoch asli digeser +8 jam SEKALI di sini; getter lain memakai nilai ini,
         * jadi offset tidak pernah terhitung dua kali.
         */
        secondsOfDay: (s) => ((s.nowSeconds + WITA_OFFSET_SECONDS) % SECONDS_PER_DAY + SECONDS_PER_DAY) % SECONDS_PER_DAY,

        /**
         * Waktu sekarang dalam MENIT sejak 00.00 WITA.
         * Dibandingkan dengan jam slot di `timeline`.
         */
        now() {
            return Math.floor(this.secondsOfDay / 60)
        },

        /** Detik berjalan sejak 00.00 WITA, untuk animasi halus. */
        secondOfMinute() {
            return this.secondsOfDay % 60
        },

        /** Jam:menit:detik WITA (HH:MM:SS) — dipakai header & hero. */
        clockHms() {
            const s = this.secondsOfDay
            const p = (n) => String(n).padStart(2, '0')
            return `${p(Math.floor(s / 3600))}:${p(Math.floor(s / 60) % 60)}:${p(s % 60)}`
        },

        /** Jam:menit WITA (HH:MM) — dipakai label ringkas. */
        clockHm() {
            const s = this.secondsOfDay
            const p = (n) => String(n).padStart(2, '0')
            return `${p(Math.floor(s / 3600))}:${p(Math.floor(s / 60) % 60)}`
        },

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

                // ETA dihitung dari detik (bukan menit) supaya hitung mundur
                // ikut berjalan tiap detik, tidak beku di satu angka.
                let eta = null
                let etaSeconds = null
                if (state === 'next') {
                    etaSeconds = Math.max(0, at * 60 - this.secondsOfDay)
                    const mins = Math.floor(etaSeconds / 60)
                    const h = Math.floor(mins / 60)
                    const m = mins % 60
                    eta = h > 0 ? `${h} jam ${m} mnt` : (m > 0 ? `${m} mnt` : `${etaSeconds} dtk`)
                }

                return { ...slot, at, state, eta, etaSeconds }
            })
        },

        /** Jumlah slot aktif, untuk badge header. */
        activeSlotCount() {
            return this.slots.length
        },

        /**
         * Sisa waktu sampai tengah malam WITA, dalam menit.
         * Dipakai untuk "sisa waktu hari ini" dan chip countdown.
         */
        remainingToMidnight() {
            return Math.max(0, Math.floor((SECONDS_PER_DAY - this.secondsOfDay) / 60))
        },

        /**
         * Sisa detik sampai slot berikutnya — untuk hitung mundur yang halus.
         * null kalau tidak ada slot tersisa hari ini.
         */
        secondsToNextSlot() {
            const next = this.timeline.find(t => t.state === 'next')
            if (! next) return null
            return Math.max(0, next.at * 60 - this.secondsOfDay)
        },

        /** Sisa waktu ke slot berikutnya, format "2 jam 15 mnt" / "47 mnt". */
        nextSlotLabel() {
            const s = this.secondsToNextSlot
            if (s === null) return null
            const mins = Math.floor(s / 60)
            const h = Math.floor(mins / 60)
            const m = mins % 60
            if (h > 0) return `${h} jam ${m} mnt`
            return `${m} mnt`
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

        /**
         * Segerakkan jam WITA. Dipanggil tiap detik oleh satu interval global.
         * Membulatkan ke detik supaya angka tidak berkedip di antara tick.
         */
        tick() {
            this.nowSeconds = Math.floor(Date.now() / 1000)
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