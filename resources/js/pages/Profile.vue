<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar -->
      <aside class="w-full md:w-64 flex-shrink-0">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm sticky top-24">
          <div class="p-6 border-b border-outline-variant">
            <div class="flex items-center gap-4">
              <div class="relative group">
                <img
                  :src="profileData.avatar || 'https://ui-avatars.com/api/?name=' + (profileData.name || 'User') + '&background=020037&color=fff'"
                  alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-primary-fixed">
                <div
                  class="absolute inset-0 bg-black/20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                  <span class="material-symbols-outlined text-white text-sm">edit</span>
                </div>
              </div>
              <div class="overflow-hidden">
                <h2 class="font-bold text-on-surface truncate">{{ profileData.name || 'Người dùng' }}</h2>
                <p class="text-xs text-on-surface-variant truncate">{{ profileData.email }}</p>
              </div>
            </div>
          </div>
          <nav class="p-2">
            <button @click="activeTab = 'info'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200',
                activeTab === 'info' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">person</span>
              <span>Thông tin cá nhân</span>
            </button>
            <button @click="activeTab = 'password'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 mt-1',
                activeTab === 'password' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">lock</span>
              <span>Đổi mật khẩu</span>
            </button>
            <router-link to="/my-ads"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface hover:bg-surface-container-low transition-all duration-200 mt-1">
              <span class="material-symbols-outlined">sell</span>
              <span>Tin đăng của tôi</span>
            </router-link>
            <router-link to="/history"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface hover:bg-surface-container-low transition-all duration-200 mt-1">
              <span class="material-symbols-outlined">history</span>
              <span>Lịch sử mua hàng</span>
            </router-link>
            <div class="border-t border-outline-variant my-2"></div>
            <button @click="logout"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-error hover:bg-error-container transition-all duration-200">
              <span class="material-symbols-outlined font-bold">logout</span>
              <span class="font-bold">Đăng xuất</span>
            </button>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-grow">
        <!-- Info Tab -->
        <div v-if="activeTab === 'info'"
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="p-6 sm:p-8 border-b border-outline-variant">
            <h1 class="text-2xl font-bold text-on-surface">Thông tin cá nhân</h1>
            <p class="text-on-surface-variant">Quản lý thông tin hồ sơ của bạn để bảo mật tài khoản</p>
          </div>

          <div class="p-6 sm:p-8">
            <form @submit.prevent="updateProfile" class="space-y-6 max-w-2xl">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="space-y-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Họ và tên</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">person</span>
                    <input v-model="profileData.name" type="text"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="Nhập họ tên của bạn">
                  </div>
                  <p v-if="errors.name" class="text-xs text-error mt-1 px-1">{{ errors.name[0] }}</p>
                </div>

                <!-- Email (Read-only) -->
                <div class="space-y-2 opacity-70">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Email</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">mail</span>
                    <input :value="profileData.email" type="email" readonly
                      class="w-full bg-surface-container-low border border-outline-variant rounded-xl pl-10 pr-4 py-3 cursor-not-allowed"
                      placeholder="email@example.com">
                  </div>
                  <p class="text-[10px] text-on-surface-variant mt-1 px-1">Email không thể thay đổi</p>
                </div>

                <!-- Phone -->
                <div class="space-y-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Số điện thoại</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">call</span>
                    <input v-model="profileData.phone" type="tel"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="09xx xxx xxx">
                  </div>
                  <p v-if="errors.phone" class="text-xs text-error mt-1 px-1">{{ errors.phone[0] }}</p>
                </div>

                <!-- Address -->
                <div class="space-y-2 sm:col-span-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Địa chỉ</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">location_on</span>
                    <input v-model="profileData.address" type="text"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="Nhập địa chỉ của bạn">
                  </div>
                  <p v-if="errors.address" class="text-xs text-error mt-1 px-1">{{ errors.address[0] }}</p>
                </div>

                <!-- Avatar URL (Simple for now) -->
                <div class="space-y-2 sm:col-span-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Link ảnh đại diện</label>
                  <div class="relative">
                    <span
                      class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">image</span>
                    <input v-model="profileData.avatar" type="text"
                      class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                      placeholder="https://example.com/avatar.jpg">
                  </div>
                  <p v-if="errors.avatar" class="text-xs text-error mt-1 px-1">{{ errors.avatar[0] }}</p>
                </div>
              </div>

              <div class="pt-4 border-t border-outline-variant flex justify-end">
                <button type="submit" :disabled="loading"
                  class="px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center gap-2">
                  <span v-if="loading"
                    class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                  Lưu thay đổi
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Password Tab -->
        <div v-if="activeTab === 'password'"
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
          <div class="p-6 sm:p-8 border-b border-outline-variant">
            <h1 class="text-2xl font-bold text-on-surface">Đổi mật khẩu</h1>
            <p class="text-on-surface-variant">Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu với người khác</p>
          </div>

          <div class="p-6 sm:p-8">
            <form @submit.prevent="changePassword" class="space-y-6 max-w-md">
              <!-- Current Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Mật khẩu hiện tại</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">lock</span>
                  <input v-model="passwordData.current_password" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
                <p v-if="passwordErrors.current_password" class="text-xs text-error mt-1 px-1">{{
                  passwordErrors.current_password[0] }}</p>
              </div>

              <!-- New Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Mật khẩu mới</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">lock_reset</span>
                  <input v-model="passwordData.new_password" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
                <p v-if="passwordErrors.new_password" class="text-xs text-error mt-1 px-1">{{
                  passwordErrors.new_password[0] }}</p>
              </div>

              <!-- Confirm Password -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant px-1">Xác nhận mật khẩu mới</label>
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">verified_user</span>
                  <input v-model="passwordData.new_password_confirmation" type="password"
                    class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                    placeholder="••••••••">
                </div>
              </div>

              <div class="pt-4 flex justify-end">
                <button type="submit" :disabled="passwordLoading"
                  class="w-full sm:w-auto px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center justify-center gap-2">
                  <span v-if="passwordLoading"
                    class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                  Đổi mật khẩu
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>

    <!-- Success Toast (Simple) -->
    <div v-if="toast.show"
      class="fixed bottom-8 right-8 z-50 bg-on-surface text-surface px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-8 duration-300">
      <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
      <span class="font-bold">{{ toast.message }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const activeTab = ref('info');
const loading = ref(false);
const passwordLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});

