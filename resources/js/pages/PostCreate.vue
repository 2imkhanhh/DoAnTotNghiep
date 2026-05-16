<template>
  <div class="post-create-page animate-in fade-in">
    <div class="container">
      <div class="form-header">
        <h1 class="title">Đăng tin mới</h1>
        <p class="subtitle">Vui lòng điền đầy đủ thông tin để bài đăng được duyệt nhanh hơn</p>
      </div>

      <form @submit.prevent="submitPost" class="main-form">
        <!-- Section: Images -->
        <section class="form-card">
          <h2 class="card-title"><span class="material-symbols-outlined">image</span> Hình ảnh sản phẩm</h2>
          <div class="image-upload-wrapper">
            <div class="image-grid">
              <div v-for="(img, index) in imagePreviews" :key="index" class="image-item">
                <img :src="img" alt="Preview" />
                <div v-if="index === 0" class="main-badge">Ảnh bìa</div>
                <button type="button" @click="removeImage(index)" class="remove-btn">
                  <span class="material-symbols-outlined">close</span>
                </button>
              </div>
              <label v-if="imagePreviews.length < 6" class="upload-btn">
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
                <input :value="formattedPrice" @input="handlePriceInput" type="text" placeholder="Nhập giá bán"
                  class="input-field" required>
                <span class="currency-unit">VNĐ</span>
              </div>
              <p v-if="errors.price" class="error-text">{{ errors.price[0] }}</p>
            </div>

            <div class="form-group">
              <label class="field-label">Số điện thoại *</label>
              <input v-model="form.phone" type="text" class="input-field" placeholder="Nhập số điện thoại" required />
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

        <div class="submit-section">
          <button type="submit" class="submit-btn" :disabled="submitting">
            <span v-if="submitting" class="spinner"></span>
            <span v-else>Đăng tin ngay</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const categories = ref([]);
const childCategories = ref([]);
const selectedParentId = ref('');
const attributes = ref([]);
const imagePreviews = ref([]);
const selectedFiles = ref([]);
const submitting = ref(false);
const errors = ref({});

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
  try {
    const [catRes, provRes] = await Promise.all([
      axios.get('/api/categories'),
      axios.get('/api/locations/provinces')
    ]);
    categories.value = catRes.data.data;
    provinces.value = provRes.data;
  } catch (error) {
    console.error('Failed to fetch initial data:', error);
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
    attributes.value.forEach(attr => {
      form.specifications[attr.key] = '';
    });
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
  const remaining = 6 - selectedFiles.value.length;
  files.slice(0, remaining).forEach(file => {
    selectedFiles.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => imagePreviews.value.push(e.target.result);
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  selectedFiles.value.splice(index, 1);
  imagePreviews.value.splice(index, 1);
};

const submitPost = async () => {
  submitting.value = true;
  errors.value = {};
  const formData = new FormData();
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
  selectedFiles.value.forEach((file, index) => formData.append(`images[${index}]`, file));

  try {
    const token = localStorage.getItem('access_token');
    await axios.post('/api/posts', formData, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Đăng tin thành công! Tin của bạn đang được chờ duyệt');
    router.push('/');
  } catch (err) {
    if (err.response?.data?.errors) errors.value = err.response.data.errors;
    else alert('Lỗi: ' + (err.response?.data?.message || 'Vui lòng thử lại sau.'));
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.post-create-page {
  background-color: #f0f2f5;
  min-height: 100vh;
  padding: 2rem 1rem;
}

.container {
  max-width: 800px;
  margin: 0 auto;
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

/* Images */
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

.upload-btn:hover {
  background: var(--color-surface-container-low);
  border-color: var(--color-primary);
  color: var(--color-primary);
}

/* Category */
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

/* Form Groups */
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

/* Submit */
.submit-section {
  margin-top: 1rem;
  margin-bottom: 3rem;
}

.submit-btn {
  width: 100%;
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

.submit-btn:hover {
  opacity: 0.9;
}

.submit-btn:disabled {
  background: var(--color-outline-variant);
  cursor: not-allowed;
}

/* Animations */
.animate-in {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 600px) {

  .attr-grid,
  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
