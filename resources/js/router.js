import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('./pages/Home.vue'),
    },
    {
        path: '/post/:slug',
        name: 'PostDetail',
        component: () => import('./pages/PostDetail.vue'),
    },
    {
        path: '/marketplace',
        name: 'AllPosts',
        component: () => import('./pages/CategoryPosts.vue'),
    },
    {
        path: '/category/:slug',
        name: 'CategoryPosts',
        component: () => import('./pages/CategoryPosts.vue'),
    },
    {
        path: '/checkout/:slug',
        name: 'Checkout',
        component: () => import('./pages/Checkout.vue'),
        meta: { requiresAuth: true }
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
        component: () => import('./pages/ForgotPassword.vue')
    },
    {
        path: '/reset-password',
        name: 'ResetPassword',
        component: () => import('./pages/ResetPassword.vue')
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import('./pages/Profile.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/my-orders',
        name: 'MyOrders',
        component: () => import('./pages/MyOrders.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/chat',
        name: 'Chat',
        component: () => import('./pages/Chat.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller/:id',
        name: 'PublicProfile',
        component: () => import('./pages/PublicProfile.vue'),
    },
    {
        path: '/profile/favorites',
        name: 'UserFavorites',
        component: () => import('./pages/UserFavorites.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/dashboard',
        name: 'SellerDashboard',
        component: () => import('./pages/seller/SellerDashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/posts',
        name: 'SellerPosts',
        component: () => import('./pages/seller/SellerPosts.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/post/create',
        name: 'PostCreate',
        component: () => import('./pages/seller/PostCreate.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/post/edit/:id',
        name: 'PostEdit',
        component: () => import('./pages/seller/PostEdit.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/orders',
        name: 'SellerOrders',
        component: () => import('./pages/seller/SellerOrders.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/seller-center/packages',
        name: 'SellerPackages',
        component: () => import('./pages/seller/Packages.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/admin/dashboard',
        name: 'AdminDashboard',
        component: () => import('./pages/admin/AdminDashboard.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/categories',
        name: 'AdminCategories',
        component: () => import('./pages/admin/AdminCategories.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/categories/:id/attributes',
        name: 'AdminCategoryAttributes',
        component: () => import('./pages/admin/AdminCategoryAttributes.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/posts',
        name: 'AdminPosts',
        component: () => import('./pages/admin/AdminPosts.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/banners',
        name: 'AdminBanners',
        component: () => import('./pages/admin/AdminBanners.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/users',
        name: 'AdminUsers',
        component: () => import('./pages/admin/AdminUsers.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/purchases',
        name: 'AdminPurchases',
        component: () => import('./pages/admin/AdminPurchases.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/packages',
        name: 'AdminPackages',
        component: () => import('./pages/admin/AdminPackages.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path: '/admin/profile',
        name: 'AdminProfile',
        component: () => import('./pages/admin/AdminProfile.vue'),
        meta: { requiresAuth: true, requiresAdmin: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
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

    // 2. Chặn Admin truy cập vào các trang dành cho User (bao gồm trang chủ, marketplace, v.v.)
    if (isLoggedIn && isAdmin && !to.path.startsWith('/admin')) {
        return next({ name: 'AdminDashboard' });
    }

    // 3. Kiểm tra quyền truy cập route yêu cầu Admin (Chỉ Admin mới được vào /admin)
    if (to.meta.requiresAdmin && !isAdmin) {
        return next({ name: 'Home' });
    }

    // 4. Nếu đã đăng nhập thì không cho vào trang Login/Register
    if (to.meta.guestOnly && isLoggedIn) {
        if (isAdmin) return next({ name: 'AdminDashboard' });
        return next({ name: 'Home' });
    }

    next();
});

export default router;
