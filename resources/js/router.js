import { createRouter, createWebHistory } from 'vue-router'
import Register from './views/Register.vue'
import Dashboard from './views/Dashboard.vue'
import Admin from './views/Admin.vue'

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Register, meta: { title: 'Daftar' } },
        { path: '/register', component: Register, meta: { title: 'Daftar' } },
        { path: '/dashboard', component: Dashboard, meta: { title: 'Presensi' } },
        { path: '/admin', component: Admin, meta: { title: 'Admin' } },
    ],
    /** Nav desktop/bottom pakai anchor (#jadwal, #panduan) — scroll ke section. */
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition
        if (to.hash) {
            return { el: to.hash, behavior: 'smooth', top: 80 }
        }
        return { top: 0 }
    },
})