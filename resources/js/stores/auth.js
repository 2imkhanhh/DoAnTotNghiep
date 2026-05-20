import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoggedIn: !!localStorage.getItem('access_token'),
    }),

    getters: {
        avatarUrl: (state) => {
            if (state.user?.avatar) return state.user.avatar;
            return `https://ui-avatars.com/api/?name=${state.user?.name || 'User'}&background=020037&color=fff`;
        },
        isAdmin: (state) => state.user?.role === 1
    },

    actions: {
        async fetchUser() {
            if (!localStorage.getItem('access_token')) {
                this.user = null;
                this.isLoggedIn = false;
                return;
            }

            try {
                const response = await axios.get('/api/auth/me');
                this.user = response.data;
                this.isLoggedIn = true;
            } catch (error) {
                this.user = null;
                this.isLoggedIn = false;
                // Interceptor will handle token refresh, so we don't necessarily clear tokens here 
                // unless it's a definitive 401/fail
            }
        },

        async login(credentials) {
            try {
                const response = await axios.post('/api/auth/login', credentials);
                if (response.data.success) {
                    localStorage.setItem('access_token', response.data.access_token);
                    localStorage.setItem('refresh_token', response.data.refresh_token);
                    this.user = response.data.user;
                    this.isLoggedIn = true;
                    // Khởi tạo lại kết nối Echo với token mới
                    if (typeof window.initializeEcho === 'function') {
                        window.initializeEcho();
                    }
                    return { success: true };
                }
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.error || error.response?.data?.message || 'Đăng nhập thất bại' 
                };
            }
        },

        async logout() {
            const refreshToken = localStorage.getItem('refresh_token');
            try {
                await axios.post('/api/auth/logout', { refresh_token: refreshToken });
            } catch (e) {
                console.error('Logout error:', e);
            } finally {
                // Ngắt kết nối Echo khi đăng xuất
                if (window.Echo) {
                    try {
                        window.Echo.disconnect();
                    } catch (e) {
                        console.error('Error disconnecting Echo on logout:', e);
                    }
                }
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                this.user = null;
                this.isLoggedIn = false;
                window.location.href = '/login';
            }
        },

        setUser(userData) {
            this.user = userData;
        }
    }
});
