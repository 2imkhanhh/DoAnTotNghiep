import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Request interceptor: Tự động đính kèm Token vào Header
window.axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Biến để kiểm soát việc đang refresh token
let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

// Response interceptor: Xử lý lỗi 401 (Hết hạn token)
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // Nếu lỗi 401 và không phải là lỗi từ các API auth cơ bản (login, refresh, ...)
        const isAuthRoute = originalRequest.url.includes('/api/auth/login') || 
                            originalRequest.url.includes('/api/auth/refresh') ||
                            originalRequest.url.includes('/api/auth/register') ||
                            originalRequest.url.includes('/api/forgot-password');

        if (error.response?.status === 401 && !originalRequest._retry && !isAuthRoute) {
            if (isRefreshing) {
                // Đợi cho đến khi token được làm mới
                return new Promise(function(resolve, reject) {
                    failedQueue.push({ resolve, reject });
                })
                .then(token => {
                    originalRequest.headers['Authorization'] = 'Bearer ' + token;
                    return window.axios(originalRequest);
                })
                .catch(err => {
                    return Promise.reject(err);
                });
            }

            originalRequest._retry = true;
            isRefreshing = true;

            const refreshToken = localStorage.getItem('refresh_token');
            
            if (!refreshToken) {
                isRefreshing = false;
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
                return Promise.reject(error);
            }

            try {
                // Gọi API refresh token (dùng axios instance gốc để tránh bị interceptor này bắt lại)
                const res = await axios.post('/api/auth/refresh', {
                    refresh_token: refreshToken
                });

                if (res.data.success) {
                    const { access_token, refresh_token } = res.data;
                    localStorage.setItem('access_token', access_token);
                    localStorage.setItem('refresh_token', refresh_token);
                    
                    window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + access_token;
                    processQueue(null, access_token);
                    
                    // Thực hiện lại request ban đầu với token mới
                    originalRequest.headers['Authorization'] = 'Bearer ' + access_token;
                    return window.axios(originalRequest);
                }
            } catch (refreshError) {
                processQueue(refreshError, null);
                localStorage.removeItem('access_token');
                localStorage.removeItem('refresh_token');
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
                return Promise.reject(refreshError);
            } finally {
                isRefreshing = false;
            }
        }

        return Promise.reject(error);
    }
);
