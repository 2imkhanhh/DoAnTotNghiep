<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="p-6 sm:p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
        <div>
          <h1 class="text-2xl font-bold text-on-surface">Đơn mua</h1>
          <p class="text-on-surface-variant">Quản lý và theo dõi các đơn hàng bạn đã mua</p>
        </div>
        <!-- Filter Status -->
        <div class="relative min-w-[170px] custom-dropdown">
          <div @click="isDropdownOpen = !isDropdownOpen" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold cursor-pointer flex items-center justify-between hover:border-primary transition-colors shadow-sm select-none">
            <span class="text-on-surface">{{ selectedFilterLabel }}</span>
            <span class="material-symbols-outlined text-on-surface-variant text-[20px] transition-transform duration-300" :class="{ 'rotate-180': isDropdownOpen }">expand_more</span>
          </div>
          
          <div v-if="isDropdownOpen" class="absolute z-20 w-full mt-2 bg-white border border-slate-100 rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
            <div 
              v-for="option in filterOptions" 
              :key="option.value"
              @click="selectFilter(option.value)"
              class="px-4 py-3 text-sm font-medium hover:bg-slate-50 cursor-pointer transition-colors flex items-center"
              :class="{ 'text-primary bg-primary/5 font-bold border-l-2 border-primary': buyerOrderFilter === option.value, 'border-l-2 border-transparent text-on-surface-variant': buyerOrderFilter !== option.value }"
            >
              {{ option.label }}
            </div>
          </div>
        </div>
      </div>

      <div class="p-6 sm:p-8 space-y-6">
        <div v-if="buyerOrdersLoading" class="text-center py-12">
          <span class="material-symbols-outlined text-4xl animate-spin text-primary">progress_activity</span>
        </div>
        
        <div v-else-if="buyerOrders.length === 0" class="text-center py-12 text-on-surface-variant">
          <span class="material-symbols-outlined text-4xl mb-2 opacity-40">shopping_bag</span>
          <p class="font-medium">Chưa có đơn hàng nào.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="order in buyerOrders" :key="order.id" @click="viewOrderDetails(order)" class="border border-slate-200 rounded-2xl p-5 sm:p-7 bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <div class="flex justify-between items-center mb-5 pb-4 border-b border-slate-100/80">
              <div class="flex items-center gap-2">
                <img :src="order.seller?.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(order.seller?.name || 'Shop') + '&background=random'" class="w-6 h-6 rounded-full object-cover">
                <span class="font-bold text-sm text-on-surface">{{ order.seller?.name }}</span>
              </div>
              <span class="text-xs font-bold px-3 py-1 rounded-full" :class="{
                'bg-amber-100 text-amber-700': order.status === 'pending',
                'bg-blue-100 text-blue-700': order.status === 'shipping',
                'bg-green-100 text-green-700': order.status === 'delivered',
                'bg-indigo-100 text-indigo-700': order.status === 'confirmed',
                'bg-surface-container text-on-surface-variant': order.status === 'cancelled' || order.status === 'rejected'
              }">
                {{ order.status === 'pending' ? 'Chờ xác nhận' :
                   order.status === 'confirmed' ? 'Đã xác nhận' :
                   order.status === 'shipping' ? 'Đang giao hàng' :
                   order.status === 'delivered' ? 'Đã giao hàng' :
                   order.status === 'rejected' ? 'Người bán từ chối' : 'Đã hủy' }}
              </span>
            </div>

            <div class="flex gap-4 mb-4">
              <img :src="order.post?.images?.[0]?.image_path || 'https://via.placeholder.com/100'" class="w-20 h-20 rounded-lg object-cover border border-outline-variant shrink-0">
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-on-surface text-sm sm:text-base mb-1 truncate">{{ order.post?.title }}</h4>
                <p class="text-xs text-on-surface-variant mb-2">Ngày đặt: {{ new Date(order.created_at).toLocaleDateString('vi-VN') }}</p>
                <div class="text-error font-extrabold">{{ new Intl.NumberFormat('vi-VN').format(order.total_price || order.post?.price) }}đ</div>
              </div>
            </div>

            <div v-if="order.status === 'pending' || order.status === 'confirmed'" class="flex justify-end pt-5 border-t border-slate-100/80 mt-2">
              <button @click.stop="cancelBuyerOrder(order.id)" class="px-4 py-2 border border-error text-error font-bold rounded-lg hover:bg-error-container transition-colors text-sm cursor-pointer">
                Hủy đơn hàng
              </button>
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="buyerOrdersPagination?.last_page > 1" class="flex justify-center my-6 gap-2">
          <button :disabled="buyerOrdersPagination.current_page === 1" @click="fetchBuyerOrders(buyerOrdersPagination.current_page - 1)" 
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>

          <template v-for="(page, index) in visiblePages" :key="index">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else @click="fetchBuyerOrders(page)"
              :class="['w-10 h-10 rounded-lg font-bold transition-all border cursor-pointer',
                buyerOrdersPagination.current_page === page ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
              {{ page }}
            </button>
          </template>

          <button :disabled="buyerOrdersPagination.current_page === buyerOrdersPagination.last_page" @click="fetchBuyerOrders(buyerOrdersPagination.current_page + 1)" 
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Details Modal -->
  <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeOrderDetails"></div>
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200 relative z-10">
      <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="text-xl font-bold text-on-surface flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">receipt_long</span>
          Chi tiết đơn hàng #{{ selectedOrder.id }}
        </h3>
        <button @click="closeOrderDetails" class="text-on-surface-variant hover:text-error transition-colors p-2 rounded-full cursor-pointer">
          <span class="material-symbols-outlined block">close</span>
        </button>
      </div>
      
      <div class="p-6 overflow-y-auto grow custom-scrollbar space-y-6">
        <!-- Status -->
        <div class="flex justify-between items-center bg-surface-container-lowest p-4 rounded-xl border border-slate-100">
          <span class="font-bold text-on-surface">Trạng thái:</span>
          <span class="text-sm font-bold px-4 py-1.5 rounded-full" :class="{
                'bg-amber-100 text-amber-700': selectedOrder.status === 'pending',
                'bg-blue-100 text-blue-700': selectedOrder.status === 'shipping',
                'bg-green-100 text-green-700': selectedOrder.status === 'delivered',
                'bg-indigo-100 text-indigo-700': selectedOrder.status === 'confirmed',
                'bg-slate-100 text-on-surface-variant': selectedOrder.status === 'cancelled' || selectedOrder.status === 'rejected'
              }">
            {{ selectedOrder.status === 'pending' ? 'Chờ xác nhận' :
               selectedOrder.status === 'confirmed' ? 'Đã xác nhận' :
               selectedOrder.status === 'shipping' ? 'Đang giao hàng' :
               selectedOrder.status === 'delivered' ? 'Đã giao hàng' :
               selectedOrder.status === 'rejected' ? 'Người bán từ chối' : 'Đã hủy' }}
          </span>
        </div>

        <!-- Product Info -->
        <div>
          <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">inventory_2</span>
            Sản phẩm
          </h4>
          <div class="flex gap-4 p-4 border border-slate-100 rounded-xl bg-slate-50/30">
            <img :src="selectedOrder.post?.images?.[0]?.image_path || 'https://via.placeholder.com/100'" class="w-24 h-24 rounded-lg object-cover border border-slate-200 shrink-0">
            <div class="flex-1">
              <router-link :to="`/post/${selectedOrder.post?.slug}`" class="font-bold text-on-surface hover:text-primary transition-colors line-clamp-2 mb-2">{{ selectedOrder.post?.title }}</router-link>
              <div class="text-error font-extrabold text-lg">{{ new Intl.NumberFormat('vi-VN').format(selectedOrder.total_price || selectedOrder.post?.price) }}đ</div>
            </div>
          </div>
        </div>

        <!-- Shipping Info -->
        <div>
          <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">local_shipping</span>
            Thông tin giao hàng
          </h4>
          <div class="bg-slate-50/30 p-5 rounded-xl border border-slate-100 space-y-3 text-sm">
            <div class="flex justify-between border-b border-slate-100 pb-2">
              <span class="text-on-surface-variant">Người nhận:</span>
              <span class="font-bold text-on-surface">{{ selectedOrder.shipping_name }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-100 pb-2">
              <span class="text-on-surface-variant">Số điện thoại:</span>
              <span class="font-medium text-on-surface">{{ selectedOrder.shipping_phone }}</span>
            </div>
            <div class="flex justify-between border-b border-slate-100 pb-2">
              <span class="text-on-surface-variant">Địa chỉ cụ thể:</span>
              <span class="font-medium text-on-surface text-right max-w-[60%]">{{ selectedOrder.shipping_address }}</span>
            </div>
            <div class="flex justify-between" v-if="selectedOrder.shipping_note">
              <span class="text-on-surface-variant">Ghi chú:</span>
              <span class="font-medium text-on-surface text-right max-w-[60%]">{{ selectedOrder.shipping_note }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50">
        <button @click="closeOrderDetails" class="px-6 py-2.5 border border-slate-300 text-on-surface-variant font-bold rounded-xl hover:bg-slate-200 transition-colors cursor-pointer">
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { toast, confirmDialog } from '../utils/alert';

import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const buyerOrders = ref([]);
const buyerOrdersPagination = ref(null);
const buyerOrdersLoading = ref(false);
const buyerOrderFilter = ref('');
const selectedOrder = ref(null);
const isDropdownOpen = ref(false);

const filterOptions = [
  { label: 'Tất cả', value: '' },
  { label: 'Chờ xác nhận', value: 'pending' },
  { label: 'Đang giao', value: 'shipping' },
  { label: 'Đã giao', value: 'delivered' },
  { label: 'Bị từ chối', value: 'rejected' },
  { label: 'Đã hủy', value: 'cancelled' }
];

const visiblePages = computed(() => {
  if (!buyerOrdersPagination.value) return [];
  
  const current = buyerOrdersPagination.value.current_page;
  const last = buyerOrdersPagination.value.last_page;
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

const selectedFilterLabel = computed(() => {
  const option = filterOptions.find(opt => opt.value === buyerOrderFilter.value);
  return option ? option.label : 'Tất cả';
});

const selectFilter = (value) => {
  buyerOrderFilter.value = value;
  isDropdownOpen.value = false;
  fetchBuyerOrders(1);
};

const closeDropdown = (e) => {
  if (!e.target.closest('.custom-dropdown')) {
    isDropdownOpen.value = false;
  }
};

const viewOrderDetails = (order) => {
  selectedOrder.value = order;
};

const closeOrderDetails = () => {
  selectedOrder.value = null;
};

const fetchBuyerOrders = async (page = 1) => {
  buyerOrdersLoading.value = true;
  try {
    const response = await axios.get(`/api/user/orders/bought?page=${page}&status=${buyerOrderFilter.value}`);
    if (response.data.success) {
      buyerOrders.value = response.data.data.data;
      buyerOrdersPagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Lỗi tải đơn hàng:', error);
  } finally {
    buyerOrdersLoading.value = false;
  }
};

const cancelBuyerOrder = async (orderId) => {
  if (!await confirmDialog('Bạn có chắc chắn muốn hủy đơn hàng này?')) return;
  try {
    const res = await axios.put(`/api/orders/${orderId}/cancel`);
    if (res.data.success) {
      toast('Hủy đơn hàng thành công', 'success');
      fetchBuyerOrders(buyerOrdersPagination.value?.current_page || 1);
    }
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  }
};

onMounted(() => {
  if (!authStore.isLoggedIn) {
    router.push('/login');
    return;
  }
  fetchBuyerOrders();
  window.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdown);
});
</script>
