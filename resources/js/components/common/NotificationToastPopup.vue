<template>
    <div class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
        <TransitionGroup name="toast-slide">
            <div v-for="notif in notificationStore.activeToasts" :key="notif.toastId"
                class="pointer-events-auto w-80 sm:w-96 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant overflow-hidden"
                @click="handleNotificationClick(notif)">
                <div class="p-4 hover:bg-surface-container-low transition-colors cursor-pointer relative bg-primary-container/10">
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
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm text-on-surface font-bold pr-2">
                                        {{ notif.data.message }}
                                    </p>
                                    <button @click.stop="closeToast(notif.toastId)" class="text-on-surface-variant hover:text-error transition-colors -mt-1 -mr-1">
                                        <span class="material-symbols-outlined text-[20px]">close</span>
                                    </button>
                                </div>
                                <p class="text-xs text-on-surface-variant mt-1 text-primary font-medium">Bấm để xem chi tiết</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../../stores/notification';

const router = useRouter();
const notificationStore = useNotificationStore();

const handleNotificationClick = async (notification) => {
    notificationStore.removeToast(notification.toastId);
    
    if (!notification.read_at) {
        await notificationStore.markAsRead(notification.id);
    }
    
    if (notification.data.url) {
        router.push(notification.data.url);
    }
};

const closeToast = (toastId) => {
    notificationStore.removeToast(toastId);
};
</script>

<style scoped>
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(100px) scale(0.95);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(100px) scale(0.95);
}
</style>
