<template>
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Quên
                mật khẩu</h1>
            <p class="text-body-lg text-on-surface-variant">Đừng lo lắng! Vui lòng nhập email của bạn, chúng tôi sẽ gửi
                liên kết khôi phục.</p>
        </div>

        <form @submit.prevent="handleForgotPassword" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Địa chỉ Email đã
                        đăng ký</span>
                    <input v-model="email"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập email của bạn" required type="email" />
                </label>
            </div>

            <div v-if="statusMessage"
                :class="['text-sm font-bold p-4 rounded-lg', isSuccess ? 'bg-[#d1e7dd] text-[#0f5132]' : 'bg-error-container text-error']">
                {{ statusMessage }}
            </div>

            <button :disabled="isLoading"
                class="w-full h-14 mt-4 bg-linear-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                type="submit">
                <span>{{ isLoading ? 'Đang gửi...' : 'Gửi liên kết khôi phục' }}</span>
                <span v-if="isLoading" class="material-symbols-outlined animate-spin">sync</span>
                <span v-else class="material-symbols-outlined">mark_email_read</span>
            </button>
        </form>

        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Nhớ ra mật khẩu rồi? <router-link
                class="font-bold text-primary hover:text-secondary transition-colors underline" to="/login">Đăng
                nhập</router-link>
        </p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const email = ref('');
const statusMessage = ref('');
const isSuccess = ref(false);
const isLoading = ref(false);

const handleForgotPassword = async () => {
    statusMessage.value = '';
    isLoading.value = true;
    isSuccess.value = false;

    try {
        const response = await axios.post('/api/forgot-password', {
            email: email.value
        });

        if (response.status === 200 || response.data.success) {
            isSuccess.value = true;
            statusMessage.value = 'Thành công! Vui lòng kiểm tra hòm thư Email của bạn.';
            email.value = '';
        }
    } catch (error) {
        isSuccess.value = false;
        if (error.response && error.response.data) {
            const data = error.response.data;
            statusMessage.value = data.errors
                ? Object.values(data.errors)[0][0]
                : (data.message || 'Có lỗi xảy ra.');
        } else {
            statusMessage.value = 'Không thể kết nối đến máy chủ.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
