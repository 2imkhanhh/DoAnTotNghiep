<template>
  <AdminLayout title="Quản lý Banner">
    <div class="banners-container">
      <div class="actions-bar">
        <div class="search-filter">
          <!-- Optional search -->
        </div>
        <button class="btn-primary" @click="openCreateModal">
          <span class="material-symbols-outlined">add</span>
          Thêm banner mới
        </button>
      </div>

      <div class="table-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 15%; text-align: center;">Thứ tự</th>
              <th style="width: 25%">Hình ảnh</th>
              <th style="width: 35%">Thông tin</th>
              <th style="width: 10%">Hiển thị</th>
              <th style="width: 15%">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="text-center py-8">
                <span class="material-symbols-outlined animate-spin text-4xl text-primary">autorenew</span>
                <p class="mt-2 text-slate-500">Đang tải dữ liệu...</p>
              </td>
            </tr>
            <tr v-else-if="banners.length === 0">
              <td colspan="5" class="empty-state">
                <span class="material-symbols-outlined text-6xl text-slate-300">hide_image</span>
                <p class="mt-4 text-slate-500">Chưa có banner nào. Hãy thêm banner mới!</p>
              </td>
            </tr>
            <tr v-else v-for="(banner, index) in banners" :key="banner.id">
              <td>
                <div class="order-controls">
                  <button @click="moveUp(index)" :disabled="index === 0" class="btn-icon" title="Lên">
                    <span class="material-symbols-outlined">keyboard_arrow_up</span>
                  </button>
                  <span class="font-bold px-1">{{ index + 1 }}</span>
                  <button @click="moveDown(index)" :disabled="index === banners.length - 1" class="btn-icon" title="Xuống">
                    <span class="material-symbols-outlined">keyboard_arrow_down</span>
                  </button>
                </div>
              </td>
              <td>
                <div class="banner-img-cell">
                  <img :src="banner.image_path" :alt="banner.title || 'Banner'">
                </div>
              </td>
              <td>
                <div class="info-cell">
                  <div class="font-bold text-[15px] text-slate-800">{{ banner.title || '(Không có tiêu đề)' }}</div>
                  <div class="text-secondary text-[13px] flex items-center gap-1 mt-1">
                    <span class="material-symbols-outlined text-[14px]">link</span>
                    {{ banner.link || 'Không có liên kết' }}
                  </div>
                </div>
              </td>
              <td>
                <label class="switch">
                  <input type="checkbox" :checked="banner.is_active" @change="toggleActive(banner, $event)">
                  <span class="slider round"></span>
                </label>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-icon edit" @click="openEditModal(banner)" title="Sửa">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button class="btn-icon delete" @click="confirmDelete(banner)" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="hasOrderChanged" class="order-save-container">
        <button @click="saveOrder" class="btn-primary" :disabled="savingOrder">
          <span v-if="savingOrder" class="material-symbols-outlined animate-spin" style="margin-right: 5px;">autorenew</span>
          <span v-else class="material-symbols-outlined" style="margin-right: 5px;">save</span>
          Lưu thứ tự mới
        </button>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ isEditing ? 'Cập nhật Banner' : 'Thêm Banner mới' }}</h3>
            <button @click="closeModal" class="close-btn">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <form @submit.prevent="submitBanner" class="modal-form">
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
              <div v-else class="icon-upload-container">
                <div class="upload-dropzone" @click="fileInput.click()">
                  <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*" class="hidden-file-input">
                  <div class="empty-mode">
                    <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                    <span class="upload-text">Nhấp để tải lên ảnh Banner</span>
                    <span class="upload-hint">Tỷ lệ gợi ý 3:1 (Tối đa 2MB)</span>
                  </div>
                </div>
              </div>
              <p v-if="errors.image" class="text-error text-sm mt-1">{{ errors.image[0] }}</p>
            </div>

            <div class="form-group">
              <label>Tiêu đề (tuỳ chọn)</label>
              <input v-model="form.title" type="text" placeholder="Nhập tiêu đề banner" />
            </div>

            <div class="form-group">
              <label>Đường dẫn liên kết (tuỳ chọn)</label>
              <input v-model="form.link" type="text" placeholder="Ví dụ: /category/dien-thoai" />
            </div>

            <div class="form-group">
              <label>Mô tả ngắn (tuỳ chọn)</label>
              <textarea v-model="form.description" rows="2" placeholder="Mô tả banner..." class="form-textarea"></textarea>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_active" />
                <span>Hiển thị banner này</span>
              </label>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-secondary" @click="closeModal">Hủy</button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                <span v-if="submitting" class="material-symbols-outlined animate-spin" style="margin-right: 5px;">autorenew</span>
                {{ isEditing ? 'Cập nhật' : 'Tạo mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { toast, confirmDialog } from '../../utils/alert';

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
const fileInput = ref(null);

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
    toast('Có lỗi xảy ra khi cập nhật thứ tự.', 'error');
  } finally {
    savingOrder.value = false;
  }
};

