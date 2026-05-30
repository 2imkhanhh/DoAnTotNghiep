<template>
    <header class="w-full bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <router-link to="/"
                        class="flex items-center gap-2 text-primary hover:text-primary-container transition-colors">
                        <span class="material-symbols-outlined text-3xl">storefront</span>
                        <span class="font-bold text-2xl tracking-tight hidden sm:block">Chợ Đồ Cũ</span>
                    </router-link>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl px-4 ml-8 hidden md:block">
                    <div class="relative w-full">
                        <input type="text"
                            class="w-full bg-surface-container border border-outline-variant text-on-surface rounded-full pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                            placeholder="Tìm kiếm điện thoại, laptop, xe cộ...">
                        <button class="absolute right-0 top-0 mt-2 mr-3 text-on-surface-variant hover:text-primary">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>
                </div>

                <!-- Right Navigation -->
                <div class="flex items-center gap-2 sm:gap-4 ml-auto">
                    <router-link to="/chat"
                        class="p-2 text-on-surface hover:text-primary hover:bg-surface-container rounded-full transition-colors hidden sm:block"
                        title="Tin nhắn">
                        <div class="relative">
                            <span class="material-symbols-outlined">chat</span>
                            <span v-if="chatStore.unreadMessagesCount > 0"
                                class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] text-on-error font-bold">
                                {{ chatStore.unreadMessagesCount }}
                            </span>
                        </div>
                    </router-link>

                    <router-link to="/notifications"
                        class="p-2 text-on-surface hover:text-primary hover:bg-surface-container rounded-full transition-colors hidden sm:block"
                        title="Thông báo">
                        <span class="material-symbols-outlined">notifications</span>
                    </router-link>

                    <router-link to="/seller-center/post/create"
                        class="hidden sm:flex items-center gap-1 font-bold text-primary hover:text-primary-container px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined">add_circle</span>
                        Đăng tin
                    </router-link>

                    <div class="h-6 w-px bg-outline-variant hidden sm:block mx-2"></div>

                    <!-- Auth Buttons (Guest) -->
                    <div v-if="!authStore.isLoggedIn" class="flex items-center gap-2">
                        <router-link v-if="$route.path !== '/login'" to="/login"
                            class="hidden sm:block px-4 py-2 text-primary font-bold hover:bg-surface-container-low rounded-full transition-colors">
                            Đăng nhập
                        </router-link>

                        <router-link v-if="$route.path !== '/register'" to="/register"
                            class="px-5 py-2 bg-primary text-on-primary font-bold rounded-full shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            Đăng ký
                        </router-link>
                    </div>

                    <!-- User Profile (Logged in) -->
                    <div v-if="authStore.isLoggedIn" class="flex items-center gap-2">
                        <div class="relative group cursor-pointer">
                            <div
                                class="flex items-center gap-2 p-1 pr-3 rounded-full border border-outline-variant hover:bg-surface-container-low transition-colors">
                                <img :src="authStore.avatarUrl" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                <span class="font-bold text-sm hidden sm:block text-on-surface">{{ authStore.user?.name
                                    || 'Tài khoản' }}</span>
                                <span
                                    class="material-symbols-outlined text-sm text-on-surface-variant group-hover:text-primary transition-colors">expand_more</span>
                            </div>

                            <!-- Dropdown menu -->
                            <div
                                class="absolute right-0 mt-2 w-48 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="py-2">
                                    <router-link to="/profile"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Trang
                                        cá nhân</router-link>
                                    <router-link v-if="authStore.isAdmin" to="/admin/dashboard"
                                        class="block px-4 py-2 text-sm text-primary font-bold hover:bg-surface-container-low hover:text-primary">Quản
                                        trị hệ thống</router-link>
                                    <router-link to="/seller-center/dashboard"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary font-bold text-primary">Cửa
                                        hàng</router-link>
                                    <router-link to="/profile/favorites"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Tin
                                        đăng yêu thích</router-link>
                                    <router-link to="/my-orders"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Đơn
                                        mua</router-link>
                                    <div class="border-t border-outline-variant my-1"></div>
                                    <button @click="authStore.logout()"
                                        class="w-full text-left block px-4 py-2 text-sm text-error hover:bg-error-container font-bold cursor-pointer">Đăng
                                        xuất</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 text-on-surface hover:bg-surface-container rounded-full">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useChatStore } from '../stores/chat';

const authStore = useAuthStore();
const chatStore = useChatStore();

onMounted(async () => {
    await authStore.fetchUser();
    if (authStore.isLoggedIn) {
        chatStore.fetchConversations();
    }
});

// Theo dõi trạng thái đăng nhập để tải danh sách khi đổi tài khoản
watch(() => authStore.isLoggedIn, (loggedIn) => {
    if (loggedIn) {
        chatStore.fetchConversations();
    } else {
        chatStore.unreadMessagesCount = 0;
        chatStore.conversations = [];
    }
});
</script>
