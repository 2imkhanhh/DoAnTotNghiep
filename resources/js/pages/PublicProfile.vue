<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
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
                :class="['absolute -bottom-1 -right-1 w-9 h-9 rounded-full shadow-lg border-[3px] border-surface-container-lowest flex items-center justify-center transition-all z-10 cursor-pointer', isFollowing ? 'bg-surface-container-high text-on-surface' : 'bg-primary text-white hover:scale-105']"
                :title="isFollowing ? 'Bỏ theo dõi' : 'Theo dõi'">
                <span class="material-symbols-outlined text-[22px] font-bold block">{{ isFollowing ? 'done' : 'add'
                }}</span>
              </button>
            </div>

            <h2 class="font-bold text-xl text-on-surface truncate">{{ seller.name || 'Người bán' }}</h2>
            <div class="flex items-center justify-center gap-1 mt-1 text-amber-500">
              <span class="material-symbols-outlined font-variation-fill text-[18px]">star</span>
              <span class="text-[14px] font-bold text-on-surface">{{ seller.rating || '5.0' }}</span>
              <span class="text-[13px] text-on-surface-variant">({{ seller.reviews_count || 20 }} đánh giá)</span>
            </div>

            <!-- Followers / Following -->
            <div class="flex justify-center gap-12 mt-6 mb-2">
              <div @click="showFollowersModal = true" class="text-center cursor-pointer group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  seller.followers_count || mockFollowers.length }}</div>
                <div
                  class="text-[12px] font-medium text-on-surface-variant group-hover:text-primary transition-colors mt-0.5">
                  Người theo dõi</div>
              </div>
              <div class="text-center cursor-pointer group">
                <div class="font-extrabold text-2xl text-on-surface group-hover:text-primary transition-colors">{{
                  seller.following_count || 5 }}</div>
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
                  {{ seller.ward_name && seller.province_name ? `${seller.ward_name}, ${seller.province_name}` : (seller.province_name ? seller.province_name : 'Đang cập nhật') }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3 mt-4">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">mail</span>
              <div class="text-sm text-left w-full">
                <p class="font-bold text-on-surface">Email</p>
                <p class="text-on-surface-variant mt-0.5 leading-relaxed truncate">{{ seller.email || 'Đang cập nhật' }}</p>
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
            <h2 class="text-[22px] font-medium text-on-surface mb-5">Tất cả tin đăng ({{ posts.length +
              (seller.sold_count || 0) }})</h2>
            <div class="flex flex-wrap gap-2.5">
              <button @click="postFilter = 'active'"
                :class="['px-4 py-2 rounded-full text-sm transition-colors cursor-pointer', postFilter === 'active' ? 'bg-[#222222] text-white font-bold shadow-sm' : 'bg-[#F2F2F2] text-[#222222] font-medium hover:bg-[#E5E5E5]']">
                Tin đang hoạt động ({{ posts.length }})
              </button>
              <button @click="postFilter = 'sold'"
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

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Tin đăng Card -->
              <div v-for="item in filteredPosts" :key="item.id" @click="goToPost(item.slug)"
                class="bg-surface-container rounded-2xl overflow-hidden border border-outline-variant hover:shadow-md transition-all cursor-pointer flex flex-col group">
                <div class="relative aspect-4/3 bg-surface-container-high overflow-hidden shrink-0">
                  <img :src="item.image" :alt="item.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  <span
                    class="absolute top-3 left-3 bg-primary/95 text-on-primary text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {{ item.category }}
                  </span>
                  <!-- Nút yêu thích (Trái tim) -->
                  <button v-if="!authStore.isLoggedIn || item.user_id !== authStore.user?.id" @click.stop="toggleFavorite(item.id)"
                    class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:scale-125 z-10 active:scale-95 group/heart">
                    <!-- Ruột Đỏ -->
                    <span :class="['material-symbols-outlined text-[22px] text-error font-variation-fill transition-all duration-300 absolute', 
                        isFavorite(item.id) ? 'opacity-100 scale-100' : 'opacity-0 scale-0']">
                        favorite
                    </span>
                    <!-- Viền Trắng -->
                    <span class="material-symbols-outlined text-[26px] text-white absolute drop-shadow-[0_2px_2px_rgba(0,0,0,0.5)]">
                        favorite
                    </span>
                  </button>
                </div>
                <div class="p-4 flex-1 flex flex-col justify-between">
                  <div>
                    <h4
                      class="font-extrabold text-on-surface line-clamp-2 leading-snug group-hover:text-primary transition-colors"
                      :title="item.title">
                      {{ item.title }}
                    </h4>
                    <p class="text-error font-black text-lg mt-2">{{ formatPrice(item.price) }}đ</p>
                  </div>

                  <div
                    class="mt-4 pt-3 border-t border-outline-variant flex items-center justify-between text-xs text-on-surface-variant">
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">schedule</span>
                      {{ item.time }}
                    </span>
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">location_on</span>
                      {{ item.location }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Khối Đánh giá từ người mua (Tách rời hoàn toàn khỏi Tin Đăng) -->
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden mt-8">
          <div class="p-5 px-6 border-b border-outline-variant bg-surface-container-low/10">
            <h2 class="text-xl font-extrabold text-on-surface">Đánh giá từ người mua ({{ mockReviews.length }})</h2>
          </div>
          <div class="p-6 space-y-6">
            <div v-if="mockReviews.length === 0" class="text-center py-16 text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl mb-3 opacity-40">rate_review</span>
              <p class="font-bold text-lg">Chưa có đánh giá nào</p>
            </div>

            <div v-else v-for="rev in mockReviews" :key="rev.id"
              class="border-b border-outline-variant last:border-0 pb-6 last:pb-0">
              <div class="flex items-start gap-4">
                <img :src="rev.reviewer_avatar"
                  class="w-10 h-10 rounded-full object-cover shrink-0 border border-outline-variant" />
                <div class="flex-1 text-left">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <h4 class="font-bold text-on-surface text-sm sm:text-base">{{ rev.reviewer_name }}</h4>
                    <span class="text-xs text-on-surface-variant">{{ rev.date }}</span>
                  </div>

                  <div class="flex items-center gap-1 text-amber-500 mb-2">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm font-variation-fill">
                      {{ star <= rev.rating ? 'star' : 'star_outline' }} </span>
                        <span class="text-xs text-on-surface-variant ml-2 font-medium">Mua hàng:
                          <span class="text-primary font-bold">{{ rev.post_title }}</span>
                        </span>
                  </div>

                  <p class="text-on-surface text-sm leading-relaxed">
                    {{ rev.comment }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Modal Hiển thị Danh sách Người theo dõi -->
    <div v-if="showFollowersModal"
      class="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="showFollowersModal = false">
      <div
        class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col max-h-[80vh] animate-fadeIn">
        <!-- Header -->
        <div
          class="px-5 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/30">
          <h3 class="font-extrabold text-lg text-on-surface">Người theo dõi ({{ mockFollowers.length }})</h3>
          <button @click="showFollowersModal = false"
            class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-full hover:bg-error/10">
            <span class="material-symbols-outlined block">close</span>
          </button>
        </div>

        <!-- List -->
        <div class="p-3 overflow-y-auto grow custom-scrollbar">
          <div v-if="mockFollowers.length === 0" class="text-center py-10 text-on-surface-variant">
            Người dùng này chưa có ai theo dõi.
          </div>
          <div v-else class="space-y-2">
            <div v-for="follower in mockFollowers" :key="follower.id"
              class="flex items-center gap-3 p-3 rounded-xl hover:bg-surface-container transition-colors border border-transparent hover:border-outline-variant cursor-pointer">
              <img :src="follower.avatar"
                class="w-12 h-12 rounded-full object-cover border border-outline-variant shrink-0" />
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm text-on-surface truncate" :title="follower.name">{{ follower.name }}</h4>
                <p class="text-xs text-on-surface-variant mt-0.5">{{ follower.mutual_friends }} bạn chung</p>
              </div>
              <button
                class="px-3 py-1.5 text-[11px] font-bold rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container-high transition-colors shrink-0">
                Xem trang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const seller = ref({});
const isFollowing = ref(false);
const posts = ref([]);
const showFollowersModal = ref(false);
const postFilter = ref('active');
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
  if (postFilter.value === 'active') return posts.value;
  return []; // Mock data rỗng cho phần Đã bán (đợi API hoàn thiện lấy bài viết đã bán sau)
});

