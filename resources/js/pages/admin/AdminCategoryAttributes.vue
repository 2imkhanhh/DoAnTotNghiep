<template>
  <AdminLayout :title="`Thuộc tính: ${categoryName}`">
    <div class="attributes-container">
      <!-- Back & Actions -->
      <div class="actions-bar">
        <router-link to="/admin/categories" class="btn-secondary">
          <span class="material-symbols-outlined">arrow_back</span>
          Quay lại danh sách
        </router-link>
        <button class="btn-primary" @click="openAddModal">
          <span class="material-symbols-outlined">add</span>
          Thêm thuộc tính mới
        </button>
      </div>

      <!-- Attributes Table -->
      <div class="table-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên thuộc tính</th>
              <th>Kiểu dữ liệu</th>
              <th>Giá trị gợi ý (nếu có)</th>
              <th>Bắt buộc</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="attr in attributes" :key="attr.id">
              <td>#{{ attr.id }}</td>
              <td class="font-bold">
                {{ attr.name }}
                <div style="font-size: 0.75rem; color: #64748b; font-weight: normal; margin-top: 0.125rem;">
                  Key: <code>{{ attr.key }}</code>
                </div>
              </td>
              <td>
                <span :class="['badge-type', attr.type]">{{ formatType(attr.type) }}</span>
              </td>
              <td class="options-cell">
                <div v-if="attr.options && attr.options.length" class="options-tags">
                  <span v-for="(opt, idx) in attr.options" :key="idx" class="tag">{{ opt }}</span>
                </div>
                <span v-else class="text-muted">Không có</span>
              </td>
              <td>
                <span :class="attr.is_required ? 'text-error font-bold' : 'text-muted'">
                  {{ attr.is_required ? 'Có' : 'Không' }}
                </span>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-icon edit" @click="openEditModal(attr)" title="Sửa">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button class="btn-icon delete" @click="confirmDelete(attr)" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="attributes.length === 0">
              <td colspan="6" class="empty-state">
                <span class="material-symbols-outlined">list_alt</span>
                <p>Danh mục này chưa có thuộc tính động nào</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ isEditing ? 'Cập nhật thuộc tính' : 'Thêm thuộc tính mới' }}</h3>
            <button @click="showModal = false" class="close-btn">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <form @submit.prevent="saveAttribute" class="modal-form">
            <div class="form-group">
              <label>Tên thuộc tính (VD: Màu sắc, Dung lượng...)</label>
              <input type="text" v-model="formData.name" required @input="handleNameInput"
                placeholder="Nhập tên thuộc tính">
            </div>

            <div class="form-group">
              <label>Mã thuộc tính</label>
              <input type="text" v-model="formData.key" required
                placeholder="Nhập mã thuộc tính (không dấu, không khoảng cách)" @input="isKeyManuallyEdited = true">
              <small style="color: #64748b; font-size: 0.8rem; display: block; margin-top: 0.25rem;">
                Mã được tự động tạo từ tên.
              </small>
            </div>

            <div class="form-group">
              <label>Kiểu hiển thị</label>
              <select v-model="formData.type" required @change="handleTypeChange">
                <option value="text">Văn bản (Text)</option>
                <option value="number">Số (Number)</option>
                <option value="select">Lựa chọn (Dropdown)</option>
                <option value="checkbox">Nhiều lựa chọn (Checkbox)</option>
              </select>
            </div>

            <!-- Options management for Select/Checkbox -->
            <div v-if="['select', 'checkbox'].includes(formData.type)" class="form-group">
              <label>Danh sách lựa chọn</label>
              <div class="options-input-wrapper">
                <input type="text" v-model="newOption" @keydown.enter.prevent="addOption"
                  placeholder="Nhập giá trị và nhấn Enter">
                <button type="button" @click="addOption" class="btn-add">Thêm</button>
              </div>
              <div class="options-list">
                <span v-for="(opt, idx) in formData.options" :key="idx" class="option-tag">
                  {{ opt }}
                  <span class="material-symbols-outlined" @click="removeOption(idx)">close</span>
                </span>
              </div>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="formData.is_required">
                <span>Bắt buộc người dùng phải nhập/chọn</span>
              </label>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-secondary" @click="showModal = false">Hủy</button>
              <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Đang lưu...' : (isEditing ? 'Lưu thay đổi' : 'Tạo mới') }}
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
import { useRoute } from 'vue-router';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const route = useRoute();
const categoryId = route.params.id;
const categoryName = ref('...');
const attributes = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const isKeyManuallyEdited = ref(false);
const newOption = ref('');

const formData = ref({
  id: null,
  name: '',
  key: '',
  type: 'text',
  options: [],
  is_required: false
});

const fetchCategoryInfo = async () => {
  try {
    const response = await axios.get('/api/categories');
    const cat = response.data.data.find(c => c.id == categoryId);
    if (cat) categoryName.value = cat.name;
  } catch (e) { }
};

