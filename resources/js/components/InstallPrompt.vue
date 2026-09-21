<template>
  <!-- ===== Banner ajakan pasang (muncul otomatis) ===== -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-6"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-6"
    >
      <div
        v-if="visible"
        class="fixed z-50 inset-x-3 bottom-20 lg:inset-x-auto lg:right-6 lg:bottom-6 lg:w-96"
        role="dialog"
        aria-labelledby="install-title"
      >
        <div class="card p-4 shadow-lg border-brand-200/70 bg-white">
          <div class="flex items-start gap-3">
            <!-- Pakai :src (binding) supaya Vite tidak membundel asset public/. -->
            <div class="w-11 h-11 rounded-xl bg-brand-700 flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/25 overflow-hidden">
              <img :src="iconUrl" alt="" class="w-8 h-8 rounded-lg" width="32" height="32">
            </div>

            <div class="min-w-0 flex-1">
              <h3 id="install-title" class="text-[13px] font-bold text-slate-900 tracking-editorial">
                Pasang MagangHub di HP kamu
              </h3>
              <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">
                Buka sekali klik dari layar utama — tidak perlu buka browser lagi. Notifikasi juga lebih andal.
              </p>
            </div>

            <button
              type="button"
              aria-label="Tutup"
              class="-mr-1 -mt-1 w-7 h-7 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors shrink-0"
              @click="dismiss()"
            >
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>

          <div class="flex items-center gap-2 mt-3.5">
            <button
              type="button"
              class="btn-primary h-10 flex-1 text-[13px]"
              :disabled="busy"
              @click="onInstallClick"
            >
              <span class="material-symbols-outlined text-[18px]">
                {{ busy ? 'hourglass_top' : (needsIosGuide ? 'help' : 'install_mobile') }}
              </span>
              {{ busy ? 'Menyiapkan…' : (needsIosGuide ? 'Lihat Caranya' : 'Pasang Sekarang') }}
            </button>
            <button type="button" class="btn-ghost h-10 px-3.5 text-[13px]" @click="dismiss()">
              Nanti
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ===== Panduan iOS (tidak ada API pemasangan di iOS) ===== -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="guideOpen"
        class="fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
        @click.self="closeGuide()"
      >
        <div class="w-full sm:max-w-sm bg-white rounded-t-2xl sm:rounded-2xl p-5 shadow-2xl">
          <div class="flex items-center justify-between mb-1">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-1.5 tracking-editorial">
              <span class="material-symbols-outlined text-brand-600 text-[18px]">ios_share</span>
              Cara Pasang di iPhone/iPad
            </h3>
            <button
              type="button"
              class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-800 transition-colors"
              aria-label="Tutup"
              @click="closeGuide()"
            >
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>

          <p class="text-[11px] text-slate-500 leading-relaxed mb-4">
            Safari tidak menyediakan tombol pasang otomatis. Ikuti 3 langkah ini:
          </p>

          <ol class="flex flex-col gap-3.5 mb-4">
            <li v-for="(step, i) in iosSteps" :key="i" class="flex items-start gap-3">
              <span class="w-6 h-6 rounded-full bg-blue-100 text-brand-700 font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">
                {{ i + 1 }}
              </span>
              <div class="min-w-0">
                <p class="text-[12px] font-bold text-slate-800">{{ step.title }}</p>
                <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">{{ step.desc }}</p>
              </div>
            </li>
          </ol>

          <div class="bg-amber-50 border border-amber-200/70 rounded-xl p-3 flex items-start gap-2 mb-3">
            <span class="material-symbols-outlined ms-filled text-[16px] text-amber-700 shrink-0 mt-0.5">info</span>
            <p class="text-[11px] text-amber-800 leading-relaxed">
              Push notifikasi di iPhone hanya berfungsi setelah app dipasang ke Layar Utama (iOS 16.4+).
            </p>
          </div>

          <button type="button" class="btn-primary w-full h-10 text-[13px]" @click="dismissFromGuide">
            Sudah Saya Pasang
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast'
import { useInstallPrompt } from '../composables/useInstallPrompt'

const toast = useToast()

const {
    visible,
    busy,
    guideOpen,
    needsIosGuide,
    openGuide,
    promptInstall,
    dismiss,
    closeGuide,
} = useInstallPrompt()

// Runtime URL, bukan import — asset ada di public/, bukan di bundle Vite.
const iconUrl = '/icons/icon-192.png'

const iosSteps = [
    {
        title: 'Buka menu Bagikan',
        desc: 'Ketuk ikon kotak dengan panah ke atas di bar bawah Safari.',
    },
    {
        title: 'Pilih "Add to Home Screen"',
        desc: 'Geser daftar menu ke bawah sampai menemukan opsi ini.',
    },
    {
        title: 'Ketuk "Add"',
        desc: 'Ikon MagangHub akan muncul di Layar Utama seperti aplikasi biasa.',
    },
]

async function onInstallClick() {
    // iOS tidak punya API pemasangan — langsung tampilkan panduan manual.
    if (needsIosGuide.value) {
        openGuide()
        return
    }

    const outcome = await promptInstall()

    if (outcome === 'accepted') {
        toast.success('MagangHub berhasil dipasang!')
    } else if (outcome === 'unavailable') {
        // Browser lain tanpa prompt (mis. Firefox) — arahkan ke menu browser.
        toast.info('Buka menu browser lalu pilih "Install app" / "Add to Home Screen".')
    }
    // outcome 'dismissed' -> biarkan, jangan cerewet.
}

function dismissFromGuide() {
    closeGuide()
    toast.success('Siap! Buka MagangHub dari ikon di Layar Utama.')
}
</script>
