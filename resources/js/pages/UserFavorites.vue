<template>
  <div class="user-favorites-page bg-slate-50 min-h-screen pb-12">
    <div class="max-w-6xl mx-auto px-4 pt-8">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900">Tin đăng yêu thích</h1>
          <p class="text-slate-500 mt-1">Danh sách các sản phẩm bạn đã lưu để xem lại sau.</p>
        </div>
        <router-link to="/marketplace"
          class="inline-flex items-center gap-2 bg-white text-slate-700 border border-slate-200 px-6 py-3 rounded-xl font-bold hover:bg-slate-50 transition-all shadow-sm w-fit">
          <span class="material-symbols-outlined">explore</span>
          Khám phá thêm
        </router-link>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-slate-500 font-medium">Đang tải tin đăng yêu thích...</p>
      </div>

      <!-- Posts List -->
      <div v-else-if="posts.length > 0" class="space-y-6">
        <div class="flex flex-col border-t border-slate-200 mt-4 pt-2">
          <div v-for="post in posts" :key="post.id"
            class="group py-4 px-3 -mx-3 border-b border-slate-200 transition-all duration-300 ease-out flex flex-row gap-4 relative post-card cursor-pointer hover:bg-white hover:shadow-xl hover:shadow-slate-200/60 hover:-translate-y-1 hover:border-transparent hover:z-10 rounded-2xl">

            <router-link :to="`/post/${post.slug}`"
              class="relative w-[120px] h-[120px] shrink-0 rounded-lg overflow-hidden block bg-slate-100">
              <img :src="getPrimaryImage(post)" :alt="post.title"
                class="w-full h-full object-cover transition-transform duration-500 ease-out">

              <span v-if="post.images && post.images.length > 0"
                class="absolute top-1 right-1 bg-black/70 text-white text-[12px] font-medium px-1.5 py-[2px] rounded-sm shadow-sm flex items-center gap-[3px] z-10 leading-none">
                <span class="material-symbols-outlined" style="font-size: 18px; line-height: 1;">photo_library</span>
                <span>{{ post.images.length }}</span>
              </span>

              <span v-if="post.status === 'sold'"
                class="absolute top-1 left-1 bg-black/60 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm backdrop-blur-sm z-10">
                Đã bán
              </span>
            </router-link>

            <div class="flex flex-col grow justify-between">
              <div>
                <router-link :to="`/post/${post.slug}`"
                  class="text-[16px] text-slate-800 line-clamp-2 transition-colors group-hover:text-primary font-medium">
                  {{ post.title }}
                </router-link>
                <p class="text-[#d0021b] font-bold text-[16px] mt-1">{{ formatPrice(post.price) }} đ</p>
              </div>

              <div class="flex items-end justify-between mt-2">
                <div class="flex flex-wrap items-center text-[13px] text-[#9b9b9b] gap-1.5">
                  <div class="flex items-center gap-1.5">
                    <img
                      :src="post.user?.avatar || 'https://ui-avatars.com/api/?name=' + (post.user?.name || 'User') + '&background=random'"
                      class="w-5 h-5 rounded-full object-cover">
                    <span>{{ post.user?.name || 'Cá Nhân' }}</span>
                  </div>
                  <span>&middot;</span>
                  <span>{{ formatTime(post.created_at) }}</span>
                  <span>&middot;</span>
                  <span>{{ post.ward_name ? post.ward_name + ', ' : '' }}{{ post.province_name }}</span>
                </div>

                <div class="flex items-center gap-4 shrink-0 ml-4 relative z-20">
                  <button
                    class="cursor-pointer px-5 py-1.5 rounded-full border border-[#00a859] text-[#00a859] text-[14px] hover:bg-[#00a859]/10 transition-colors">
                    Chat
                  </button>
                  <button @click.prevent="removeFavorite(post.id)" title="Bỏ yêu thích"
                    class="cursor-pointer text-[#d0021b] hover:scale-125 transition-transform flex items-center justify-center p-1">
                    <span class="material-symbols-outlined text-[24px] font-variation-fill">favorite</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center my-6 gap-2">
          <button :disabled="pagination.current_page === 1" @click="fetchFavorites(pagination.current_page - 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>

          <template v-for="(page, index) in visiblePages" :key="index">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else @click="fetchFavorites(page)"
              :class="['w-10 h-10 rounded-lg font-bold transition-all border cursor-pointer',
                pagination.current_page === page ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
              {{ page }}
            </button>
          </template>

          <button :disabled="pagination.current_page === pagination.last_page"
            @click="fetchFavorites(pagination.current_page + 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else
        class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 relative">
          <span class="material-symbols-outlined text-slate-300 text-[48px]">favorite</span>
          <span class="material-symbols-outlined text-slate-400 text-[24px] absolute bottom-2 right-2">search</span>
        </div>
        <h2 class="text-xl font-bold text-slate-800">Chưa có tin đăng yêu thích nào</h2>
        <p class="text-slate-500 mt-2 mb-8 text-center max-w-md">Bạn chưa lưu bất kỳ sản phẩm nào. Hãy khám phá và lưu
          lại những món đồ bạn thích nhé.</p>
        <router-link to="/"
          class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:scale-105 transition-all shadow-xl shadow-primary/20">
          Khám phá ngay
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const posts = ref([]);
const loading = ref(true);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const left = current - delta;
  const right = current + delta + 1;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= last; i++) {
    if (i == 1 || i == last || i >= left && i < right) {
      range.push(i);
    }
  }

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  }

  return rangeWithDots;
});

