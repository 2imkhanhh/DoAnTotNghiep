<template>
  <SellerLayout title="Quản lý đơn hàng">
    <div class="orders-page max-w-6xl mx-auto py-8 px-4">

      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 px-2">
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900">Quản lý đơn hàng</h1>
          <p class="text-slate-500 mt-1 font-medium">Xử lý và theo dõi trạng thái các đơn hàng từ khách hàng của bạn.
          </p>
        </div>
      </div>

      <!-- Status Tabs -->
      <div
        class="bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200 flex flex-nowrap overflow-x-auto mb-8 no-scrollbar">
        <button v-for="tab in statusTabs" :key="tab.value" @click="setTab(tab.value)" :class="['flex-1 min-w-[120px] py-3 px-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer',
          currentTab === tab.value ? 'bg-blue-500 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50']">
          <span class="material-symbols-outlined text-[20px]">{{ tab.icon }}</span>
          <span>
            {{ tab.label }}
            <span v-if="tab.count !== null" class="ml-1 text-[14px]">({{ tab.count }})</span>
          </span>
        </button>
      </div>

      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <div v-else-if="orders.length > 0" class="space-y-6">
        <div v-for="(tx, index) in orders" :key="tx.id"
          class="order-card bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 group"
          :style="{ animationDelay: `${index * 0.05}s` }">

          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-slate-50/80 backdrop-blur-sm">
            <div class="flex items-center gap-3">
              <div class="relative">
                <img :src="getUserAvatar(tx.buyer)"
                  class="w-11 h-11 rounded-full object-cover border-2 border-white shadow-sm">
              </div>
              <div>
                <p class="font-bold text-slate-800 text-[15px] group-hover:text-primary transition-colors">{{
                  tx.buyer?.name || tx.shipping_name || 'Khách hàng' }}</p>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mt-0.5">
                  <span class="material-symbols-outlined text-[14px]">schedule</span>
                  {{ formatDate(tx.created_at) }}
                </div>
              </div>
            </div>
            <div
              :class="['px-4 py-1.5 rounded-full text-xs font-bold border flex items-center gap-1.5 shadow-sm', getStatusBadgeClass(tx.status)]">
              <span class="material-symbols-outlined text-[16px]">{{ getStatusIcon(tx.status) }}</span>
              {{ getStatusText(tx.status) }}
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-5 flex flex-col md:flex-row gap-6">
            <!-- Product Image -->
            <div
              class="relative w-full md:w-32 h-32 shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-100 shadow-inner">
              <img :src="getPrimaryImage(tx.post)" class="w-full h-full object-cover transition-transform duration-500">
            </div>

            <div class="flex-1 flex flex-col justify-between">
              <div>
                <router-link :to="`/post/${tx.post?.slug}`"
                  class="font-bold text-lg text-slate-800 hover:text-primary transition-colors block mb-1 line-clamp-2 leading-tight">
                  {{ tx.post?.title || 'Sản phẩm không xác định' }}
                </router-link>
                <p class="text-error font-black text-xl mb-4">{{ formatPrice(tx.post?.price) }} <span
                    class="text-sm font-bold text-slate-400">VNĐ</span></p>
              </div>

              <!-- Shipping Details -->
              <div
                class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-100 text-sm text-slate-700 relative overflow-hidden group-hover:bg-slate-50 transition-colors">
                <div class="absolute top-0 left-0 w-1 h-full bg-slate-300 group-hover:bg-primary transition-colors">
                </div>
                <div class="flex items-start gap-2 mb-2">
                  <span class="material-symbols-outlined text-slate-400 text-[18px]">local_shipping</span>
                  <p class="font-semibold text-slate-800">Thông tin giao hàng:</p>
                </div>
                <div class="pl-6 space-y-1">
                  <p><span class="text-slate-500">Người nhận:</span> <span class="font-medium text-slate-800">{{
                    tx.shipping_name }}</span> - <span class="font-medium text-primary">{{ tx.shipping_phone }}</span>
                  </p>
                  <p class="text-slate-600 line-clamp-1"><span class="text-slate-500">Địa chỉ:</span> {{
                    tx.shipping_address }}, {{ getLocationString(tx) }}</p>
                  <p v-if="tx.shipping_note"
                    class="text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md text-xs font-medium inline-block mt-1.5 border border-amber-100">
                    <span class="font-bold">Ghi chú:</span> {{ tx.shipping_note }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="flex md:flex-col justify-center gap-2.5 shrink-0 md:w-36 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
              <button v-if="tx.status === 'pending'" @click="acceptOrder(tx.id)"
                class="action-btn w-full py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 transition-all flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Duyệt đơn
              </button>
              <button v-if="tx.status === 'pending'" @click="cancelOrder(tx.id)"
                class="action-btn w-full py-2.5 bg-red-50 text-red-600 rounded-xl font-bold text-sm hover:bg-red-600 hover:text-white transition-all border border-red-200 hover:border-red-600 flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">cancel</span> Từ chối
              </button>

              <button v-if="tx.status === 'confirmed'" @click="startShipping(tx.id)"
                class="action-btn w-full py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-600/30 transition-all flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">local_shipping</span> Giao hàng
              </button>
              <button v-if="tx.status === 'confirmed'" @click="cancelOrder(tx.id)"
                class="action-btn w-full py-2.5 bg-red-50 text-red-600 rounded-xl font-bold text-sm hover:bg-red-600 hover:text-white transition-all border border-red-200 hover:border-red-600 flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">cancel</span> Hủy đơn
              </button>

              <button v-if="tx.status === 'shipping'" @click="deliverOrder(tx.id)"
                class="action-btn w-full py-2.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/30 transition-all flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Đã giao
              </button>

              <div v-if="tx.status === 'delivered'"
                class="w-full py-2.5 bg-green-50 text-green-700 rounded-xl font-bold text-sm border border-green-200 flex items-center justify-center gap-1.5 select-none">
                <span class="material-symbols-outlined text-[18px]">check_circle</span> Hoàn thành
              </div>
              <div v-if="tx.status === 'cancelled' || tx.status === 'rejected'"
                class="w-full py-2.5 bg-slate-100 text-slate-500 rounded-xl font-bold text-sm border border-slate-200 flex items-center justify-center gap-1.5 select-none">
                <span class="material-symbols-outlined text-[18px]">block</span> Đã hủy
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="flex justify-center my-6 gap-2">
          <button :disabled="pagination.current_page === 1" @click="fetchOrders(pagination.current_page - 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>

          <template v-for="(page, index) in visiblePages" :key="index">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else @click="fetchOrders(page)"
              :class="['w-10 h-10 rounded-lg font-bold transition-all border cursor-pointer',
                pagination.current_page === page ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
              {{ page }}
            </button>
          </template>

          <button :disabled="pagination.current_page === pagination.last_page"
            @click="fetchOrders(pagination.current_page + 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-24 bg-white rounded-xl border border-slate-200">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
          <span class="material-symbols-outlined text-slate-300 text-[40px]">receipt_long</span>
        </div>
        <h2 class="text-lg font-bold text-slate-800">Chưa có đơn hàng nào</h2>
        <p class="text-slate-500 mt-1">Không tìm thấy giao dịch nào ở trạng thái này.</p>
      </div>
    </div>
  </SellerLayout>
</template>

<script setup>
import { toast, confirmDialog } from '../../utils/alert';

import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import SellerLayout from '../../components/seller/SellerLayout.vue';

const loading = ref(true);
const orders = ref([]);
const currentTab = ref('');
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const statusTabs = ref([
  { label: 'Tất cả', value: '', icon: 'list', count: null },
  { label: 'Chờ xác nhận', value: 'pending', icon: 'pending_actions', count: null },
  { label: 'Đã xác nhận', value: 'confirmed', icon: 'thumb_up', count: null },
  { label: 'Đang giao', value: 'shipping', icon: 'local_shipping', count: null },
  { label: 'Đã giao', value: 'delivered', icon: 'check_circle', count: null },
  { label: 'Đã hủy/Từ chối', value: 'cancelled', icon: 'cancel', count: null }
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

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get('/api/seller/orders', {
      headers: { 'Authorization': `Bearer ${token}` },
      params: {
        page,
        status: currentTab.value === 'cancelled' ? 'cancelled,rejected' : currentTab.value
      }
    });

    if (response.data && response.data.success) {
      orders.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        total: response.data.data.total
      };
      
      const counts = response.data.counts;
      if (counts) {
        statusTabs.value[0].count = counts.all;
        statusTabs.value[1].count = counts.pending;
        statusTabs.value[2].count = counts.confirmed;
        statusTabs.value[3].count = counts.shipping;
        statusTabs.value[4].count = counts.delivered;
        statusTabs.value[5].count = counts.cancelled;
      }
    }
  } catch (error) {
    console.error('Failed to load orders', error);
  } finally {
    loading.value = false;
  }
};

