<template>
  <div class="space-y-4">
    <section class="bg-white rounded-xl shadow p-5">
      <h2 class="font-semibold text-lg">Status Absen Hari Ini</h2>
      <p class="text-sm text-slate-600 mt-1">{{ today }}</p>

      <div v-if="store.checkedInToday" class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg p-3">
        ✅ Sudah absen pukul <strong>{{ formatTime(store.checkinTime) }}</strong>.
      </div>
      <div v-else class="mt-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3">
        ⚠️ Belum absen hari ini. Batas: <strong>tengah malam (00.00 WITA)</strong>.
      </div>

      <div class="mt-4 grid grid-cols-2 gap-2">
        <button @click="openDashboard"
          class="bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 rounded-lg text-sm">
          Buka Dashboard
        </button>
        <button @click="markCheckedIn" :disabled="store.checkedInToday || loading"
          class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold py-2 rounded-lg text-sm">
          {{ loading ? '...' : (store.checkedInToday ? 'Sudah Absen' : 'Tandai Sudah Absen') }}
        </button>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow p-5 text-sm space-y-2">
      <h3 class="font-semibold">Pengingat</h3>
      <p class="text-xs text-slate-500">Reminder akan dikirim pada jam-jam berikut (WITA):</p>
      <ul class="text-sm space-y-1 ml-4 list-disc text-slate-700">
        <li>16.30 WITA — pengingat sore</li>
        <li>20.30 WITA — pengingat malam</li>
        <li>23.00 WITA — peringatan terakhir</li>
      </ul>
      <p class="text-xs text-slate-500 pt-2 border-t border-slate-100">
        Kalau tidak terima notifikasi, cek:
        <br>· Browser setting → notifikasi diizinkan
        <br>· Device tidak dalam mode "Do Not Disturb"
      </p>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '../stores/user'

const store = useUserStore()
const loading = ref(false)

const today = computed(() => new Date().toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Makassar'
}))

function formatTime(iso) {
    if (! iso) return ''
    return new Date(iso).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Makassar' })
}

function openDashboard() {
    window.open('https://monev.maganghub.kemnaker.go.id/dashboard/riwayat', '_blank', 'noopener')
}

async function markCheckedIn() {
    if (! store.userId) return
    loading.value = true
    try {
        const r = await fetch('/api/checkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: store.userId, source: 'button' }),
        })
        const j = await r.json()
        if (! r.ok) throw new Error(j.error || 'Gagal')
        await store.refreshToday()
    } catch (e) {
        alert('Error: ' + e.message)
    } finally {
        loading.value = false
    }
}

onMounted(() => store.refreshToday())
</script>