const fetchAttributes = async () => {
  try {
    const response = await axios.get(`/api/categories/${categoryId}/attributes`);
    attributes.value = response.data.data;
  } catch (error) {
    console.error('Lỗi khi tải thuộc tính:', error);
  }
};

const formatType = (type) => {
  const types = {
    text: 'Văn bản',
    number: 'Số',
    select: 'Dropdown',
    checkbox: 'Checkbox'
  };
  return types[type] || type;
};

const generateKey = (name) => {
  if (!name) return '';
  let str = name.toLowerCase();
  str = str.replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, "a");
  str = str.replace(/[èéẹẻẽêềếệểễ]/g, "e");
  str = str.replace(/[ìíịỉĩ]/g, "i");
  str = str.replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, "o");
  str = str.replace(/[ùúụủũưừứựửữ]/g, "u");
  str = str.replace(/[ỳýỵỷỹ]/g, "y");
  str = str.replace(/đ/g, "d");

  str = str.replace(/[^a-z0-9\s_-]/g, '');
  str = str.replace(/\s+/g, '_');
  str = str.replace(/_+/g, '_');
  return str.trim().replace(/^_+|_+$/g, '');
};

const handleNameInput = () => {
  if (!isEditing.value && !isKeyManuallyEdited.value) {
    formData.value.key = generateKey(formData.value.name);
  }
};

const openAddModal = () => {
  isEditing.value = false;
  isKeyManuallyEdited.value = false;
  formData.value = {
    id: null,
    name: '',
    key: '',
    type: 'text',
    options: [],
    is_required: false
  };
  showModal.value = true;
};

const openEditModal = (attr) => {
  isEditing.value = true;
  isKeyManuallyEdited.value = true;
  formData.value = {
    ...attr,
    options: attr.options ? [...attr.options] : []
  };
  showModal.value = true;
};

const handleTypeChange = () => {
  if (!['select', 'checkbox'].includes(formData.type)) {
    formData.value.options = [];
  }
};

const addOption = () => {
  const val = newOption.value.trim();
  if (val && !formData.value.options.includes(val)) {
    formData.value.options.push(val);
    newOption.value = '';
  }
};

const removeOption = (idx) => {
  formData.value.options.splice(idx, 1);
};

const saveAttribute = async () => {
  loading.value = true;
  try {
    const data = { ...formData.value };
    if (isEditing.value) {
      await axios.put(`/api/categories/${categoryId}/attributes/${data.id}`, data);
    } else {
      await axios.post(`/api/categories/${categoryId}/attributes`, data);
    }
    await fetchAttributes();
    showModal.value = false;
  } catch (error) {
    alert('Lỗi khi lưu thuộc tính');
  } finally {
    loading.value = false;
  }
};

const confirmDelete = async (attr) => {
  if (confirm(`Bạn có chắc muốn xóa thuộc tính "${attr.name}"?`)) {
    try {
      await axios.delete(`/api/categories/${categoryId}/attributes/${attr.id}`);
      await fetchAttributes();
    } catch (error) {
      alert('Không thể xóa thuộc tính');
    }
  }
};

onMounted(() => {
  fetchCategoryInfo();
  fetchAttributes();
});
</script>

<style scoped>
.attributes-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #e2e8f0;
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
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.badge-type {
  padding: 0.25rem 0.6rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.badge-type.text {
  background: #e0f2fe;
  color: #0369a1;
}

.badge-type.number {
  background: #fef3c7;
  color: #92400e;
}

.badge-type.select {
  background: #dcfce7;
  color: #166534;
}

.badge-type.checkbox {
  background: #f5f3ff;
  color: #5b21b6;
}

.options-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.tag {
  background: #f1f5f9;
  padding: 0.1rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #475569;
}

.action-btns {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
}

.btn-icon.edit {
  color: #3b82f6;
}

.btn-icon.edit:hover {
  background: #eff6ff;
}

.btn-icon.delete {
  color: #ef4444;
}

.btn-icon.delete:hover {
  background: #fef2f2;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
  overflow-y: auto;
  padding: 2rem 1rem;
}

.modal-content {
  background: white;
  width: 100%;
  max-width: 550px;
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
  margin-bottom: 2rem;
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
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
}

.options-input-wrapper {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.btn-add {
  background: #334155;
  color: white;
  border: none;
  padding: 0 1rem;
  border-radius: 0.75rem;
  cursor: pointer;
}

.options-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.option-tag {
  background: #f1f5f9;
  padding: 0.4rem 0.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.option-tag span {
  font-size: 1rem;
  cursor: pointer;
  color: #ef4444;
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
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.empty-state {
  text-align: center;
  padding: 4rem !important;
  color: #94a3b8;
}

.empty-state span {
  font-size: 3rem;
  margin-bottom: 1rem;
}
</style>
