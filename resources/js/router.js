import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('./pages/Home.vue'),
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import('./pages/Login.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('./pages/Register.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/forgot-password',
        name: 'ForgotPassword',
        component: () => import('./pages/ForgotPassword.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/reset-password',
        name: 'ResetPassword',
        component: () => import('./pages/ResetPassword.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('./pages/Profile.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/post/create',
        name: 'PostCreate',
        component: () => import('./pages/PostCreate.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/admin/dashboard',
        name: 'AdminDashboard',
        component: () => import('./pages/admin/AdminDashboard.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Đảm bảo đã load thông tin user nếu đã đăng nhập
    if (authStore.isLoggedIn && !authStore.user) {
        await authStore.fetchUser();
    }

    const isLoggedIn = authStore.isLoggedIn;
    const isAdmin = authStore.isAdmin;

    // 1. Kiểm tra quyền truy cập route yêu cầu đăng nhập
    if (to.meta.requiresAuth && !isLoggedIn) {
        return next({ name: 'Login' });
    }

    // 2. Kiểm tra quyền truy cập route yêu cầu Admin
    if (to.meta.requiresAdmin && !isAdmin) {
        return next({ name: 'Home' });
    }

    // 3. Nếu đã đăng nhập thì không cho vào trang Login/Register
    if (to.meta.guestOnly && isLoggedIn) {
        return next({ name: 'Home' });
    }

    next();
});

export default router;
