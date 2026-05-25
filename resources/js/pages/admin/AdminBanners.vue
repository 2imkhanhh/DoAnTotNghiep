<template>
  <AdminLayout>
    <div class="admin-banners-page">
      <div class="page-header">
        <div>
          <h1 class="page-title">Quản lý Banner</h1>
          <p class="page-subtitle">Thêm, sửa, xoá và sắp xếp các banner hiển thị trên trang chủ.</p>
        </div>
        <button @click="openCreateModal" class="btn btn-primary">
          <span class="material-symbols-outlined">add</span>
          Thêm Banner
        </button>
      </div>

      <!-- Banners List -->
      <div class="banners-list">
        <div v-if="loading" class="text-center py-8">
          <span class="material-symbols-outlined animate-spin text-4xl text-primary">autorenew</span>
          <p class="mt-2 text-slate-500">Đang tải dữ liệu...</p>
        </div>

        <div v-else-if="banners.length === 0" class="empty-state">
          <span class="material-symbols-outlined text-6xl text-slate-300">hide_image</span>
          <p class="mt-4 text-slate-500">Chưa có banner nào. Hãy thêm banner mới!</p>
        </div>

        <div v-else class="banners-grid">
          <div v-for="(banner, index) in banners" :key="banner.id" class="banner-card">
            <!-- Order controls -->
            <div class="order-controls">
              <button @click="moveUp(index)" :disabled="index === 0" class="order-btn" title="Di chuyển lên">
                <span class="material-symbols-outlined">expand_less</span>
              </button>
              <span class="order-number">{{ index + 1 }}</span>
              <button @click="moveDown(index)" :disabled="index === banners.length - 1" class="order-btn" title="Di chuyển xuống">
                <span class="material-symbols-outlined">expand_more</span>
              </button>
            </div>

            <!-- Banner Image -->
            <div class="banner-image">
              <img :src="banner.image_path" :alt="banner.title || 'Banner'" />
              <div class="status-badge" :class="banner.is_active ? 'active' : 'inactive'">
                {{ banner.is_active ? 'Đang hiển thị' : 'Đang ẩn' }}
              </div>
            </div>

            <!-- Banner Info -->
            <div class="banner-info">
              <h3 class="banner-title" :title="banner.title">{{ banner.title || '(Không có tiêu đề)' }}</h3>
              <p class="banner-link" :title="banner.link">
                <span class="material-symbols-outlined text-[14px]">link</span>
                {{ banner.link || 'Không có liên kết' }}
              </p>
            </div>

            <!-- Actions -->
            <div class="banner-actions">
              <button @click="toggleActive(banner)" class="action-btn" :class="banner.is_active ? 'btn-warning' : 'btn-success'" :title="banner.is_active ? 'Ẩn banner' : 'Hiển thị banner'">
                <span class="material-symbols-outlined">{{ banner.is_active ? 'visibility_off' : 'visibility' }}</span>
              </button>
              <button @click="openEditModal(banner)" class="action-btn btn-primary" title="Chỉnh sửa">
                <span class="material-symbols-outlined">edit</span>
              </button>
              <button @click="confirmDelete(banner)" class="action-btn btn-danger" title="Xoá">
                <span class="material-symbols-outlined">delete</span>
              </button>
            </div>
          </div>
        </div>
        
        <div v-if="hasOrderChanged" class="order-save-container">
          <button @click="saveOrder" class="btn btn-primary" :disabled="savingOrder">
            <span v-if="savingOrder" class="material-symbols-outlined animate-spin">autorenew</span>
            <span v-else class="material-symbols-outlined">save</span>
            Lưu thứ tự mới
          </button>
        </div>
      </div>

      <!-- Modal Add/Edit Banner -->
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Chỉnh sửa Banner' : 'Thêm Banner mới' }}</h2>
            <button @click="closeModal" class="close-btn"><span class="material-symbols-outlined">close</span></button>
          </div>

          <form @submit.prevent="submitBanner" class="modal-body">
            <div class="form-group">
              <label>Hình ảnh Banner <span class="text-error" v-if="!isEditing">*</span></label>
              
              <!-- Image Preview -->
              <div v-if="imagePreview" class="image-preview-container">
                <img :src="imagePreview" alt="Preview" class="image-preview" />
                <button type="button" @click="removeImage" class="remove-image-btn">
                  <span class="material-symbols-outlined">close</span>
                </button>
              </div>
              
              <!-- Upload Area -->
              <label v-else class="upload-area">
                <input type="file" @change="handleImageUpload" accept="image/*" hidden />
                <span class="material-symbols-outlined text-4xl text-slate-400">cloud_upload</span>
                <span class="mt-2 text-slate-500 font-medium">Nhấn để chọn ảnh (Tỷ lệ gợi ý 3:1)</span>
              </label>
              
              <p v-if="errors.image" class="text-error text-sm mt-1">{{ errors.image[0] }}</p>
            </div>

            <div class="form-group">
              <label>Tiêu đề (tuỳ chọn)</label>
              <input v-model="form.title" type="text" class="form-input" placeholder="Nhập tiêu đề banner" />
            </div>

            <div class="form-group">
              <label>Đường dẫn liên kết (tuỳ chọn)</label>
              <input v-model="form.link" type="text" class="form-input" placeholder="Ví dụ: /category/dien-thoai" />
            </div>

            <div class="form-group">
              <label>Mô tả ngắn (tuỳ chọn)</label>
              <textarea v-model="form.description" class="form-input" rows="2" placeholder="Mô tả banner..."></textarea>
            </div>

            <div class="form-group checkbox-group">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="form.is_active" />
                <span>Hiển thị banner này</span>
              </label>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeModal" class="btn btn-outline">Huỷ</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <span v-if="submitting" class="material-symbols-outlined animate-spin">autorenew</span>
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
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const banners = ref([]);
const loading = ref(true);
const hasOrderChanged = ref(false);
const savingOrder = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const submitting = ref(false);
const errors = ref({});

