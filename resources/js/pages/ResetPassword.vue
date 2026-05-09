<template>
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Tạo mật
                khẩu mới</h1>
            <p v-if="isValidLink" class="text-body-lg text-on-surface-variant">Cho tài khoản: <b class="text-primary">{{
                    email }}</b></p>
            <p v-else class="text-body-lg text-on-surface-variant">Đang tải thông tin...</p>
        </div>

        <form v-if="isValidLink" @submit.prevent="handleResetPassword" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Mật khẩu mới</span>
                    <input v-model="form.password"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập mật khẩu mới" required type="password" />
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Xác nhận mật khẩu
                        mới</span>
                    <input v-model="form.password_confirmation"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập lại mật khẩu mới" required type="password" />
                </label>
            </div>

            <div v-if="errorMessage" class="text-error text-sm font-bold">
                {{ errorMessage }}
            </div>

            <button :disabled="isLoading"
                class="w-full h-14 mt-4 bg-linear-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                type="submit">
                <span>{{ isLoading ? 'Đang xử lý...' : 'Cập nhật mật khẩu' }}</span>
                <span v-if="!isLoading" class="material-symbols-outlined">lock_reset</span>
            </button>
        </form>

        <div v-else class="text-center">
            <span class="material-symbols-outlined text-error text-5xl mb-2">error</span>
            <p class="text-error font-bold mb-4">Đường dẫn không hợp lệ hoặc thiếu thông tin.</p>
            <router-link to="/forgot-password" class="text-primary underline">Quay lại trang quên mật khẩu</router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const email = ref('');
const token = ref('');
const isValidLink = ref(false);

const form = reactive({
    password: '',
    password_confirmation: ''
});

const errorMessage = ref('');
const isLoading = ref(false);

onMounted(() => {
    // Extract from query string like /reset-password?token=...&email=...
    token.value = route.query.token;
    email.value = route.query.email;

    if (token.value && email.value) {
        isValidLink.value = true;
    } else {
        isValidLink.value = false;
    }
});

const handleResetPassword = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await axios.post('/api/reset-password', {
            email: email.value,
            token: token.value,
            password: form.password,
            password_confirmation: form.password_confirmation
        });

        if (response.ok || response.data.success || response.status === 200) {
            alert('Đổi mật khẩu thành công! Bạn sẽ được chuyển hướng về trang Đăng nhập.');
            router.push('/login');
        }
    } catch (error) {
        if (error.response && error.response.data) {
            const data = error.response.data;
            errorMessage.value = data.errors
                ? Object.values(data.errors)[0][0]
                : (data.error || 'Đổi mật khẩu thất bại.');
        } else {
            errorMessage.value = 'Không thể kết nối đến máy chủ.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
