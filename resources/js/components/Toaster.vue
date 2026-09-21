<template>
  <Teleport to="body">
    <div class="fixed z-[60] bottom-20 lg:bottom-6 inset-x-0 lg:inset-x-auto lg:right-6 flex flex-col items-center lg:items-end gap-2 px-4 pointer-events-none">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-3"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
      >
        <div
          v-for="t in toasts"
          :key="t.id"
          class="pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg max-w-sm w-full lg:w-auto"
          :class="t.tone"
          role="status"
        >
          <span class="material-symbols-outlined ms-filled text-[19px] shrink-0">{{ t.icon }}</span>
          <span class="text-[13px] font-semibold leading-snug">{{ t.message }}</span>
          <button
            type="button"
            class="ml-auto shrink-0 opacity-70 hover:opacity-100 transition-opacity"
            aria-label="Tutup"
            @click="dismiss(t.id)"
          >
            <span class="material-symbols-outlined text-[17px]">close</span>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast'

const { toasts, dismiss } = useToast()
</script>
