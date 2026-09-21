<template>
  <div class="min-h-screen flex flex-col bg-slate-50 text-slate-900">
    <header class="bg-blue-700 text-white px-4 py-3 shadow">
      <h1 class="text-lg font-semibold">⏰ Reminder Absen MagangHub</h1>
      <p class="text-xs opacity-90">Pengingat otomatis 3× sehari (04.30 / 08.30 / 11.00 WITA)</p>
    </header>

    <main class="flex-1 max-w-md mx-auto w-full p-4">
      <router-view />
    </main>

    <footer class="text-center text-xs text-slate-500 p-3 border-t border-slate-200">
      Batas absen: <strong>12.00 malam WITA</strong> · <a href="https://monev.maganghub.kemnaker.go.id/dashboard/riwayat" target="_blank" rel="noopener" class="text-blue-700 underline">Dashboard MagangHub</a>
    </footer>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useUserStore } from './stores/user'

const store = useUserStore()

onMounted(async () => {
  // Load VAPID public key ASAP — dibutuhkan oleh usePushSubscription composable.
  try {
    await store.loadVapidKey()
  } catch (e) {
    console.error('Failed to load VAPID key:', e)
  }

  // Restore user_id dari localStorage kalau ada.
  const savedId = localStorage.getItem('reminder_absen_user_id')
  if (savedId) store.setUserId(parseInt(savedId, 10))
  await store.refreshToday()
})
</script>