const imagePreview = ref(null);
const selectedFile = ref(null);

const form = reactive({
  id: null,
  title: '',
  link: '',
  description: '',
  is_active: true
});

const fetchBanners = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/banners');
    if (response.data.success) {
      banners.value = response.data.data;
      hasOrderChanged.value = false;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách banner:', error);
  } finally {
    loading.value = false;
  }
};

const moveUp = (index) => {
  if (index > 0) {
    const temp = banners.value[index];
    banners.value[index] = banners.value[index - 1];
    banners.value[index - 1] = temp;
    hasOrderChanged.value = true;
  }
};

const moveDown = (index) => {
  if (index < banners.value.length - 1) {
    const temp = banners.value[index];
    banners.value[index] = banners.value[index + 1];
    banners.value[index + 1] = temp;
    hasOrderChanged.value = true;
  }
};

const saveOrder = async () => {
  savingOrder.value = true;
  try {
    const orders = banners.value.map((b, index) => ({ id: b.id, order: index }));
    const response = await axios.post('/api/admin/banners/update-order', { orders });
    if (response.data.success) {
      hasOrderChanged.value = false;
    }
  } catch (error) {
    console.error('Lỗi khi cập nhật thứ tự:', error);
    alert('Có lỗi xảy ra khi cập nhật thứ tự.');
  } finally {
    savingOrder.value = false;
  }
};

