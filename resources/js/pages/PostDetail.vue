<template>
  <div class="post-detail-page" v-if="post">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-container">
      <div class="container">
        <nav class="breadcrumb">
          <router-link to="/">Trang chủ</router-link>
          <span class="separator">/</span>
          <router-link :to="`/category/${post.category?.slug}`">{{ post.category?.name }}</router-link>
          <span class="separator">/</span>
          <span class="current">{{ post.title }}</span>
        </nav>
      </div>
    </div>

    <div class="container main-content">
      <div class="content-grid">
        <!-- Left Column: Gallery & Info -->
        <div class="left-col">
          <!-- Image Gallery -->
          <div class="gallery-card card">
            <div class="main-image">
              <img :src="post.images[activeImage]?.image_path" :alt="post.title">
              <button class="zoom-btn">
                <span class="material-symbols-outlined">zoom_in</span>
              </button>
            </div>
            <div class="thumbnails" v-if="post.images.length > 1">
              <div 
                v-for="(img, index) in post.images" 
                :key="index"
                :class="['thumb-item', { active: activeImage === index }]"
                @click="activeImage = index"
              >
                <img :src="img.image_path" alt="">
              </div>
            </div>
          </div>

          <!-- Description Section -->
          <div class="info-card card">
            <div class="post-header">
              <div class="flex justify-between items-start gap-4">
                <div class="flex-1 min-w-0">
                  <div class="post-meta-top mb-2">
                    <span class="category-tag">{{ post.category?.name }}</span>
                    <span v-if="post.status !== 1" :class="['status-badge', getStatusClass(post.status)]">
                      {{ getStatusText(post.status) }}
                    </span>
                  </div>
                  <h1 class="post-title">{{ post.title }}</h1>
                </div>
                
                <button @click="toggleFavorite(post.id)" 
                  class="pill-favorite-btn"
                  :class="{ 'active': isFavorite(post.id) }">
                  <span class="material-symbols-outlined" :class="{ 'font-variation-fill': isFavorite(post.id) }">
                    favorite
                  </span>
                  <span class="btn-text">Lưu</span>
                </button>
              </div>
            </div>
            <div class="post-meta">
              <div class="meta-item">
                <span class="material-symbols-outlined">schedule</span>
                {{ formatDate(post.created_at) }}
              </div>
              <div class="meta-item items-start">
                <span class="material-symbols-outlined mt-1">location_on</span>
                <div class="flex flex-col">
                  <span>{{ post.ward_name }}</span>
                  <span>{{ post.province_name }}</span>
                </div>
              </div>
              <div class="meta-item">
                <span class="material-symbols-outlined">visibility</span>
                1,234 lượt xem
              </div>
            </div>

            <div class="section">
              <h3 class="section-title">Mô tả chi tiết</h3>
              <div class="description-content">
                {{ post.description }}
              </div>
            </div>

            <!-- Dynamic Specifications -->
            <div class="section" v-if="hasSpecs">
              <h3 class="section-title">Thông số kỹ thuật</h3>
              <div class="specs-grid">
                <div v-for="(val, key) in post.specifications" :key="key" class="spec-row">
                  <span class="spec-label">{{ getAttributeName(post, key) }}</span>
                  <span class="spec-value">{{ Array.isArray(val) ? val.join(', ') : val }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Pricing & Seller -->
        <div class="right-col">
          <div class="sticky-sidebar">
            <!-- Price Card -->
            <div class="price-card card">
              <div class="price-value">{{ formatPrice(post.price) }}đ</div>
              <div class="action-buttons">
                <a :href="`tel:${post.phone || post.user?.phone}`" class="btn-primary call-btn">
                  <span class="material-symbols-outlined">call</span>
                  {{ post.phone || post.user?.phone }}
                </a>
                <button class="btn-outline chat-btn">
                  <span class="material-symbols-outlined">chat</span>
                  Nhắn tin ngay
                </button>
              </div>
            </div>

            <!-- Seller Card -->
            <div class="seller-card card">
              <div class="seller-header">
                <div class="avatar">
                  <img :src="`https://ui-avatars.com/api/?name=${post.user?.name}&background=random`" alt="">
                </div>
                <div class="seller-meta">
                  <h4 class="seller-name">{{ post.user?.name }}</h4>
                  <div class="seller-rating">
                    <span class="material-symbols-outlined filled">star</span>
                    <span class="rating-text">4.8 (20 đánh giá)</span>
                  </div>
                </div>
                <button class="view-profile">Xem trang</button>
              </div>
              <div class="seller-stats">
                <div class="stat-item">
                  <span class="label">Tham gia</span>
                  <span class="value">2 năm</span>
                </div>
                <div class="stat-item">
                  <span class="label">Phản hồi</span>
                  <span class="value">95%</span>
                </div>
              </div>
            </div>

            <!-- Safety Card -->
            <div class="safety-card">
              <span class="material-symbols-outlined">verified_user</span>
              <p>Mẹo mua hàng: Giao dịch trực tiếp để tránh rủi ro lừa đảo qua mạng.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Posts -->
      <section class="related-section" v-if="relatedPosts.length">
        <h2 class="section-title-large">Tin đăng tương tự</h2>
        <div class="post-grid">
           <!-- Using your existing PostCard component or simplified version -->
           <div v-for="rel in relatedPosts" :key="rel.id" class="rel-post-card" @click="goToPost(rel.slug)">
              <div class="rel-thumb">
                <img :src="rel.images[0]?.image_path" alt="">
              </div>
              <div class="rel-body">
                <h4 class="rel-title">{{ rel.title }}</h4>
                <p class="rel-price">{{ formatPrice(rel.price) }}đ</p>
                <span class="rel-time">{{ formatDate(rel.created_at) }}</span>
              </div>
           </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const post = ref(null);
const relatedPosts = ref([]);
const activeImage = ref(0);
const loading = ref(true);
const favoriteIds = ref([]); // Demo local state

const isFavorite = (postId) => {
    if (!postId) return false;
    return favoriteIds.value.includes(postId) || favoriteIds.value.includes(String(postId)) || favoriteIds.value.includes(Number(postId));
};

const toggleFavorite = (postId) => {
    if (!postId) return;
    const index = favoriteIds.value.indexOf(postId);
    if (index === -1) {
        favoriteIds.value.push(postId);
    } else {
        favoriteIds.value.splice(index, 1);
    }
};

const fetchPostDetail = async () => {
  loading.value = true;
  try {
    const slug = route.params.slug;
    const response = await axios.get(`/api/posts/${slug}`);
    post.value = response.data.data;
    relatedPosts.value = response.data.related;
    activeImage.value = 0;
  } catch (error) {
    console.error('Lỗi khi tải chi tiết tin:', error);
  } finally {
    loading.value = false;
  }
};

const hasSpecs = computed(() => {
  return post.value?.specifications && Object.keys(post.value.specifications).length > 0;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  });
};

