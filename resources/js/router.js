import { createRouter, createWebHistory } from 'vue-router'
import Register from './views/Register.vue'
import Dashboard from './views/Dashboard.vue'
import Admin from './views/Admin.vue'

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Register, meta: { title: 'Daftar' } },
        { path: '/register', component: Register, meta: { title: 'Daftar' } },
        { path: '/dashboard', component: Dashboard, meta: { title: 'Dashboard' } },
        { path: '/admin', component: Admin, meta: { title: 'Admin' } },
    ],
})