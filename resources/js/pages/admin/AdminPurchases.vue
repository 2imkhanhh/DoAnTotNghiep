<template>
  <AdminLayout title="Duyệt Mua Gói">
    <div class="posts-container max-w-7xl mx-auto">

      <!-- Filters Bar -->
      <div class="filters-bar">
        <div class="status-tabs">
          <button @click="setFilter('')" :class="['tab-btn', filter === '' ? 'active' : '']">
            Tất cả
          </button>
          <button @click="setFilter('pending')" :class="['tab-btn', filter === 'pending' ? 'active' : '']">
            Chờ duyệt
          </button>
          <button @click="setFilter('active')" :class="['tab-btn', filter === 'active' ? 'active' : '']">
            Thành công
          </button>
          <button @click="setFilter('rejected')" :class="['tab-btn', filter === 'rejected' ? 'active' : '']">
            Bị từ chối
          </button>
        </div>

        <div class="flex gap-2">
          <button @click="fetchPurchases(1)"
            class="flex items-center gap-1 px-4 py-2 text-sm font-bold text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">refresh</span> Làm mới
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th>STT</th>
              <th>Người dùng</th>
              <th>Gói dịch vụ</th>
              <th style="text-align: right">Số tiền</th>
              <th>Ngày mua</th>
              <th>Trạng thái</th>
              <th style="text-align: center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="py-12">
                <LoadingState />
              </td>
            </tr>
            <tr v-else-if="purchases.length === 0">
              <td colspan="7" class="empty-state">
                <span class="material-symbols-outlined">inbox</span>
                <p>Không có dữ liệu yêu cầu mua gói</p>
              </td>
            </tr>
            <tr v-else v-for="(pur, index) in purchases" :key="pur.id">
              <td class="text-sm font-bold text-slate-700 text-center">{{ getIndex(index) }}</td>
              <td>
                <div class="user-cell">
                  <img
                    :src="pur.user?.avatar || 'https://ui-avatars.com/api/?name=' + pur.user?.name + '&background=random'"
                    alt="">
                  <div>
                    <div style="font-weight: 700; color: #1e293b">{{ pur.user?.name }}</div>
                    <div style="font-size: 0.75rem; color: #64748b">{{ pur.user?.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-[16px]"
                    :class="pur.package?.type === 'vip' ? 'text-amber-500' : 'text-blue-500'">
                    {{ pur.package?.type === 'vip' ? 'workspace_premium' : 'note_add' }}
                  </span>
                  <span class="text-sm font-bold text-slate-700">{{ pur.package?.name }}</span>
                </div>
              </td>
              <td style="text-align: right">
                <span class="price">{{ formatPrice(pur.price_paid) }}đ</span>
              </td>
              <td class="text-secondary">{{ formatDateTime(pur.created_at) }}</td>
              <td>
                <span :class="['status-badge', getStatusClass(pur.status)]">
                  {{ getStatusText(pur.status) }}
                </span>
              </td>
              <td style="text-align: center">
                <div class="action-btns" v-if="pur.status === 'pending'">
                  <button @click="approvePurchase(pur)" :disabled="processingId === pur.id" class="btn-icon approve"
                    title="Duyệt (Đã nhận được tiền)">
                    <span class="material-symbols-outlined">check_circle</span>
                  </button>
                  <button @click="rejectPurchase(pur)" :disabled="processingId === pur.id" class="btn-icon reject"
                    title="Từ chối">
                    <span class="material-symbols-outlined">block</span>
                  </button>
                </div>
                <span v-else class="text-xs text-slate-400 italic">Đã xử lý</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center my-6 gap-2">
          <button :disabled="pagination.current_page === 1" @click="fetchPurchases(pagination.current_page - 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>

          <template v-for="(page, index) in visiblePages" :key="index">
            <span v-if="page === '...'" class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
            <button v-else @click="fetchPurchases(page)"
              :class="['w-10 h-10 rounded-lg font-bold transition-all border cursor-pointer',
                pagination.current_page === page ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">
              {{ page }}
            </button>
          </template>

          <button :disabled="pagination.current_page === pagination.last_page"
            @click="fetchPurchases(pagination.current_page + 1)"
            class="w-10 h-10 rounded-lg font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer disabled:cursor-not-allowed">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast, confirmDialog } from '../../utils/alert';
import AdminLayout from '../../components/admin/AdminLayout.vue';
import LoadingState from '../../components/common/LoadingState.vue';

const purchases = ref([]);
const pagination = ref(null);
const loading = ref(true);
const filter = ref('');
const processingId = ref(null);

const visiblePages = computed(() => {
  if (!pagination.value) return [];
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

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const formatDateTime = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleString('vi-VN');
};

const getStatusClass = (status) => {
  const classes = {
    'pending': 'pending',
    'active': 'approved',
    'rejected': 'rejected'
  };
  return classes[status] || '';
};

const getStatusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ duyệt';
    case 'active': return 'Thành công';
    case 'rejected': return 'Bị từ chối';
    default: return status;
  }
};

