<template>
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Đăng nhập</h1>
            <p class="text-body-lg text-on-surface-variant">Chào mừng bạn quay trở lại.</p>
        </div>

        <form @submit.prevent="handleLogin" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Địa chỉ Email</span>
                    <input v-model="email"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập email của bạn" required type="email" />
                </label>
                <label class="flex flex-col relative">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-label-md font-bold text-primary tracking-wide uppercase">Mật khẩu</span>
                        <router-link to="/forgot-password"
                            class="text-sm font-bold text-primary hover:text-secondary transition-colors" tabindex="-1">
                            Quên mật khẩu?
                        </router-link>
                    </div>
                    <input v-model="password"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập mật khẩu" required type="password" />
                </label>
            </div>

            <div v-if="errorMessage" class="text-error text-sm font-bold">
                {{ errorMessage }}
            </div>

            <button
                :disabled="isLoading"
                class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                type="submit">
                <span>{{ isLoading ? 'Đang xử lý...' : 'Đăng nhập' }}</span>
                <span v-if="!isLoading" class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Chưa có tài khoản? <router-link class="font-bold text-primary hover:text-secondary transition-colors underline"
                to="/register">Đăng ký ngay</router-link>
        </p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

const handleLogin = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await axios.post('/api/auth/login', {
            email: email.value,
            password: password.value
        });

        if (response.data.success) {
            localStorage.setItem('access_token', response.data.access_token);
            localStorage.setItem('refresh_token', response.data.refresh_token);
            alert('Đăng nhập thành công!');
            // Redirect to home and reload to let App.vue and Header.vue catch the new token
            // In a real app we would use Pinia to update the global state without reloading
            window.location.href = '/';
        }
    } catch (error) {
        if (error.response && error.response.data) {
            const data = error.response.data;
            errorMessage.value = data.errors 
                ? Object.values(data.errors)[0][0] 
                : (data.error || 'Đăng nhập thất bại.');
        } else {
            errorMessage.value = 'Lỗi kết nối máy chủ.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