const getAttributeName = (post, key) => {
  if (!post.category?.attributes) return key;
  const attr = post.category.attributes.find(a => a.key === key);
  return attr ? attr.name : key;
};

const goToPost = (slug) => {
  router.push(`/post/${slug}`);
};

// Re-fetch when slug changes
watch(() => route.params.slug, () => {
  fetchPostDetail();
  window.scrollTo(0, 0);
});

onMounted(() => {
  fetchPostDetail();
});
</script>

<style scoped>
.post-detail-page {
  background-color: #f8fafc;
  padding-bottom: 4rem;
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Breadcrumbs */
.breadcrumb-container {
  background: white;
  padding: 1rem 0;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 2rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #64748b;
}

.breadcrumb a {
  color: #3b82f6;
  text-decoration: none;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

.breadcrumb .separator {
  color: #94a3b8;
}

.breadcrumb .current {
  color: #1e293b;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Layout */
.content-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 2rem;
}

.card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

/* Gallery */
.main-image {
  position: relative;
  width: 100%;
  aspect-ratio: 4/3;
  background: #f1f5f9;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.zoom-btn {
  position: absolute;
  right: 1rem;
  bottom: 1rem;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.thumbnails {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  overflow-x: auto;
}

.thumb-item {
  width: 80px;
  height: 80px;
  border-radius: 0.75rem;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  flex-shrink: 0;
}

.thumb-item.active {
  border-color: #3b82f6;
}

.thumb-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.info-card {
  padding: 2rem;
}

.post-header {
  margin-bottom: 1.5rem;
}

.pill-favorite-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 1.25rem;
  border: 1px solid #dddfe2;
  border-radius: 999px;
  background: white;
  color: #1c1e21;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  flex-shrink: 0;
  height: fit-content;
  margin-top: 0.25rem;
}

.pill-favorite-btn:hover {
  background: #f2f3f5;
  border-color: #ccd0d5;
}

.pill-favorite-btn.active {
  border-color: #fee2e2;
  background: #fef2f2;
  color: #ef4444;
}

.pill-favorite-btn .material-symbols-outlined {
  font-size: 22px;
}

.pill-favorite-btn .btn-text {
  font-size: 1rem;
  font-weight: 600;
}

.post-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 1rem;
  line-height: 1.2;
  word-break: break-word;
  overflow-wrap: break-word;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.9rem;
}

.meta-item span {
  font-size: 1.25rem;
}

.section {
  margin-bottom: 2.5rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
  position: relative;
  display: inline-block;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  width: 30px;
  height: 3px;
  background: #3b82f6;
  border-radius: 10px;
}

.description-content {
  color: #475569;
  line-height: 1.8;
  white-space: pre-line;
}

/* Specs Table */
.specs-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 1rem;
}

