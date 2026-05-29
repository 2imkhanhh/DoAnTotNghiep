<template>
  <SellerLayout title="Sửa tin đăng">
    <div class="post-edit-page animate-in fade-in">
    <div class="container">
      <div v-if="loading" class="loading-state">
        <span class="spinner"></span>
        <p>Đang tải dữ liệu tin đăng...</p>
      </div>

      <div v-else>
        <div class="form-header">
          <h1 class="title">Chỉnh sửa tin đăng</h1>
          <p class="subtitle">Cập nhật thông tin để tin đăng của bạn chính xác hơn</p>
        </div>

        <form @submit.prevent="submitUpdate" class="main-form">
          <!-- Section: Images -->
          <section class="form-card">
            <h2 class="card-title"><span class="material-symbols-outlined">image</span> Hình ảnh sản phẩm</h2>
            <div class="image-upload-wrapper">
              <div class="image-grid">
                <div v-for="(img, index) in postImages" :key="index" class="image-item"
                  @click="triggerReplaceImage(index)" style="cursor: pointer;" title="Nhấn để thay đổi ảnh">
                  <img :src="img.path" alt="Preview" />
                  <div v-if="index === 0" class="main-badge">Ảnh bìa</div>
                  <div class="replace-overlay">
                    <button type="button" @click.stop="setAsCover(index)" v-if="index !== 0" class="cover-btn"
                      title="Đặt làm ảnh bìa">
                      <span class="material-symbols-outlined">star</span>
                    </button>
                    <button type="button" @click.stop="triggerReplaceImage(index)" class="edit-btn"
                      title="Thay đổi ảnh">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                  </div>
                  <button type="button" @click.stop="removeImage(index)" class="remove-btn" title="Xóa ảnh">
                    <span class="material-symbols-outlined">close</span>
                  </button>
                </div>
                <input type="file" ref="replaceImageInput" accept="image/*" @change="executeReplaceImage" hidden />
                <label v-if="postImages.length < 6" class="upload-btn">
                  <input type="file" multiple accept="image/*" @change="handleImageUpload" hidden />
                  <span class="material-symbols-outlined">add_a_photo</span>
                  <span>Thêm ảnh</span>
                </label>
              </div>
              <p v-if="errors.images" class="error-text">{{ errors.images[0] }}</p>
            </div>
          </section>

          <!-- Section: Category -->
          <section class="form-card">
            <h2 class="card-title"><span class="material-symbols-outlined">category</span> Danh mục sản phẩm</h2>
            <div class="category-selection">
              <label class="label-hint">Chọn danh mục chính:</label>
              <div class="parent-grid">
                <div v-for="cat in categories" :key="cat.id" class="parent-item"
                  :class="{ active: selectedParentId === cat.id }" @click="selectParent(cat)">
                  <img v-if="cat.icon" :src="cat.icon" class="cat-icon" />
                  <span class="cat-name">{{ cat.name }}</span>
                </div>
              </div>

              <div v-if="selectedParentId" class="child-section animate-in slide-in-from-bottom-4">
                <label class="label-hint">Chọn chi tiết:</label>
                <div class="child-chips">
                  <button type="button" v-for="sub in childCategories" :key="sub.id" class="chip-btn"
                    :class="{ active: form.category_id === sub.id }" @click="selectChild(sub.id)">
                    {{ sub.name }}
                  </button>
                </div>
              </div>
            </div>
          </section>

          <!-- Section: Basic Info -->
          <section class="form-card">
            <h2 class="card-title"><span class="material-symbols-outlined">edit_note</span> Thông tin chi tiết</h2>

            <div class="form-group">
              <label class="field-label">Tiêu đề tin đăng *</label>
              <input v-model="form.title" type="text" class="input-field"
                placeholder="Ví dụ: iPhone 13 Pro Max màu xanh 128GB" required />
              <p v-if="errors.title" class="error-text">{{ errors.title[0] }}</p>
            </div>

            <!-- Dynamic Attributes -->
            <div v-if="attributes.length > 0" class="attributes-wrapper">
              <div class="attr-grid">
                <div v-for="attr in attributes" :key="attr.id" class="form-group">
                  <label class="field-label">{{ attr.name }} {{ attr.is_required ? '*' : '' }}</label>

                  <select v-if="attr.type === 'select'" v-model="form.specifications[attr.key]"
                    :required="attr.is_required" class="input-field">
                    <option value="">Chọn {{ attr.name }}</option>
                    <option v-for="opt in parseOptions(attr.options)" :key="opt" :value="opt">{{ opt }}</option>
                  </select>

                  <input v-else-if="attr.type === 'number'" v-model.number="form.specifications[attr.key]" type="number"
                    :required="attr.is_required" class="input-field" />

                  <div v-else-if="attr.type === 'checkbox'" class="checkbox-options-grid">
                    <label v-for="opt in parseOptions(attr.options)" :key="opt" class="custom-checkbox-item">
                      <input type="checkbox" :value="opt" v-model="form.specifications[attr.key]" />
                      <span>{{ opt }}</span>
                    </label>
                  </div>

                  <input v-else v-model="form.specifications[attr.key]" type="text" :required="attr.is_required"
                    class="input-field" />
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="field-label">Mô tả sản phẩm *</label>
              <textarea v-model="form.description" rows="6" class="input-field textarea"
                placeholder="Mô tả tình trạng, thời gian sử dụng, bảo hành..."></textarea>
              <p v-if="errors.description" class="error-text">{{ errors.description[0] }}</p>
            </div>
          </section>

          <!-- Section: Price & Location -->
          <section class="form-card">
            <h2 class="card-title"><span class="material-symbols-outlined">payments</span> Giá & Địa điểm</h2>

            <div class="info-grid mb-6">
              <div class="form-group">
                <label class="field-label">Giá bán *</label>
                <div class="price-input-wrapper">
                  <span class="currency-icon material-symbols-outlined">payments</span>
                  <input :value="formattedPrice" @input="handlePriceInput" type="text" placeholder="Ví dụ: 5.000.000"
                    class="input-field with-icon" required>
                  <span class="currency-unit">VNĐ</span>
                </div>
                <p v-if="errors.price" class="error-text">{{ errors.price[0] }}</p>
              </div>

              <div class="form-group">
                <label class="field-label">Số điện thoại *</label>
                <input v-model="form.phone" type="tel" pattern="0[0-9]{9}" maxlength="10" class="input-field"
                  placeholder="Nhập số điện thoại (10 chữ số)" required />
                <p v-if="errors.phone" class="error-text">{{ errors.phone[0] }}</p>
              </div>
            </div>

            <!-- Administrative Units -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div class="form-group">
                <label class="field-label">Tỉnh / Thành phố *</label>
                <select v-model="form.province_id" @change="onProvinceChange" class="input-field" required>
                  <option value="">Chọn Tỉnh/Thành</option>
                  <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
                </select>
                <p v-if="errors.province_id" class="error-text">{{ errors.province_id[0] }}</p>
              </div>

              <div class="form-group">
                <label class="field-label">Phường / Xã *</label>
                <select v-model="form.ward_id" @change="onWardChange" class="input-field" :disabled="!form.province_id"
                  required>
                  <option value="">Chọn Phường/Xã</option>
                  <option v-for="w in wards" :key="w.code" :value="w.code">{{ w.name }}</option>
                </select>
                <p v-if="errors.ward_id" class="error-text">{{ errors.ward_id[0] }}</p>
              </div>
            </div>


          </section>

          <div class="submit-section flex gap-4">
            <button type="button" @click="$router.back()" class="cancel-btn">Hủy bỏ</button>
            <button type="submit" class="submit-btn" :disabled="submitting">
              <span v-if="submitting" class="spinner"></span>
              <span v-else>Cập nhật tin đăng</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  </SellerLayout>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';
