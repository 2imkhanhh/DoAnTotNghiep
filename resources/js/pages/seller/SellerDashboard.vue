<template>
  <SellerLayout title="Tổng quan Bảng điều khiển">
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
    </div>
    <div v-else class="dashboard-content">
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card">
          <div class="stat-icon bg-blue-100 text-blue-600">
            <span class="material-symbols-outlined">inventory_2</span>
          </div>
          <div class="stat-info">
            <h3 class="stat-title">Tổng tin đăng</h3>
            <p class="stat-value">{{ stats.posts.total }}</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-green-100 text-green-600">
            <span class="material-symbols-outlined">check_circle</span>
          </div>
          <div class="stat-info">
            <h3 class="stat-title">Đang hiển thị</h3>
            <p class="stat-value">{{ stats.posts.active }}</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-orange-100 text-orange-600">
            <span class="material-symbols-outlined">receipt_long</span>
          </div>
          <div class="stat-info">
            <h3 class="stat-title">Đơn hàng mới</h3>
            <p class="stat-value">{{ stats.transactions.requested }}</p>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-purple-100 text-purple-600">
            <span class="material-symbols-outlined">payments</span>
          </div>
          <div class="stat-info">
            <h3 class="stat-title">Doanh thu dự kiến</h3>
            <p class="stat-value text-primary font-bold">{{ formatPrice(stats.revenue) }}đ</p>
          </div>
        </div>
      </div>
      
      <!-- Chart/Info Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">pie_chart</span>
            Trạng thái đơn hàng
          </h2>
          <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="font-medium text-slate-600">Đơn yêu cầu mới:</span>
              <span class="font-bold text-primary text-lg">{{ stats.transactions.requested }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="font-medium text-slate-600">Đang giao dịch:</span>
              <span class="font-bold text-blue-600 text-lg">{{ stats.transactions.trading }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="font-medium text-slate-600">Đã hoàn thành:</span>
              <span class="font-bold text-green-600 text-lg">{{ stats.transactions.completed }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
              <span class="font-medium text-slate-600">Tổng cộng:</span>
              <span class="font-bold text-slate-800 text-lg">{{ stats.transactions.total }}</span>
            </div>
          </div>
          <div class="mt-6 text-center">
            <router-link to="/seller-center/transactions" class="text-primary hover:underline font-medium">Xem chi tiết đơn hàng &rarr;</router-link>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center items-center text-center">
          <div class="w-24 h-24 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-5xl">rocket_launch</span>
          </div>
          <h2 class="text-xl font-bold text-slate-800 mb-2">Phát triển gian hàng của bạn</h2>
          <p class="text-slate-500 mb-6">Đăng thêm nhiều sản phẩm chất lượng để thu hút khách hàng và tăng doanh thu.</p>
          <router-link to="/seller-center/post/create" class="bg-primary text-white font-bold py-3 px-6 rounded-xl hover:bg-primary-dark transition shadow-lg shadow-primary/30">
            Đăng tin mới ngay
          </router-link>
        </div>
      </div>
    </div>
  </SellerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SellerLayout from '../../components/seller/SellerLayout.vue';

const loading = ref(true);
const stats = ref({
  posts: { total: 0, active: 0, pending: 0, sold: 0 },
  transactions: { total: 0, completed: 0, trading: 0, requested: 0 },
  revenue: 0
});

onMounted(async () => {
  try {
    const token = localStorage.getItem('access_token');
    const response = await axios.get('/api/seller/dashboard/stats', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    if (response.data && response.data.success) {
      stats.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to load dashboard stats', error);
  } finally {
    loading.value = false;
  }
});

const formatPrice = (price) => {
  if (!price) return '0';
  return new Intl.NumberFormat('vi-VN').format(price);
};
</script>

<style scoped>
.stat-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon .material-symbols-outlined {
  font-size: 2rem;
}

.stat-info {
  flex: 1;
}

.stat-title {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
}
</style>
