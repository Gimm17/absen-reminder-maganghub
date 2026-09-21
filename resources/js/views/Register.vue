<template>
  <div class="flex flex-col gap-4 lg:gap-6">

    <!-- ============ BELUM TERDAFTAR: FORM ============ -->
    <template v-if="! store.userId">
      <!-- Hero -->
      <section class="reveal">
        <span class="pill px-3 py-1.5 text-[11px] bg-brand-50 text-brand-700 border border-brand-100 mb-3">
          <span class="live-dot"></span>
          Onboarding • Sekali Setup
        </span>
        <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 tracking-editorial leading-tight">
          Aktifkan Pengingat Absen
        </h1>
        <p class="text-sm lg:text-[15px] text-slate-500 mt-1.5 leading-relaxed">
          Dapat 3 notifikasi otomatis setiap hari menjelang batas absen tengah malam.
          Cukup daftar sekali — pengingat berjalan sendiri.
        </p>
      </section>

      <div class="lg:grid lg:grid-cols-12 lg:gap-6 lg:items-start flex flex-col gap-4 lg:gap-0">
        <div class="lg:col-span-7 flex flex-col gap-4 lg:gap-6">

          <!-- Form card -->
          <section class="card p-4 lg:p-6 reveal" style="animation-delay: 60ms">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
              <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
              </div>
              <h3 class="text-sm lg:text-base font-bold text-slate-900 tracking-editorial">Data Peserta</h3>
            </div>

            <p v-if="error" class="mb-4 text-[12px] text-rose-600 bg-rose-50 border border-rose-200 rounded-lg p-3 flex items-start gap-2">
              <span class="material-symbols-outlined text-[16px] shrink-0">error</span>
              <span>{{ error }}</span>
            </p>

            <form class="flex flex-col gap-4" @submit.prevent="register">
              <label class="block">
                <span class="text-[11px] font-bold text-slate-700">Nama Lengkap</span>
                <input
                  v-model="form.name"
                  required
                  minlength="2"
                  type="text"
                  placeholder="Nama lengkap"
                  class="input-field h-11 px-3 text-sm mt-1.5"
                >
              </label>

              <label class="block">
                <span class="text-[11px] font-bold text-slate-700">Email Penerima Notifikasi</span>
                <input
                  v-model="form.email"
                  required
                  type="email"
                  placeholder="nama@kampus.ac.id"
                  class="input-field h-11 px-3 text-sm mt-1.5"
                >
                <span class="text-[11px] text-slate-500 mt-1.5 flex items-center gap-1">
                  <span class="material-symbols-outlined text-[13px]">info</span>
                  Email ini menerima salinan notifikasi selain push browser.
                </span>
              </label>

              <button type="submit" class="btn-primary h-11 text-sm mt-1" :disabled="loading">
                <span class="material-symbols-outlined text-[19px]">
                  {{ loading ? 'hourglass_top' : 'notifications_active' }}
                </span>
                {{ loading ? 'Memproses…' : 'Daftar & Aktifkan Notifikasi' }}
              </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100">
              <p class="text-[11px] text-slate-500 leading-relaxed flex items-start gap-1.5">
                <span class="material-symbols-outlined text-[14px] text-brand-600 shrink-0 mt-0.5">shield</span>
                Kami hanya menyimpan nama, email, dan endpoint push browser Anda. Tidak ada data portal
                MagangHub yang diakses.
              </p>
            </div>
          </section>
        </div>

        <div class="lg:col-span-5 flex flex-col gap-4 lg:gap-6">
          <!-- Jadwal -->
          <section class="card p-4 lg:p-5 reveal" style="animation-delay: 120ms">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-700 flex items-center justify-center">
                  <span class="material-symbols-outlined text-[18px]">notifications_active</span>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-slate-900 tracking-editorial">Jadwal Pengingat</h3>
                  <p class="text-[11px] text-slate-500">3× sehari, zona WITA</p>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2.5">
              <div
                v-for="(slot, i) in previewSlots"
                :key="slot.key"
                class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100/80"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border" :class="slot.bg">
                    <span class="material-symbols-outlined text-[19px]">{{ slot.icon }}</span>
                  </div>
                  <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-slate-800">{{ slot.title }}</span>
                    <span class="text-[11px] text-slate-500 truncate">{{ slot.desc }}</span>
                  </div>
                </div>
                <span class="tabular text-xs font-bold text-slate-900 tracking-tight shrink-0 pl-2">{{ slot.time }} WITA</span>
              </div>
            </div>
          </section>

          <!-- Tips -->
          <section class="bg-amber-50/70 border border-amber-200/70 rounded-2xl p-3.5 lg:p-4 shadow-xs flex items-start gap-3 reveal" style="animation-delay: 180ms">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
              <span class="material-symbols-outlined ms-filled text-[20px]">lightbulb</span>
            </div>
            <div class="flex flex-col min-w-0">
              <span class="text-xs font-bold text-amber-900">Tip Cepat di Android/iOS</span>
              <p class="text-[11px] text-amber-800 leading-snug mt-0.5">
                Buka menu browser (<span class="font-mono font-bold">⋮</span> atau ikon bagikan), lalu pilih
                <strong class="underline decoration-amber-400 font-bold">"Add to Home Screen"</strong>
                untuk membuka MagangHub layaknya aplikasi.
              </p>
            </div>
          </section>

          <!-- Batas -->
          <section class="bg-slate-100/90 border border-slate-200 rounded-xl p-3 lg:p-4 flex items-center gap-2.5 reveal" style="animation-delay: 240ms">
            <span class="material-symbols-outlined text-[18px] text-rose-500 shrink-0">timer</span>
            <p class="text-[11px] lg:text-xs text-slate-600 font-medium leading-relaxed">
              Batas absen harian: <span class="tabular font-bold text-slate-900">00.00 WITA</span>
            </p>
          </section>
        </div>
      </div>
    </template>

    <!-- ============ SUDAH TERDAFTAR ============ -->
    <template v-else>
      <section class="card p-5 lg:p-6 reveal max-w-2xl">
        <div class="flex items-start gap-3.5">
          <div class="w-11 h-11 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-500/30">
            <span class="material-symbols-outlined ms-filled text-[24px]">check</span>
          </div>
          <div class="min-w-0 flex-1">
            <h2 class="text-base lg:text-lg font-bold text-emerald-950 tracking-editorial">
              Reminder sudah aktif!
            </h2>
            <p class="text-[13px] text-slate-600 mt-1 leading-relaxed">
              Halo <strong>{{ store.name }}</strong> — pengingat dikirim ke
              <strong class="text-slate-800">{{ store.email }}</strong>
              pada jam berikut:
            </p>

            <div class="flex flex-wrap gap-2 mt-3">
              <span
                v-for="slot in registeredSlots"
                :key="slot.key"
                class="pill tabular px-2.5 py-1 text-[11px] bg-brand-50 text-brand-700 border border-brand-100"
              >
                {{ slot.time }} WITA
              </span>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 mt-5">
              <RouterLink to="/dashboard" class="btn-primary h-11 px-5 text-sm">
                <span class="material-symbols-outlined text-[18px]">dashboard</span>
                Lihat Dashboard
              </RouterLink>
              <button type="button" class="btn-ghost h-11 px-5 text-sm flex items-center justify-center gap-1.5" @click="reset">
                <span class="material-symbols-outlined text-[17px]">person_add</span>
                Daftarkan email lain
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="card p-4 lg:p-5 reveal max-w-2xl" style="animation-delay: 60ms">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
          <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-[18px]">alt_route</span>
          </div>
          <h3 class="text-sm font-bold text-slate-900 tracking-editorial">Cara Kerja</h3>
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
    </template>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useUserStore } from '../stores/user'
