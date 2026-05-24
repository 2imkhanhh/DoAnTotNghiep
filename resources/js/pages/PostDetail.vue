<template>
  <!-- Loading State -->
  <div v-if="loading" class="min-h-[70vh] flex flex-col items-center justify-center bg-slate-50 text-primary">
    <span class="material-symbols-outlined text-5xl animate-spin mb-4 text-primary">progress_activity</span>
    <p class="font-bold text-lg text-slate-700">Đang tải chi tiết tin đăng...</p>
  </div>

  <div class="post-detail-page" v-else-if="post">
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

              <!-- Navigation Arrows -->
              <button v-if="post.images.length > 1" class="gallery-nav-btn prev-btn" @click="prevImage"
                aria-label="Ảnh trước">
                <span class="material-symbols-outlined">chevron_left</span>
              </button>
              <button v-if="post.images.length > 1" class="gallery-nav-btn next-btn" @click="nextImage"
                aria-label="Ảnh sau">
                <span class="material-symbols-outlined">chevron_right</span>
              </button>
            </div>
            <div class="thumbnails" v-if="post.images.length > 1">
              <div v-for="(img, index) in post.images" :key="index"
                :class="['thumb-item', { active: activeImage === index }]" @click="activeImage = index">
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

                <button v-if="!authStore.isLoggedIn || post.user_id !== authStore.user?.id"
                  @click="toggleFavorite(post.id)" class="pill-favorite-btn" :class="{ 'active': isFavorite(post.id) }">
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
                  <span class="spec-value">{{ Array.isArray(val) ? val.filter(Boolean).join(', ') : val }}</span>
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
                <button @click="startConversation" class="btn-outline chat-btn">
                  <span class="material-symbols-outlined">chat</span>
                  Nhắn tin ngay
                </button>
              </div>
            </div>

            <div class="seller-card card">
              <div class="seller-header">
                <div class="avatar">
                  <img
                    :src="post.user?.avatar || `https://ui-avatars.com/api/?name=${post.user?.name}&background=random`"
                    alt="Avatar">
                </div>
                <div class="seller-meta">
                  <h4 class="seller-name">{{ post.user?.name }}</h4>
                  <div class="seller-rating" :class="post.user?.reviews_count > 0 ? 'text-amber-500' : 'text-outline-variant'">
                    <span class="material-symbols-outlined filled" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="rating-text font-medium" v-if="post.user?.reviews_count > 0">{{ parseFloat(post.user.average_rating).toFixed(1) }}</span>
                    <span class="rating-text text-sm font-medium" v-else>Chưa có đánh giá</span>
                  </div>
                </div>
                <button class="view-profile" @click="goToSellerProfile">Xem trang</button>
              </div>
              <div class="seller-stats">
                <div class="stat-item">
                  <span class="label">Đã bán</span>
                  <span class="value">{{ post.user?.sold_count || 0 }}</span>
                </div>
                <div class="stat-item">
                  <span class="label">Đánh giá</span>
                  <span class="value">{{ post.user?.reviews_count || 0 }}</span>
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
        <div class="related-header">
          <h2 class="section-title-large">Tin đăng tương tự</h2>
          <span class="related-subtitle">Khám phá các sản phẩm cùng danh mục có thể bạn quan tâm</span>
        </div>
        <div class="post-grid">
          <div v-for="rel in relatedPosts" :key="rel.id" class="rel-post-card" @click="goToPost(rel.slug)">
            <div class="rel-thumb">
              <img :src="getRelatedPrimaryImage(rel)" :alt="rel.title" class="rel-img">
              <div class="rel-thumb-overlay"></div>
              <span class="rel-badge" v-if="rel.category">{{ rel.category.name }}</span>
            </div>
            <div class="rel-body">
              <h4 class="rel-title" :title="rel.title">{{ rel.title }}</h4>
              <p class="rel-price">{{ formatPrice(rel.price) }}đ</p>

              <div class="rel-meta">
                <div class="rel-meta-item">
                  <span class="material-symbols-outlined">schedule</span>
                  <span>{{ formatTime(rel.created_at) }}</span>
                </div>
                <div class="rel-meta-item items-start">
                  <span class="material-symbols-outlined mt-0.5">location_on</span>
                  <div class="flex flex-col leading-tight">
                    <span>{{ rel.ward_name || 'Đang cập nhật' }}</span>
                    <span>{{ rel.province_name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Empty State / Error State -->
  <div v-else class="min-h-[70vh] flex flex-col items-center justify-center px-4 bg-slate-50">
    <img src="/images/empty.png" alt="Không tìm thấy" class="w-64 max-w-full mb-6 pointer-events-none" />
    <h2 class="text-2xl font-bold text-[#222222] mb-3">Tin đăng không còn tồn tại</h2>
    <p class="text-[15px] text-[#222222] mb-8 text-center max-w-md">Tin đăng này đã hết hạn hoặc đã bán. Hãy thử những tin đăng khác, bạn nhé.</p>
    <router-link to="/" class="px-8 py-3 bg-primary text-white rounded-full font-bold hover:opacity-90 transition-opacity">
      Về trang chủ
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const post = ref(null);
const relatedPosts = ref([]);
const activeImage = ref(0);
const loading = ref(true);
const favoriteIds = ref([]); // Danh sách ID các tin đã yêu thích

const getStatusClass = (status) => {
  switch (Number(status)) {
    case 0: return 'status-pending';
    case 1: return 'status-active';
    case 2: return 'status-sold';
    case 3: return 'status-rejected';
    default: return '';
  }
};

const getStatusText = (status) => {
  switch (Number(status)) {
    case 0: return 'Chờ duyệt';
    case 1: return 'Hiển thị';
    case 2: return 'Đã bán';
    case 3: return 'Bị từ chối';
    default: return 'Không xác định';
  }
};
const prevImage = () => {
  if (!post.value || !post.value.images.length) return;
  if (activeImage.value === 0) {
    activeImage.value = post.value.images.length - 1;
  } else {
    activeImage.value--;
  }
};

const nextImage = () => {
  if (!post.value || !post.value.images.length) return;
  if (activeImage.value === post.value.images.length - 1) {
    activeImage.value = 0;
  } else {
    activeImage.value++;
  }
};

const isFavorite = (postId) => {
  if (!postId) return false;
  return favoriteIds.value.includes(Number(postId));
};

const fetchFavorites = async () => {
  if (!authStore.isLoggedIn) return;
  try {
    const response = await axios.get('/api/user/favorites');
    if (response.data.success) {
      favoriteIds.value = response.data.data.data.map(p => p.id);
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách yêu thích:', error);
  }
};

const toggleFavorite = async (postId) => {
  if (!authStore.isLoggedIn) {
    alert('Vui lòng đăng nhập để sử dụng tính năng này');
    return;
  }

  if (!postId) return;

  try {
    const response = await axios.post(`/api/posts/${postId}/favorite`);
    if (response.data.success) {
      if (response.data.is_favorited) {
        favoriteIds.value.push(Number(postId));
      } else {
        favoriteIds.value = favoriteIds.value.filter(id => id !== Number(postId));
      }
    }
  } catch (error) {
    console.error('Lỗi khi thực hiện yêu thích:', error);
    const msg = error.response?.data?.message || 'Đã xảy ra lỗi khi thực hiện yêu thích';
    alert(msg);
  }
};

const startConversation = async () => {
  if (!authStore.isLoggedIn) {
    alert('Vui lòng đăng nhập để gửi tin nhắn cho người bán');
    router.push({ name: 'Login', query: { redirect: route.fullPath } });
    return;
  }

  try {
    const response = await axios.post('/api/conversations', {
      post_id: post.value.id
    });
    if (response.data.success) {
      router.push({
        path: '/chat',
        query: {
          conversation_id: response.data.conversation_id,
          attach_post_id: post.value.id
        }
      });
    }
  } catch (error) {
    console.error('Lỗi khi bắt đầu cuộc trò chuyện:', error);
    alert(error.response?.data?.message || 'Không thể kết nối đến hộp thư nhắn tin');
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

const getRelatedPrimaryImage = (relPost) => {
  if (relPost.images && relPost.images.length > 0) {
    const primary = relPost.images.find(img => img.is_primary);
    return primary ? primary.image_path : relPost.images[0].image_path;
  }
  return 'https://via.placeholder.com/400x300?text=No+Image';
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return 'Vừa xong';
  if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
  if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;

  return date.toLocaleDateString('vi-VN');
};

const goToPost = (slug) => {
  router.push(`/post/${slug}`);
};

const goToSellerProfile = () => {
  if (!post.value || !post.value.user) return;

  if (authStore.isLoggedIn && post.value.user_id === authStore.user?.id) {
    router.push('/profile');
  } else {
    router.push({
      name: 'PublicProfile',
      params: { id: post.value.user_id },
      state: { seller: post.value.user }
    });
  }
};

// Re-fetch when slug changes
watch(() => route.params.slug, () => {
  fetchPostDetail();
  window.scrollTo(0, 0);
});

onMounted(() => {
  fetchPostDetail();
  fetchFavorites();
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

/* Gallery Navigation Arrows */
.gallery-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(15, 23, 42, 0.6);
  /* Nền tối sang trọng (Dark Charcoal) */
  backdrop-filter: blur(8px);
  border: none;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0.7;
  /* Luôn hiển thị mờ để báo hiệu có thể click */
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.main-image:hover .gallery-nav-btn {
  opacity: 1;
}

.gallery-nav-btn:hover {
  background: rgba(15, 23, 42, 0.85);
  /* Tăng độ đậm khi hover */
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
}

.gallery-nav-btn:active {
  transform: translateY(-50%) scale(0.95);
}

.prev-btn {
  left: 1rem;
}

.next-btn {
  right: 1rem;
}

.gallery-nav-btn span {
  font-size: 28px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .gallery-nav-btn {
    opacity: 0.95 !important;
    width: 38px;
    height: 38px;
    background: rgba(15, 23, 42, 0.75);
    /* Đậm hơn trên mobile để nhìn rõ */
  }

  .gallery-nav-btn span {
    font-size: 24px;
  }

  .prev-btn {
    left: 0.5rem;
  }

  .next-btn {
    right: 0.5rem;
  }
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

.meta-item .material-symbols-outlined {
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
  align-items: center;
  text-align: center;
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
  border-top: 1px solid #e2e8f0;
  padding-top: 3.5rem;
  margin-top: 5rem;
}

.related-header {
  margin-bottom: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.section-title-large {
  font-size: 1.65rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
}

.related-subtitle {
  font-size: 0.9rem;
  color: #64748b;
}

.post-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.rel-post-card {
  background: white;
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02), 0 0 0 1px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

.rel-post-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(59, 130, 246, 0.1);
}

.rel-thumb {
  width: 100%;
  aspect-ratio: 4/3;
  position: relative;
  overflow: hidden;
  background: #f1f5f9;
}

.rel-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.rel-post-card:hover .rel-img {
  transform: scale(1);
}

.rel-thumb-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.1) 0%, transparent 100%);
  pointer-events: none;
}

.rel-badge {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  background: rgba(59, 130, 246, 0.9);
  backdrop-filter: blur(4px);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.rel-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.rel-title {
  margin: 0 0 0.5rem 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.4;
  transition: color 0.3s;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.rel-price {
  color: #ef4444;
  font-size: 1.1rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

.rel-meta {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid #f1f5f9;
}

.rel-meta-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 500;
}

.rel-meta-item span.material-symbols-outlined {
  font-size: 1rem;
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

@media (max-width: 576px) {
  .post-grid {
    grid-template-columns: 1fr;
  }
}

/* Cập nhật style cho trạng thái hiển thị */
.post-meta-top {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.status-badge {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.status-sold {
  background-color: #ef4444; /* red-500 */
  color: white;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.status-pending {
  background-color: #eab308; /* yellow-500 */
  color: white;
}

.status-rejected {
  background-color: #94a3b8; /* slate-400 */
  color: white;
}

.status-active {
  background-color: #22c55e; /* green-500 */
  color: white;
}
</style>