import SellerLayout from '../../components/seller/SellerLayout.vue';

const router = useRouter();
const route = useRoute();
const postImages = ref([]);
const replaceImageInput = ref(null);
const replacingIndex = ref(null);
const loading = ref(true);
const submitting = ref(false);
const errors = ref({});

const categories = ref([]);
const childCategories = ref([]);
const selectedParentId = ref('');
const attributes = ref([]);

// Administrative Units
const provinces = ref([]);
const wards = ref([]);

const form = reactive({
  title: '',
  description: '',
  price: null,
  address: '',
  province_id: '',
  province_name: '',
  ward_id: '',
  ward_name: '',
  phone: '',
  category_id: '',
  specifications: {}
});

const formattedPrice = computed(() => {
  if (!form.price) return '';
  return new Intl.NumberFormat('vi-VN').format(form.price);
});

const handlePriceInput = (e) => {
  const value = e.target.value.replace(/\D/g, '');
  form.price = value ? parseInt(value) : null;
};

onMounted(async () => {
  const postId = route.params.id;
  try {
    // 1. Fetch ALL categories and provinces
    const [catRes, provRes] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/locations/provinces')
    ]);
    categories.value = catRes.data.data;
    provinces.value = provRes.data;

    // 2. Fetch the post details
    const response = await axios.get(`/api/posts/${postId}/edit`);
    const post = response.data.data;

    // Ngăn chặn sửa tin đã bán
    if (post.status === 'sold') {
      alert('Tin đăng này đã bán, không thể chỉnh sửa.');
      router.push('/seller-center/posts');
      return;
    }

    // Fill form
    form.title = post.title;
    form.description = post.description;
    form.price = Math.floor(post.price);
    form.address = post.address;
    form.province_id = post.province_id;
    form.province_name = post.province_name;
    form.ward_id = post.ward_id;
    form.ward_name = post.ward_name;
    form.phone = post.phone;
    form.category_id = post.category_id;
    form.specifications = post.specifications || {};

    // 3. Category logic
    if (post.category) {
      if (post.category.parent_id) {
        selectedParentId.value = post.category.parent_id;
        const parent = categories.value.find(c => c.id === post.category.parent_id);
        childCategories.value = parent ? parent.children : [];
      } else {
        selectedParentId.value = post.category.id;
        childCategories.value = post.category.children || [];
      }
    }

    // 4. Fetch Wards for initial values
    if (form.province_id) {
      const wRes = await axios.get(`/api/locations/wards/${form.province_id}`);
      wards.value = wRes.data;
    }

    // Image previews
    if (post.images) {
      postImages.value = post.images.map(img => ({
        id: img.id,
        path: img.image_path,
        file: null
      }));
    }

    await fetchAttributes();
    loading.value = false;
  } catch (error) {
    console.error('Failed to fetch post:', error);
    alert('Không thể tải dữ liệu tin đăng');
    router.push('/seller-center/posts');
  }
});