import { usePushSubscription } from '../composables/usePushSubscription'

const store = useUserStore()
const loading = ref(false)
const error = ref('')
const form = reactive({ name: '', email: '' })

// Getter — baca store.vapidPublicKey saat subscribe() dipanggil, bukan saat setup.
const { subscribe } = usePushSubscription(() => store.vapidPublicKey)

const previewSlots = [
    { key: 'slot-1', time: '16.30', title: 'Pengingat Sore', desc: 'Kesiapan laporan harian', icon: 'wb_twilight', bg: 'bg-amber-50 text-amber-600 border-amber-100' },
    { key: 'slot-2', time: '20.30', title: 'Pengingat Malam', desc: 'Evaluasi aktivitas magang', icon: 'bedtime', bg: 'bg-brand-50 text-brand-700 border-brand-100' },
    { key: 'slot-3', time: '23.00', title: 'Peringatan Terakhir', desc: '1 jam sebelum penutupan', icon: 'notification_important', bg: 'bg-rose-50 text-rose-600 border-rose-100' },
]

const steps = [
    { title: 'Terima notifikasi tepat waktu', desc: 'Notifikasi otomatis tiba di HP atau browser pada 16.30, 20.30, dan 23.00 WITA.' },
    { title: 'Tap notifikasi & isi presensi', desc: 'Klik link menuju portal resmi MagangHub lalu submit jurnal aktivitas harian Anda.' },
    { title: 'Konfirmasi kehadiran', desc: 'Status diperbarui otomatis menjadi hijau bertanda tugas hari ini telah rampung.' },
]

const registeredSlots = computed(() => previewSlots.filter(s => store.slots.includes(s.key)))

async function register() {
    error.value = ''
    loading.value = true
    try {
        if (! store.vapidPublicKey) await store.loadVapidKey()
        const subscription = await subscribe()
        if (! subscription) throw new Error('Gagal subscribe notifikasi.')

        const r = await fetch('/api/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: form.name, email: form.email, subscription }),
        })
        const j = await r.json()
        if (! r.ok) throw new Error(j.error || j.message || 'Gagal mendaftar.')

        store.setUserId(j.user_id)
        store.name = form.name
        store.email = form.email
        await store.loadProfile()
    } catch (e) {
        error.value = e.message || String(e)
    } finally {
        loading.value = false
    }
}

function reset() {
    store.clear()
    store.checkedInToday = false
    form.name = ''
    form.email = ''
}
</script>
