import { defineStore } from 'pinia';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        loading: false,
        error: null,
        isListening: false,
        activeToasts: [],
    }),
    
    actions: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/api/notifications');
                if (response.data.success) {
                    this.notifications = response.data.data.data; // paginate
                    this.unreadCount = response.data.unread_count;
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Lỗi tải thông báo';
            } finally {
                this.loading = false;
            }
        },

        async markAsRead(id) {
            try {
                const response = await axios.put(`/api/notifications/${id}/read`);
                if (response.data.success) {
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification && !notification.read_at) {
                        notification.read_at = new Date().toISOString();
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                }
            } catch (error) {
                console.error('Lỗi khi đánh dấu đã đọc', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await axios.put('/api/notifications/read-all');
                if (response.data.success) {
                    this.notifications.forEach(n => {
                        if (!n.read_at) {
                            n.read_at = new Date().toISOString();
                        }
                    });
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Lỗi khi đánh dấu tất cả đã đọc', error);
            }
        },

        addNotification(notification) {
            // Chuẩn hóa format từ pusher
            const notifId = notification.id;
            
            // Tránh trùng lặp nếu bắt được từ nhiều event listener
            if (this.notifications.some(n => n.id === notifId)) return;

            const newNotif = {
                id: notification.id,
                type: notification.type,
                data: notification.data || notification,
                read_at: null,
                created_at: new Date().toISOString(),
                toastId: Date.now() + Math.random(),
            };
            this.notifications.unshift(newNotif);
            this.unreadCount++;
            
            // Xóa bớt nếu vượt quá 15 cái trên dropdown
            if (this.notifications.length > 15) {
                this.notifications.pop();
            }

            // Thêm vào danh sách popup góc phải
            const toastInfo = { ...newNotif };
            this.activeToasts.push(toastInfo);
            
            // Tự động ẩn sau 15 giây (tăng lên để người dùng kịp nhìn thấy khi alt tab)
            setTimeout(() => {
                this.removeToast(toastInfo.toastId);
            }, 15000);
        },

        removeToast(toastId) {
            this.activeToasts = this.activeToasts.filter(t => t.toastId !== toastId);
        },

        listenForNotifications() {
            const authStore = useAuthStore();
            if (!authStore.isLoggedIn || !authStore.user) return;
            
            if (this.isListening) return; // Đã listen rồi thì thôi

            const userId = authStore.user.id;
            
            if (window.Echo) {
                const channel = window.Echo.private(`App.Models.User.${userId}`);
                
                // Mặc định của Echo (có thể bị lỗi format tên sự kiện do Reverb)
                channel.notification((notification) => {
                    this.addNotification(notification);
                });

                // Bắt trực tiếp event gốc bằng Pusher để khắc phục sự cố không tương thích tên namespace
                const pusherClient = window.Echo.connector.pusher;
                if (pusherClient) {
                    pusherClient.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (notification) => {
                        this.addNotification(notification);
                    });
                }

                this.isListening = true;
            }
        },

        stopListening() {
            const authStore = useAuthStore();
            if (authStore.user && window.Echo) {
                window.Echo.leave(`App.Models.User.${authStore.user.id}`);
            }
            this.isListening = false;
        }
    }
});
