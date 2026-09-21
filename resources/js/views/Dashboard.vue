<template>
  <div class="flex flex-col gap-4 lg:gap-6">

    <!-- ==================== DESKTOP: Hero ==================== -->
    <section class="hidden lg:flex items-start justify-between gap-6 reveal">
      <div>
        <span class="pill px-3 py-1.5 text-[11px] bg-brand-50 text-brand-700 border border-brand-100 mb-3">
          <span class="live-dot"></span>
          Notifikasi Terjadwal Aktif • Batch 1 — 2026
        </span>
        <h1 class="text-3xl font-bold text-slate-900 tracking-editorial leading-tight">
          Halo, {{ firstName }}!
        </h1>
        <p class="text-[15px] text-slate-500 mt-1.5">
          Sistem pengingat harian otomatis aktif untuk memastikan kehadiran Anda tercatat tepat waktu.
        </p>
      </div>

      <div class="card px-4 py-3 flex items-center gap-3 shrink-0">
        <span class="material-symbols-outlined text-[22px] text-brand-600">schedule</span>
        <div>
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Zona Waktu Aktif</div>
          <div class="tabular text-xl font-bold text-slate-900 leading-tight">
            {{ store.clockHms }}
            <span class="text-[11px] text-slate-500 font-semibold">WITA (UTC+8)</span>
          </div>
          <div v-if="nextSlotCountdown" class="tabular text-[11px] font-semibold text-brand-700 mt-0.5 flex items-center gap-1">
            <span class="live-dot"></span>
            Pengingat berikutnya dalam {{ nextSlotCountdown }}
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== DESKTOP: 2 kolom ==================== -->
    <div class="lg:grid lg:grid-cols-12 lg:gap-6 lg:items-start flex flex-col gap-4 lg:gap-0">

      <!-- ---------- KOLOM KIRI ---------- -->
      <div class="lg:col-span-7 flex flex-col gap-4 lg:gap-6">

        <!-- 1. Status banner + histori -->
        <section
          id="status"
          class="rounded-2xl border p-4 lg:p-5 transition-all reveal"
          :class="store.checkedInToday
            ? 'bg-emerald-50/80 border-emerald-200/90'
            : 'bg-amber-50/80 border-amber-200/90'"
          style="animation-delay: 60ms"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
              <div
                class="w-10 h-10 rounded-full text-white flex items-center justify-center shrink-0 mt-0.5 shadow-sm"
                :class="store.checkedInToday ? 'bg-emerald-500 shadow-emerald-500/30' : 'bg-amber-500 shadow-amber-500/30'"
              >
                <span class="material-symbols-outlined ms-filled text-[22px]">
                  {{ store.checkedInToday ? 'check' : 'priority_high' }}
                </span>
              </div>

              <div class="flex flex-col min-w-0">
                <h2
                  class="text-sm lg:text-base font-bold leading-snug tracking-editorial"
                  :class="store.checkedInToday ? 'text-emerald-950' : 'text-amber-950'"
                >
                  {{ store.checkedInToday ? 'Kamu sudah absen hari ini.' : 'Kamu belum absen hari ini.' }}
                </h2>

                <p
                  class="text-xs mt-0.5 leading-relaxed"
                  :class="store.checkedInToday ? 'text-emerald-800/90' : 'text-amber-800/90'"
                >
                  <template v-if="store.checkedInToday">
                    Tercatat pada sistem •
                    <span class="tabular font-semibold text-emerald-900">{{ checkinTimeLabel }}</span>
                  </template>
                  <template v-else>
                    Batas absen <span class="tabular font-semibold">{{ deadlineLabel }}</span> — sisa
                    <span class="tabular font-semibold">{{ remainingLabel }}</span>
                  </template>
                </p>

                <span
                  class="text-[11px] mt-1 flex items-center gap-1 font-medium"
                  :class="store.checkedInToday ? 'text-emerald-700/80' : 'text-amber-700/80'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="store.checkedInToday ? 'bg-emerald-600' : 'bg-amber-600'"></span>
                  {{ store.checkedInToday ? 'Status terverifikasi otomatis' : 'Segera lapor sebelum tengah malam' }}
                </span>
              </div>
            </div>

            <button
              type="button"
              class="shrink-0 flex items-center gap-1 px-2.5 py-1.5 bg-white border rounded-lg text-[11px] font-semibold shadow-xs active:scale-95 transition-all"
              :class="store.checkedInToday
                ? 'border-emerald-200 text-emerald-800 hover:bg-emerald-50'
                : 'border-amber-200 text-amber-800 hover:bg-amber-50'"
              :disabled="refreshing"
              @click="refresh"
            >
              <span class="material-symbols-outlined text-[15px]" :class="{ 'animate-spin': refreshing }">sync</span>
              Refresh
            </button>
          </div>

          <!-- Histori 5 hari -->
          <div
            class="mt-3 pt-3 border-t"
            :class="store.checkedInToday ? 'border-emerald-200/60' : 'border-amber-200/60'"
          >
            <div class="flex items-center justify-between flex-wrap gap-2">
              <span
                class="text-[11px] font-semibold flex items-center gap-1"
                :class="store.checkedInToday ? 'text-emerald-800' : 'text-amber-800'"
              >
                <span class="material-symbols-outlined ms-filled text-[14px]" :class="store.checkedInToday ? 'text-emerald-600' : 'text-amber-600'">history</span>
                HISTORI PRESENSI {{ store.history.length }} HARI TERAKHIR:
              </span>

              <div class="flex items-center gap-1.5 flex-wrap">
                <span
                  v-for="d in store.history"
                  :key="d.date"
                  class="tabular text-[10px] font-bold px-2 py-0.5 rounded-md border"
                  :class="dayClass(d)"
                  :title="d.label_full"
                >
                  {{ d.label }}: {{ d.checked_in ? d.time : '—' }}
                </span>
              </div>
            </div>

            <div
              class="mt-2.5 flex items-center gap-3 text-[11px] font-medium flex-wrap"
              :class="store.checkedInToday ? 'text-emerald-800' : 'text-amber-800'"
            >
              <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">event_available</span>
                Kehadiran Minggu Ini: {{ store.week.checked }}/{{ store.week.expected }} Hari Kerja ({{ store.week.rate }}%)
              </span>
              <span class="w-1 h-1 rounded-full bg-current opacity-40"></span>
              <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">verified</span>
                Status Akun: {{ store.userId ? 'Terdaftar' : 'Belum terdaftar' }}
              </span>
            </div>
          </div>
        </section>

        <!-- 2. Aksi cepat + jadwal (desktop: 2 kartu terpisah) -->
        <section class="lg:hidden card p-4 flex flex-col gap-3.5 reveal" style="animation-delay: 120ms">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">notifications_active</span>
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-editorial">Pengingat Aktif Otomatis</h3>
                <p class="text-[11px] text-slate-500">Notifikasi dikirim ke push browser &amp; email</p>
              </div>
            </div>
            <span class="pill px-2 py-1 text-[11px] bg-brand-50 text-brand-700 border border-brand-100">
              {{ store.activeSlotCount }}× Harian
            </span>
          </div>

          <Timeline />

          <div class="flex flex-col gap-2.5 pt-1">
            <a :href="dashboardUrl" target="_blank" rel="noopener" class="btn-primary w-full h-11 text-xs sm:text-sm">
              <span>Buka Dashboard MagangHub</span>
              <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            </a>
            <div class="flex items-center justify-between px-1 text-xs">
              <button type="button" class="text-slate-600 hover:text-brand-700 font-medium flex items-center gap-1 py-1 transition-colors" @click="openEmailModal">
                <span class="material-symbols-outlined text-[16px]">alternate_email</span>
                Daftarkan email lain
              </button>
              <button type="button" class="font-semibold flex items-center gap-1 py-1 transition-colors disabled:opacity-60" :class="store.checkedInToday ? 'text-slate-500 hover:text-rose-600' : 'text-brand-700 hover:text-brand-800'" :disabled="sendingTest" @click="toggleCheckin">
                <span class="material-symbols-outlined text-[15px]">
                  {{ sendingTest ? 'hourglass_top' : (store.checkedInToday ? 'undo' : 'send') }}
                </span>
                {{ sendingTest ? 'Menyimpan…' : (store.checkedInToday ? 'Batalkan absen' : 'Tandai Sudah Absen') }}
              </button>
            </div>
          </div>
        </section>

        <!-- Desktop: Aksi Cepat -->
        <section class="hidden lg:block card p-5 reveal" style="animation-delay: 120ms">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-brand-600"></span>
              <h3 class="text-base font-bold text-slate-900 tracking-editorial">Aksi Cepat Presensi</h3>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Langsung ke SSO Kemnaker</span>
          </div>

          <p class="text-[13px] text-slate-500 leading-relaxed mb-4">
            Akses portal utama secara terenkripsi untuk pengisian logbook harian, konfirmasi
            foto selfie geo-lokasi, dan pelaporan kegiatan mandiri.
          </p>

          <a :href="dashboardUrl" target="_blank" rel="noopener" class="btn-primary w-full h-12 px-5 mb-3">
            <span class="material-symbols-outlined text-[20px]">login</span>
            <span class="flex flex-col items-start leading-tight">
              <span class="text-sm font-bold">Buka Dashboard MagangHub</span>
              <span class="text-[11px] font-medium opacity-85">monev.maganghub.kemnaker.go.id • Jalur Langsung</span>
            </span>
            <span class="material-symbols-outlined text-[18px] ml-auto">arrow_outward</span>
          </a>

          <div class="grid grid-cols-2 gap-2.5">
            <button
              type="button"
              class="btn-ghost h-11 text-[13px] flex items-center justify-center gap-1.5"
              :class="store.checkedInToday ? 'text-slate-600' : ''"
              :disabled="sendingTest"
              @click="toggleCheckin"
            >
              <span class="material-symbols-outlined text-[17px]" :class="store.checkedInToday ? 'text-slate-500' : 'text-brand-600'">
                {{ sendingTest ? 'hourglass_top' : (store.checkedInToday ? 'undo' : 'notifications') }}
              </span>
              {{ sendingTest ? 'Menyimpan…' : (store.checkedInToday ? 'Batalkan Absen' : 'Tandai Sudah Absen') }}
            </button>
            <button type="button" class="btn-ghost h-11 text-[13px] flex items-center justify-center gap-1.5" @click="openEmailModal">
              <span class="material-symbols-outlined text-[17px] text-slate-500">mail</span>
              Daftarkan / Ganti Email
            </button>
          </div>
        </section>

        <!-- Desktop: Jadwal -->
        <section id="jadwal" class="hidden lg:block card p-5 reveal" style="animation-delay: 180ms">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
              <h3 class="text-base font-bold text-slate-900 tracking-editorial">Jadwal Pengingat Aktif</h3>
              <p class="text-[11px] text-slate-500">Pengiriman terjadwal otomatis via Web Push &amp; Surel</p>
            </div>
            <span class="pill px-2.5 py-1 text-[11px] bg-brand-50 text-brand-700 border border-brand-100">
              <span class="material-symbols-outlined ms-filled text-[13px]">bolt</span>
              {{ store.activeSlotCount }}× Sehari
            </span>
          </div>

          <Timeline variant="desktop" />

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[15px]">devices</span>
              Target Sinkronisasi: Android, iOS, &amp; Chrome Desktop
            </span>
            <button type="button" class="text-brand-700 hover:text-brand-800 font-semibold flex items-center gap-1 transition-colors" @click="openEmailModal">
              Atur Email Penerima
              <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </button>
          </div>
        </section>
      </div>

      <!-- ---------- KOLOM KANAN (desktop) ---------- -->
      <div class="lg:col-span-5 flex flex-col gap-4 lg:gap-6">

        <!-- Cara Kerja -->
        <section id="panduan" class="card p-4 lg:p-5 reveal" style="animation-delay: 240ms">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">alt_route</span>
              </div>
              <h3 class="text-sm lg:text-base font-bold text-slate-900 tracking-editorial">Cara Kerja</h3>
            </div>
            <span class="hidden lg:flex w-7 h-7 rounded-full bg-slate-100 items-center justify-center text-[12px] font-bold text-slate-600">3</span>
          </div>

          <div class="flex flex-col gap-3.5 relative">
            <div class="absolute left-3.5 top-3 bottom-4 w-0.5 bg-slate-100"></div>
            <div v-for="(step, i) in steps" :key="i" class="flex items-start gap-3 relative z-10">
              <div class="w-7 h-7 rounded-full bg-blue-100 border-2 border-white text-brand-700 font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                {{ i + 1 }}
              </div>
              <div class="flex flex-col min-w-0 pt-0.5">
                <span class="text-xs font-bold text-slate-800">{{ step.title }}</span>
                <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">{{ step.desc }}</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Tips -->
        <section class="bg-amber-50/70 border border-amber-200/70 rounded-2xl p-3.5 lg:p-4 shadow-xs flex items-start gap-3 reveal" style="animation-delay: 300ms">
          <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
            <span class="material-symbols-outlined ms-filled text-[20px]">lightbulb</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-xs font-bold text-amber-900">Tip Cepat di Android/iOS</span>
            <p class="text-[11px] text-amber-800 leading-snug mt-0.5">
              Buka menu browser Anda (<span class="font-mono font-bold">⋮</span> atau ikon bagikan), lalu pilih
              <strong class="underline decoration-amber-400 font-bold">"Add to Home Screen"</strong>
              untuk membuka MagangHub layaknya aplikasi langsung.
            </p>
          </div>
        </section>

        <!-- Batas akhir -->
        <section class="bg-slate-100/90 border border-slate-200 rounded-xl p-3 lg:p-4 reveal" style="animation-delay: 360ms">
          <div class="flex items-start gap-2.5">
            <span class="material-symbols-outlined text-[18px] text-rose-500 shrink-0 mt-0.5">timer</span>
            <div class="min-w-0">
              <p class="text-[11px] lg:text-xs text-slate-600 font-medium leading-relaxed">
                Batas absen harian: <span class="tabular font-bold text-slate-900">00.00 WITA (Tengah Malam)</span>
              </p>
              <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                MagangHub menerapkan sistem absensi tepat pada pukul
                <span class="tabular font-semibold text-slate-700">00.00 WITA</span> setiap hari. Keterlambatan
                input tidak dapat ditoleransi oleh sistem pusat.
              </p>
              <p class="tabular text-[11px] font-bold text-rose-600 mt-2">
                Sisa waktu hari ini: ~{{ remainingLabel }}
              </p>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- ==================== MODAL EMAIL ==================== -->
    <Teleport to="body">
      <div
        v-if="emailModalOpen"
        class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4"
        @click.self="closeEmailModal"
      >
        <div class="w-full max-w-sm bg-white rounded-2xl p-5 shadow-2xl flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
              <span class="material-symbols-outlined text-brand-600 text-[18px]">mail</span>
              Pendaftaran Email Presensi
            </h4>
            <button type="button" class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors" @click="closeEmailModal" aria-label="Tutup">
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Masukkan alamat email aktif untuk menerima notifikasi cadangan setiap jam pengingat.
          </p>

          <div class="flex flex-col gap-1 mt-1">
            <label class="text-[11px] font-bold text-slate-700" for="input-email">Email Penerima</label>
            <input
              id="input-email"
              v-model="emailInput"
              type="email"
              placeholder="nama@kampus.ac.id"
              class="input-field h-10 px-3 text-xs"
              @keyup.enter="saveEmail"
            >
          </div>

          <p v-if="emailError" class="text-[11px] text-rose-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            {{ emailError }}
          </p>

          <p v-if="emailSaved" class="text-[11px] text-emerald-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">check_circle</span>
            Email tersimpan.
          </p>

          <button type="button" class="btn-primary h-9 text-xs mt-2" :disabled="emailSaving" @click="saveEmail">
            {{ emailSaving ? 'Menyimpan…' : 'Simpan Email' }}
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useUserStore } from '../stores/user'
import { useToast } from '../composables/useToast'
import Timeline from '../components/Timeline.vue'