const setTab = (status) => {
  currentTab.value = status;
  fetchOrders(1);
};

const acceptOrder = async (id) => {
  if (!await confirmDialog('Bạn có chắc chắn muốn duyệt đơn hàng này và bắt đầu giao hàng?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/accept`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    toast('Đã duyệt đơn hàng!', 'info');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  }
};

const startShipping = async (id) => {
  if (!await confirmDialog('Bạn xác nhận bắt đầu giao đơn hàng này cho đơn vị vận chuyển?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/ship`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    toast('Đã chuyển sang Đang giao hàng!', 'info');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  }
};

const cancelOrder = async (id) => {
  if (!await confirmDialog('Bạn có chắc chắn muốn hủy/từ chối đơn hàng này?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/cancel`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    toast('Đã hủy/từ chối đơn hàng!', 'info');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  }
};

const deliverOrder = async (id) => {
  if (!await confirmDialog('Xác nhận đã giao hàng thành công? Sản phẩm sẽ chuyển sang trạng thái Đã Bán.')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/deliver`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    toast('Xác nhận giao hàng thành công!', 'success');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  }
};

const getPrimaryImage = (post) => {
  if (!post || !post.images || post.images.length === 0) return '/images/no-image.png';
  const primary = post.images.find(img => img.is_primary);
  return primary ? primary.image_path : post.images[0].image_path;
};

const getUserAvatar = (user) => {
  if (user?.avatar) {
    return user.avatar.startsWith('http') ? user.avatar : user.avatar;
  }
  return `https://ui-avatars.com/api/?name=${user?.name || 'User'}&background=random&color=fff`;
};

const getStatusText = (status) => {
  const texts = { 'pending': 'Chờ xác nhận', 'confirmed': 'Đã xác nhận', 'shipping': 'Đang giao hàng', 'delivered': 'Đã giao hàng', 'rejected': 'Từ chối', 'cancelled': 'Đã hủy' };
  return texts[status] || 'Không rõ';
};

const getStatusIcon = (status) => {
  const icons = {
    'pending': 'schedule',
    'confirmed': 'thumb_up',
    'shipping': 'local_shipping',
    'delivered': 'check_circle',
    'rejected': 'block',
    'cancelled': 'cancel'
  };
  return icons[status] || 'info';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    'pending': 'bg-amber-100 text-amber-700 border-amber-200',
    'confirmed': 'bg-indigo-100 text-indigo-700 border-indigo-200',
    'shipping': 'bg-blue-100 text-blue-700 border-blue-200',
    'delivered': 'bg-green-100 text-green-700 border-green-200',
    'rejected': 'bg-slate-100 text-slate-600 border-slate-200',
    'cancelled': 'bg-red-100 text-red-600 border-red-200'
  };
  return classes[status] || 'bg-slate-50 text-slate-600';
};

const formatPrice = (price) => {
  if (!price) return '0';
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('vi-VN', {
    hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit', year: 'numeric'
  });
};

const getLocationString = (tx) => {
  // If we had ward/province models we'd map the IDs to names, but for now we might not have names saved in tx.
  // Fortunately the post usually has ward_name / province_name.
  if (tx.post && tx.post.province_name) {
    return `${tx.post.ward_name ? tx.post.ward_name + ', ' : ''}${tx.post.province_name}`;
  }
  return '';
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
.action-btn {
  cursor: pointer !important;
}

.order-card {
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

.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
