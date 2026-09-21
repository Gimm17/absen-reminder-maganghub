<template>
  <div class="flex flex-col gap-2.5">
    <div
      v-for="slot in store.timeline"
      :key="slot.key"
      class="flex items-center justify-between p-3 rounded-xl border transition-colors"
      :class="rowClass(slot)"
    >
      <!-- Kiri: ikon + label -->
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border" :class="iconClass(slot)">
          <span class="material-symbols-outlined text-[19px]">{{ slot.icon }}</span>
        </div>

        <div class="flex flex-col min-w-0">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-xs font-bold" :class="titleClass(slot)">
              {{ variant === 'desktop' ? slot.time + ' WITA' : slot.title }}
              <span v-if="variant === 'desktop'" class="text-slate-400 font-semibold">• {{ slot.short }}</span>
            </span>

            <span
              v-if="slot.state === 'next'"
              class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-brand-200/80 text-brand-900"
            >
              Berikutnya
            </span>
          </div>

          <span class="text-[11px] truncate" :class="descClass(slot)">
            {{ variant === 'desktop' ? slot.title + ' — ' + slot.desc : slot.desc }}
          </span>
        </div>
      </div>

      <!-- Kanan: jam + status -->
      <div class="flex flex-col items-end shrink-0 pl-2">
        <span class="tabular text-xs font-bold tracking-tight" :class="titleClass(slot)">
          {{ slot.time }} WITA
        </span>

        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded mt-0.5 flex items-center gap-0.5 border tabular" :class="badgeClass(slot)">
          <span v-if="slot.state === 'sent'" class="material-symbols-outlined text-[12px]">done_all</span>
          {{ badgeText(slot) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useUserStore } from '../stores/user'

defineProps({
    variant: { type: String, default: 'mobile' },
})

const store = useUserStore()

function rowClass(slot) {
    if (slot.state === 'next') return 'bg-blue-50/70 border-blue-200/70 shadow-xs'
    if (slot.state === 'off') return 'bg-slate-50 border-slate-100/80 opacity-60'
    return 'bg-slate-50 border-slate-100/80'
}

function iconClass(slot) {
    if (slot.state === 'next') return 'bg-brand-600 text-white border-brand-600 shadow-xs'
    if (slot.state === 'off') return 'bg-slate-100 text-slate-400 border-slate-200'
    if (slot.tone === 'amber') return 'bg-amber-50 text-amber-600 border-amber-100'
    if (slot.tone === 'rose') return 'bg-rose-50 text-rose-600 border-rose-100'
    return 'bg-slate-100 text-slate-600 border-slate-200'
}

function titleClass(slot) {
    if (slot.state === 'next') return 'text-brand-900'
    if (slot.key === 'slot-3' && slot.state !== 'off') return 'text-rose-600'
    if (slot.state === 'off') return 'text-slate-400'
    return 'text-slate-800'
}

function descClass(slot) {
    if (slot.state === 'next') return 'text-brand-800/80'
    if (slot.state === 'off') return 'text-slate-400'
    return 'text-slate-500'
}

function badgeClass(slot) {
    if (slot.state === 'sent') return 'text-emerald-600 bg-emerald-50 border-emerald-100'
    if (slot.state === 'next') return 'text-brand-700 bg-white/90 border-blue-200'
    if (slot.state === 'off') return 'text-slate-400 bg-slate-50 border-slate-200'
    return 'text-rose-700 bg-rose-50 border-rose-100'
}

function badgeText(slot) {
    if (slot.state === 'sent') return 'Terkirim'
    if (slot.state === 'next') return slot.eta ? `Dalam ${slot.eta}` : 'Berikutnya'
    if (slot.state === 'off') return 'Nonaktif'
    return 'Siaga'
}</script>
