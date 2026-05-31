<template>
  <AdminLayout title="Hồ sơ quản trị viên">
    <div class="max-w-4xl mx-auto py-2">
      
      <!-- Toast Notification -->
      <div v-if="toast.show" 
        class="fixed top-24 right-4 z-50 bg-inverse-surface text-inverse-on-surface px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-in slide-in-from-right-8 duration-300">
        <span class="material-symbols-outlined text-primary-fixed">check_circle</span>
        <span class="font-bold text-sm">{{ toast.message }}</span>
      </div>

      <div class="flex gap-4 mb-6">
        <button @click="activeTab = 'info'"
          :class="['px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2 cursor-pointer', 
            activeTab === 'info' ? 'bg-primary text-on-primary shadow-md' : 'bg-white border border-outline-variant text-on-surface hover:bg-surface-container-low']">
          <span class="material-symbols-outlined text-lg">person</span>
          Thông tin cá nhân
        </button>
        <button @click="activeTab = 'password'"
          :class="['px-6 py-2.5 rounded-xl font-bold transition-all text-sm flex items-center gap-2 cursor-pointer', 
            activeTab === 'password' ? 'bg-primary text-on-primary shadow-md' : 'bg-white border border-outline-variant text-on-surface hover:bg-surface-container-low']">
          <span class="material-symbols-outlined text-lg">lock</span>
          Đổi mật khẩu
        </button>
      </div>

      <!-- Info Tab -->
      <div v-if="activeTab === 'info'" class="bg-white rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="p-6 border-b border-outline-variant">
          <h2 class="text-xl font-bold text-on-surface mb-1">Cập nhật hồ sơ</h2>
          <p class="text-sm text-on-surface-variant">Quản lý thông tin hiển thị của quản trị viên</p>
        </div>

        <div class="p-6">
          <form @submit.prevent="updateProfile" class="space-y-6">
            
            <!-- Avatar Section -->
            <div class="flex items-center gap-6 mb-8">
              <div class="relative group cursor-pointer" @click="$refs.fileInput.click()">
                <img :src="profileData.avatar || `https://ui-avatars.com/api/?name=${profileData.name || 'Admin'}&background=020037&color=fff`" 
                  alt="Avatar" 
                  class="w-24 h-24 rounded-full object-cover border-4 border-surface-container-high shadow-sm">
                <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <span class="material-symbols-outlined text-white">photo_camera</span>
                </div>
              </div>
              <div>
                <h3 class="font-bold text-on-surface mb-1">Ảnh đại diện</h3>
                <p class="text-xs text-on-surface-variant mb-3">Định dạng JPG, PNG. Tối đa 2MB.</p>
                <button type="button" @click="$refs.fileInput.click()" class="px-4 py-1.5 border border-outline-variant rounded-lg text-sm font-bold hover:bg-surface-container-low transition-colors cursor-pointer">
                  Đổi ảnh
                </button>
                <input type="file" ref="fileInput" class="hidden" accept="image/jpeg, image/png" @change="handleFileUpload">
                <p v-if="errors.avatar" class="text-xs text-error mt-2">{{ errors.avatar[0] }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Name -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant">Họ và tên</label>
                <input v-model="profileData.name" type="text"
                  class="w-full bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <p v-if="errors.name" class="text-xs text-error mt-1">{{ errors.name[0] }}</p>
              </div>

              <!-- Email -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant">Email</label>
                <input :value="profileData.email" type="email" readonly
                  class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-on-surface-variant cursor-not-allowed">
              </div>

              <!-- Phone -->
              <div class="space-y-2">
                <label class="text-sm font-bold text-on-surface-variant">Số điện thoại</label>
                <input v-model="profileData.phone" type="tel"
                  class="w-full bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <p v-if="errors.phone" class="text-xs text-error mt-1">{{ errors.phone[0] }}</p>
              </div>
            </div>

            <div class="pt-6 border-t border-outline-variant flex justify-end">
              <button type="submit" :disabled="loading"
                class="px-8 py-2.5 bg-primary text-on-primary font-bold rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer">
                <span v-if="loading" class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                Lưu thông tin
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Password Tab -->
      <div v-if="activeTab === 'password'" class="bg-white rounded-2xl border border-outline-variant shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500">
        <div class="p-6 border-b border-outline-variant">
          <h2 class="text-xl font-bold text-on-surface mb-1">Đổi mật khẩu bảo mật</h2>
          <p class="text-sm text-on-surface-variant">Đảm bảo mật khẩu của bạn đủ mạnh và không chia sẻ cho ai.</p>
        </div>

        <div class="p-6">
          <form @submit.prevent="changePassword" class="space-y-6 max-w-md">
            <div class="space-y-2">
              <label class="text-sm font-bold text-on-surface-variant">Mật khẩu hiện tại</label>
              <input v-model="passwordData.current_password" type="password"
                class="w-full bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
              <p v-if="passwordErrors.current_password" class="text-xs text-error">{{ passwordErrors.current_password[0] }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-on-surface-variant">Mật khẩu mới</label>
              <input v-model="passwordData.new_password" type="password"
                class="w-full bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
              <p v-if="passwordErrors.new_password" class="text-xs text-error">{{ passwordErrors.new_password[0] }}</p>
            </div>

            <div class="space-y-2">
              <label class="text-sm font-bold text-on-surface-variant">Xác nhận mật khẩu mới</label>
              <input v-model="passwordData.new_password_confirmation" type="password"
                class="w-full bg-surface-container-lowest border border-outline-variant rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
            </div>

            <div class="pt-6">
              <button type="submit" :disabled="passwordLoading"
                class="px-8 py-2.5 bg-primary text-on-primary font-bold rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer">
                <span v-if="passwordLoading" class="w-4 h-4 border-2 border-on-primary border-t-transparent rounded-full animate-spin"></span>
                Cập nhật mật khẩu
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const authStore = useAuthStore();
const activeTab = ref('info');
const fileInput = ref(null);
const avatarFile = ref(null);
const loading = ref(false);
const passwordLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});

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