// Mock dữ liệu danh sách người theo dõi
const mockFollowers = ref([
  {
    id: 1,
    name: 'Phạm Thu Hương',
    avatar: 'https://ui-avatars.com/api/?name=Pham+Thu+Huong&background=f43f5e&color=fff',
    mutual_friends: 2
  },
  {
    id: 2,
    name: 'Lê Minh Tuấn',
    avatar: 'https://ui-avatars.com/api/?name=Le+Minh+Tuan&background=3b82f6&color=fff',
    mutual_friends: 0
  },
  {
    id: 3,
    name: 'Trần Quỳnh Như',
    avatar: 'https://ui-avatars.com/api/?name=Tran+Quynh+Nhu&background=10b981&color=fff',
    mutual_friends: 5
  },
  {
    id: 4,
    name: 'Hoàng Quốc Việt',
    avatar: 'https://ui-avatars.com/api/?name=Hoang+Quoc+Viet&background=f59e0b&color=fff',
    mutual_friends: 1
  }
]);

const toggleFollow = () => {
  isFollowing.value = !isFollowing.value;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const goToPost = (slug) => {
  router.push(`/post/${slug}`);
};

// Gọi API dữ liệu tin đăng và thông tin người bán thực tế
const fetchSellerProfile = async () => {
  const sellerId = route.params.id;
  try {
    const response = await axios.get(`/api/seller/${sellerId}`);
    if (response.data.success) {
      seller.value = response.data.data.user;

      posts.value = response.data.data.posts.map(post => {
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
          slug: post.slug
        };
      });
    }
  } catch (error) {
    console.error('Lỗi khi tải thông tin người bán:', error);
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

// Mock đánh giá chất lượng người bán từ khách hàng (hệ thống chưa có bảng review)
const mockReviews = ref([
  {
    id: 1,
    reviewer_name: 'Nguyễn Văn Hùng',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Nguyen+Van+Hung&background=3b82f6&color=fff',
    rating: 5,
    date: '12/05/2026',
    post_title: 'iPhone 13 Pro Max 256GB Gold',
    comment: 'Điện thoại dùng rất tốt, pin còn 92% đúng như mô tả. Người bán uy tín hỗ trợ ship nhanh, đóng gói rất cẩn thận 3 lớp chống sốc.'
  },
  {
    id: 2,
    reviewer_name: 'Trần Thị Lan',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Tran+Thi+Lan&background=10b981&color=fff',
    rating: 5,
    date: '08/05/2026',
    post_title: 'MacBook Air M1 8GB/256GB Gray',
    comment: 'Máy dùng siêu mượt, màn hình đẹp không vết trầy. Giao dịch nhanh chóng tại nhà, anh chủ siêu nhiệt tình test máy từ A-Z giúp mình.'
  }
]);

onMounted(() => {
  // Lấy dữ liệu công khai từ backend thay vì truyền qua history state
  fetchSellerProfile();
  fetchFavorites();

  window.scrollTo(0, 0);
});

// Xử lý yêu thích (favorite)
const toggleFavorite = async (postId) => {
  if (!authStore.isLoggedIn) {
    alert('Vui lòng đăng nhập để sử dụng tính năng này');
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
