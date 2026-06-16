<template>
  <div v-if="authStore.isLoggedIn && !authStore.user" class="h-screen w-screen flex items-center justify-center bg-surface">
     <!-- Màn hình chờ khi đang fetch user lần đầu để tránh nhấp nháy UI -->
     <span class="material-symbols-outlined animate-spin text-4xl text-primary">progress_activity</span>
  </div>

  <div v-else class="min-h-screen bg-surface flex flex-col font-sans text-on-surface"
    :class="{ 'items-center justify-center p-4': isAuthRoute }">
    <!-- Full Header cho các trang thường -->
    <Header v-if="!isAuthRoute && !isAdminRoute && !isSellerRoute" />

    <!-- Minimal Header cho trang Auth -->
    <header v-if="isAuthRoute" class="absolute top-0 left-0 w-full p-6 flex items-center z-10">
      <router-link to="/" class="flex items-center gap-2 text-primary hover:opacity-80 transition-opacity">
        <span class="material-symbols-outlined text-3xl font-bold">storefront</span>
        <span class="font-bold text-xl tracking-tight">Chợ Đồ Cũ</span>
      </router-link>
    </header>

    <!-- Nội dung chính của trang -->
    <main :class="[
      isAuthRoute ? 'w-full max-w-[480px] mt-16 relative' : 'grow w-full',
      isAdminRoute || isSellerRoute ? 'h-screen overflow-hidden' : '',
      isChatRoute ? 'overflow-hidden' : ''
    ]">
      <router-view></router-view>
    </main>

    <!-- Footer cho các trang thường -->
    <Footer v-if="!isAuthRoute && !isAdminRoute && !isSellerRoute && !isChatRoute" />

    <!-- AI Chatbot Widget (Hiển thị Global) -->
    <ChatbotWidget v-if="!isAuthRoute && !isAdminRoute && !isSellerRoute" />

    <!-- Popup Thông Báo Realtime Góc Dưới Phải -->
    <NotificationToastPopup v-if="authStore.isLoggedIn" />
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "./stores/auth";
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
import ChatbotWidget from "./components/ChatbotWidget.vue";
import NotificationToastPopup from "./components/common/NotificationToastPopup.vue";

const route = useRoute();
const authStore = useAuthStore();
const isAuthRoute = computed(() =>
  ["/login", "/register", "/forgot-password", "/reset-password"].includes(
    route.path,
  ),
);

const isAdminRoute = computed(() => route.path.startsWith('/admin'));
const isSellerRoute = computed(() => route.path.startsWith('/seller-center'));
const isChatRoute = computed(() => route.path === '/chat');
</script>

<style>
/* CSS toàn cục có thể để ở đây, hoặc dùng Tailwind trong file CSS */
</style>
