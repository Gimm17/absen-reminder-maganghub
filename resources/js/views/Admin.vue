<template>
  <div class="space-y-4">
    <section class="bg-white rounded-xl shadow p-5">
      <h2 class="font-semibold text-lg">Ringkasan Kepatuhan</h2>
      <p v-if="loading" class="text-sm text-slate-500">Memuat…</p>
      <div v-else-if="summary" class="grid grid-cols-3 gap-3 mt-3 text-center">
        <div class="bg-blue-50 rounded-lg p-3">
          <div class="text-2xl font-bold text-blue-700">{{ summary.total_users }}</div>
          <div class="text-xs text-slate-600">User aktif</div>
        </div>
        <div class="bg-emerald-50 rounded-lg p-3">
          <div class="text-2xl font-bold text-emerald-700">{{ summary.checked_today }}</div>
          <div class="text-xs text-slate-600">Sudah absen</div>
        </div>
        <div class="bg-amber-50 rounded-lg p-3">
          <div class="text-2xl font-bold text-amber-700">{{ summary.today_rate }}%</div>
          <div class="text-xs text-slate-600">Tingkat absen</div>
        </div>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow p-5">
      <h3 class="font-semibold">Detail Per User</h3>
      <a v-if="summary" href="/api/admin/export.csv" class="text-xs text-blue-700 underline float-right">Export CSV</a>
      <table v-if="summary && summary.per_user.length" class="mt-3 w-full text-sm">
        <thead>
          <tr class="text-left text-slate-500 border-b border-slate-200">
            <th class="py-1">Nama</th>
            <th class="py-1">Email</th>
            <th class="py-1 text-right">Checkin</th>
            <th class="py-1 text-right">%</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in summary.per_user" :key="u.id" class="border-b border-slate-100">
            <td class="py-1.5">{{ u.name }}</td>
            <td class="py-1.5 text-slate-500">{{ u.email }}</td>
            <td class="py-1.5 text-right">{{ u.checked }}/{{ u.expected }}</td>
            <td class="py-1.5 text-right" :class="u.rate < 50 ? 'text-red-600' : u.rate < 80 ? 'text-amber-600' : 'text-emerald-600'">
              {{ u.rate }}%
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else-if="! loading" class="text-sm text-slate-500 mt-3">Tidak ada user aktif.</p>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const summary = ref(null)
const loading = ref(true)

async function load() {
    loading.value = true
    try {
        const r = await fetch('/api/admin/summary')
        const j = await r.json()
        if (r.ok) summary.value = j
        else alert('Gagal: ' + (j.error || 'unauthorized'))
    } catch (e) {
        alert('Error: ' + e.message)
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>