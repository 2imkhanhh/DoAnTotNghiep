import { createRouter, createWebHistory } from 'vue-router';

// Sẽ tạo các component này ở bước sau
// import Home from './pages/Home.vue';
// import Login from './pages/Login.vue';
// import Register from './pages/Register.vue';

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
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('./pages/Register.vue'),
    },
    {
        path: '/forgot-password',
        name: 'ForgotPassword',
        component: () => import('./pages/ForgotPassword.vue'),
    },
    {
        path: '/reset-password',
        name: 'ResetPassword',
        component: () => import('./pages/ResetPassword.vue'),
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('./pages/Profile.vue'),
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
