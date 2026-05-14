<template>
  <AdminLayout title="Quản lý danh mục">
    <div class="categories-container">
      <!-- Header Actions -->
      <div class="actions-bar">
        <div class="search-filter">
          <div class="search-input">
            <span class="material-symbols-outlined">search</span>
            <input type="text" v-model="searchQuery" placeholder="Tìm kiếm danh mục...">
          </div>
        </div>
        <button class="btn-primary" @click="openAddModal">
          <span class="material-symbols-outlined">add</span>
          Thêm danh mục mới
        </button>
      </div>

      <!-- Categories Table -->
      <div class="table-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Icon</th>
              <th>Tên danh mục</th>
              <th>Slug</th>
              <th>Nổi bật</th>
              <th>Số thuộc tính</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cat in filteredCategories" :key="cat.id">
              <td>#{{ cat.id }}</td>
              <td>
                <div class="cat-icon">
                  <img v-if="cat.icon" :src="cat.icon" :alt="cat.name">
                  <span v-else class="material-symbols-outlined">category</span>
                </div>
              </td>
              <td class="font-bold">{{ cat.name }}</td>
              <td class="text-secondary">{{ cat.slug }}</td>
              <td>
                <label class="switch">
                  <input type="checkbox" :checked="cat.is_featured" @change="toggleFeatured(cat)">
                  <span class="slider round"></span>
                </label>
              </td>
              <td>
                <span class="badge-count">{{ cat.attributes_count || 0 }}</span>
              </td>
              <td>
                <div class="action-btns">
                  <button class="btn-icon edit" @click="openEditModal(cat)" title="Sửa">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button class="btn-icon attr" @click="manageAttributes(cat)" title="Quản lý thuộc tính">
                    <span class="material-symbols-outlined">list_alt</span>
                  </button>
                  <button class="btn-icon delete" @click="confirmDelete(cat)" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCategories.length === 0">
              <td colspan="7" class="empty-state">
                <span class="material-symbols-outlined">search_off</span>
                <p>Không tìm thấy danh mục nào</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add/Edit Modal (Simplified placeholder) -->
      <div v-if="showModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ isEditing ? 'Cập nhật danh mục' : 'Thêm danh mục mới' }}</h3>
            <button @click="showModal = false" class="close-btn">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
          <form @submit.prevent="saveCategory" class="modal-form">
            <div class="form-group">
              <label>Tên danh mục</label>
              <input type="text" v-model="formData.name" required placeholder="VD: Điện thoại, Laptop...">
            </div>
            <div class="form-group">
              <label>Icon (URL hoặc Upload)</label>
              <div class="icon-upload">
                <input type="file" @change="handleIconUpload" accept="image/*">
                <div v-if="iconPreview" class="preview">
                  <img :src="iconPreview" alt="Preview">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="formData.is_featured">
                <span>Đặt làm danh mục nổi bật</span>
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
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const categories = ref([]);
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const iconPreview = ref(null);

const formData = ref({
  id: null,
  name: '',
  icon: null,
  is_featured: false
});

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories');
    categories.value = response.data.data;
  } catch (error) {
    console.error('Lỗi khi tải danh mục:', error);
  }
};

const filteredCategories = computed(() => {
  if (!searchQuery.value) return categories.value;
  return categories.value.filter(cat => 
    cat.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const openAddModal = () => {
  isEditing.value = false;
  formData.value = { id: null, name: '', icon: null, is_featured: false };
  iconPreview.value = null;
  showModal.value = true;
};

const openEditModal = (cat) => {
  isEditing.value = true;
  formData.value = { ...cat };
  iconPreview.value = cat.icon;
  showModal.value = true;
};

const toggleFeatured = async (cat) => {
  try {
    const newVal = !cat.is_featured;
    await axios.put(`/api/categories/${cat.id}`, { 
      is_featured: newVal,
      name: cat.name // API requires name
    });
    cat.is_featured = newVal;
  } catch (error) {
    alert('Không thể cập nhật trạng thái nổi bật');
  }
};

const handleIconUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    formData.value.icon = file;
    iconPreview.value = URL.createObjectURL(file);
  }
};

const saveCategory = async () => {
  loading.value = true;
  try {
    const data = new FormData();
    data.append('name', formData.value.name);
    data.append('is_featured', formData.value.is_featured ? 1 : 0);
    if (formData.value.icon instanceof File) {
      data.append('icon', formData.value.icon);
    }
    
    if (isEditing.value) {
      data.append('_method', 'PUT');
      await axios.post(`/api/categories/${formData.value.id}`, data);
    } else {
      await axios.post('/api/categories', data);
    }
    
    await fetchCategories();
    showModal.value = false;
  } catch (error) {
    alert('Lỗi khi lưu danh mục');
  } finally {
    loading.value = false;
  }
};

const confirmDelete = async (cat) => {
  if (confirm(`Bạn có chắc muốn xóa danh mục "${cat.name}"?`)) {
    try {
      await axios.delete(`/api/categories/${cat.id}`);
      await fetchCategories();
    } catch (error) {
      alert('Không thể xóa danh mục này');
    }
  }
};

const manageAttributes = (cat) => {
  // Logic to navigate to attributes management
  alert(`Quản lý thuộc tính cho: ${cat.name}`);
};

onMounted(fetchCategories);
</script>

<style scoped>
.categories-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-input {
  position: relative;
  width: 300px;
}

.search-input span {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-input input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
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

.btn-primary:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

.table-card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.cat-icon {
  width: 40px;
  height: 40px;
  background: #f1f5f9;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cat-icon img {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

.text-secondary {
  color: #64748b;
  font-size: 0.875rem;
}

.badge-count {
  background: #e0f2fe;
  color: #0369a1;
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-weight: 700;
  font-size: 0.75rem;
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
}

.btn-icon.edit { color: #3b82f6; }
.btn-icon.edit:hover { background: #eff6ff; }
.btn-icon.attr { color: #8b5cf6; }
.btn-icon.attr:hover { background: #f5f3ff; }
.btn-icon.delete { color: #ef4444; }
.btn-icon.delete:hover { background: #fef2f2; }

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

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
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
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h3 { font-size: 1.25rem; font-weight: 800; margin: 0; }

.close-btn { background: none; border: none; cursor: pointer; color: #64748b; }

.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-weight: 700; margin-bottom: 0.5rem; font-size: 0.9rem; }
.form-group input[type="text"] {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
}

.empty-state {
  text-align: center;
  padding: 4rem !important;
  color: #94a3b8;
}

.empty-state span { font-size: 3rem; margin-bottom: 1rem; }
</style>