const fetchFavorites = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/user/favorites', {
      params: { page }
    });

    if (response.data && response.data.success) {
      const result = response.data.data;
      posts.value = result.data || [];
      pagination.value = {
        current_page: result.current_page || 1,
        last_page: result.last_page || 1,
        total: result.total || 0
      };
    }
  } catch (error) {
    console.error('Lỗi khi tải tin đăng yêu thích:', error);
  } finally {
    loading.value = false;
  }
};

const removeFavorite = async (postId) => {
  if (!confirm('Bạn muốn bỏ yêu thích tin đăng này?')) return;

  try {
    const response = await axios.post(`/api/posts/${postId}/favorite`);
    if (response.data.success) {
      // Remove from the current list visually without reloading everything
      posts.value = posts.value.filter(post => post.id !== postId);
      pagination.value.total -= 1;

      // If the page is empty after removal and we have previous pages, go back one page
      if (posts.value.length === 0 && pagination.value.current_page > 1) {
        fetchFavorites(pagination.value.current_page - 1);
      } else if (posts.value.length < 5 && pagination.value.current_page < pagination.value.last_page) {
        // Option: refill the page if there are more items, or just leave it until they change page
        fetchFavorites(pagination.value.current_page);
      }
    }
  } catch (error) {
    console.error('Lỗi khi bỏ yêu thích:', error);
    alert('Có lỗi xảy ra, vui lòng thử lại sau.');
  }
};

const getPrimaryImage = (post) => {
  if (!post.images || post.images.length === 0) return '/images/no-image.png';
  const primary = post.images.find(img => img.is_primary);
  return primary ? primary.image_path : post.images[0].image_path;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
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

onMounted(() => {
  fetchFavorites();
});
</script>

<style scoped>
.post-card {
  animation: slideUp 0.4s ease-out backwards;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.post-card:nth-child(1) {
  animation-delay: 0.05s;
}

.post-card:nth-child(2) {
  animation-delay: 0.1s;
}

.post-card:nth-child(3) {
  animation-delay: 0.15s;
}

.post-card:nth-child(4) {
  animation-delay: 0.2s;
}

.post-card:nth-child(5) {
  animation-delay: 0.25s;
}

.post-card:nth-child(6) {
  animation-delay: 0.3s;
}

.post-card:nth-child(7) {
  animation-delay: 0.35s;
}

.post-card:nth-child(8) {
  animation-delay: 0.4s;
}
</style>