const store = useUserStore()
const toast = useToast()

const dashboardUrl = 'https://monev.maganghub.kemnaker.go.id/dashboard/riwayat'

const steps = [
    { title: 'Terima notifikasi tepat waktu', desc: 'Notifikasi otomatis tiba di HP atau browser pada 16.30, 20.30, dan 23.00 WITA.' },
    { title: 'Tap notifikasi & isi presensi', desc: 'Klik link menuju portal resmi MagangHub lalu submit jurnal aktivitas harian Anda.' },
    { title: 'Konfirmasi kehadiran', desc: 'Status diperbarui otomatis menjadi hijau bertanda tugas hari ini telah rampung.' },
]

const refreshing = ref(false)
const sendingTest = ref(false)

const emailModalOpen = ref(false)
const emailInput = ref('')
const emailSaving = ref(false)
const emailError = ref('')
const emailSaved = ref(false)

const firstName = computed(() => (store.name || 'Peserta Magang').split(' ')[0])

const checkinTimeLabel = computed(() => {
    if (! store.checkinTime) return '—'
    const d = new Date(store.checkinTime)
    const wita = new Date(d.getTime() + (8 * 60 + d.getTimezoneOffset()) * 60000)
    return `${wita.toTimeString().slice(0, 5)} WITA`
})

