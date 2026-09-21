import { computed, onMounted, onUnmounted, ref } from 'vue'

/**
 * Menangani pemasangan PWA.
 *
 * Kenyataan platform (penting, jangan diasumsikan bisa seragam):
 *
 *  - `beforeinstallprompt` HANYA ada di Chromium (Chrome/Edge/Opera di
 *    Android & desktop). Firebase/Safari/Firefox TIDAK memicu event ini.
 *  - iOS tidak punya API pemasangan sama sekali. Satu-satunya jalan adalah
 *    instruksi manual: Share → "Add to Home Screen".
 *  - Event bisa terpicu SEBELUM Vue mount, jadi sudah ditangkap lebih awal
 *    di app.blade.php (window.__pwaInstallEvent) — lihat catatan di sana.
 *  - `prompt()` hanya boleh dipanggil SEKALI per event. Kalau user menolak,
 *    event itu hangus; tunggu event baru sebelum menawarkan lagi.
 *
 * @returns {{
 *   canPrompt: import('vue').ComputedRef<boolean>,
 *   needsIosGuide: import('vue').ComputedRef<boolean>,
 *   isStandalone: import('vue').Ref<boolean>,
 *   installed: import('vue').Ref<boolean>,
 *   visible: import('vue').ComputedRef<boolean>,
 *   busy: import('vue').Ref<boolean>,
 *   promptInstall: () => Promise<'accepted'|'dismissed'|'unavailable'>,
 *   dismiss: (days?: number) => void,
 *   openGuide: () => void,
 * }}
 */

const DISMISS_KEY = 'pwa_install_dismissed_at'
const DISMISS_DAYS = 7

/** Deteksi iOS — termasuk iPadOS 13+ yang menyamar jadi macOS. */
function detectIos() {
    const ua = navigator.userAgent || ''
    if (/iPad|iPhone|iPod/.test(ua)) return true
    // iPadOS 13+ melapor sebagai "MacIntel" tapi punya layar sentuh.
    return navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1
}

/** Sudah terpasang / dibuka sebagai app? */
function detectStandalone() {
    if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true
    if (window.matchMedia && window.matchMedia('(display-mode: minimal-ui)').matches) return true
    // Properti khusus Safari iOS
    if (window.navigator.standalone === true) return true
    return false
}

function dismissedRecently() {
    try {
        const raw = localStorage.getItem(DISMISS_KEY)
        if (! raw) return false
        const elapsed = Date.now() - parseInt(raw, 10)
        return elapsed < DISMISS_DAYS * 86400 * 1000
    } catch (e) {
        return false
    }
}

export function useInstallPrompt() {
    // Ambil event yg mungkin sudah ditangkap script inline di <head>.
    const deferredEvent = ref(window.__pwaInstallEvent || null)

    const installed = ref(detectStandalone())
    const isIos = ref(detectIos())
    const dismissed = ref(dismissedRecently())
    const busy = ref(false)
    const guideOpen = ref(false)

    const isStandalone = computed(() => installed.value)
    const canPrompt = computed(() => !!deferredEvent.value && !installed.value && !dismissed.value)
    const needsIosGuide = computed(() => isIos.value && !installed.value && !dismissed.value)
    const visible = computed(() => (canPrompt.value || needsIosGuide.value) && !guideOpen.value)

    function onBeforeInstall(e) {
        // Simpan supaya bisa dipicu nanti dari tombol kita sendiri.
        e.preventDefault()
        deferredEvent.value = e
        window.__pwaInstallEvent = e
    }

    function onInstalled() {
        installed.value = true
        deferredEvent.value = null
        window.__pwaInstallEvent = null
        try { localStorage.removeItem(DISMISS_KEY) } catch (err) { /* ignore */ }
    }

    async function promptInstall() {
        const evt = deferredEvent.value
        if (! evt) return 'unavailable'

        busy.value = true
        try {
            evt.prompt()
            const { outcome } = await evt.userChoice
            // Event hangus setelah dipakai — kosongkan supaya tidak dipanggil dua kali.
            deferredEvent.value = null
            window.__pwaInstallEvent = null
            if (outcome === 'accepted') installed.value = true
            return outcome
        } catch (e) {
            return 'unavailable'
        } finally {
            busy.value = false
        }
    }

    /** Tunda penawaran; muncul lagi setelah `days` hari. */
    function dismiss(days = DISMISS_DAYS) {
        dismissed.value = true
        try {
            localStorage.setItem(DISMISS_KEY, String(Date.now()))
        } catch (e) { /* ignore */ }
    }

    function openGuide() {
        guideOpen.value = true
    }

    function closeGuide() {
        guideOpen.value = false
    }

    onMounted(() => {
        window.addEventListener('beforeinstallprompt', onBeforeInstall)
        window.addEventListener('appinstalled', onInstalled)
    })

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', onBeforeInstall)
        window.removeEventListener('appinstalled', onInstalled)
    })

    return {
        canPrompt,
        needsIosGuide,
        isStandalone,
        isIos,
        installed,
        visible,
        busy,
        guideOpen,
        promptInstall,
        dismiss,
        openGuide,
        closeGuide,
    }
}
