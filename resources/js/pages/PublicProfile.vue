<template>
  <div v-if="isLoading" class="min-h-[70vh] flex flex-col items-center justify-center">
    <LoadingState />
  </div>

  <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar Trái: Thông tin người bán -->
      <aside class="w-full md:w-80 shrink-0">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm sticky top-24">
          <div class="p-6 border-b border-outline-variant text-center">
            <!-- Avatar -->
            <div class="relative w-24 h-24 mx-auto mb-4 mt-2">
              <img
                :src="seller.avatar || 'https://ui-avatars.com/api/?name=' + (seller.name || 'Seller') + '&background=020037&color=fff'"
                alt="Avatar" class="w-full h-full rounded-full object-cover border-[3px] border-primary/20 shadow-sm">
              <!-- Nút theo dõi (Icon góc đen, viền trắng) -->
              <button @click="toggleFollow"
                :class="['absolute -bottom-1 -right-1 w-9 h-9 rounded-full shadow-lg border-[3px] border-surface-container-lowest flex items-center justify-center transition-all z-10 cursor-pointer', seller.is_followed ? 'bg-surface-container-high text-on-surface' : 'bg-primary text-white hover:scale-105']"
                :title="seller.is_followed ? 'Bỏ theo dõi' : 'Theo dõi'">
                <span class="material-symbols-outlined text-[22px] font-bold block">{{ seller.is_followed ? 'done' :
                  'add'
                  }}</span>
              </button>
            </div>

            <h2 class="font-bold text-xl text-on-surface truncate flex justify-center items-center gap-1">
              {{ seller.name || 'Người bán' }}
              <span v-if="seller.is_vip" class="material-symbols-outlined text-blue-500 text-[20px]" title="Thành viên VIP">verified</span>
            </h2>
            <div class="flex items-center justify-center gap-1 mt-1" :class="seller.reviews_count > 0 ? 'text-amber-500' : 'text-outline-variant'">
              <span class="material-symbols-outlined font-variation-fill text-[18px]">star</span>
              <span class="text-[14px] font-bold text-on-surface" v-if="seller.reviews_count > 0">{{ parseFloat(seller.average_rating).toFixed(1) }}</span>
              <span class="text-[13px] font-medium" v-else>Chưa có đánh giá</span>
              <span class="text-[13px] text-on-surface-variant" v-if="seller.reviews_count > 0">({{ seller.reviews_count }} đánh giá)</span>
            </div>

            <!-- Followers / Following -->
            <div class="flex justify-center gap-12 mt-6 mb-2">
              <div @click="openFollowModal('followers')" class="text-center cursor-pointer group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  seller.followers_count || 0 }}</div>
                <div
                  class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">
                  Người theo dõi</div>
              </div>
              <div @click="openFollowModal('following')" class="text-center cursor-pointer group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  seller.followings_count || 0 }}</div>
                <div
                  class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">
                  Đang theo dõi</div>
              </div>
            </div>
          </div>

          <!-- Thông tin chi tiết liên hệ -->
          <div class="p-6 space-y-4 border-b border-outline-variant bg-surface-container-low/20">
            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">location_on</span>
              <div class="text-sm text-left">
                <p class="font-bold text-on-surface">Địa chỉ</p>
                <p class="text-on-surface-variant mt-0.5 leading-relaxed">
                  {{ seller.ward_name && seller.province_name ? `${seller.ward_name}, ${seller.province_name}` :
                    (seller.province_name ? seller.province_name : 'Đang cập nhật') }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3 mt-4">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">mail</span>
              <div class="text-sm text-left w-full">
                <p class="font-bold text-on-surface">Email</p>
                <p class="text-on-surface-variant mt-0.5 leading-relaxed truncate">{{ seller.email || 'Đang cập nhật' }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3 mt-4">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">call</span>
              <div class="text-sm text-left w-full">
                <p class="font-bold text-on-surface">Số điện thoại</p>
                <p class="text-on-surface-variant mt-0.5 leading-relaxed">{{ seller.phone || 'Đang cập nhật' }}</p>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Cột Phải: Danh sách tin đăng của người bán -->
      <main class="grow">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden min-h-[600px]">
          <!-- Header Tin đăng -->
          <div class="p-6 border-b border-outline-variant bg-surface-container-low/10">
            <h2 class="text-[22px] font-medium text-on-surface mb-5">Tất cả tin đăng ({{ (seller.active_count || 0) + (seller.sold_count || 0) }})</h2>
            <div class="flex flex-wrap gap-2.5">
              <button @click="setTab('active')"
                :class="['px-4 py-2 rounded-full text-sm transition-colors cursor-pointer', postFilter === 'active' ? 'bg-[#222222] text-white font-bold shadow-sm' : 'bg-[#F2F2F2] text-[#222222] font-medium hover:bg-[#E5E5E5]']">
                Tin đang hoạt động ({{ seller.active_count || 0 }})
              </button>
              <button @click="setTab('sold')"
                :class="['px-4 py-2 rounded-full text-sm transition-colors cursor-pointer', postFilter === 'sold' ? 'bg-[#222222] text-white font-bold shadow-sm' : 'bg-[#F2F2F2] text-[#222222] font-medium hover:bg-[#E5E5E5]']">
                Đã bán ({{ seller.sold_count || 0 }})
              </button>
            </div>
          </div>

          <!-- Danh sách tin đăng -->
          <div class="p-6 bg-surface-container-lowest">
            <div v-if="filteredPosts.length === 0" class="text-center py-20 text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl mb-3 opacity-40">inventory_2</span>
              <p class="font-bold text-lg">Chưa có tin đăng nào</p>
              <p class="text-sm mt-1">Các tin đăng thuộc mục này hiện đang trống.</p>
            </div>

            <div v-else class="flex flex-col gap-4">
              <!-- Tin đăng Card Ngang -->
              <div v-for="item in filteredPosts" :key="item.id" @click="item.status !== 'sold' ? goToPost(item.slug) : null"
                class="bg-surface-container rounded-2xl overflow-hidden border border-outline-variant hover:shadow-md transition-all flex flex-col sm:flex-row group relative"
                :class="item.status === 'sold' ? 'cursor-default' : 'cursor-pointer'">
                
                <!-- Nút yêu thích (Trái tim) -->
                <button v-if="(!authStore.isLoggedIn || item.user_id !== authStore.user?.id) && item.status !== 'sold'"
                  @click.stop="toggleFavorite(item.id)"
                  class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:scale-125 z-10 active:scale-95 group/heart">
                  <!-- Ruột Đỏ -->
                  <span :class="['material-symbols-outlined text-[22px] text-error font-variation-fill transition-all duration-300 absolute',
                    isFavorite(item.id) ? 'opacity-100 scale-100' : 'opacity-0 scale-0']">
                    favorite
                  </span>
                  <!-- Viền Trắng -->
                  <span
                    class="material-symbols-outlined text-[26px] text-white absolute drop-shadow-[0_2px_2px_rgba(0,0,0,0.5)]">
                    favorite
                  </span>
                </button>
                <div class="relative w-full sm:w-[240px] sm:h-[180px] aspect-[4/3] sm:aspect-auto bg-surface-container-high overflow-hidden shrink-0">
                  <img :src="item.image" :alt="item.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  <span
                    class="absolute top-3 left-3 bg-primary/95 text-on-primary text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {{ item.category }}
                  </span>
                  
                  <!-- Nhãn đã bán -->
                  <div v-if="item.status === 'sold'" class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <span class="bg-surface-container-lowest text-on-surface px-3 py-1.5 rounded-full text-xs font-bold shadow-md">Đã bán</span>
                  </div>


                </div>
                <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between min-w-0">
                  <div class="pr-8">
                    <h4
                      :class="['font-bold text-on-surface text-sm sm:text-base line-clamp-2 leading-snug group-hover:text-primary transition-colors', item.status === 'sold' ? 'cursor-text' : '']"
                      :title="item.title">
                      {{ item.title }}
                    </h4>
                    <p :class="['text-error font-extrabold text-base sm:text-lg mt-1', item.status === 'sold' ? 'cursor-text' : '']">{{ formatPrice(item.price) }}đ</p>
                  </div>

                  <div
                    :class="['mt-4 sm:mt-0 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-6 text-xs text-on-surface-variant', item.status === 'sold' ? 'cursor-text' : '']">
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">schedule</span>
                      {{ item.time }}
                    </span>
                    <span class="flex items-center gap-1 truncate">
                      <span class="material-symbols-outlined text-sm">location_on</span>
                      {{ item.location }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Phân trang Load More -->
            <div v-if="postState[postFilter].hasMore" class="flex justify-center items-center gap-4 mt-8">
                <button
                    @click="loadMorePosts"
                    :disabled="loadingMore"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-full border-2 border-outline-variant text-[#222222] hover:border-primary hover:text-primary transition-all font-bold flex items-center justify-center gap-2 disabled:opacity-50">
                    <span v-if="loadingMore" class="material-symbols-outlined animate-spin text-[20px]">refresh</span>
                    {{ loadingMore ? 'Đang tải...' : 'Xem thêm tin khác' }}
                </button>
            </div>
          </div>

        </div>

        <!-- Khối Đánh giá từ người mua (Tách rời hoàn toàn khỏi Tin Đăng) -->
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden mt-8">
          <div class="p-5 px-6 border-b border-outline-variant bg-surface-container-low/10">
            <h2 class="text-xl font-extrabold text-on-surface">Đánh giá ({{ seller.reviews_count || 0 }})</h2>
          </div>
          <div class="p-6 space-y-6">
            <div v-if="reviews.length === 0" class="text-center py-16 text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl mb-3 opacity-40">rate_review</span>
              <p class="font-bold text-lg">Chưa có đánh giá nào</p>
            </div>

            <div v-else v-for="rev in reviews" :key="rev.id"
              class="border-b border-outline-variant last:border-0 pb-6 last:pb-0">
              <div class="flex items-start gap-4">
                <img :src="rev.reviewer.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(rev.reviewer.name) + '&background=random'"
                  class="w-10 h-10 rounded-full object-cover shrink-0 border border-outline-variant" />
                <div class="flex-1 text-left">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <h4 class="font-bold text-on-surface text-sm sm:text-base">{{ rev.reviewer.name }}</h4>
                    <span class="text-xs text-on-surface-variant">{{ formatTime(rev.created_at) }}</span>
                  </div>

                  <div class="flex items-center gap-1 mb-2">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm"
                          :class="star <= rev.rating ? 'text-amber-500' : 'text-outline-variant'"
                          :style="star <= rev.rating ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                      star </span>
                  </div>

                  <p class="text-on-surface text-sm leading-relaxed whitespace-pre-line mb-3">
                    {{ rev.comment }}
                  </p>

                  <router-link v-if="rev.order && rev.order.post" :to="`/post/${rev.order.post.slug}`"
                    class="flex items-center gap-3 group/post cursor-pointer">
                    <img :src="rev.order.post.images && rev.order.post.images.length > 0 ? (rev.order.post.images.find(img => img.is_primary)?.image_path || rev.order.post.images[0].image_path) : 'https://via.placeholder.com/100x100?text=No+Image'" 
                         alt="Product Image" class="w-12 h-12 rounded-lg object-cover bg-surface-container-low shrink-0" />
                    <div class="flex-1 min-w-0">
                      <h5 class="text-[15px] font-medium text-slate-800 line-clamp-1 group-hover/post:text-primary transition-colors">
                        {{ rev.order.post.title }}
                      </h5>
                      <p class="text-[14px] font-semibold text-error mt-0.5">{{ formatPrice(rev.order.post.price) }}đ</p>
                    </div>
                  </router-link>
                </div>
              </div>
            </div>
            
            <div v-if="reviewsPagination && reviewsPagination.next_page_url" class="text-center pt-2">
              <button @click="fetchReviews(reviewsPagination.current_page + 1)" class="text-sm font-bold text-primary hover:underline">
                Xem thêm đánh giá
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Modal Hiển thị Danh sách Người theo dõi / Đang theo dõi -->
    <div v-if="followModal.show"
      class="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="followModal.show = false">
      <div
        class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col max-h-[80vh] animate-fadeIn">
        <!-- Header -->
        <div
          class="px-5 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
          <h3 class="font-extrabold text-lg text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">
              {{ followModal.type === 'followers' ? 'group' : 'person_add' }}
            </span>
            {{ followModal.type === 'followers' ? 'Người theo dõi' : 'Đang theo dõi' }} ({{ followModal.list.length }})
          </h3>
          <button @click="followModal.show = false"
            class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-full cursor-pointer">
            <span class="material-symbols-outlined block">close</span>
          </button>
        </div>

        <!-- List -->
        <div class="p-3 overflow-y-auto grow custom-scrollbar">
          <div v-if="followModal.isLoading" class="flex flex-col items-center justify-center py-10">
            <LoadingState />
          </div>
          <div v-else-if="followModal.list.length === 0" class="text-center py-10 text-on-surface-variant">
            Danh sách này hiện đang trống.
          </div>
          <div v-else class="space-y-2">
            <div v-for="user in followModal.list" :key="user.id"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors border border-transparent hover:border-outline-variant cursor-pointer">
              <img :src="user.avatar || 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'"
                class="w-12 h-12 rounded-full object-cover border border-outline-variant shrink-0" />
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-on-surface truncate" :title="user.name">{{ user.name }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { toast, confirmDialog } from '../utils/alert';

import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import LoadingState from '../components/common/LoadingState.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const isLoading = ref(true);

// Mock dữ liệu danh sách người theo dõi
const followModal = ref({ show: false, type: 'followers', list: [], isLoading: false });

const openFollowModal = async (type) => {
  followModal.value.type = type;
  followModal.value.show = true;
  followModal.value.list = [];
  followModal.value.isLoading = true;

  try {
    const endpoint = type === 'followers' ? `/api/users/${seller.value.id}/followers` : `/api/users/${seller.value.id}/followings`;
    const response = await axios.get(endpoint);
    if (response.data.success) {
      followModal.value.list = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách theo dõi:', error);
  } finally {
    followModal.value.isLoading = false;
  }
};

const seller = ref({});
const postState = ref({
  active: { data: [], page: 1, hasMore: false },
  sold: { data: [], page: 1, hasMore: false }
});
const postFilter = ref('active');
const loadingMore = ref(false);
const showFollowersModal = ref(false);
const favoriteIds = ref([]); // Danh sách ID các tin đã yêu thích

const isFavorite = (postId) => {
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

const filteredPosts = computed(() => {
  return postState.value[postFilter.value].data;
});

const setTab = (tab) => {
  if (postFilter.value === tab) return;
  postFilter.value = tab;
  if (postState.value[tab].data.length === 0) {
    fetchSellerProfile(tab, 1);
  }
};

const toggleFollow = async () => {
  if (!authStore.isLoggedIn) {
    toast('Vui lòng đăng nhập để sử dụng tính năng này!', 'info');
    router.push('/login');
    return;
  }

  try {
    const response = await axios.post(`/api/users/${seller.value.id}/follow`);
    if (response.data.success) {
      seller.value.is_followed = response.data.is_following;
      seller.value.followers_count = response.data.followers_count;
    }
  } catch (error) {
    console.error('Lỗi khi thao tác theo dõi:', error);
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const goToPost = (slug) => {
  router.push(`/post/${slug}`);
};

const loadMorePosts = () => {
  const tab = postFilter.value;
  if (!postState.value[tab].hasMore || loadingMore.value) return;
  
  const nextPage = postState.value[tab].page + 1;
  fetchSellerProfile(tab, nextPage);
};

// Gọi API dữ liệu tin đăng và thông tin người bán thực tế
const fetchSellerProfile = async (status = 'active', page = 1) => {
  const sellerId = route.params.id;
  loadingMore.value = true;
  try {
    const response = await axios.get(`/api/seller/${sellerId}?status=${status}&page=${page}`);
    if (response.data.success) {
      // Cập nhật seller profile ở lần đầu
      if (page === 1 && status === 'active' && !seller.value.id) {
        seller.value = response.data.data.user;
        seller.value.active_count = response.data.data.active_count;
        seller.value.sold_count = response.data.data.sold_count;
      }

      const formattedPosts = response.data.data.posts.data.map(post => {
        let imagePath = 'https://via.placeholder.com/400x300?text=No+Image';
        if (post.images && post.images.length > 0) {
          const primary = post.images.find(img => img.is_primary);
          imagePath = primary ? primary.image_path : post.images[0].image_path;
        }

        return {
          id: post.id,
          title: post.title,
          price: post.price,
          category: post.category?.name || 'Khác',
          image: imagePath,
          time: formatTime(post.created_at),
          location: `${post.ward_name || ''}, ${post.province_name || ''}`.replace(/^,\s/, '') || 'Đang cập nhật',
          slug: post.slug,
          status: post.status
        };
      });

      const pagination = response.data.data.posts;
      const type = status;

      if (page === 1) {
        postState.value[type].data = formattedPosts;
      } else {
        postState.value[type].data = [...postState.value[type].data, ...formattedPosts];
      }

      postState.value[type].page = pagination.current_page;
      postState.value[type].hasMore = pagination.current_page < pagination.last_page;
      
      // Fetch reviews sau khi load xong profile (chỉ gọi lần 1)
      if (page === 1 && status === 'active') {
        fetchReviews(1);
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải thông tin người bán:', error);
  } finally {
    loadingMore.value = false;
  }
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return 'Vừa xong';
  if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
  if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;

  return date.toLocaleDateString('vi-VN');
};

const reviews = ref([]);
const reviewsPagination = ref(null);

const fetchReviews = async (page = 1) => {
  if (!seller.value.id) return;
  try {
    const response = await axios.get(`/api/users/${seller.value.id}/reviews?page=${page}`);
    if (response.data.success) {
      if (page === 1) {
        reviews.value = response.data.data.data;
      } else {
        reviews.value = [...reviews.value, ...response.data.data.data];
      }
      reviewsPagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi khi lấy danh sách đánh giá:', error);
  }
};

onMounted(async () => {
  window.scrollTo(0, 0);
  isLoading.value = true;
  
  try {
    await Promise.all([
      fetchSellerProfile(),
      fetchFavorites()
    ]);
  } finally {
    isLoading.value = false;
  }
});

// Xử lý yêu thích (favorite)
const toggleFavorite = async (postId) => {
  if (!authStore.isLoggedIn) {
    toast('Vui lòng đăng nhập để sử dụng tính năng này', 'info');
    router.push('/login');
    return;
  }

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
    console.error('Lỗi khi thao tác yêu thích:', error);
  }
};
</script>

<style scoped>
.text-decoration-none {
  text-decoration: none;
}

.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
</style>

