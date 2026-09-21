<template>
  <div class="space-y-4">
    <section class="bg-white rounded-xl shadow p-5">
      <h2 class="font-semibold text-lg mb-3">Aktifkan Reminder</h2>

        <div v-if="store.userId && store.checkedInToday" class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg p-3 text-sm">
          ✅ Kamu sudah absen hari ini.
          <button @click="store.refreshToday" class="ml-2 underline text-xs">refresh</button>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm">
          {{ error }}
        </div>

        <form v-if="!store.userId" @submit.prevent="register" class="space-y-3">
          <label class="block">
            <span class="text-sm text-slate-700">Nama</span>
            <input v-model="form.name" required minlength="2"
              class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              placeholder="Nama lengkap">
          </label>
          <label class="block">
            <span class="text-sm text-slate-700">Email</span>
            <input v-model="form.email" required type="email"
              class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
              placeholder="nama@email.com">
          </label>
          <button type="submit" :disabled="loading"
            class="w-full bg-blue-700 hover:bg-blue-800 disabled:opacity-50 text-white font-semibold py-2.5 rounded-lg">
            {{ loading ? 'Memproses...' : 'Daftar & Aktifkan Notifikasi' }}
          </button>
        </form>

        <div v-else class="space-y-3">
          <p class="text-sm text-slate-700">
            Halo, <strong>{{ store.name }}</strong>. Reminder sudah aktif untuk:
          </p>
          <ul class="text-sm space-y-1 ml-4 list-disc text-slate-600">
            <li>16.30 WITA — pengingat sore</li>
            <li>20.30 WITA — pengingat malam</li>
            <li>23.00 WITA — peringatan terakhir</li>
          </ul>
          <router-link to="/dashboard" class="block text-center w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2.5 rounded-lg">
            Lihat Dashboard
          </router-link>
          <button @click="reset" class="block w-full text-sm text-slate-500 underline">Daftarkan email lain</button>
        </div>
      </section>

      <section class="bg-white rounded-xl shadow p-5 text-sm text-slate-700 space-y-2">
        <h3 class="font-semibold">Cara kerja</h3>
        <ol class="list-decimal ml-4 space-y-1">
          <li>Setelah daftar, kamu akan dapat 3 notifikasi setiap hari.</li>
          <li>Tap notifikasi → buka dashboard MagangHub → login & absen.</li>
          <li>Setelah absen, balik ke sini & tap tombol <strong>"Sudah Absen"</strong> (muncul di notifikasi).</li>
        </ol>
        <p class="text-xs text-slate-500 pt-2 border-t border-slate-100">
          💡 Tip: di Android, klik ⋮ menu → <em>Add to Home screen</em> untuk install seperti APK.
        </p>
      </section>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useUserStore } from '../stores/user'
import { usePushSubscription } from '../composables/usePushSubscription'

const store = useUserStore()
const loading = ref(false)
const error = ref('')
const form = reactive({ name: '', email: '' })

// Getter — baca store.vapidPublicKey saat subscribe() dipanggil, bukan saat setup.
const { subscribe } = usePushSubscription(() => store.vapidPublicKey)

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
            body: JSON.stringify({
                name: form.name,
                email: form.email,
                subscription,
            }),
        })
        const j = await r.json()
        if (! r.ok) throw new Error(j.error || 'Gagal daftar')
        store.setUserId(j.user_id)
        store.name = form.name
        store.email = form.email
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