const deadlineLabel = computed(() => '00.00 WITA')

/** Sisa waktu sampai tengah malam (WITA) — dihitung ulang tiap detik via store. */
const remainingLabel = computed(() => {
    const mins = store.remainingToMidnight
    if (mins <= 0) return 'kurang dari 1 mnt'
    const h = Math.floor(mins / 60)
    const m = mins % 60
    return h > 0 ? `${h} jam ${m} mnt` : `${m} mnt`
})

/**
 * Countdown berjalan tiap detik (bukan tiap menit) supaya terlihat hidup.
 * Format: "1:15:04" -> jam:menit:detik menuju slot pengingat berikutnya.
 */
const nextSlotCountdown = computed(() => {
    const s = store.secondsToNextSlot
    if (s === null) return null
    const p = (n) => String(n).padStart(2, '0')
    return `${Math.floor(s / 3600)}:${p(Math.floor(s / 60) % 60)}:${p(s % 60)}`
})

function dayClass(d) {
    if (d.is_weekend) return 'bg-slate-50 text-slate-400 border-slate-200'
    return store.checkedInToday
        ? 'bg-white/90 text-emerald-800 border-emerald-200'
        : 'bg-white/90 text-amber-800 border-amber-200'
}

async function refresh() {
    refreshing.value = true
    try {
        await store.refreshToday()
        if (! store.userId) {
            toast.info('Belum ada akun terdaftar di perangkat ini.')
        } else {
            toast.success('Status diperbarui.')
        }
    } catch (e) {
        toast.error('Gagal memperbarui status.')
    } finally {
        refreshing.value = false
    }
}

