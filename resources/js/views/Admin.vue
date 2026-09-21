<template>
  <div class="flex flex-col gap-4 lg:gap-6">

    <!-- Hero -->
    <section class="reveal">
      <span class="pill px-3 py-1.5 text-[11px] bg-brand-50 text-brand-700 border border-brand-100 mb-3">
        <span class="material-symbols-outlined text-[13px]">admin_panel_settings</span>
        Panel Admin
      </span>
      <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 tracking-editorial leading-tight">
        Ringkasan Kepatuhan
      </h1>
      <p class="text-sm lg:text-[15px] text-slate-500 mt-1.5">
        Pantau kehadiran peserta dan ekspor laporan absensi.
      </p>
    </section>

    <p v-if="loading" class="card p-5 text-sm text-slate-500 flex items-center gap-2 reveal">
      <span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
      Memuat data…
    </p>

    <p v-else-if="error" class="card p-5 text-sm text-rose-600 flex items-start gap-2 reveal">
      <span class="material-symbols-outlined text-[18px] shrink-0">error</span>
      {{ error }}
    </p>

    <template v-else-if="summary">
      <!-- Stat tiles -->
      <section class="grid grid-cols-3 gap-3 lg:gap-4 reveal" style="animation-delay: 60ms">
        <div class="card p-3.5 lg:p-5">
          <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[18px] text-brand-600">group</span>
            <span class="text-[10px] lg:text-[11px] font-bold text-slate-500 uppercase tracking-wide">User Aktif</span>
          </div>
          <div class="tabular text-2xl lg:text-3xl font-bold text-slate-900 leading-none">{{ summary.total_users }}</div>
        </div>

        <div class="card p-3.5 lg:p-5">
          <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[18px] text-emerald-600">task_alt</span>
            <span class="text-[10px] lg:text-[11px] font-bold text-slate-500 uppercase tracking-wide">Sudah Absen</span>
          </div>
          <div class="tabular text-2xl lg:text-3xl font-bold text-emerald-600 leading-none">{{ summary.checked_today }}</div>
        </div>

        <div class="card p-3.5 lg:p-5">
          <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-[18px] text-amber-600">trending_up</span>
            <span class="text-[10px] lg:text-[11px] font-bold text-slate-500 uppercase tracking-wide">Tingkat</span>
          </div>
          <div class="tabular text-2xl lg:text-3xl font-bold text-amber-600 leading-none">{{ summary.today_rate }}%</div>
        </div>
      </section>

      <!-- Table -->
      <section class="card p-4 lg:p-5 reveal" style="animation-delay: 120ms">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-3 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
              <span class="material-symbols-outlined text-[18px]">table_chart</span>
            </div>
            <h3 class="text-sm lg:text-base font-bold text-slate-900 tracking-editorial">Detail Per Peserta</h3>
          </div>

          <button type="button" class="btn-ghost h-9 px-3.5 text-[12px] flex items-center gap-1.5" @click="exportCsv">
            <span class="material-symbols-outlined text-[16px]">download</span>
            Export CSV
          </button>
        </div>

        <div v-if="summary.per_user.length" class="overflow-x-auto -mx-4 lg:mx-0 px-4 lg:px-0">
          <table class="w-full text-sm min-w-[520px]">
            <thead>
              <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wide border-b border-slate-200">
                <th class="py-2.5">Nama</th>
                <th class="py-2.5">Email</th>
                <th class="py-2.5 text-right">Checkin</th>
                <th class="py-2.5 text-right w-40">Kepatuhan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in summary.per_user" :key="u.id" class="border-b border-slate-100 last:border-0">
                <td class="py-3 font-semibold text-slate-800">{{ u.name }}</td>
                <td class="py-3 text-slate-500 text-[13px]">{{ u.email }}</td>
                <td class="tabular py-3 text-right font-semibold text-slate-700">{{ u.checked }}/{{ u.expected }}</td>
                <td class="py-3">
                  <div class="flex items-center justify-end gap-2">
                    <div class="w-20 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                      <div class="h-full rounded-full transition-all" :class="barClass(u.rate)" :style="{ width: Math.min(u.rate, 100) + '%' }"></div>
                    </div>
                    <span class="tabular text-[12px] font-bold w-11 text-right" :class="textClass(u.rate)">{{ u.rate }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else class="text-sm text-slate-500 py-6 text-center">Belum ada peserta aktif.</p>
      </section>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useToast } from '../composables/useToast'

const toast = useToast()
const summary = ref(null)
const loading = ref(true)
const error = ref('')

function exportCsv() {
    toast.info('Menyiapkan file CSV…')
    window.location.href = '/api/admin/export.csv'
}

function barClass(rate) {
    if (rate < 50) return 'bg-rose-500'
    if (rate < 80) return 'bg-amber-500'
    return 'bg-emerald-500'
}

function textClass(rate) {
    if (rate < 50) return 'text-rose-600'
    if (rate < 80) return 'text-amber-600'
    return 'text-emerald-600'
}

async function load() {
    loading.value = true
    error.value = ''
    try {
        const r = await fetch('/api/admin/summary', { headers: { Accept: 'application/json' } })
        const j = await r.json()
        if (! r.ok) throw new Error(j.error || j.message || 'Gagal memuat data.')
        summary.value = j
    } catch (e) {
        error.value = e.message
        toast.error(e.message)
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
