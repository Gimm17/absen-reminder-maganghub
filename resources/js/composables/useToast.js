import { ref } from 'vue'

/**
 * Toast global — supaya tidak ada aksi user yang selesai tanpa umpan balik.
 * Dipakai lewat <Toaster /> yang dipasang sekali di App.vue.
 */
const toasts = ref([])
let seq = 0

const ICONS = {
    success: 'check_circle',
    error: 'error',
    info: 'info',
}

const TONES = {
    success: 'bg-emerald-600 text-white',
    error: 'bg-rose-600 text-white',
    info: 'bg-slate-900 text-white',
}

function push(message, type = 'success', duration = 3200) {
    const id = ++seq
    toasts.value.push({ id, message, type, icon: ICONS[type] ?? ICONS.info, tone: TONES[type] ?? TONES.info })

    setTimeout(() => dismiss(id), duration)
    return id
}

function dismiss(id) {
    const i = toasts.value.findIndex(t => t.id === id)
    if (i !== -1) toasts.value.splice(i, 1)
}

export function useToast() {
    return {
        toasts,
        dismiss,
        success: (m, d) => push(m, 'success', d),
        error: (m, d) => push(m, 'error', d ?? 4500),
        info: (m, d) => push(m, 'info', d),
    }
}
