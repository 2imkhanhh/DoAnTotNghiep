<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar -->
      <aside class="w-full md:w-64 shrink-0">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm sticky top-24">
          <div class="p-6 border-b border-outline-variant">
            <div class="flex items-center gap-4">
              <div class="relative group">
                <img
                  :src="profileData.avatar || 'https://ui-avatars.com/api/?name=' + (profileData.name || 'User') + '&background=020037&color=fff'"
                  alt="Avatar" class="w-12 h-12 rounded-full object-cover border-2 border-primary-fixed">
                <div @click="$refs.fileInput.click()"
                  class="absolute inset-0 bg-black/20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                  <span class="material-symbols-outlined text-white text-sm">edit</span>
                </div>
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileUpload">
              </div>
              <div class="overflow-hidden">
                <h2 class="font-bold text-on-surface truncate">{{ profileData.name || 'Người dùng' }}</h2>
                <p class="text-xs text-on-surface-variant truncate">{{ profileData.email }}</p>
              </div>
            </div>
          </div>
          <nav class="p-2">
            <button @click="activeTab = 'info'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer',
                activeTab === 'info' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">person</span>
              <span>Thông tin cá nhân</span>
            </button>
            <button @click="activeTab = 'password'"
              :class="['w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 mt-1 cursor-pointer',
                activeTab === 'password' ? 'bg-primary text-on-primary font-bold shadow-md' : 'text-on-surface hover:bg-surface-container-low']">
              <span class="material-symbols-outlined">lock</span>
              <span>Đổi mật khẩu</span>
            </button>
            <router-link to="/profile/posts"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface hover:bg-surface-container-low transition-all duration-200 mt-1">
              <span class="material-symbols-outlined">sell</span>
              <span>Tin đăng của tôi</span>
            </router-link>
            <router-link to="/profile/favorites"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-on-surface hover:bg-surface-container-low transition-all duration-200 mt-1">
              <span class="material-symbols-outlined">favorite</span>
              <span>Tin đăng yêu thích</span>
            </router-link>
            <div class="border-t border-outline-variant my-2"></div>
            <button @click="authStore.logout()"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-error hover:bg-error-container transition-all duration-200 cursor-pointer">
              <span class="material-symbols-outlined font-bold">logout</span>
              <span class="font-bold">Đăng xuất</span>
            </button>
          </nav>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="grow">
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
                      placeholder="Nhập số điện thoại">
                  </div>
                  <p v-if="errors.phone" class="text-xs text-error mt-1 px-1">{{ errors.phone[0] }}</p>
                </div>

                <!-- Administrative Units -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:col-span-2">
                  <div class="space-y-2">
                    <label class="text-sm font-bold text-on-surface-variant px-1">Tỉnh / Thành phố</label>
                    <div class="relative">
                      <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">map</span>
                      <select v-model="profileData.province_id" @change="onProvinceChange"
                        class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none">
                        <option value="">Chọn Tỉnh/Thành</option>
                        <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
                      </select>
                    </div>
                    <p v-if="errors.province_id" class="text-xs text-error mt-1 px-1">{{ errors.province_id[0] }}</p>
                  </div>

                  <div class="space-y-2">
                    <label class="text-sm font-bold text-on-surface-variant px-1">Phường / Xã</label>
                    <div class="relative">
                      <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant">location_city</span>
                      <select v-model="profileData.ward_id" @change="onWardChange" :disabled="!profileData.province_id"
                        class="w-full bg-surface-container border border-outline-variant rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all appearance-none disabled:opacity-50">
                        <option value="">Chọn Phường/Xã</option>
                        <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.name }}</option>
                      </select>
                    </div>
                    <p v-if="errors.ward_id" class="text-xs text-error mt-1 px-1">{{ errors.ward_id[0] }}</p>
                  </div>
                </div>



                <!-- Avatar (File Upload) -->
                <div class="space-y-2 sm:col-span-2">
                  <label class="text-sm font-bold text-on-surface-variant px-1">Ảnh đại diện</label>
                  <div
                    class="flex items-center gap-4 p-4 bg-surface-container border border-outline-variant rounded-xl">
                    <img
                      :src="profileData.avatar || 'https://ui-avatars.com/api/?name=' + (profileData.name || 'User') + '&background=020037&color=fff'"
                      class="w-16 h-16 rounded-full object-cover border-2 border-primary-fixed">
                    <div class="grow">
                      <p class="text-xs text-on-surface-variant mb-2">Dung lượng file tối đa 2MB. Định dạng: .JPEG, .PNG
                      </p>
                      <button type="button" @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-surface-container-high text-on-surface text-sm font-bold rounded-lg border border-outline-variant hover:bg-surface-dim transition-all cursor-pointer">
                        Chọn ảnh mới
                      </button>
                    </div>
                  </div>
                  <p v-if="errors.avatar" class="text-xs text-error mt-1 px-1">{{ errors.avatar[0] }}</p>
                </div>
              </div>

              <div class="pt-4 border-t border-outline-variant flex justify-end">
                <button type="submit" :disabled="loading"
                  class="px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center gap-2 cursor-pointer">
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
                  class="w-full sm:w-auto px-8 py-3 bg-primary text-on-primary font-bold rounded-xl shadow-lg hover:shadow-primary/20 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 flex items-center justify-center gap-2 cursor-pointer">
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
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const activeTab = ref('info');
const fileInput = ref(null);
const loading = ref(false);
const passwordLoading = ref(false);
const errors = ref({});
const passwordErrors = ref({});

