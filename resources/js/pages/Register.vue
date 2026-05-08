<template>
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Tạo tài khoản</h1>
            <p class="text-body-lg text-on-surface-variant">Bắt đầu hành trình sưu tầm những món đồ độc bản của riêng bạn.</p>
        </div>

        <form @submit.prevent="handleRegister" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Họ và tên</span>
                    <input v-model="form.name"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập họ và tên" required type="text" />
                </label>
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Email</span>
                    <input v-model="form.email"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Địa chỉ email của bạn" required type="email" />
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Mật khẩu</span>
                    <input v-model="form.password"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Tạo mật khẩu an toàn" required type="password" />
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Xác nhận mật khẩu</span>
                    <input v-model="form.password_confirmation"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập lại mật khẩu" required type="password" />
                </label>
            </div>

            <div v-if="errorMessage" class="text-error text-sm font-bold">
                {{ errorMessage }}
            </div>

            <button
                :disabled="isLoading"
                class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                type="submit">
                <span>{{ isLoading ? 'Đang xử lý...' : 'Tạo tài khoản' }}</span>
                <span v-if="!isLoading" class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>
        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Đã có tài khoản? <router-link class="font-bold text-primary hover:text-secondary transition-colors underline"
                to="/login">Đăng nhập ngay</router-link>
        </p>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const errorMessage = ref('');
const isLoading = ref(false);

const handleRegister = async () => {
    errorMessage.value = '';
    isLoading.value = true;

    try {
        const response = await axios.post('/api/auth/register', form);

        if (response.data.success || response.status === 201) {
            alert('Đăng ký thành công! Vui lòng đăng nhập.');
            router.push('/login');
        }
    } catch (error) {
        if (error.response && error.response.data) {
            const data = error.response.data;
            errorMessage.value = data.errors 
                ? Object.values(data.errors)[0][0] 
                : (data.message || 'Có lỗi xảy ra.');
        } else {
            errorMessage.value = 'Không thể kết nối đến máy chủ.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>
