<template>
<footer class="w-full bg-surface-container-high border-t border-outline-variant pt-12 pb-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <!-- Col 1 -->
            <div class="col-span-1 md:col-span-1">
                <router-link to="/" class="flex items-center gap-2 text-primary mb-4">
                    <span class="material-symbols-outlined text-3xl">storefront</span>
                    <span class="font-bold text-xl tracking-tight">Chợ Đồ Cũ</span>
                </router-link>
                <p class="text-on-surface-variant text-sm mb-4">
                    Nền tảng giao dịch đồ cũ an toàn, tiện lợi. Kết nối người mua và người bán dễ dàng qua Chat Realtime và AI Chatbot.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-on-surface-variant hover:text-primary"><span class="material-symbols-outlined">facebook</span></a>
                    <a href="#" class="text-on-surface-variant hover:text-primary"><span class="material-symbols-outlined">play_circle</span></a>
                </div>
            </div>

            <!-- Col 2 -->
            <div>
                <h4 class="font-bold text-on-surface mb-4">Về chúng tôi</h4>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><router-link to="/about" class="hover:text-primary hover:underline">Giới thiệu</router-link></li>
                    <li><router-link to="/rules" class="hover:text-primary hover:underline">Quy chế hoạt động</router-link></li>
                    <li><router-link to="/privacy" class="hover:text-primary hover:underline">Chính sách bảo mật</router-link></li>
                    <li><router-link to="/dispute" class="hover:text-primary hover:underline">Giải quyết tranh chấp</router-link></li>
                </ul>
            </div>

            <!-- Col 3 -->
            <div>
                <h4 class="font-bold text-on-surface mb-4">Hỗ trợ khách hàng</h4>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><router-link to="/help" class="hover:text-primary hover:underline">Trung tâm trợ giúp</router-link></li>
                    <li><router-link to="/safety" class="hover:text-primary hover:underline">An toàn mua bán</router-link></li>
                    <li><router-link to="/contact" class="hover:text-primary hover:underline">Liên hệ hỗ trợ</router-link></li>
                    <li><router-link to="/guide" class="hover:text-primary hover:underline">Hướng dẫn mua hàng</router-link></li>
                </ul>
            </div>

            <!-- Col 4 -->
            <div>
                <h4 class="font-bold text-on-surface mb-4">Tải ứng dụng</h4>
                <div class="flex flex-col gap-3">
                    <a href="#" class="hover:opacity-80 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="Download on the App Store" class="h-10">
                    </a>
                    <a href="#" class="hover:opacity-80 transition-opacity">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="h-10">
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-outline-variant pt-8 text-center text-on-surface-variant text-sm">
            <p>&copy; {{ new Date().getFullYear() }} Nền tảng giao dịch đồ cũ. Đồ án tốt nghiệp.</p>
        </div>
    </div>
</footer>
</template>

<script setup>
// Footer component
</script>
