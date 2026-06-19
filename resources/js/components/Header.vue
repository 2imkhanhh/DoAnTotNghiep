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
                            v-model="searchQuery"
                            @keyup.enter="handleSearch"
                            class="w-full bg-surface-container border border-outline-variant text-on-surface rounded-full pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                            placeholder="Tìm kiếm điện thoại, laptop, xe cộ...">
                        <button @click="handleSearch" class="absolute right-0 top-0 mt-2 mr-3 text-on-surface-variant hover:text-primary cursor-pointer">
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

                    <NotificationDropdown />

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
                        <div class="relative cursor-pointer custom-dropdown" @click="isProfileDropdownOpen = !isProfileDropdownOpen">
                            <div
                                class="flex items-center gap-2 p-1 pr-3 rounded-full border border-outline-variant hover:bg-surface-container-low transition-colors">
                                <img :src="authStore.avatarUrl" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                <span class="font-bold text-sm hidden sm:block text-on-surface">{{ authStore.user?.name
                                    || 'Tài khoản' }}</span>
                                <span
                                    class="material-symbols-outlined text-sm text-on-surface-variant transition-transform duration-300"
                                    :class="{ 'rotate-180': isProfileDropdownOpen }">expand_more</span>
                            </div>

                            <!-- Dropdown menu -->
                            <div v-if="isProfileDropdownOpen"
                                class="absolute right-0 mt-2 w-48 bg-surface-container-lowest rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] border border-outline-variant transition-all z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                                <div class="py-2">
                                    <router-link to="/profile" @click="isProfileDropdownOpen = false"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Trang
                                        cá nhân</router-link>
                                    <router-link to="/seller-center/dashboard" @click="isProfileDropdownOpen = false"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary font-bold text-primary">Cửa
                                        hàng</router-link>
                                    <router-link to="/profile/favorites" @click="isProfileDropdownOpen = false"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Tin
                                        đăng yêu thích</router-link>
                                    <router-link to="/my-orders" @click="isProfileDropdownOpen = false"
                                        class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Đơn
                                        mua</router-link>
                                    <div class="border-t border-outline-variant my-1"></div>
                                    <button @click="authStore.logout(); isProfileDropdownOpen = false"
                                        class="w-full text-left block px-4 py-2 text-sm text-error hover:bg-error-container font-bold cursor-pointer">Đăng
                                        xuất</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <button @click="isMobileMenuOpen = true" class="md:hidden p-2 text-on-surface hover:bg-surface-container rounded-full cursor-pointer">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay & Drawer -->
        <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 flex md:hidden">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="isMobileMenuOpen = false"></div>
            
            <!-- Drawer -->
            <div class="relative w-4/5 max-w-sm h-full bg-surface-container-lowest shadow-2xl flex flex-col transform transition-transform animate-in slide-in-from-left-full">
                <div class="p-4 border-b border-outline-variant flex items-center justify-between">
                    <router-link to="/" @click="isMobileMenuOpen = false" class="flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-3xl">storefront</span>
                        <span class="font-bold text-xl">Chợ Đồ Cũ</span>
                    </router-link>
                    <button @click="isMobileMenuOpen = false" class="p-2 text-on-surface-variant hover:text-error rounded-full bg-surface-container-low cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 custom-scrollbar">
                    <!-- Search -->
                    <div class="relative w-full mb-6">
                        <input type="text" v-model="searchQuery" @keyup.enter="handleSearch(); isMobileMenuOpen = false"
                            class="w-full bg-surface-container border border-outline-variant text-on-surface rounded-full pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                            placeholder="Tìm kiếm...">
                        <button @click="handleSearch(); isMobileMenuOpen = false" class="absolute right-0 top-0 mt-2 mr-3 text-on-surface-variant cursor-pointer">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                    </div>

                    <!-- Post Button -->
                    <router-link to="/seller-center/post/create" @click="isMobileMenuOpen = false"
                        class="flex items-center justify-center gap-2 font-bold text-primary bg-primary/10 px-4 py-3 rounded-xl mb-6 hover:bg-primary/20 transition-colors">
                        <span class="material-symbols-outlined">add_circle</span>
                        Đăng tin miễn phí
                    </router-link>

                    <!-- Links -->
                    <div class="space-y-1 border-t border-outline-variant pt-4 mb-6">
                        <router-link to="/" @click="isMobileMenuOpen = false" class="block px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium">Trang chủ</router-link>
                        <router-link to="/chat" @click="isMobileMenuOpen = false" class="block px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium flex justify-between items-center">
                            Tin nhắn
                            <span v-if="chatStore.unreadMessagesCount > 0" class="bg-error text-on-error text-xs font-bold px-2 py-0.5 rounded-full">{{ chatStore.unreadMessagesCount }}</span>
                        </router-link>
                    </div>

                    <!-- Auth Section -->
                    <div v-if="!authStore.isLoggedIn" class="space-y-3">
                        <router-link to="/login" @click="isMobileMenuOpen = false" class="block w-full text-center px-4 py-3 border border-primary text-primary font-bold rounded-xl">Đăng nhập</router-link>
                        <router-link to="/register" @click="isMobileMenuOpen = false" class="block w-full text-center px-4 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-md">Đăng ký</router-link>
                    </div>
                    
                    <div v-else class="border-t border-outline-variant pt-4">
                        <div class="flex items-center gap-3 px-4 py-2 mb-4">
                            <img :src="authStore.avatarUrl" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
                            <div>
                                <div class="font-bold text-on-surface">{{ authStore.user?.name || 'Tài khoản' }}</div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <router-link to="/profile" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium">
                                <span class="material-symbols-outlined text-on-surface-variant">person</span> Trang cá nhân
                            </router-link>
                            <router-link to="/seller-center/dashboard" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium">
                                <span class="material-symbols-outlined text-on-surface-variant">store</span> Cửa hàng của tôi
                            </router-link>
                            <router-link to="/profile/favorites" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium">
                                <span class="material-symbols-outlined text-on-surface-variant">favorite</span> Tin đã lưu
                            </router-link>
                            <router-link to="/my-orders" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-on-surface hover:bg-surface-container rounded-xl font-medium">
                                <span class="material-symbols-outlined text-on-surface-variant">shopping_bag</span> Đơn mua
                            </router-link>
                            <button @click="authStore.logout(); isMobileMenuOpen = false" class="w-full flex items-center gap-3 px-4 py-3 text-error hover:bg-error-container rounded-xl font-bold cursor-pointer">
                                <span class="material-symbols-outlined">logout</span> Đăng xuất
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useChatStore } from '../stores/chat';
import NotificationDropdown from './common/NotificationDropdown.vue';

const router = useRouter();
const authStore = useAuthStore();
const chatStore = useChatStore();

const searchQuery = ref('');
const isMobileMenuOpen = ref(false);

const isProfileDropdownOpen = ref(false);

const closeDropdown = (e) => {
    if (!e.target.closest('.custom-dropdown')) {
        isProfileDropdownOpen.value = false;
    }
};

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.push({ path: '/marketplace', query: { search: searchQuery.value.trim() } });
    } else {
        router.push({ path: '/marketplace' });
    }
};

onMounted(async () => {
    window.addEventListener('click', closeDropdown);
    await authStore.fetchUser();
    if (authStore.isLoggedIn) {
        chatStore.fetchConversations();
    }
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
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