const toggleActive = async (banner) => {
  try {
    const response = await axios.patch(`/api/admin/banners/${banner.id}/toggle-active`);
    if (response.data.success) {
      banner.is_active = response.data.data.is_active;
    }
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái:', error);
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  selectedFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeImage = () => {
  selectedFile.value = null;
  imagePreview.value = null;
  // If editing, they must select a new image or it will keep the old one on the server
  // But visually we clear it so they know they need to re-upload if they want to change
};

const openCreateModal = () => {
  isEditing.value = false;
  form.id = null;
  form.title = '';
  form.link = '';
  form.description = '';
  form.is_active = true;
  imagePreview.value = null;
  selectedFile.value = null;
  errors.value = {};
  showModal.value = true;
};

const openEditModal = (banner) => {
  isEditing.value = true;
  form.id = banner.id;
  form.title = banner.title || '';
  form.link = banner.link || '';
  form.description = banner.description || '';
  form.is_active = banner.is_active;
  imagePreview.value = banner.image_path;
  selectedFile.value = null;
  errors.value = {};
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitBanner = async () => {
  submitting.value = true;
  errors.value = {};

  const formData = new FormData();
  if (form.title) formData.append('title', form.title);
  if (form.link) formData.append('link', form.link);
  if (form.description) formData.append('description', form.description);
  formData.append('is_active', form.is_active ? '1' : '0');
  
  if (selectedFile.value) {
    formData.append('image', selectedFile.value);
  } else if (!isEditing.value) {
    errors.value = { image: ['Vui lòng chọn hình ảnh banner.'] };
    submitting.value = false;
    return;
  }

  try {
    if (isEditing.value) {
      // Axios update with file upload requires POST and _method=PUT or POST endpoint configured
      const response = await axios.post(`/api/admin/banners/${form.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (response.data.success) {
        const index = banners.value.findIndex(b => b.id === form.id);
        if (index !== -1) banners.value[index] = response.data.data;
        closeModal();
      }
    } else {
      const response = await axios.post('/api/admin/banners', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (response.data.success) {
        banners.value.push(response.data.data);
        closeModal();
      }
    }
  } catch (error) {
    console.error('Lỗi khi lưu banner:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Đã xảy ra lỗi hệ thống.');
    }
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = async (banner) => {
  if (confirm('Bạn có chắc chắn muốn xoá banner này không?')) {
    try {
      const response = await axios.delete(`/api/admin/banners/${banner.id}`);
      if (response.data.success) {
        banners.value = banners.value.filter(b => b.id !== banner.id);
      }
    } catch (error) {
      console.error('Lỗi khi xoá banner:', error);
      alert('Đã xảy ra lỗi khi xoá.');
    }
  }
};

onMounted(() => {
  fetchBanners();
});
</script>

<style scoped>
.admin-banners-page {
  padding: 1.5rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.page-subtitle {
  color: #64748b;
  margin-top: 0.25rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #000022;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-outline {
  background: white;
  border: 1px solid #cbd5e1;
  color: #475569;
}

.btn-outline:hover {
  background: #f1f5f9;
}

.banners-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.banner-card {
  display: flex;
  align-items: center;
  background: white;
  border-radius: 0.75rem;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border: 1px solid #e2e8f0;
  gap: 1.5rem;
}

.order-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.order-btn {
  background: #f1f5f9;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
}

.order-btn:hover:not(:disabled) {
  background: #e2e8f0;
  color: #0f172a;
}

.order-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.order-number {
  font-weight: 700;
  color: #0f172a;
  min-width: 1.5rem;
  text-align: center;
}

.banner-image {
  position: relative;
  width: 240px;
  height: 80px;
  border-radius: 0.5rem;
  overflow: hidden;
  flex-shrink: 0;
  background: #f1f5f9;
}

.banner-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-badge {
  position: absolute;
  top: 0.25rem;
  left: 0.25rem;
  padding: 0.125rem 0.5rem;
  border-radius: 1rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: white;
}

.status-badge.active {
  background: #10b981;
}

.status-badge.inactive {
  background: #64748b;
}

.banner-info {
  flex-grow: 1;
}

.banner-title {
  font-weight: 700;
  color: #0f172a;
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.banner-link {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #64748b;
  font-size: 0.875rem;
}

.banner-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  color: white;
}

.btn-success { background: #10b981; }
.btn-success:hover { background: #059669; }

.btn-warning { background: #f59e0b; }
.btn-warning:hover { background: #d97706; }

.btn-danger { background: #ef4444; }
.btn-danger:hover { background: #dc2626; }

.order-save-container {
  margin-top: 1.5rem;
  display: flex;
  justify-content: flex-end;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 1rem;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
}

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #64748b;
}

.close-btn:hover {
  color: #0f172a;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #334155;
  margin-bottom: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(2, 0, 55, 0.1);
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 160px;
  border: 2px dashed #cbd5e1;
  border-radius: 0.5rem;
  cursor: pointer;
  background: #f8fafc;
  transition: all 0.2s;
}

.upload-area:hover {
  border-color: var(--color-primary);
  background: #f1f5f9;
}

.image-preview-container {
  position: relative;
  width: 100%;
  height: 160px;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #cbd5e1;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(0,0,0,0.6);
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.remove-image-btn:hover {
  background: rgba(0,0,0,0.8);
}

.custom-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.custom-checkbox input {
  width: 1.25rem;
  height: 1.25rem;
  accent-color: var(--color-primary);
}

.custom-checkbox span {
  font-weight: 500;
  color: #334155;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}
</style>