// Administrative Units
const provinces = ref([]);
const wards = ref([]);

// Khởi tạo dữ liệu từ Store ngay lập tức để tránh nhấp nháy UI
const profileData = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  address: authStore.user?.address || '',
  province_id: authStore.user?.province_id || '',
  province_name: authStore.user?.province_name || '',
  ward_id: authStore.user?.ward_id || '',
  ward_name: authStore.user?.ward_name || '',
  avatar: authStore.user?.avatar || ''
});

// Đồng bộ lại nếu Store thay đổi (ví dụ khi Header nạp xong dữ liệu muộn hơn)
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    profileData.value = {
      name: newUser.name,
      email: newUser.email,
      phone: newUser.phone,
      address: newUser.address,
      province_id: newUser.province_id,
      province_name: newUser.province_name,
      ward_id: newUser.ward_id,
      ward_name: newUser.ward_name,
      avatar: newUser.avatar
    };
    if (newUser.province_id) fetchInitialWards(newUser.province_id);
  }
}, { deep: true });

const onProvinceChange = async () => {
  profileData.value.ward_id = '';
  profileData.value.ward_name = '';
  wards.value = [];

  if (!profileData.value.province_id) {
    profileData.value.province_name = '';
    return;
  }

  const selected = provinces.value.find(p => p.code === profileData.value.province_id);
  profileData.value.province_name = selected ? selected.name : '';

  try {
    const res = await axios.get(`/api/locations/wards/${profileData.value.province_id}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch wards:', error);
  }
};

const onWardChange = () => {
  const selected = wards.value.find(w => w.code === profileData.value.ward_id);
  profileData.value.ward_name = selected ? selected.name : '';
};

const fetchInitialWards = async (provinceId) => {
  try {
    const res = await axios.get(`/api/locations/wards/${provinceId}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch initial wards:', error);
  }
};

const fetchProvinces = async () => {
  try {
    const res = await axios.get('/api/locations/provinces');
    provinces.value = res.data;
  } catch (error) {
    console.error('Failed to fetch provinces:', error);
  }
};

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
  try {
    const response = await axios.get('/api/auth/profile');
    const userData = response.data.data;
    profileData.value = userData;
    // Cập nhật lại Store để Header đồng bộ theo
    authStore.setUser(userData);
  } catch (error) {
    console.error('Lỗi khi lấy thông tin cá nhân:', error);
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) {
    errors.value.avatar = ['Dung lượng file không được vượt quá 2MB'];
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    profileData.value.avatar = e.target.result; // Base64 string
  };
  reader.readAsDataURL(file);
};

const updateProfile = async () => {
  loading.value = true;
  errors.value = {};

  try {
    const response = await axios.put('/api/auth/profile', profileData.value);
    const updatedUser = response.data.data;
    profileData.value = updatedUser;

    // QUAN TRỌNG: Cập nhật Store để Header thay đổi ngay lập tức
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
  fetchProvinces();
  fetchProfile().then(() => {
    if (profileData.value.province_id) {
      fetchInitialWards(profileData.value.province_id);
    }
  });
});
</script>