.spec-row {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.spec-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
}

.spec-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
}

/* Sticky Sidebar */
.sticky-sidebar {
  position: sticky;
  top: 100px;
}

.price-card {
  padding: 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
}

.price-value {
  font-size: 2rem;
  font-weight: 900;
  color: #ef4444;
  margin-bottom: 1.5rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.call-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: #16a34a;
  color: white;
  padding: 1rem;
  border-radius: 0.75rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s;
}

.call-btn:hover {
  background: #15803d;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}

.chat-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  background: white;
  border: 2px solid #3b82f6;
  color: #3b82f6;
  padding: 0.875rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.chat-btn:hover {
  background: #eff6ff;
}

/* Seller Card */
.seller-card {
  padding: 1.5rem;
}

.seller-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  overflow: hidden;
  background: #f1f5f9;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.seller-name {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

.seller-rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-top: 0.25rem;
}

.seller-rating span.filled {
  color: #f59e0b;
  font-size: 1.1rem;
}

.rating-text {
  font-size: 0.8rem;
  color: #64748b;
}

.view-profile {
  margin-left: auto;
  background: #f1f5f9;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
}

.seller-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-item .label {
  font-size: 0.75rem;
  color: #94a3b8;
}

.stat-item .value {
  font-weight: 700;
  color: #1e293b;
}

.safety-card {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 1rem;
  color: #92400e;
  font-size: 0.8rem;
  line-height: 1.4;
}

.safety-card span {
  color: #d97706;
}

/* Related Section */
.related-section {
  margin-top: 4rem;
}

.section-title-large {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 2rem;
}

.post-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.rel-post-card {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.3s;
}

.rel-post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.rel-thumb {
  width: 100%;
  aspect-ratio: 1;
}

.rel-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.rel-body {
  padding: 1rem;
}

.rel-title {
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.rel-price {
  color: #ef4444;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.rel-time {
  font-size: 0.75rem;
  color: #94a3b8;
}

@media (max-width: 992px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  .sticky-sidebar {
    position: static;
  }
  .post-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
