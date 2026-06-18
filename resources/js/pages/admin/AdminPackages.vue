<template>
  <AdminLayout title="Quản lý Gói dịch vụ">
    <div class="packages-container max-w-7xl mx-auto">
      
      <!-- Top Actions Bar -->
      <div class="filters-bar">
        <h2 class="text-xl font-bold text-slate-800">Danh sách gói dịch vụ</h2>
        
        <div class="flex gap-2">
          <button @click="openCreateModal" class="flex items-center gap-1 px-4 py-2 text-sm font-bold text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm gói mới
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-card mt-6">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Tên gói</th>
              <th>Loại gói</th>
              <th style="text-align: right">Giá tiền</th>
              <th>Quyền lợi</th>
              <th>Trạng thái</th>
              <th style="text-align: center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="py-12">
                <LoadingState />
              </td>
            </tr>
            <tr v-else-if="packages.length === 0">
              <td colspan="6" class="empty-state">
                <span class="material-symbols-outlined">inventory_2</span>
                <p>Chưa có gói dịch vụ nào</p>
              </td>
            </tr>
            <tr v-else v-for="pkg in packages" :key="pkg.id">
              <td>
                <div class="font-bold text-slate-800">{{ pkg.name }}</div>
              </td>
              <td>
                <span class="type-badge" :class="pkg.type === 'vip' ? 'vip' : 'post'">
                  <span class="material-symbols-outlined text-[14px]">
                    {{ pkg.type === 'vip' ? 'workspace_premium' : 'note_add' }}
                  </span>
                  {{ pkg.type === 'vip' ? 'Gói VIP' : 'Gói Lượt Đăng' }}
                </span>
              </td>
              <td style="text-align: right">
                <span class="price">{{ formatPrice(pkg.price) }}đ</span>
              </td>
              <td>
                <div class="text-sm text-slate-600">
                  <span v-if="pkg.type === 'vip'">{{ pkg.duration_days }} ngày sử dụng</span>
                  <span v-else>Cộng {{ pkg.post_quota }} lượt đăng</span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', pkg.is_active ? 'active' : 'inactive']">
                  {{ pkg.is_active ? 'Đang hoạt động' : 'Đang ẩn' }}
                </span>
              </td>
              <td style="text-align: center">
                <div class="action-btns">
                  <button @click="openEditModal(pkg)" class="btn-icon edit" title="Chỉnh sửa">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button @click="toggleVisibility(pkg)" class="btn-icon view" :title="pkg.is_active ? 'Ẩn gói này' : 'Hiện gói này'">
                    <span class="material-symbols-outlined">{{ pkg.is_active ? 'visibility_off' : 'visibility' }}</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Chỉnh sửa gói dịch vụ' : 'Thêm gói dịch vụ mới' }}</h3>
          <button @click="showModal = false" class="close-btn">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group">
              <label>Tên gói <span class="text-error">*</span></label>
              <input v-model="form.name" type="text" required placeholder="Nhập tên gói..." class="form-control" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Loại gói <span class="text-error">*</span></label>
                <select v-model="form.type" required class="form-control" :disabled="isEditing">
                  <option value="vip">Gói VIP (Tính theo ngày)</option>
                  <option value="post">Gói Lượt đăng</option>
                </select>
              </div>
              <div class="form-group">
                <label>Giá tiền (VNĐ) <span class="text-error">*</span></label>
                <input v-model="form.price" type="number" min="0" required class="form-control" />
              </div>
            </div>

            <div class="form-group" v-if="form.type === 'vip'">
              <label>Thời hạn sử dụng (Ngày) <span class="text-error">*</span></label>
              <input v-model="form.duration_days" type="number" min="1" :required="form.type === 'vip'" class="form-control" />
            </div>

            <div class="form-group" v-if="form.type === 'post'">
              <label>Số lượt đăng cộng thêm <span class="text-error">*</span></label>
              <input v-model="form.post_quota" type="number" min="1" :required="form.type === 'post'" class="form-control" />
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_active" />
                <span>Trạng thái (Cho phép bán)</span>
              </label>
              <p class="text-xs text-slate-500 mt-1">Bỏ check nếu bạn muốn tạm ẩn gói này, không cho người mua nhìn thấy.</p>
            </div>
            
            <div class="modal-footer mt-6">
              <button type="button" @click="showModal = false" class="btn-cancel">Hủy</button>
              <button type="submit" class="btn-submit" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="material-symbols-outlined animate-spin text-[18px]">refresh</span>
                {{ isEditing ? 'Cập nhật' : 'Thêm mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { toast, confirmDialog } from '../../utils/alert';
import AdminLayout from '../../components/admin/AdminLayout.vue';
import LoadingState from '../../components/common/LoadingState.vue';

const packages = ref([]);
const loading = ref(true);

const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);

const form = reactive({
  id: null,
  name: '',
  type: 'vip',
  price: 0,
  duration_days: null,
  post_quota: null,
  is_active: true
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const fetchPackages = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/packages');
    if (response.data.success) {
      packages.value = response.data.data;
    }
  } catch (error) {
    toast('Lỗi khi tải danh sách gói', 'error');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.id = null;
  form.name = '';
  form.type = 'vip';
  form.price = 0;
  form.duration_days = null;
  form.post_quota = null;
  form.is_active = true;
  showModal.value = true;
};

const openEditModal = (pkg) => {
  isEditing.value = true;
  form.id = pkg.id;
  form.name = pkg.name;
  form.type = pkg.type;
  form.price = pkg.price;
  form.duration_days = pkg.duration_days;
  form.post_quota = pkg.post_quota;
  form.is_active = Boolean(pkg.is_active);
  showModal.value = true;
};

const submitForm = async () => {
  isSubmitting.value = true;
  
  try {
    let response;
    const data = {
      name: form.name,
      type: form.type,
      price: form.price,
      is_active: form.is_active
    };
    
    if (form.type === 'vip') {
      data.duration_days = form.duration_days;
      data.post_quota = null;
    } else {
      data.post_quota = form.post_quota;
      data.duration_days = null;
    }

    if (isEditing.value) {
      response = await axios.put(`/api/admin/packages/${form.id}`, data);
    } else {
      response = await axios.post('/api/admin/packages', data);
    }

    if (response.data.success) {
      toast(response.data.message, 'success');
      showModal.value = false;
      fetchPackages();
    }
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const toggleVisibility = async (pkg) => {
  const actionText = pkg.is_active ? 'ẩn' : 'hiện';
  confirmDialog(
    `Xác nhận ${actionText}`,
    `Bạn có chắc chắn muốn ${actionText} gói "${pkg.name}"?`,
    'Đồng ý',
    'Hủy'
  ).then(async (isConfirmed) => {
    if (isConfirmed) {
      try {
        const response = await axios.patch(`/api/admin/packages/${pkg.id}/toggle-active`);
        if (response.data.success) {
          toast(response.data.message, 'success');
          fetchPackages();
        }
      } catch (error) {
        toast(error.response?.data?.message || `Không thể ${actionText} gói`, 'error');
      }
    }
  });
};

onMounted(() => {
  fetchPackages();
});
</script>

<style scoped>
.packages-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Filters Bar */
.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Table Styles */
.table-card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  text-align: left;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 700;
  border-bottom: 1px solid #e2e8f0;
}

.admin-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.admin-table td:last-child {
  white-space: nowrap;
}

.price {
  font-weight: 800;
  color: #ef4444;
}

.type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
}
.type-badge.vip {
  background: #fef3c7;
  color: #d97706;
}
.type-badge.post {
  background: #dbeafe;
  color: #2563eb;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
}
.status-badge.active {
  background: #dcfce7;
  color: #166534;
}
.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

.action-btns {
  display: flex;
  gap: 0.25rem;
  justify-content: center;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-icon.edit { color: #3b82f6; }
.btn-icon.edit:hover { background: #eff6ff; }
.btn-icon.view { color: #8b5cf6; }
.btn-icon.view:hover { background: #f5f3ff; }

.empty-state {
  text-align: center;
  padding: 5rem !important;
  color: #94a3b8;
}
.empty-state span {
  font-size: 4rem;
  margin-bottom: 1rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-content {
  background: white;
  width: 100%;
  max-width: 500px;
  border-radius: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  animation: modalPopup 0.3s ease-out;
}
@keyframes modalPopup {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
}
.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  transition: color 0.2s;
}
.close-btn:hover { color: #ef4444; }

.modal-body { padding: 1.5rem; }
.form-group { margin-bottom: 1.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}
.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
  font-size: 0.95rem;
  transition: all 0.2s;
}
.form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form-control:disabled {
  background: #f1f5f9;
  color: #94a3b8;
  cursor: not-allowed;
}

.checkbox-label {
  display: block;
  cursor: pointer;
  user-select: none;
  font-weight: 700;
  font-size: 0.9rem;
  line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
  margin: 0 0.75rem 0 0 !important;
  cursor: pointer;
  vertical-align: -2px !important;
  appearance: checkbox !important;
  -webkit-appearance: checkbox !important;
  padding: 0 !important;
  border: none !important;
  outline: none !important;
  box-shadow: none !important;
}

.modal-footer {
  display: flex;
  gap: 1rem;
}
.btn-cancel {
  flex: 1;
  padding: 0.75rem;
  background: #f1f5f9;
  color: #475569;
  border: none;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-cancel:hover { background: #e2e8f0; }
.btn-submit {
  flex: 2;
  padding: 0.75rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.2s;
}
.btn-submit:hover:not(:disabled) { background: #2563eb; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
