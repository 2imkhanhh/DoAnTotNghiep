<template>
    <div class="relative group cursor-pointer" ref="dropdownRef">
        <!-- Nút chuông -->
        <button @click="toggleDropdown"
            class="p-2 text-on-surface hover:text-primary hover:bg-surface-container rounded-full transition-colors relative cursor-pointer"
            title="Thông báo">
            <span class="material-symbols-outlined" :class="{ 'text-primary': isOpen }">notifications</span>
            
            <!-- Badge số lượng -->
            <span v-if="notificationStore.unreadCount > 0"
                class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-error text-[10px] text-on-error font-bold">
                {{ notificationStore.unreadCount > 99 ? '99+' : notificationStore.unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div v-show="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant z-50 overflow-hidden transition-all origin-top-right">
            
            <!-- Header -->
            <div class="px-4 py-3 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h3 class="font-bold text-on-surface">Thông báo</h3>
                <button v-if="notificationStore.unreadCount > 0" 
                    @click="markAllAsRead"
                    class="text-xs text-primary hover:text-primary-container font-medium cursor-pointer">
                    Đánh dấu tất cả đã đọc
                </button>
            </div>

            <!-- Loading -->
            <div v-if="notificationStore.loading && notificationStore.notifications.length === 0" class="p-8 flex justify-center">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            </div>

            <!-- Trống -->
            <div v-else-if="notificationStore.notifications.length === 0" class="p-8 text-center text-on-surface-variant flex flex-col items-center">
                <span class="material-symbols-outlined text-4xl mb-2 text-outline">notifications_off</span>
                <p class="text-sm">Bạn chưa có thông báo nào</p>
            </div>

            <!-- Danh sách -->
            <div v-else class="max-h-[60vh] overflow-y-auto overscroll-contain">
                <div v-for="notif in notificationStore.notifications" :key="notif.id"
                    @click="handleNotificationClick(notif)"
                    class="p-4 border-b border-outline-variant last:border-0 hover:bg-surface-container-low transition-colors cursor-pointer relative"
                    :class="{ 'bg-primary-container/10': !notif.read_at }">
                    
                    <!-- Chấm đỏ chưa đọc -->
                    <div v-if="!notif.read_at" class="absolute left-2 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-primary"></div>
                    
                    <div class="pl-2">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary" v-if="notif.data.type === 'post_pending'">pending_actions</span>
                                <span class="material-symbols-outlined text-success" v-else-if="notif.data.type === 'post_approved'">check_circle</span>
                                <span class="material-symbols-outlined text-error" v-else-if="notif.data.type === 'post_rejected'">cancel</span>
                                <span class="material-symbols-outlined text-tertiary" v-else-if="notif.data.type === 'new_review'">star_rate</span>
                                <span class="material-symbols-outlined text-warning" v-else-if="notif.data.type === 'new_order'">shopping_bag</span>
                                <span class="material-symbols-outlined text-error" v-else-if="notif.data.type === 'order_cancelled'">remove_shopping_cart</span>
                                <span class="material-symbols-outlined text-primary" v-else>notifications</span>
                            </div>
                            <div>
                                <p class="text-sm text-on-surface" :class="{ 'font-bold': !notif.read_at, 'font-medium': notif.read_at }">
                                    {{ notif.data.message }}
                                </p>
                                <p class="text-xs text-on-surface-variant mt-1">{{ formatDate(notif.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-outline-variant text-center" v-if="notificationStore.notifications.length > 0">
                <!-- Chỉ là dummy text hoặc link tới /notifications nếu muốn -->
                <div class="py-2 text-xs text-on-surface-variant font-medium">
                    Lịch sử thông báo
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../../stores/notification';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const notificationStore = useNotificationStore();
const authStore = useAuthStore();
const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && notificationStore.notifications.length === 0 && !notificationStore.loading) {
        notificationStore.fetchNotifications();
    }
};

const markAllAsRead = async (e) => {
    e.stopPropagation();
    await notificationStore.markAllAsRead();
};

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        await notificationStore.markAsRead(notification.id);
    }
    
    isOpen.value = false;
    
    if (notification.data.url) {
        router.push(notification.data.url);
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // in seconds

    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
    if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;
    
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// Đóng dropdown khi click ra ngoài
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Gọi tải thông báo và listen khi component mount nếu đã đăng nhập
    if (authStore.isLoggedIn) {
        notificationStore.fetchNotifications();
        notificationStore.listenForNotifications();
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Watch sự thay đổi đăng nhập để fetch hoặc stop listen
watch(() => authStore.isLoggedIn, (loggedIn) => {
    if (loggedIn) {
        notificationStore.fetchNotifications();
        notificationStore.listenForNotifications();
    } else {
        notificationStore.stopListening();
        notificationStore.notifications = [];
        notificationStore.unreadCount = 0;
    }
});
</script>