const profileData = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  avatar: ''
});

const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const toast = ref({
  show: false,
  message: ''
});

const showToast = (message) => {
  toast.value.message = message;
  toast.value.show = true;
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const fetchProfile = async () => {
  const token = localStorage.getItem('access_token');
  if (!token) {
    router.push('/login');
    return;
  }

  try {
    const response = await axios.get('/api/auth/profile', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    profileData.value = response.data.data;
  } catch (error) {
    if (error.response?.status === 401) {
      router.push('/login');
    }
  }
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = {};
  const token = localStorage.getItem('access_token');

  try {
    const response = await axios.put('/api/auth/profile', profileData.value, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    profileData.value = response.data.data;
    showToast('Cập nhật hồ sơ thành công!');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};

const changePassword = async () => {
  passwordLoading.value = true;
  passwordErrors.value = {};
  const token = localStorage.getItem('access_token');

  try {
    await axios.put('/api/auth/profile/password', passwordData.value, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    passwordData.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    };
    showToast('Đổi mật khẩu thành công!');
  } catch (error) {
    if (error.response?.status === 422 || error.response?.status === 400) {
      passwordErrors.value = error.response.data.errors;
    }
  } finally {
    passwordLoading.value = false;
  }
};

const logout = async () => {
  const token = localStorage.getItem('access_token');
  const refreshToken = localStorage.getItem('refresh_token');

  if (token) {
    try {
      await axios.post('/api/auth/logout',
        { refresh_token: refreshToken },
        { headers: { 'Authorization': `Bearer ${token}` } }
      );
    } catch (e) {
      // Ignore
    }
  }

  localStorage.removeItem('access_token');
  localStorage.removeItem('refresh_token');
  router.push('/login');
};

onMounted(() => {
  fetchProfile();
});
</script>

<style scoped>
.bg-surface-container-lowest {
  background-color: var(--color-surface-container-lowest);
}

.bg-surface-container {
  background-color: var(--color-surface-container);
}

.bg-surface-container-low {
  background-color: var(--color-surface-container-low);
}

.border-outline-variant {
  border-color: var(--color-outline-variant);
}

.text-on-surface {
  color: var(--color-on-surface);
}

.text-on-surface-variant {
  color: var(--color-on-surface-variant);
}

.text-primary {
  color: var(--color-primary);
}

.bg-primary {
  background-color: var(--color-primary);
}

.text-on-primary {
  color: var(--color-on-primary);
}

.text-error {
  color: var(--color-error);
}

.bg-error-container {
  background-color: var(--color-error-container);
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideInFromBottom {
  from {
    transform: translateY(1rem);
    opacity: 0;
  }

  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes slideInFromRight {
  from {
    transform: translateX(2rem);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-in {
  animation-duration: 400ms;
  animation-fill-mode: both;
}

.fade-in {
  animation-name: fadeIn;
}

.slide-in-from-bottom-4 {
  animation-name: slideInFromBottom;
}

.slide-in-from-right-8 {
  animation-name: slideInFromRight;
}
</style>