/**
 * Toggle absen hari ini.
 * - Belum absen  -> catat (POST)
 * - Sudah absen  -> batalkan (DELETE), supaya salah klik bisa dibatalkan
 * Selalu ada umpan balik: toast sukses, toast error, atau toast info.
 */
async function toggleCheckin() {
    if (! store.userId) {
        toast.error('Daftarkan diri dulu sebelum menandai absen.')
        return
    }

    sendingTest.value = true
    const wasCheckedIn = store.checkedInToday

    try {
        const r = await fetch('/api/checkin', {
            method: wasCheckedIn ? 'DELETE' : 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                user_id: store.userId,
                source: 'button',
            }),
        })

        const j = await r.json().catch(() => ({}))

        if (! r.ok) {
            const msg = j.message || (j.errors && Object.values(j.errors)[0]?.[0]) || 'Gagal menyimpan.'
            throw new Error(msg)
        }

        if (wasCheckedIn) {
            toast.info(j.deleted ? 'Catatan absen hari ini dibatalkan.' : 'Belum ada catatan absen hari ini.')
        } else {
            toast.success(j.message || 'Absen hari ini tercatat.')
        }

        await store.refreshToday()
    } catch (e) {
        toast.error(e.message || 'Gagal menghubungi server. Coba lagi.')
    } finally {
        sendingTest.value = false
    }
}