const toggleActive = async (banner, event) => {
  const previousState = banner.is_active;
  try {
    const response = await axios.patch(`/api/admin/banners/${banner.id}/toggle-active`);
    if (response.data.success) {
      banner.is_active = response.data.data.is_active;
    }
  } catch (error) {
    console.error('Lỗi khi thay đổi trạng thái:', error);
    toast('Không thể cập nhật trạng thái', 'error');
    if (event) {
      event.target.checked = previousState;
      banner.is_active = previousState;
    }
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
  form.is_active = !!banner.is_active;
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
      toast('Đã xảy ra lỗi hệ thống.', 'error');
    }
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = async (banner) => {
  if (await confirmDialog('Bạn có chắc chắn muốn xoá banner này không?')) {
    try {
      const response = await axios.delete(`/api/admin/banners/${banner.id}`);
      if (response.data.success) {
        banners.value = banners.value.filter(b => b.id !== banner.id);
      }
    } catch (error) {
      console.error('Lỗi khi xoá banner:', error);
      toast('Đã xảy ra lỗi khi xoá.', 'error');
    }
  }
};

onMounted(() => {
  fetchBanners();
});
</script>

<style scoped>
.banners-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-filter {
  flex: 1;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: white;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f1f5f9;
}

.table-card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
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

.admin-table th:first-child,
.admin-table td:first-child {
  text-align: center;
}

.admin-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.order-controls {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  padding: 0.25rem;
}

.banner-img-cell {
  width: 160px;
  height: 60px;
  border-radius: 0.5rem;
  overflow: hidden;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.banner-img-cell img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.text-secondary {
  color: #64748b;
}

.action-btns {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  border: none;
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #64748b;
}

.btn-icon.edit { color: #3b82f6; }
.btn-icon.edit:hover { background: #eff6ff; }
.btn-icon.delete { color: #ef4444; }
.btn-icon.delete:hover { background: #fef2f2; }

.btn-icon:hover:not(:disabled) {
  background: #e2e8f0;
  color: #0f172a;
}
.btn-icon:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* Switch Toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider { background-color: #3b82f6; }
input:checked + .slider:before { transform: translateX(20px); }

/* Empty state */
.empty-state {
  text-align: center;
  padding: 4rem 0;
}

.order-save-container {
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
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  width: 100%;
  max-width: 500px;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
}
.close-btn:hover { color: #0f172a; }

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  color: #334155;
}

.form-group input[type="text"],
.form-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
  background-color: white;
  font-family: inherit;
  transition: border-color 0.2s;
}

.form-group input[type="text"]:focus,
.form-textarea:focus {
  border-color: #3b82f6;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.9rem;
  gap: 0.75rem;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #3b82f6;
}

/* Upload Area */
.icon-upload-container { width: 100%; }
.upload-dropzone {
  width: 100%;
  height: 140px;
  border: 2px dashed #cbd5e1;
  border-radius: 1rem;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
}
.upload-dropzone:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
}
.hidden-file-input { display: none; }
.empty-mode {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #64748b;
}
.upload-icon {
  font-size: 2.5rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}
.upload-text {
  font-weight: 600;
  margin-bottom: 0.25rem;
}
.upload-hint {
  font-size: 0.75rem;
}
.image-preview-container {
  position: relative;
  width: 100%;
  height: 140px;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid #e2e8f0;
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
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}
.text-error { color: #ef4444; }
</style>
