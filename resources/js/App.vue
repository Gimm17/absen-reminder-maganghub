<template>
  <div class="min-h-screen flex flex-col bg-slate-50">

    <!-- ============ MOBILE HEADER (frosted, fixed) ============ -->
    <header class="lg:hidden fixed top-0 inset-x-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 pt-safe">
      <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-9 h-9 rounded-xl bg-brand-700 text-white flex items-center justify-center shadow-sm shadow-blue-500/20 shrink-0">
            <span class="material-symbols-outlined ms-filled text-[20px]">verified</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-slate-900 text-base tracking-editorial leading-none">MagangHub</span>
            <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1 mt-0.5">
              <span class="live-dot"></span>
              Sync WITA Aktif
            </span>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0 relative">
          <span class="pill px-2.5 py-1 text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200/60">
            <span class="material-symbols-outlined ms-filled text-[13px]">bolt</span>
            {{ store.activeSlotCount }}× Aktif
          </span>
          <button
            type="button"
            aria-label="Profil"
            class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 active:scale-95 transition-transform"
            @click="profileOpen = !profileOpen"
          >
            <span class="material-symbols-outlined text-[18px]">person</span>
          </button>

          <!-- Dropdown profil -->
          <div v-if="profileOpen" data-profile-menu class="absolute top-11 right-0 w-56 card p-3 shadow-lg z-50">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
              <div class="w-9 h-9 rounded-full bg-brand-700 text-white flex items-center justify-center text-sm font-bold shrink-0">
                {{ (store.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0">
                <p class="text-[13px] font-bold text-slate-900 truncate">{{ store.name || 'Belum terdaftar' }}</p>
                <p class="text-[11px] text-slate-500 truncate">{{ store.email || '—' }}</p>
              </div>
            </div>
            <RouterLink to="/dashboard" class="flex items-center gap-2 py-2 text-[13px] text-slate-700 hover:text-brand-700 transition-colors" @click="profileOpen = false">
              <span class="material-symbols-outlined text-[17px]">today</span>
              Presensi
            </RouterLink>
            <RouterLink to="/admin" class="flex items-center gap-2 py-2 text-[13px] text-slate-700 hover:text-brand-700 transition-colors" @click="profileOpen = false">
              <span class="material-symbols-outlined text-[17px]">admin_panel_settings</span>
              Panel Admin
            </RouterLink>
            <RouterLink to="/register" class="flex items-center gap-2 py-2 text-[13px] text-slate-700 hover:text-brand-700 transition-colors" @click="profileOpen = false">
              <span class="material-symbols-outlined text-[17px]">person_add</span>
              Daftar / Ganti Akun
            </RouterLink>
          </div>
        </div>
      </div>
    </header>

    <!-- ============ DESKTOP HEADER ============ -->
    <header class="hidden lg:block sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80">
      <div class="max-w-7xl mx-auto px-6 xl:px-12 h-16 flex items-center justify-between gap-8">
        <!-- Brand -->
        <div class="flex items-center gap-3 shrink-0">
          <div class="w-10 h-10 rounded-xl bg-brand-700 text-white flex items-center justify-center shadow-sm shadow-blue-500/20">
            <span class="material-symbols-outlined ms-filled text-[22px]">verified</span>
          </div>
          <div class="flex flex-col">
            <span class="font-bold text-slate-900 text-[15px] tracking-editorial leading-tight">Reminder Absen</span>
            <span class="text-[11px] text-slate-500 font-medium leading-tight">MagangHub Ecosystem</span>
          </div>
          <span class="pill ml-2 px-2.5 py-1 text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200/60">
            <span class="live-dot"></span>
            Live Sync Aktif • WITA
          </span>
        </div>

        <!-- Nav -->
        <nav class="flex items-center gap-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
            :class="isActive(item) ? 'bg-brand-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <!-- Right -->
        <div class="flex items-center gap-3 shrink-0">
          <span class="tabular text-sm font-semibold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-lg">
            {{ clock }}
          </span>
          <a
            :href="dashboardUrl"
            target="_blank"
            rel="noopener"
            class="btn-ghost px-3.5 py-2 text-sm inline-flex items-center gap-1.5"
          >
            Portal Utama
            <span class="material-symbols-outlined text-[15px]">open_in_new</span>
          </a>
          <button
            type="button"
            aria-label="Profil"
            class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition-colors"
            @click="goProfile"
          >
            <span class="material-symbols-outlined text-[19px]">person</span>
          </button>
        </div>
      </div>
    </header>

    <!-- ============ MAIN ============ -->
    <main class="flex-1 w-full max-w-md lg:max-w-7xl mx-auto px-4 lg:px-6 xl:px-12 pt-[76px] lg:pt-8 pb-32 lg:pb-16">
      <RouterView />
    </main>

    <!-- ============ MOBILE BOTTOM NAV ============ -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 pb-safe">
      <div class="max-w-md mx-auto px-2 h-14 flex items-center justify-around">
        <RouterLink
          v-for="tab in bottomTabs"
          :key="tab.to"
          :to="tab.to"
          class="flex flex-col items-center justify-center gap-0.5 w-16 py-1 transition-colors"
          :class="isActive(tab) ? 'text-brand-700' : 'text-slate-500 hover:text-slate-800'"
        >
          <span class="material-symbols-outlined text-[22px]" :class="{ 'ms-filled': isActive(tab) }">{{ tab.icon }}</span>
          <span class="text-[10px] tracking-tight" :class="isActive(tab) ? 'font-bold' : 'font-medium'">{{ tab.label }}</span>
        </RouterLink>
      </div>
    </nav>

    <!-- ============ FOOTER (desktop) ============ -->
    <footer class="hidden lg:block border-t border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-6 xl:px-12 py-5 flex items-center justify-between gap-6">
        <span class="pill px-3 py-1.5 text-[11px] bg-amber-50 text-amber-800 border border-amber-200">
          <span class="material-symbols-outlined text-[14px]">schedule</span>
          Batas Absen: Tengah Malam (00.00 WITA)
        </span>
        <div class="flex items-center gap-5 text-[12px] text-slate-500">
          <a :href="dashboardUrl" target="_blank" rel="noopener" class="hover:text-slate-800 inline-flex items-center gap-1 transition-colors">
            Dashboard MagangHub Resmi
            <span class="material-symbols-outlined text-[14px]">open_in_new</span>
          </a>
          <span class="w-1 h-1 rounded-full bg-slate-300"></span>
          <span>Sinkronisasi Otomatis 3× Sehari</span>
          <span class="w-1 h-1 rounded-full bg-slate-300"></span>
          <span>© {{ year }} MagangHub Internal Service</span>
        </div>
      </div>
    </footer>

    <!-- Toast global — umpan balik semua aksi -->
    <Toaster />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useUserStore } from './stores/user'
import Toaster from './components/Toaster.vue'

const store = useUserStore()
const route = useRoute()
const router = useRouter()
const profileOpen = ref(false)

const dashboardUrl = 'https://monev.maganghub.kemnaker.go.id/dashboard/riwayat'
const year = 2026

const navItems = [
    { to: '/dashboard', label: 'Beranda', match: ['/dashboard', '/'] },
    { to: '/dashboard#jadwal', label: 'Jadwal Pengingat', match: [] },
    { to: '/dashboard#status', label: 'Status Server', match: [] },
    { to: '/dashboard#panduan', label: 'Bantuan', match: [] },
]

const bottomTabs = [
    { to: '/dashboard', label: 'Presensi', icon: 'today' },
    { to: '/dashboard#jadwal', label: 'Jadwal', icon: 'alarm' },
    { to: '/dashboard#panduan', label: 'Panduan', icon: 'help_center' },
    { to: '/admin', label: 'Profil', icon: 'settings' },
]

function isActive(item) {
    if (item.match && item.match.length) return item.match.includes(route.path)
    if (item.to.includes('#')) return false
    return route.path === item.to
}

const clock = ref('--:--:--')

function updateClock() {
    const now = new Date()
    const wita = new Date(now.getTime() + (8 * 60 + now.getTimezoneOffset()) * 60000)
    clock.value = wita.toTimeString().slice(0, 8)
}

function goProfile() {
    router.push(store.userId ? '/admin' : '/register')
}

/** Tutup dropdown profil saat klik di luar. */
function onDocClick(e) {
    if (profileOpen.value && ! e.target.closest('[data-profile-menu]')) {
        profileOpen.value = false
    }
}

let clockTimer = null

onMounted(async () => {
    try {
        await store.loadVapidKey()
    } catch (e) {
        console.error('VAPID key load failed:', e)
    }

    const savedId = localStorage.getItem('reminder_absen_user_id')
    if (savedId) store.setUserId(parseInt(savedId, 10))

    store.tick()
    await store.loadProfile()
    await store.refreshToday()

    updateClock()
    clockTimer = setInterval(() => {
        updateClock()
        store.tick()
    }, 30000)

    document.addEventListener('click', onDocClick)
})

onUnmounted(() => {
    if (clockTimer) clearInterval(clockTimer)
    document.removeEventListener('click', onDocClick)
})
</script>
