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
            const newNotif = {
                id: notification.id,
                type: notification.type,
                data: notification.data || notification,
                read_at: null,
                created_at: new Date().toISOString()
            };
            this.notifications.unshift(newNotif);
            this.unreadCount++;
            
            // Xóa bớt nếu vượt quá 15 cái trên dropdown
            if (this.notifications.length > 15) {
                this.notifications.pop();
            }
        },

        listenForNotifications() {
            const authStore = useAuthStore();
            if (!authStore.isLoggedIn || !authStore.user) return;
            
            if (this.isListening) return; // Đã listen rồi thì thôi

            const userId = authStore.user.id;
            
            if (window.Echo) {
                window.Echo.private(`App.Models.User.${userId}`)
                    .notification((notification) => {
                        this.addNotification(notification);
                        
                        // Show toast
                        if (window.toast) {
                            window.toast.info(notification.data?.message || 'Bạn có thông báo mới!');
                        } else if (window.Swal) {
                            window.Swal.fire({
                                toast: true,
                                position: 'bottom-end',
                                showConfirmButton: false,
                                timer: 3000,
                                icon: 'info',
                                title: notification.data?.message || 'Bạn có thông báo mới!'
                            });
                        }
                    });
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