const onProvinceChange = async () => {
  form.ward_id = '';
  form.ward_name = '';
  wards.value = [];

  if (!form.province_id) {
    form.province_name = '';
    return;
  }

  const selected = provinces.value.find(p => p.code === form.province_id);
  form.province_name = selected ? selected.name : '';

  try {
    const res = await axios.get(`/api/locations/wards/${form.province_id}`);
    wards.value = res.data;
  } catch (error) {
    console.error('Failed to fetch wards:', error);
  }
};

const onWardChange = () => {
  const selected = wards.value.find(w => w.code === form.ward_id);
  form.ward_name = selected ? selected.name : '';
};

const selectParent = (cat) => {
  selectedParentId.value = cat.id;
  childCategories.value = cat.children || [];
  form.category_id = '';
  attributes.value = [];
  form.specifications = {};
};

const selectChild = (id) => {
  form.category_id = id;
  fetchAttributes();
};

const fetchAttributes = async () => {
  if (!form.category_id) return;
  try {
    const response = await axios.get(`/api/categories/${form.category_id}/attributes`);
    attributes.value = response.data.data;
    // Keep existing specs if they match the keys, otherwise set empty
    const newSpecs = {};
    attributes.value.forEach(attr => {
      if (form.specifications[attr.key] !== undefined && form.specifications[attr.key] !== null) {
        if (attr.type === 'checkbox' && !Array.isArray(form.specifications[attr.key])) {
          form.specifications[attr.key] = typeof form.specifications[attr.key] === 'string'
            ? [form.specifications[attr.key]]
            : [];
        }
        newSpecs[attr.key] = form.specifications[attr.key];
      } else {
        newSpecs[attr.key] = attr.type === 'checkbox' ? [] : '';
      }
    });
    form.specifications = newSpecs;
  } catch (error) {
    console.error('Failed to fetch attributes:', error);
  }
};

