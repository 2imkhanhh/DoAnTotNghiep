<template>
  <SellerLayout title="Quản lý tin đăng">
    <div class="user-posts-page bg-slate-50 min-h-screen pb-12">
      <div class="max-w-6xl mx-auto px-4 pt-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
          <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Quản lý tin đăng</h1>
            <p class="text-slate-500 mt-1">Xem và quản lý tất cả các sản phẩm bạn đã đăng bán.</p>
          </div>
          <router-link to="/seller-center/post/create"
            class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primary-dark transition-all shadow-lg shadow-primary/20 w-fit">
            <span class="material-symbols-outlined">add_circle</span>
            Đăng tin mới
          </router-link>
        </div>

        <!-- Status Tabs -->
        <div
          class="bg-white p-1 rounded-2xl shadow-sm border border-slate-200 flex flex-nowrap overflow-x-auto mb-8 no-scrollbar">
          <button v-for="tab in statusTabs" :key="tab.value" @click="setTab(tab.value)" :class="['flex-1 min-w-[100px] py-3 px-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer',
            currentTab === tab.value ? 'bg-blue-500 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50']">
            <span class="material-symbols-outlined text-[20px]">{{ tab.icon }}</span>
            {{ tab.label }}
            <span v-if="tab.count !== null" class="ml-0.5 text-[14px]">({{ tab.count }})</span>
          </button>
        </div>

        <!-- Posts List -->
        <LoadingState v-if="loading" />

        <div v-else-if="posts.length > 0" class="space-y-4">
          <div v-for="post in posts" :key="post.id"
            class="post-card bg-white rounded-2xl border border-slate-200 p-4 hover:shadow-xl transition-all duration-300 group">
            <div class="flex flex-col sm:flex-row gap-6">
              <!-- Image Thumbnail -->
              <div class="w-full sm:w-48 h-48 sm:h-32 rounded-xl overflow-hidden relative shrink-0 bg-slate-100">
                <img :src="getPrimaryImage(post)" :alt="post.title"
                  class="w-full h-full object-cover transition-transform duration-500">
                <div
                  :class="['absolute top-2 left-2 px-3 py-1 rounded-full text-[10px] font-bold shadow-sm backdrop-blur-md', getStatusBadgeClass(post.status)]">
                  {{ getStatusText(post.status) }}
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 flex flex-col">
                <div class="flex justify-between items-start gap-4">
                  <div class="flex-1">
                    <span class="text-[12px] font-bold text-primary tracking-wider">{{ post.category?.name
                      }}</span>
                    <h3
                      class="text-lg font-bold text-slate-800 line-clamp-1 mb-1 group-hover:text-primary transition-colors">
                      {{ post.title }}</h3>
                    <p class="text-error font-black text-xl">{{ formatPrice(post.price) }}đ</p>
                  </div>

                  <!-- Action Buttons Desktop -->
                  <div class="hidden md:flex items-center gap-2">
                    <button v-if="post.status === 1" @click="markAsSold(post)"
                      class="btn-action bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white" title="Đã bán">
                      <span class="material-symbols-outlined">check_circle</span>
                    </button>
                    <router-link v-if="post.status !== 2" :to="`/seller-center/post/edit/${post.id}`"
                      class="btn-action bg-slate-50 text-slate-600 hover:bg-slate-800 hover:text-white" title="Sửa tin">
                      <span class="material-symbols-outlined">edit</span>
                    </router-link>
                    <button @click="confirmDelete(post)"
                      class="btn-action bg-red-50 text-red-600 hover:bg-red-600 hover:text-white" title="Xóa tin">
                      <span class="material-symbols-outlined">delete</span>
                    </button>
                  </div>
                </div>

                <div class="mt-auto flex items-center gap-4 text-[12px] text-slate-400 font-medium">
                  <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                    {{ formatDate(post.created_at) }}
                  </span>
                </div>

                <!-- Rejection Reason -->
                <div v-if="post.status === 3 && post.reject_reason"
                  class="mt-3 p-3 bg-red-50 border border-red-100 rounded-xl flex items-start gap-2 animate-pulse">
                  <span class="material-symbols-outlined text-red-500 text-[18px]">info</span>
                  <p class="text-[12px] text-red-700 font-medium">
                    <span class="font-bold">Bị từ chối:</span> {{ post.reject_reason }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Mobile Action Buttons -->
            <div class="flex md:hidden items-center gap-2 mt-4 pt-4 border-t border-slate-100">
              <button v-if="post.status === 1" @click="markAsSold(post)"
                class="flex-1 py-2 bg-blue-600 text-white rounded-lg font-bold text-sm flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Đã bán
              </button>
              <router-link v-if="post.status !== 2" :to="`/seller-center/post/edit/${post.id}`"
                class="flex-1 py-2 bg-slate-100 text-slate-700 rounded-lg font-bold text-sm flex items-center justify-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">edit</span> Sửa
              </router-link>
              <button @click="confirmDelete(post)"
                class="flex-1 py-2 bg-red-50 text-red-600 rounded-lg font-bold text-sm flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">delete</span> Xóa
              </button>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="flex justify-center my-6 gap-2">
            <button :disabled="pagination.current_page === 1" @click="fetchPosts(pagination.current_page - 1)"
              class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
              <span class="material-symbols-outlined">chevron_left</span>
            </button>

            <template v-for="(page, index) in visiblePages" :key="index">
              <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
              <button v-else @click="fetchPosts(page)"
                :class="['w-10 h-10 rounded-lg font-bold transition-all border cursor-pointer',
                  pagination.current_page === page ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
                {{ page }}
              </button>
            </template>

            <button :disabled="pagination.current_page === pagination.last_page"
              @click="fetchPosts(pagination.current_page + 1)"
              class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
              <span class="material-symbols-outlined">chevron_right</span>
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else
          class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
          <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-slate-300 text-[48px]">inventory_2</span>
          </div>
          <h2 class="text-xl font-bold text-slate-800">Không tìm thấy tin đăng</h2>
          <p class="text-slate-500 mt-2 mb-8">Bạn hiện tại không có tin đăng nào cho trạng thái này</p>
          <router-link to="/seller-center/post/create"
            class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:scale-105 transition-all shadow-xl shadow-primary/20">
            Đăng tin
          </router-link>
        </div>
      </div>
    </div>
  </SellerLayout>
</template>

<script setup>
import { toast, confirmDialog } from '../../utils/alert';

import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import SellerLayout from '../../components/seller/SellerLayout.vue';
import LoadingState from '../../components/common/LoadingState.vue';

const posts = ref([]);
const loading = ref(true);
const currentTab = ref('');
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const statusTabs = ref([
  { label: 'Tất cả', value: '', icon: 'apps', count: null },
  { label: 'Đang hiển thị', value: 'active', icon: 'check_circle', count: null },
  { label: 'Chờ duyệt', value: 'pending', icon: 'schedule', count: null },
  { label: 'Đã bán', value: 'sold', icon: 'shopping_bag', count: null },
  { label: 'Bị từ chối', value: 'rejected', icon: 'cancel', count: null },
  { label: 'Tạm ẩn', value: 'hidden', icon: 'visibility_off', count: null },
]);

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

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/user/posts', {
      params: {
        page,
        status: currentTab.value
      }
    });

    if (response.data && response.data.success) {
      const result = response.data.data;
      posts.value = result.data || [];
      pagination.value = {
        current_page: result.current_page || 1,
        last_page: result.last_page || 1,
        total: result.total || 0
      };

      // Cập nhật số lượng cho các tab
      const counts = response.data.counts;
      if (counts) {
        statusTabs.value[0].count = counts.all;
        statusTabs.value[1].count = counts.approved;
        statusTabs.value[2].count = counts.pending;
        statusTabs.value[3].count = counts.sold;
        statusTabs.value[4].count = counts.rejected;
        statusTabs.value[5].count = counts.hidden || 0;
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải tin đăng:', error);
  } finally {
    loading.value = false;
  }
};