const profileData = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  avatar: authStore.user?.avatar || '',
});

const fetchProfile = async () => {
  try {
    const response = await axios.get('/api/auth/profile');
    const userData = response.data.data;
    profileData.value = {
      name: userData.name,
      email: userData.email,
      phone: userData.phone,
      avatar: userData.avatar,
    };
    authStore.setUser(userData);
  } catch (error) {
    console.error('Lỗi khi lấy thông tin:', error);
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    errors.value.avatar = ['Dung lượng file không được vượt quá 2MB'];
    return;
  }

  avatarFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    profileData.value.avatar = e.target.result; 
  };
  reader.readAsDataURL(file);
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = {};

  try {
    let response;

    if (avatarFile.value) {
      const formData = new FormData();
      formData.append('_method', 'PUT');
      if (profileData.value.name) formData.append('name', profileData.value.name);
      if (profileData.value.phone) formData.append('phone', profileData.value.phone);
      if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
      }

      response = await axios.post('/api/auth/profile', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      const dataToSend = { ...profileData.value };
      delete dataToSend.avatar;
      delete dataToSend.email;
      response = await axios.put('/api/auth/profile', dataToSend);
    }

    const updatedUser = response.data.data;
    profileData.value.name = updatedUser.name;
    profileData.value.phone = updatedUser.phone;
    profileData.value.avatar = updatedUser.avatar;
    avatarFile.value = null;

    authStore.setUser(updatedUser);
    showToast('Cập nhật hồ sơ thành công!');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};

const passwordData = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const changePassword = async () => {
  passwordLoading.value = true;
  passwordErrors.value = {};

  try {
    await axios.put('/api/auth/profile/password', passwordData.value);
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

onMounted(() => {
  fetchProfile();
});
</script>
