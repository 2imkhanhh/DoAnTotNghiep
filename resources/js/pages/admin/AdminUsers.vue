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
            <div @click="isRoleDropdownOpen = !isRoleDropdownOpen; isStatusDropdownOpen = false"
              class="bg-surface-container border border-outline-variant rounded-xl px-4 py-2 text-sm font-medium cursor-pointer flex items-center justify-between hover:border-primary transition-colors shadow-sm select-none">
              <span class="text-on-surface">{{ selectedRoleLabel }}</span>
              <span
                class="material-symbols-outlined text-on-surface-variant text-[18px] transition-transform duration-300"
                :class="{ 'rotate-180': isRoleDropdownOpen }">expand_more</span>
            </div>

            <div v-if="isRoleDropdownOpen"
              class="absolute z-20 w-full mt-2 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              <div v-for="option in roleOptions" :key="option.value" @click="selectRole(option.value)"
                class="px-4 py-3 text-sm font-medium hover:bg-surface-container cursor-pointer transition-colors flex items-center"
                :class="{ 'text-primary bg-primary/5 font-bold border-l-2 border-primary': roleFilter === option.value, 'border-l-2 border-transparent text-on-surface-variant': roleFilter !== option.value }">
                {{ option.label }}
              </div>
            </div>
          </div>

          <!-- Filter Status -->
          <div class="relative min-w-[150px] custom-dropdown-status">
            <div @click="isStatusDropdownOpen = !isStatusDropdownOpen; isRoleDropdownOpen = false"
              class="bg-surface-container border border-outline-variant rounded-xl px-4 py-2 text-sm font-medium cursor-pointer flex items-center justify-between hover:border-primary transition-colors shadow-sm select-none">
              <span class="text-on-surface">{{ selectedStatusLabel }}</span>
              <span
                class="material-symbols-outlined text-on-surface-variant text-[18px] transition-transform duration-300"
                :class="{ 'rotate-180': isStatusDropdownOpen }">expand_more</span>
            </div>

            <div v-if="isStatusDropdownOpen"
              class="absolute z-20 right-0 md:left-0 w-full md:min-w-[170px] mt-2 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
              <div v-for="option in statusOptions" :key="option.value" @click="selectStatus(option.value)"
                class="px-4 py-3 text-sm font-medium hover:bg-surface-container cursor-pointer transition-colors flex items-center"
                :class="{ 'text-primary bg-primary/5 font-bold border-l-2 border-primary': statusFilter === option.value, 'border-l-2 border-transparent text-on-surface-variant': statusFilter !== option.value }">
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
                    <button @click="openUserModal(user)"
                      class="font-bold text-on-surface hover:text-primary transition-colors inline-block cursor-pointer text-left"
                      title="Xem chi tiết người dùng">
                      {{ user.name }}
                    </button>
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

    <!-- User Details Modal -->
    <div v-if="isUserModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeUserModal"></div>
      <div
        class="bg-surface-container-lowest rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200 relative z-10">
        <div class="p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container/50">
          <h3 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">person</span>
            Hồ sơ người dùng
          </h3>
          <button @click="closeUserModal"
            class="text-on-surface-variant hover:text-error transition-colors p-2 rounded-full cursor-pointer">
            <span class="material-symbols-outlined block">close</span>
          </button>
        </div>

        <div class="p-6 overflow-y-auto grow custom-scrollbar space-y-6" v-if="selectedUser">
          <div class="flex flex-col items-center gap-4 text-center pb-6 border-b border-outline-variant/50">
            <img
              :src="selectedUser.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedUser.name) + '&background=random'"
              class="w-24 h-24 rounded-full object-cover border-4 border-surface shadow-md">
            <div>
              <h2 class="text-2xl font-bold text-on-surface">{{ selectedUser.name }}</h2>
              <div class="flex items-center justify-center gap-2 mt-2">
                <span
                  :class="selectedUser.role === 1 ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant'"
                  class="px-3 py-1 rounded-full text-xs font-bold">
                  {{ selectedUser.role === 1 ? 'Admin' : 'User' }}
                </span>
                <span
                  :class="selectedUser.status === 1 ? 'bg-green-500/10 text-green-600 border-green-200' : 'bg-red-500/10 text-red-600 border-red-200'"
                  class="px-3 py-1 rounded-full text-xs font-bold border">
                  {{ selectedUser.status === 1 ? 'Hoạt động' : 'Bị khóa' }}
                </span>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h4 class="font-bold text-on-surface text-lg mb-4">Thông tin liên hệ</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-surface-container p-4 rounded-xl border border-outline-variant/50">
                <p class="text-xs text-on-surface-variant mb-1">Email</p>
                <p class="font-bold text-sm text-on-surface break-all" :title="selectedUser.email">{{ selectedUser.email }}</p>
              </div>
              <div class="bg-surface-container p-4 rounded-xl border border-outline-variant/50">
                <p class="text-xs text-on-surface-variant mb-1">Số điện thoại</p>
                <p class="font-bold text-sm text-on-surface">{{ selectedUser.phone || 'Chưa cập nhật' }}</p>
              </div>
              <div class="bg-surface-container p-4 rounded-xl border border-outline-variant/50">
                <p class="text-xs text-on-surface-variant mb-1">Ngày tham gia</p>
                <p class="font-bold text-sm text-on-surface">{{ new Date(selectedUser.created_at).toLocaleDateString('vi-VN') }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h4 class="font-bold text-on-surface text-lg mb-4">Hoạt động</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Card Tin đã đăng -->
              <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 to-indigo-50/30 p-4 rounded-2xl border border-blue-200/50 hover:shadow-md transition-shadow group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-colors duration-500"></div>
                <div class="flex justify-between items-start relative z-10 gap-2">
                  <div class="min-w-0">
                    <p class="text-[11px] font-bold text-blue-700/70 uppercase tracking-wider mb-1 whitespace-nowrap">Tin đăng</p>
                    <h3 class="text-3xl font-black text-blue-700 leading-none">{{ selectedUser.posts_count || 0 }}</h3>
                    <p class="text-[11px] font-medium text-blue-700/60 mt-1.5 whitespace-nowrap">Tổng số tin đăng</p>
                  </div>
                  <div class="w-10 h-10 shrink-0 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow-sm border border-blue-100 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-blue-600 text-[20px]">post_add</span>
                  </div>
                </div>
              </div>

              <!-- Card Giao dịch thành công -->
              <div class="relative overflow-hidden bg-gradient-to-br from-emerald-50 to-green-50/30 p-4 rounded-2xl border border-emerald-200/50 hover:shadow-md transition-shadow group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-colors duration-500"></div>
                <div class="flex justify-between items-start relative z-10 gap-2">
                  <div class="min-w-0">
                    <p class="text-[11px] font-bold text-emerald-700/70 uppercase tracking-wider mb-1 whitespace-nowrap">Bán thành công</p>
                    <h3 class="text-3xl font-black text-emerald-700 leading-none">{{ selectedUser.successful_orders_count || 0 }}</h3>
                    <p class="text-[11px] font-medium text-emerald-700/60 mt-1.5 whitespace-nowrap">Đơn đã giao hàng</p>
                  </div>
                  <div class="w-10 h-10 shrink-0 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow-sm border border-emerald-100 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
                  </div>
                </div>
              </div>

              <!-- Card Độ uy tín -->
              <div class="relative overflow-hidden bg-gradient-to-br from-amber-50 to-orange-50/30 p-4 rounded-2xl border border-amber-200/50 hover:shadow-md transition-shadow group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-colors duration-500"></div>
                <div class="flex justify-between items-start relative z-10 gap-2">
                  <div class="min-w-0">
                    <p class="text-[11px] font-bold text-amber-700/70 uppercase tracking-wider mb-1 whitespace-nowrap">Đánh giá</p>
                    <div class="flex items-baseline gap-1">
                      <h3 class="text-3xl font-black text-amber-600 leading-none">{{ selectedUser.average_rating || 0 }}</h3>
                      <span class="text-sm font-bold text-amber-700/60">/ 5.0</span>
                    </div>
                    <p class="text-[11px] font-medium text-amber-700/60 mt-1.5 whitespace-nowrap">Từ {{ selectedUser.reviews_count || 0 }} nhận xét</p>
                  </div>
                  <div class="w-10 h-10 shrink-0 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center shadow-sm border border-amber-100 group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-amber-500 text-[20px]" style="font-variation-settings: 'FILL' 1;">grade</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { toast, confirmDialog } from '../../utils/alert';

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

const isUserModalOpen = ref(false);
const selectedUser = ref(null);

const openUserModal = (user) => {
  selectedUser.value = user;
  isUserModalOpen.value = true;
};

const closeUserModal = () => {
  isUserModalOpen.value = false;
  setTimeout(() => { selectedUser.value = null; }, 200);
};

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
    toast('Không thể tải dữ liệu người dùng', 'error');
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
  if (await confirmDialog(`Bạn có chắc muốn ${user.role === 1 ? 'hạ quyền Admin của' : 'cấp quyền Admin cho'} người dùng ${user.name}?`)) {
    try {
      const response = await axios.patch(`/api/admin/users/${user.id}/toggle-role`);
      if (response.data.success) {
        user.role = response.data.data.role;
      }
    } catch (error) {
      console.error(error);
      toast(error.response?.data?.message || 'Có lỗi xảy ra khi thay đổi quyền.', 'error');
    }
  }
};

const toggleStatus = async (user) => {
  const action = user.status === 1 ? 'khóa' : 'mở khóa';
  if (await confirmDialog(`Bạn có chắc muốn ${action} tài khoản của ${user.name}?`)) {
    try {
      const response = await axios.patch(`/api/admin/users/${user.id}/toggle-status`);
      if (response.data.success) {
        user.status = response.data.data.status;
      }
    } catch (error) {
      console.error(error);
      toast(error.response?.data?.message || 'Có lỗi xảy ra khi thay đổi trạng thái.', 'error');
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