const getIndex = (index) => {
  if (!pagination.value) return index + 1;
  return (pagination.value.current_page - 1) * pagination.value.per_page + index + 1;
};

const setFilter = (status) => {
  filter.value = status;
  fetchPurchases(1);
};

const fetchPurchases = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/admin/purchases?page=${page}&status=${filter.value}`);
    if (response.data.success) {
      purchases.value = response.data.data.data;
      pagination.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching purchases', error);
    toast('Lỗi khi tải dữ liệu', 'error');
  } finally {
    loading.value = false;
  }
};

const approvePurchase = (pur) => {
  confirmDialog(
    'Xác nhận đã nhận tiền',
    `Bạn xác nhận đã nhận được ${formatPrice(pur.price_paid)}đ từ ${pur.user?.name} cho yêu cầu #${pur.id}?\nHệ thống sẽ tự động cộng quyền lợi cho tài khoản này.`,
    'Duyệt ngay',
    'Hủy'
  ).then((isConfirmed) => {
    if (isConfirmed) {
      processAction(pur.id, 'approve');
    }
  });
};

const rejectPurchase = (pur) => {
  confirmDialog(
    'Từ chối yêu cầu',
    `Bạn muốn từ chối yêu cầu mua gói #${pur.id} của ${pur.user?.name}?`,
    'Từ chối',
  ).then((isConfirmed) => {
    if (isConfirmed) {
      processAction(pur.id, 'reject');
    }
  });
};

const processAction = async (id, action) => {
  processingId.value = id;
  try {
    const response = await axios.put(`/api/admin/purchases/${id}/${action}`);
    if (response.data.success) {
      toast(response.data.message, 'success');
      // Refresh list
      fetchPurchases(pagination.value?.current_page || 1);
    }
  } catch (error) {
    toast(error.response?.data?.message || 'Có lỗi xảy ra', 'error');
  } finally {
    processingId.value = null;
  }
};

onMounted(() => {
  fetchPurchases();
});
</script>

<style scoped>
.posts-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Filters Bar */
.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.status-tabs {
  display: flex;
  gap: 0.5rem;
  background: #f1f5f9;
  padding: 0.25rem;
  border-radius: 0.75rem;
}

.tab-btn {
  border: none;
  background: none;
  padding: 0.5rem 1rem;
  border-radius: 0.6rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.tab-btn.active {
  background: white;
  color: #3b82f6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Table Styles */
.table-card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  text-align: left;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 700;
  border-bottom: 1px solid #e2e8f0;
}

.admin-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.admin-table td:last-child {
  white-space: nowrap;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
}

.user-cell img {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.price {
  font-weight: 800;
  color: #ef4444;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
  display: inline-block;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.approved {
  background: #dcfce7;
  color: #166534;
}

.status-badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

.action-btns {
  display: flex;
  gap: 0.25rem;
  justify-content: center;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-icon.approve {
  color: #16a34a;
}

.btn-icon.approve:hover {
  background: #dcfce7;
}

.btn-icon.reject {
  color: #f97316;
}

.btn-icon.reject:hover {
  background: #ffedd5;
}

.empty-state {
  text-align: center;
  padding: 5rem !important;
  color: #94a3b8;
}

.empty-state span {
  font-size: 4rem;
  margin-bottom: 1rem;
}
</style>