function openEmailModal() {
    emailInput.value = store.email || ''
    emailError.value = ''
    emailSaved.value = false
    emailModalOpen.value = true
}

function closeEmailModal() {
    emailModalOpen.value = false
    emailError.value = ''
    emailSaved.value = false
}

async function saveEmail() {
    emailError.value = ''
    emailSaved.value = false

    if (! store.userId) {
        emailError.value = 'Daftarkan diri dulu sebelum mengganti email.'
        return
    }

    if (! emailInput.value || ! /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailInput.value)) {
        emailError.value = 'Masukkan alamat email yang valid.'
        return
    }

    if (emailInput.value === store.email) {
        emailError.value = 'Email ini sudah terdaftar.'
        return
    }

    emailSaving.value = true
    try {
        await store.updateEmail(emailInput.value)
        emailSaved.value = true
        toast.success('Email notifikasi diperbarui.')
        setTimeout(closeEmailModal, 700)
    } catch (e) {
        emailError.value = e.message
        toast.error(e.message)
    } finally {
        emailSaving.value = false
    }
}

// Email di store diisi async (loadProfile) — jaga input tetap sinkron.
watch(() => store.email, (v) => {
    if (! emailModalOpen.value) emailInput.value = v || ''
})

onMounted(async () => {
    await store.refreshToday()
    emailInput.value = store.email || ''
})
</script>
