<template>
  <div class="min-h-screen bg-surface flex flex-col font-sans text-on-surface"
    :class="{ 'items-center justify-center p-4': isAuthRoute }">
    <!-- Full Header cho các trang thường -->
    <Header v-if="!isAuthRoute" />

    <!-- Minimal Header cho trang Auth -->
    <header v-else class="absolute top-0 left-0 w-full p-6 flex items-center z-10">
      <router-link to="/" class="flex items-center gap-2 text-primary hover:opacity-80 transition-opacity">
        <span class="material-symbols-outlined text-3xl font-bold">storefront</span>
        <span class="font-bold text-xl tracking-tight">Chợ Đồ Cũ</span>
      </router-link>
    </header>

    <!-- Nội dung chính của trang -->
    <main :class="isAuthRoute
      ? 'w-full max-w-[480px] mt-16 relative'
      : 'flex-grow w-full'
      ">
      <router-view></router-view>
    </main>

    <!-- Footer cho các trang thường -->
    <Footer v-if="!isAuthRoute" />
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";

const route = useRoute();
const isAuthRoute = computed(() =>
  ["/login", "/register", "/forgot-password", "/reset-password"].includes(
    route.path,
  ),
);
</script>

<style>
/* CSS toàn cục có thể để ở đây, hoặc dùng Tailwind trong file CSS */
</style>
