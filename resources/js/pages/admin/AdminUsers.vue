<template>
  <AdminLayout title="Quản lý Người Dùng">
    <div class="users-container p-6 bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant/30">
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full md:w-96">
          <input type="text" v-model="searchQuery" @keyup.enter="fetchUsers"
            placeholder="Tìm kiếm theo tên hoặc email..."
            class="w-full pl-10 pr-4 py-2 bg-surface-container border border-outline-variant rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
          <span
            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
        </div>
        <div class="flex flex-wrap gap-3 w-full md:w-auto">
          <!-- Filter Role -->
          <div class="relative min-w-[140px] custom-dropdown-role">
            <div @click="isRoleDropdownOpen = !isRoleDropdownOpen; isStatusDropdownOpen = false" class="bg-surface-container border border-outline-variant rounded-xl px-4 py-2 text-sm font-medium cursor-pointer flex items-center justify-between hover:border-primary transition-colors shadow-sm select-none">
              <span class="text-on-surface">{{ selectedRoleLabel }}</span>
              <span class="material-symbols-outlined text-on-surface-variant text-[18px] transition-transform duration-300" :class="{ 'rotate-180': isRoleDropdownOpen }">expand_more</span>
            </div>
            
            <div v-if="isRoleDropdownOpen" class="absolute z-20 w-full mt-2 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              <div 
                v-for="option in roleOptions" 
                :key="option.value"
                @click="selectRole(option.value)"
                class="px-4 py-3 text-sm font-medium hover:bg-surface-container cursor-pointer transition-colors flex items-center"
                :class="{ 'text-primary bg-primary/5 font-bold border-l-2 border-primary': roleFilter === option.value, 'border-l-2 border-transparent text-on-surface-variant': roleFilter !== option.value }"
              >
                {{ option.label }}
              </div>
            </div>
          </div>

          <!-- Filter Status -->
          <div class="relative min-w-[150px] custom-dropdown-status">
            <div @click="isStatusDropdownOpen = !isStatusDropdownOpen; isRoleDropdownOpen = false" class="bg-surface-container border border-outline-variant rounded-xl px-4 py-2 text-sm font-medium cursor-pointer flex items-center justify-between hover:border-primary transition-colors shadow-sm select-none">
              <span class="text-on-surface">{{ selectedStatusLabel }}</span>
              <span class="material-symbols-outlined text-on-surface-variant text-[18px] transition-transform duration-300" :class="{ 'rotate-180': isStatusDropdownOpen }">expand_more</span>
            </div>
            
            <div v-if="isStatusDropdownOpen" class="absolute z-20 right-0 md:left-0 w-full md:min-w-[170px] mt-2 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              <div 
                v-for="option in statusOptions" 
                :key="option.value"
                @click="selectStatus(option.value)"
                class="px-4 py-3 text-sm font-medium hover:bg-surface-container cursor-pointer transition-colors flex items-center"
                :class="{ 'text-primary bg-primary/5 font-bold border-l-2 border-primary': statusFilter === option.value, 'border-l-2 border-transparent text-on-surface-variant': statusFilter !== option.value }"
              >
                {{ option.label }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b-2 border-outline-variant/50 text-on-surface-variant text-sm">
              <th class="py-3 px-4 font-semibold">Người dùng</th>
              <th class="py-3 px-4 font-semibold text-center">Vai trò</th>
              <th class="py-3 px-4 font-semibold text-center">Trạng thái</th>
              <th class="py-3 px-4 font-semibold text-center">Bài đăng</th>
              <th class="py-3 px-4 font-semibold text-center">Đánh giá</th>
              <th class="py-3 px-4 font-semibold text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="text-center py-12">
                <span class="material-symbols-outlined animate-spin text-4xl text-primary">autorenew</span>
                <p class="mt-2 text-on-surface-variant">Đang tải dữ liệu...</p>
              </td>
            </tr>
            <tr v-else-if="users.length === 0">
              <td colspan="5" class="text-center py-12">
                <span class="material-symbols-outlined text-5xl text-outline mb-2">person_off</span>
                <p class="text-on-surface-variant">Không tìm thấy người dùng nào.</p>
              </td>
            </tr>
            <tr v-else v-for="user in users" :key="user.id"
              class="border-b border-outline-variant/30 hover:bg-surface-container/50 transition-colors">
              <td class="py-4 px-4">
                <div class="flex items-center gap-3">
                  <img :src="user.avatar || 'https://images.unsplash.com/photo-1633332755192-727a05c4013d?q=80&w=100'"
                    alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-outline-variant/50">
                  <div>
                    <router-link :to="'/seller/' + user.id" target="_blank"
                      class="font-bold text-on-surface hover:text-primary transition-colors inline-block"
                      title="Xem hồ sơ công khai">
                      {{ user.name }}
                    </router-link>
                    <p class="text-xs text-on-surface-variant mt-0.5">{{ user.email }}</p>
                    <p v-if="user.phone" class="text-[10px] text-on-surface-variant mt-0.5">{{ user.phone }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 text-center">
                <span
                  :class="user.role === 1 ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant'"
                  class="px-2 py-1 rounded text-xs font-bold whitespace-nowrap">
                  {{ user.role === 1 ? 'Admin' : 'User' }}
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <span
                  :class="user.status === 1 ? 'bg-green-500/10 text-green-600 border-green-200' : 'bg-red-500/10 text-red-600 border-red-200'"
                  class="px-2 py-1 rounded text-xs font-bold border whitespace-nowrap">
                  {{ user.status === 1 ? 'Hoạt động' : 'Bị khóa' }}
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <span class="text-sm font-bold text-on-surface-variant">
                  {{ user.posts_count || 0 }}
                </span>
              </td>
              <td class="py-4 px-4 text-center">
                <div v-if="user.reviews_count > 0"
                  class="inline-flex items-center gap-1 bg-amber-500/10 px-3 py-1 rounded-full text-sm font-bold text-amber-600"
                  title="Đánh giá trung bình">
                  <span class="material-symbols-outlined text-[16px]"
                    style="font-variation-settings: 'FILL' 1;">star</span>
                  {{ user.average_rating }} <span class="text-xs text-amber-600/70 ml-0.5">({{ user.reviews_count
                  }})</span>
                </div>
                <div v-else class="text-xs text-on-surface-variant italic">
                  Chưa có
                </div>
              </td>
              <td class="py-4 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="toggleRole(user)"
                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors border cursor-pointer"
                    :class="user.role === 1 ? 'border-primary/30 text-primary hover:bg-primary/10 bg-primary/5' : 'border-outline-variant text-on-surface-variant hover:bg-surface-container'"
                    :title="user.role === 1 ? 'Hạ xuống User' : 'Chọn làm Admin'">
                    <span class="material-symbols-outlined text-[18px]">{{ user.role === 1 ? 'person_remove' :
                      'admin_panel_settings' }}</span>
                  </button>
                  <button @click="toggleStatus(user)"
                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors border cursor-pointer"
                    :class="user.status === 0 ? 'border-green-500/30 text-green-600 hover:bg-green-500/10 bg-green-500/5' : 'border-red-500/30 text-red-600 hover:bg-red-500/10 bg-red-500/5'"
                    :title="user.status === 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản'">
                    <span class="material-symbols-outlined text-[18px]">{{ user.status === 1 ? 'lock' : 'lock_open'
                    }}</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-6 flex justify-center gap-1">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
          class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>

        <button v-for="page in totalPages" :key="page" @click="changePage(page)"
          class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold transition-colors border"
          :class="currentPage === page ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant hover:bg-surface-container text-on-surface-variant'">
          {{ page }}
        </button>

        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
          class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
          <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const users = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const isRoleDropdownOpen = ref(false);
const isStatusDropdownOpen = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const roleOptions = [
  { label: 'Tất cả vai trò', value: '' },
  { label: 'Admin', value: '1' },
  { label: 'User', value: '0' }
];

const statusOptions = [
  { label: 'Tất cả trạng thái', value: '' },
  { label: 'Hoạt động', value: '1' },
  { label: 'Bị khóa', value: '0' }
];

const selectedRoleLabel = computed(() => {
  const opt = roleOptions.find(o => o.value === roleFilter.value);
  return opt ? opt.label : 'Tất cả vai trò';
});

const selectedStatusLabel = computed(() => {
  const opt = statusOptions.find(o => o.value === statusFilter.value);
  return opt ? opt.label : 'Tất cả trạng thái';
});

const selectRole = (val) => {
  roleFilter.value = val;
  isRoleDropdownOpen.value = false;
  fetchUsers(1);
};

const selectStatus = (val) => {
  statusFilter.value = val;
  isStatusDropdownOpen.value = false;
  fetchUsers(1);
};

const closeDropdowns = (e) => {
  if (!e.target.closest('.custom-dropdown-role')) {
    isRoleDropdownOpen.value = false;
  }
  if (!e.target.closest('.custom-dropdown-status')) {
    isStatusDropdownOpen.value = false;
  }
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/users', {
      params: {
        page: typeof page === 'number' ? page : 1,
        search: searchQuery.value,
        role: roleFilter.value,
        status: statusFilter.value
      }
    });
    if (response.data.success) {
      users.value = response.data.data.data;
      currentPage.value = response.data.data.current_page;
      totalPages.value = response.data.data.last_page;
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách người dùng:', error);
    alert('Không thể tải dữ liệu người dùng');
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchUsers(page);
  }
};

const toggleRole = async (user) => {
  if (confirm(`Bạn có chắc muốn ${user.role === 1 ? 'hạ quyền Admin của' : 'cấp quyền Admin cho'} người dùng ${user.name}?`)) {
    try {
      const response = await axios.patch(`/api/admin/users/${user.id}/toggle-role`);
      if (response.data.success) {
        user.role = response.data.data.role;
      }
    } catch (error) {
      console.error(error);
      alert(error.response?.data?.message || 'Có lỗi xảy ra khi thay đổi quyền.');
    }
  }
};

const toggleStatus = async (user) => {
  const action = user.status === 1 ? 'khóa' : 'mở khóa';
  if (confirm(`Bạn có chắc muốn ${action} tài khoản của ${user.name}?`)) {
    try {
      const response = await axios.patch(`/api/admin/users/${user.id}/toggle-status`);
      if (response.data.success) {
        user.status = response.data.data.status;
      }
    } catch (error) {
      console.error(error);
      alert(error.response?.data?.message || 'Có lỗi xảy ra khi thay đổi trạng thái.');
    }
  }
};

onMounted(() => {
  fetchUsers();
  window.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
});
</script>
