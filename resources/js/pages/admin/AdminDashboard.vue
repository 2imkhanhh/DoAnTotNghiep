<template>
  <AdminLayout title="Bảng điều khiển">
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon users">
          <span class="material-symbols-outlined">group</span>
        </div>
        <div class="stat-details">
          <h3>Người dùng</h3>
          <p class="stat-value">{{ stats.users }}</p>
          <p class="stat-change" :class="stats.users_percent >= 0 ? 'positive' : 'negative'">
            {{ stats.users_percent > 0 ? '+' : '' }}{{ stats.users_percent }}% tháng này
          </p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon posts">
          <span class="material-symbols-outlined">article</span>
        </div>
        <div class="stat-details">
          <h3>Tin đang hiển thị</h3>
          <p class="stat-value">{{ stats.active_posts }}</p>
          <p class="stat-change" :class="stats.posts_percent >= 0 ? 'positive' : 'negative'">
            {{ stats.posts_percent > 0 ? '+' : '' }}{{ stats.posts_percent }}% hôm nay
          </p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orders">
          <span class="material-symbols-outlined">local_shipping</span>
        </div>
        <div class="stat-details">
          <h3>Đơn hàng</h3>
          <p class="stat-value">{{ stats.completed_orders }}</p>
          <p class="stat-change" :class="stats.orders_percent >= 0 ? 'positive' : 'negative'">
            {{ stats.orders_percent > 0 ? '+' : '' }}{{ stats.orders_percent }}% tháng này
          </p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon report">
          <span class="material-symbols-outlined">report</span>
        </div>
        <div class="stat-details">
          <h3>Báo cáo vi phạm</h3>
          <p class="stat-value">{{ stats.reports }}</p>
          <p class="stat-change negative">Cần xử lý gấp</p>
        </div>
      </div>
    </div>

    <!-- Recent Activity Sections -->
    <div class="dashboard-grid">
      <!-- Pending Posts -->
      <section class="dashboard-section posts-pending">
        <div class="section-header">
          <h2 class="section-title">Tin đăng chờ duyệt</h2>
          <router-link to="/admin/posts" class="view-all">Xem tất cả</router-link>
        </div>
        <div class="table-container">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Người đăng</th>
                <th>Tiêu đề</th>
                <th>Giá</th>
                <th>Ngày đăng</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="post in recentPosts" :key="post.id">
                <td>
                  <div class="user-cell">
                    <img :src="post.user_avatar || `https://ui-avatars.com/api/?name=${post.user_name}`" alt="">
                    <span>{{ post.user_name }}</span>
                  </div>
                </td>
                <td class="post-title">{{ post.title }}</td>
                <td class="price">{{ formatPrice(post.price) }}đ</td>
                <td class="date">{{ formatDate(post.created_at) }}</td>
                <td>
                  <div class="action-btns">
                    <button class="btn-approve" title="Duyệt">
                      <span class="material-symbols-outlined">check_circle</span>
                    </button>
                    <button class="btn-reject" title="Từ chối">
                      <span class="material-symbols-outlined">cancel</span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="recentPosts.length === 0">
                <td colspan="5" class="empty-state">Không có tin đăng nào chờ duyệt</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Top Users -->
      <section class="dashboard-section top-users">
        <div class="section-header">
          <h2 class="section-title">Người dùng tích cực</h2>
        </div>
        <div class="user-list">
          <div v-for="user in topUsers" :key="user.id" class="user-item">
            <div class="user-info">
              <img :src="user.avatar || `https://ui-avatars.com/api/?name=${user.name}`" alt="">
              <div>
                <p class="name">{{ user.name }}</p>
                <p class="email">{{ user.email }}</p>
              </div>
            </div>
            <div class="user-stats">
              <span class="post-count">{{ user.post_count }} tin</span>
              <div class="rating">
                <span class="material-symbols-outlined">star</span>
                {{ user.rating }}
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const stats = ref({
  users: 0,
  users_percent: 0,
  active_posts: 0,
  posts_percent: 0,
  completed_orders: 0,
  orders_percent: 0,
  reports: 0
});

const recentPosts = ref([]);
const topUsers = ref([]);

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price);
const formatDate = (date) => new Date(date).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

const fetchDashboardData = async () => {
  try {
    const response = await axios.get('/api/admin/dashboard/stats');
    if (response.data.success) {
      const data = response.data.data;
      stats.value = data.stats;
      recentPosts.value = data.recentPosts;
      topUsers.value = data.topUsers;
    }
  } catch (error) {
    console.error('Lỗi lấy dữ liệu dashboard:', error);
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
/* Dashboard Body Specific Styles */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon span {
  font-size: 2rem;
}

.stat-icon.users {
  background: #e0f2fe;
  color: #0ea5e9;
}

.stat-icon.posts {
  background: #fef3c7;
  color: #d97706;
}

.stat-icon.orders {
  background: #dcfce7;
  color: #16a34a;
}

.stat-icon.report {
  background: #fee2e2;
  color: #dc2626;
}

.stat-details h3 {
  margin: 0;
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  margin: 0.25rem 0;
}

.stat-change {
  font-size: 0.75rem;
  margin: 0;
  font-weight: 600;
}

.stat-change.positive {
  color: #16a34a;
}

.stat-change.negative {
  color: #dc2626;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
}

.dashboard-section {
  background: white;
  border-radius: 1.25rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}

.view-all {
  font-size: 0.875rem;
  color: #3b82f6;
  font-weight: 600;
  text-decoration: none;
}

/* Table Styles */
.table-container {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
}

.admin-table th {
  text-align: left;
  padding: 1rem;
  font-size: 0.8rem;
  text-transform: uppercase;
  color: #94a3b8;
  font-weight: 700;
  border-bottom: 1px solid #f1f5f9;
}

.admin-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-cell img {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.post-title {
  font-weight: 600;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.price {
  font-weight: 700;
  color: #ef4444;
}

.date {
  color: #64748b;
}

.action-btns {
  display: flex;
  gap: 0.5rem;
}

.action-btns button {
  border: none;
  background: none;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.4rem;
  transition: all 0.2s;
}

.btn-approve {
  color: #16a34a;
}

.btn-approve:hover {
  background: #dcfce7;
}

.btn-reject {
  color: #dc2626;
}

.btn-reject:hover {
  background: #fee2e2;
}

/* User List Styles */
.user-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.user-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-info img {
  width: 44px;
  height: 44px;
  border-radius: 1rem;
}

.user-info .name {
  margin: 0;
  font-weight: 700;
  font-size: 0.95rem;
}

.user-info .email {
  margin: 0;
  font-size: 0.8rem;
  color: #64748b;
}

.user-stats {
  text-align: right;
}

.post-count {
  display: block;
  font-weight: 700;
  font-size: 0.85rem;
}

.rating {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.25rem;
  font-size: 0.8rem;
  color: #f59e0b;
  font-weight: 600;
}

.rating span {
  font-size: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem !important;
  color: #94a3b8;
}

@media (max-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}
</style>
