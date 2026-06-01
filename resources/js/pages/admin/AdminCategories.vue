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
              <th style="width: 40%">Tên danh mục</th>
              <th style="width: 10%">Icon</th>
              <th style="width: 25%">Slug</th>
              <th style="width: 10%">Nổi bật</th>
              <th style="width: 15%">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="parent in filteredTree" :key="parent.id">
              <!-- Parent Row -->
              <tr class="parent-row">
                <td>
                  <div class="name-cell">
                    <button v-if="parent.children && parent.children.length" @click="toggleRow(parent.id)"
                      class="expand-btn" :class="{ 'is-expanded': isExpanded(parent.id) }">
                      <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                    <span v-else class="expand-placeholder"></span>
                    <span class="font-bold">{{ parent.name }}</span>
                  </div>
                </td>
                <td>
                  <div class="cat-icon">
                    <img v-if="parent.icon" :src="parent.icon" :alt="parent.name">
                    <span v-else class="material-symbols-outlined">category</span>
                  </div>
                </td>
                <td class="text-secondary">{{ parent.slug }}</td>
                <td>
                  <label class="switch">
                    <input type="checkbox" :checked="parent.is_featured" @change="toggleFeatured(parent, $event)">
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <div class="action-btns">
                    <button class="btn-icon edit" @click="openEditModal(parent)" title="Sửa">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button class="btn-icon attr" @click="manageAttributes(parent)" title="Quản lý thuộc tính">
                      <span class="material-symbols-outlined">list_alt</span>
                    </button>
                    <button class="btn-icon delete" @click="confirmDelete(parent)" title="Xóa">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Child Rows & Grandchild Rows -->
              <template v-if="isExpanded(parent.id)" v-for="child in parent.children" :key="child.id">
                <tr class="child-row">
                  <td>
                    <div class="name-cell child-cell">
                      <button v-if="child.children && child.children.length" @click="toggleRow(child.id)"
                        class="expand-btn" :class="{ 'is-expanded': isExpanded(child.id) }">
                        <span class="material-symbols-outlined">chevron_right</span>
                      </button>
                      <span v-else class="expand-placeholder"></span>
                      <span class="material-symbols-outlined sub-icon">subdirectory_arrow_right</span>
                      <span>{{ child.name }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="cat-icon small">
                      <img v-if="child.icon" :src="child.icon" :alt="child.name">
                      <span v-else class="material-symbols-outlined">category</span>
                    </div>
                  </td>
                  <td class="text-secondary">{{ child.slug }}</td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" :checked="child.is_featured" @change="toggleFeatured(child, $event)">
                      <span class="slider round"></span>
                    </label>
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="btn-icon edit" @click="openEditModal(child)" title="Sửa">
                        <span class="material-symbols-outlined">edit</span>
                      </button>
                      <button class="btn-icon attr" @click="manageAttributes(child)" title="Quản lý thuộc tính">
                        <span class="material-symbols-outlined">list_alt</span>
                      </button>
                      <button class="btn-icon delete" @click="confirmDelete(child)" title="Xóa">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Grandchild Rows (Level 3) -->
                <tr v-if="isExpanded(child.id)" v-for="grandchild in child.children" :key="grandchild.id" class="child-row grandchild-row">
                  <td>
                    <div class="name-cell child-cell" style="padding-left: 4.5rem;">
                      <span class="expand-placeholder"></span>
                      <span class="material-symbols-outlined sub-icon" style="color: #cbd5e1;">subdirectory_arrow_right</span>
                      <span>{{ grandchild.name }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="cat-icon small">
                      <img v-if="grandchild.icon" :src="grandchild.icon" :alt="grandchild.name">
                      <span v-else class="material-symbols-outlined">category</span>
                    </div>
                  </td>
                  <td class="text-secondary">{{ grandchild.slug }}</td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" :checked="grandchild.is_featured" @change="toggleFeatured(grandchild, $event)">
                      <span class="slider round"></span>
                    </label>
                  </td>
                  <td>
                    <div class="action-btns">
                      <button class="btn-icon edit" @click="openEditModal(grandchild)" title="Sửa">
                        <span class="material-symbols-outlined">edit</span>
                      </button>
                      <button class="btn-icon attr" @click="manageAttributes(grandchild)" title="Quản lý thuộc tính">
                        <span class="material-symbols-outlined">list_alt</span>
                      </button>
                      <button class="btn-icon delete" @click="confirmDelete(grandchild)" title="Xóa">
                        <span class="material-symbols-outlined">delete</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </template>

            <tr v-if="filteredTree.length === 0">
              <td colspan="5" class="empty-state">
                <span class="material-symbols-outlined">search_off</span>
                <p>Không tìm thấy danh mục nào</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add/Edit Modal -->
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
              <label>Danh mục cha (Để trống nếu là danh mục lớn)</label>
              <select v-model="formData.parent_id">
                <option :value="null">-- Không có (Danh mục gốc) --</option>
                <option v-for="opt in allFlattenedOptions" :key="opt.id" :value="opt.id"
                  :disabled="isEditing && (opt.id === formData.id || isDescendantOf(formData.id, opt.id))"
                  :style="opt.depth === 0 ? 'font-weight: bold; color: #0f172a; background-color: #f1f5f9; font-size: 0.95rem;' : 'color: #475569;'">
                  {{ opt.displayName }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Biểu tượng danh mục (Icon)</label>
              <div class="icon-upload-container">
                <div class="upload-dropzone" @click="$refs.fileInput.click()">
                  <input type="file" ref="fileInput" @change="handleIconUpload" accept="image/*" class="hidden-file-input">
                  
                  <div v-if="iconPreview" class="preview-mode">
                    <img :src="iconPreview" alt="Icon Preview" class="preview-img">
                    <span class="change-overlay">
                      <span class="material-symbols-outlined">cached</span>
                      Thay đổi
                    </span>
                  </div>
                  
                  <div v-else class="empty-mode">
                    <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                    <span class="upload-text">Nhấp để tải lên ảnh Icon</span>
                    <span class="upload-hint">Hỗ trợ PNG, SVG, JPG (Tối đa 2MB)</span>
                  </div>
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
import { toast, confirmDialog } from '../../utils/alert';

import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const router = useRouter();
const categories = ref([]); // This will hold the tree from Backend
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const iconPreview = ref(null);
const expandedRows = ref([]); // Store IDs of expanded parents

const formData = ref({
  id: null,
  name: '',
  parent_id: null,
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

// Toggle row expansion
const toggleRow = (id) => {
  const index = expandedRows.value.indexOf(id);
  if (index > -1) {
    expandedRows.value.splice(index, 1);
  } else {
    expandedRows.value.push(id);
  }
  sessionStorage.setItem('admin_expanded_categories', JSON.stringify(expandedRows.value));
};

const isExpanded = (id) => expandedRows.value.includes(id);

// Tree filtering logic
const filteredTree = computed(() => {
  const search = searchQuery.value.toLowerCase();
  if (!search) return categories.value;

  return categories.value.filter(parent => {
    const parentMatches = parent.name.toLowerCase().includes(search);
    const childrenMatches = parent.children?.some(c => c.name.toLowerCase().includes(search));

    // Auto-expand if children match search
    if (childrenMatches && !expandedRows.value.includes(parent.id)) {
      expandedRows.value.push(parent.id);
      sessionStorage.setItem('admin_expanded_categories', JSON.stringify(expandedRows.value));
    }

    return parentMatches || childrenMatches;
  });
});

// List of categories that can be parents
const allFlattenedOptions = computed(() => {
  const options = [];
  const traverse = (list, depth = 0) => {
    for (const item of list) {
      // Build a display name with visual hierarchy using whitespace and sub-level arrows
      let prefix = '';
      if (depth > 0) {
        prefix = '  '.repeat(depth) + '↳ ';
      }
      options.push({
        id: item.id,
        name: item.name,
        displayName: prefix + item.name,
        depth: depth
      });
      if (item.children && item.children.length) {
        traverse(item.children, depth + 1);
      }
    }
  };
  traverse(categories.value);
  return options;
});

const isDescendantOf = (parentId, childId) => {
  if (!parentId || !childId) return false;
  
  const findNode = (list, id) => {
    for (const node of list) {
      if (node.id === id) return node;
      if (node.children && node.children.length) {
        const found = findNode(node.children, id);
        if (found) return found;
      }
    }
    return null;
  };

  const parentNode = findNode(categories.value, parentId);
  if (!parentNode) return false;

  const checkHasChild = (node, targetId) => {
    if (!node.children) return false;
    for (const c of node.children) {
      if (c.id === targetId) return true;
      if (checkHasChild(c, targetId)) return true;
    }
    return false;
  };

  return checkHasChild(parentNode, childId);
};

const openAddModal = () => {
  isEditing.value = false;
  formData.value = { id: null, name: '', parent_id: null, icon: null, is_featured: false };
  iconPreview.value = null;
  showModal.value = true;
};

const openEditModal = (cat) => {
  isEditing.value = true;
  formData.value = {
    id: cat.id,
    name: cat.name,
    parent_id: cat.parent_id,
    is_featured: !!cat.is_featured // Ensure boolean
  };
  iconPreview.value = cat.icon;
  showModal.value = true;
};
const toggleFeatured = async (cat, event) => {
  const newVal = !cat.is_featured;

  try {
    await axios.put(`/api/categories/${cat.id}`, {
      is_featured: newVal ? 1 : 0,
      name: cat.name
    });
    cat.is_featured = newVal;
  } catch (error) {
    toast(error.response?.data?.message || 'Không thể cập nhật trạng thái nổi bật', 'error');
    if (event) {
      event.target.checked = cat.is_featured; // Revert checkbox if API validation failed
    }
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
    if (formData.value.parent_id) {
      data.append('parent_id', formData.value.parent_id);
    }

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
    toast(error.response?.data?.message || 'Lỗi khi lưu danh mục', 'error');
  } finally {
    loading.value = false;
  }
};

const confirmDelete = async (cat) => {
  if (await confirmDialog(`Bạn có chắc muốn xóa danh mục "${cat.name}"?`)) {
    try {
      await axios.delete(`/api/categories/${cat.id}`);
      await fetchCategories();
    } catch (error) {
      toast('Không thể xóa danh mục này', 'error');
    }
  }
};

const manageAttributes = (cat) => {
  router.push(`/admin/categories/${cat.id}/attributes`);
};

onMounted(async () => {
  await fetchCategories();
  const saved = sessionStorage.getItem('admin_expanded_categories');
  if (saved) {
    try {
      expandedRows.value = JSON.parse(saved);
    } catch (e) { }
  }
});
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

.admin-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.name-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.expand-btn {
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.expand-btn.is-expanded {
  transform: rotate(90deg);
}

.expand-placeholder {
  width: 24px;
}

.child-row {
  background: #fcfdfe;
}

.grandchild-row {
  background: #f8fafc;
}

.child-cell {
  padding-left: 2.5rem;
}

.sub-icon {
  font-size: 1.1rem;
  color: #94a3b8;
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

.cat-icon.small {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
}

.cat-icon img {
  width: 24px;
  height: 24px;
  object-fit: contain;
}

.cat-icon.small img {
  width: 18px;
  height: 18px;
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

.btn-icon.edit {
  color: #3b82f6;
}

.btn-icon.edit:hover {
  background: #eff6ff;
}

.btn-icon.attr {
  color: #8b5cf6;
}

.btn-icon.attr:hover {
  background: #f5f3ff;
}

.btn-icon.delete {
  color: #ef4444;
}

.btn-icon.delete:hover {
  background: #fef2f2;
}

/* Switch Toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: .4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked+.slider {
  background-color: #3b82f6;
}

input:checked+.slider:before {
  transform: translateX(20px);
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
.form-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
  background-color: white;
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

/* Premium Icon Upload Dropzone */
.icon-upload-container {
  width: 100%;
}

.upload-dropzone {
  width: 100%;
  height: 120px;
  border: 2px dashed #cbd5e1;
  border-radius: 1rem;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.upload-dropzone:hover {
  border-color: #3b82f6;
  background: #f1f5f9;
}

.hidden-file-input {
  display: none !important;
}

.empty-mode {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  color: #64748b;
}

.upload-icon {
  font-size: 2.2rem;
  color: #3b82f6;
  margin-bottom: 0.25rem;
}

.upload-text {
  font-weight: 700;
  font-size: 0.85rem;
  color: #334155;
}

.upload-hint {
  font-size: 0.75rem;
  color: #94a3b8;
}

.preview-mode {
  position: relative;
  width: 90px;
  height: 90px;
  border-radius: 0.75rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.preview-img {
  width: 70px;
  height: 70px;
  object-fit: contain;
  transition: all 0.3s ease;
}

.change-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.75);
  color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  opacity: 0;
  transition: all 0.3s ease;
  gap: 0.25rem;
}

.change-overlay span {
  font-size: 1.25rem;
}

.preview-mode:hover .change-overlay {
  opacity: 1;
}

.preview-mode:hover .preview-img {
  transform: scale(0.9);
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

.empty-state span {
  font-size: 3rem;
  margin-bottom: 1rem;
}
</style>
