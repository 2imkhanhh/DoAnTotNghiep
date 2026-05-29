<template>
  <SellerLayout title="Quản lý đơn hàng">
    <div class="orders-page max-w-6xl mx-auto py-8">
      
      <!-- Status Tabs -->
      <div class="bg-white p-1 rounded-2xl shadow-sm border border-slate-200 flex flex-nowrap overflow-x-auto mb-8 no-scrollbar">
        <button v-for="tab in statusTabs" :key="tab.value" @click="setTab(tab.value)" :class="['flex-1 min-w-[120px] py-3 px-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer',
          currentTab === tab.value ? 'bg-blue-500 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50']">
          <span class="material-symbols-outlined text-[20px]">{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>

      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <div v-else-if="orders.length > 0" class="space-y-6">
        <div v-for="tx in orders" :key="tx.id" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          
          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3">
              <img :src="getUserAvatar(tx.buyer)" class="w-10 h-10 rounded-full object-cover">
              <div>
                <p class="font-bold text-slate-800">{{ tx.buyer?.name || tx.shipping_name || 'Khách hàng' }}</p>
                <p class="text-xs text-slate-500">{{ formatDate(tx.created_at) }}</p>
              </div>
            </div>
            <div :class="['px-3 py-1 rounded-full text-xs font-bold border', getStatusBadgeClass(tx.status)]">
              {{ getStatusText(tx.status) }}
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-4 flex flex-col md:flex-row gap-4">
            <img :src="getPrimaryImage(tx.post)" class="w-full md:w-24 h-24 object-cover rounded-lg bg-slate-100 shrink-0">
            <div class="flex-1">
              <router-link :to="`/post/${tx.post?.slug}`" class="font-bold text-lg text-slate-800 hover:text-blue-600 transition block mb-1">
                {{ tx.post?.title || 'Sản phẩm không xác định' }}
              </router-link>
              <p class="text-error font-bold text-lg mb-2">{{ formatPrice(tx.post?.price) }}đ</p>
              
              <!-- Shipping Details -->
              <div class="mt-3 bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm text-slate-700">
                <p><strong>Người nhận:</strong> {{ tx.shipping_name }} - {{ tx.shipping_phone }}</p>
                <p class="mt-1 flex items-start gap-1">
                  <span class="material-symbols-outlined text-[16px] text-slate-500 mt-0.5">location_on</span>
                  <span>{{ tx.shipping_address }}, {{ getLocationString(tx) }}</span>
                </p>
                <p v-if="tx.shipping_note" class="mt-1 text-slate-500 italic"><strong>Ghi chú:</strong> {{ tx.shipping_note }}</p>
              </div>
            </div>
            
            <!-- Actions -->
            <div class="flex md:flex-col justify-end gap-2 shrink-0 md:w-32 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-4">
              <button v-if="tx.status === 'pending'" @click="acceptOrder(tx.id)" class="w-full py-2 bg-blue-600 text-white rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                Duyệt đơn
              </button>
              <button v-if="tx.status === 'pending'" @click="cancelOrder(tx.id)" class="w-full py-2 bg-red-50 text-red-600 rounded-lg font-bold text-sm hover:bg-red-100 transition border border-red-200">
                Từ chối
              </button>
              
              <button v-if="tx.status === 'confirmed'" @click="startShipping(tx.id)" class="w-full py-2 bg-blue-600 text-white rounded-lg font-bold text-sm hover:bg-blue-700 transition">
                Giao hàng
              </button>
              <button v-if="tx.status === 'confirmed'" @click="cancelOrder(tx.id)" class="w-full py-2 bg-red-50 text-red-600 rounded-lg font-bold text-sm hover:bg-red-100 transition border border-red-200">
                Hủy đơn
              </button>

              <button v-if="tx.status === 'shipping'" @click="deliverOrder(tx.id)" class="w-full py-2 bg-green-600 text-white rounded-lg font-bold text-sm hover:bg-green-700 transition">
                Xác nhận đã giao
              </button>
              
              <button v-if="tx.status === 'delivered'" disabled class="w-full py-2 bg-green-50 text-green-600 rounded-lg font-bold text-sm border border-green-200 cursor-not-allowed">
                Đã hoàn thành
              </button>
              <button v-if="tx.status === 'cancelled' || tx.status === 'rejected'" disabled class="w-full py-2 bg-slate-100 text-slate-500 rounded-lg font-bold text-sm border border-slate-200 cursor-not-allowed">
                Đã hủy/từ chối
              </button>
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

          <button :disabled="pagination.current_page === pagination.last_page" @click="fetchOrders(pagination.current_page + 1)" 
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

const statusTabs = [
  { label: 'Tất cả', value: '', icon: 'list' },
  { label: 'Chờ xác nhận', value: 'pending', icon: 'pending_actions' },
  { label: 'Đã xác nhận', value: 'confirmed', icon: 'thumb_up' },
  { label: 'Đang giao', value: 'shipping', icon: 'local_shipping' },
  { label: 'Đã giao', value: 'delivered', icon: 'check_circle' },
  { label: 'Đã hủy/Từ chối', value: 'cancelled', icon: 'cancel' }
];

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
  if(!confirm('Bạn có chắc chắn muốn duyệt đơn hàng này và bắt đầu giao hàng?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/accept`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Đã duyệt đơn hàng!');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    alert(error.response?.data?.message || 'Có lỗi xảy ra');
  }
};

const startShipping = async (id) => {
  if(!confirm('Bạn xác nhận bắt đầu giao đơn hàng này cho đơn vị vận chuyển?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/ship`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Đã chuyển sang Đang giao hàng!');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    alert(error.response?.data?.message || 'Có lỗi xảy ra');
  }
};

const cancelOrder = async (id) => {
  if(!confirm('Bạn có chắc chắn muốn hủy/từ chối đơn hàng này?')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/cancel`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Đã hủy/từ chối đơn hàng!');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    alert(error.response?.data?.message || 'Có lỗi xảy ra');
  }
};

const deliverOrder = async (id) => {
  if(!confirm('Xác nhận đã giao hàng thành công? Sản phẩm sẽ chuyển sang trạng thái Đã Bán.')) return;
  try {
    const token = localStorage.getItem('access_token');
    await axios.put(`/api/orders/${id}/deliver`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    alert('Xác nhận giao hàng thành công!');
    fetchOrders(pagination.value.current_page);
  } catch (error) {
    alert(error.response?.data?.message || 'Có lỗi xảy ra');
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
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