const setTab = (status) => {
  currentTab.value = status;
  fetchPosts(1);
};

const markAsSold = async (post) => {
  if (!await confirmDialog('Xác nhận sản phẩm này đã được bán thành công?')) return;

  try {
    const response = await axios.put(`/api/posts/${post.id}/status`, { status: 'sold' });
    if (response.data.success) {
      post.status = 'sold';
    }
  } catch (error) {
    toast('Lỗi khi cập nhật trạng thái', 'error');
  }
};

const confirmDelete = async (post) => {
  if (!await confirmDialog('Bạn có chắc chắn muốn xóa tin đăng này vĩnh viễn?')) return;

  try {
    await axios.delete(`/api/posts/${post.id}`);
    fetchPosts(pagination.value.current_page);
  } catch (error) {
    toast('Lỗi khi xóa tin đăng', 'error');
  }
};

const getPrimaryImage = (post) => {
  if (!post.images || post.images.length === 0) return '/images/no-image.png';
  const primary = post.images.find(img => img.is_primary);
  return primary ? primary.image_path : post.images[0].image_path;
};

const getStatusText = (status) => {
  const texts = { 'pending': 'Chờ duyệt', 'active': 'Đang hiển thị', 'sold': 'Đã bán', 'rejected': 'Bị từ chối', 'hidden': 'Tạm ẩn' };
  return texts[status] || 'Không rõ';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    'pending': 'bg-amber-100 text-amber-700 border border-amber-200',
    'active': 'bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]',
    'sold': 'bg-blue-100 text-blue-700 border border-blue-200',
    'rejected': 'bg-red-100 text-red-700 border border-red-200',
    'hidden': 'bg-slate-100 text-slate-700 border border-slate-300'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
.btn-action {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  cursor: pointer !important;
}

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

/* Stagger animation for items */
.post-card:nth-child(1) {
  animation-delay: 0.1s;
}

.post-card:nth-child(2) {
  animation-delay: 0.15s;
}

.post-card:nth-child(3) {
  animation-delay: 0.2s;
}

.post-card:nth-child(4) {
  animation-delay: 0.25s;
}
</style>