const parseOptions = (options) => {
  if (!options) return [];
  try {
    return typeof options === 'string' ? JSON.parse(options) : options;
  } catch (e) {
    return options.split(',').map(o => o.trim());
  }
};

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  const currentCount = postImages.value.length;
  const remaining = 6 - currentCount;

  files.slice(0, remaining).forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      postImages.value.push({
        id: null,
        path: e.target.result,
        file: file
      });
    };
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  postImages.value.splice(index, 1);
};

const setAsCover = (index) => {
  if (index === 0) return;
  const img = postImages.value[index];
  postImages.value.splice(index, 1);
  postImages.value.unshift(img);
};

const triggerReplaceImage = (index) => {
  replacingIndex.value = index;
  replaceImageInput.value.click();
};

const executeReplaceImage = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const idx = replacingIndex.value;
  const reader = new FileReader();
  reader.onload = (e) => {
    postImages.value[idx] = {
      id: null,
      path: e.target.result,
      file: file
    };
    event.target.value = '';
    replacingIndex.value = null;
  };
  reader.readAsDataURL(file);
};

const submitUpdate = async () => {
  submitting.value = true;
  errors.value = {};

  const formData = new FormData();
  formData.append('_method', 'PUT'); // Trick for Laravel form-data PUT
  formData.append('title', form.title);
  formData.append('description', form.description);
  formData.append('price', form.price);
  formData.append('address', form.address);
  formData.append('province_id', form.province_id);
  formData.append('province_name', form.province_name);
  formData.append('ward_id', form.ward_id);
  formData.append('ward_name', form.ward_name);
  formData.append('phone', form.phone);
  formData.append('category_id', form.category_id);
  formData.append('specifications', JSON.stringify(form.specifications));

  // Append new files
  const newFiles = postImages.value.filter(img => img.file !== null).map(img => img.file);
  newFiles.forEach((file, index) => {
    formData.append(`images[${index}]`, file);
  });

  // Append remaining old image IDs
  const remainingOldIds = postImages.value.filter(img => img.id !== null).map(img => img.id);
  formData.append('remaining_images', JSON.stringify(remainingOldIds));

  // Determine primary image
  if (postImages.value.length > 0) {
    const primaryImg = postImages.value[0];
    if (primaryImg.id !== null) {
      formData.append('primary_image_id', primaryImg.id);
    } else {
      // Find its index in the newFiles array
      const primaryNewIndex = newFiles.indexOf(primaryImg.file);
      formData.append('primary_new_file_index', primaryNewIndex);
    }
  }

  try {
    const postId = route.params.id;
    const response = await axios.post(`/api/posts/${postId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      alert(response.data.message);
      router.push('/seller-center/posts');
    }
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else alert('Lỗi: ' + (err.response?.data?.message || 'Vui lòng thử lại sau.'));
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
/* Reuse styles from PostCreate but add specific ones */
.post-edit-page {
  background-color: #f0f2f5;
  min-height: 100vh;
  padding: 2rem 1rem;
}

.container {
  max-width: 800px;
  margin: 0 auto;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 5rem 0;
  gap: 1rem;
}

.form-header {
  margin-bottom: 2rem;
}

.title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1c1e21;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #65676b;
  font-size: 0.95rem;
}

.main-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.card-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1c1e21;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #ebedf0;
  padding-bottom: 1rem;
}

.card-title .material-symbols-outlined {
  color: var(--color-primary);
}

/* Category Selection Styles */
.label-hint {
  display: block;
  font-size: 0.85rem;
  color: #65676b;
  margin-bottom: 0.75rem;
}

.parent-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
  gap: 0.75rem;
}

.parent-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.parent-item:hover {
  background: #f0f2f5;
}

.parent-item.active {
  border-color: var(--color-primary);
  background: var(--color-primary-fixed);
  color: var(--color-primary);
}

.cat-icon {
  width: 32px;
  height: 32px;
  margin-bottom: 0.5rem;
  object-fit: contain;
}

.cat-name {
  font-size: 0.75rem;
  font-weight: 600;
  text-align: center;
}

.child-section {
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px dashed #ddd;
}

.child-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.chip-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 1rem;
  background: white;
  font-size: 0.85rem;
  cursor: pointer;
}

.chip-btn.active {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 0.75rem;
}

.image-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #ddd;
}

.image-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.replace-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.2s;
  border-radius: 0.5rem;
}

.image-item:hover .replace-overlay {
  opacity: 1;
}

.replace-overlay button {
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.replace-overlay .cover-btn {
  background: #fef3c7;
  color: #d97706;
}

.replace-overlay .cover-btn:hover {
  transform: scale(1.1);
}

.replace-overlay .edit-btn {
  background: white;
  color: var(--color-primary);
}

.replace-overlay .edit-btn:hover {
  transform: scale(1.1);
}

.main-badge {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: var(--color-primary);
  color: white;
  font-size: 0.7rem;
  text-align: center;
  padding: 0.2rem 0;
}

.remove-btn {
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-btn {
  aspect-ratio: 1;
  border: 2px dashed #ccd0d5;
  border-radius: 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #65676b;
  transition: all 0.2s;
}

.form-group {
  margin-bottom: 1.25rem;
}

.field-label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1c1e21;
  margin-bottom: 0.5rem;
}

.input-field {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #dddfe2;
  border-radius: 0.5rem;
  font-size: 1rem;
  outline: none;
}

.input-field:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary-fixed);
}

.textarea {
  resize: vertical;
}

.attr-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.info-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 1rem;
}

.price-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-icon {
  position: absolute;
  left: 0.75rem;
  color: #65676b;
  font-size: 1.2rem;
}

.currency-unit {
  position: absolute;
  right: 1rem;
  font-weight: 700;
  color: var(--color-primary);
  font-size: 0.9rem;
}

.address-box {
  position: relative;
  display: flex;
  align-items: center;
}

.address-box span {
  position: absolute;
  left: 0.75rem;
  color: #65676b;
}

.with-icon {
  padding-left: 2.5rem;
}

.error-text {
  color: #d32f2f;
  font-size: 0.8rem;
  margin-top: 0.25rem;
}

.submit-section {
  margin-top: 1rem;
  margin-bottom: 3rem;
}

.submit-btn {
  flex: 2;
  padding: 1rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}

.cancel-btn {
  flex: 1;
  padding: 1rem;
  background: #f0f2f5;
  color: #1c1e21;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, .3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 600px) {

  .attr-grid,
  .info-grid {
    grid-template-columns: 1fr;
  }
}

/* Premium Checkbox Attribute Style */
.checkbox-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.75rem;
  margin-top: 0.5rem;
  background: #f8fafc;
  padding: 1rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
}

.custom-checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
  font-size: 0.9rem;
  font-weight: 500;
  color: #334155;
  transition: color 0.2s ease;
}

.custom-checkbox-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin: 0 !important;
  cursor: pointer;
  vertical-align: middle !important;
  appearance: checkbox !important;
  -webkit-appearance: checkbox !important;
}

.custom-checkbox-item:hover {
  color: var(--color-primary, #3b82f6);
}
</style>

