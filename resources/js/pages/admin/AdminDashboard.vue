<template>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
      <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">shield_person</span>
        <span class="logo-text">Admin Panel</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" class="nav-item active">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="nav-label">Tổng quan</span>
        </router-link>
        <router-link to="/admin/categories" class="nav-item">
          <span class="material-symbols-outlined">category</span>
          <span class="nav-label">Danh mục</span>
        </router-link>
        <router-link to="/admin/posts" class="nav-item">
          <span class="material-symbols-outlined">article</span>
          <span class="nav-label">Tin đăng</span>
          <span v-if="pendingPosts > 0" class="badge">{{ pendingPosts }}</span>
        </router-link>
        <router-link to="/admin/users" class="nav-item">
          <span class="material-symbols-outlined">group</span>
          <span class="nav-label">Người dùng</span>
        </router-link>
        <div class="nav-divider"></div>
        <router-link to="/admin/settings" class="nav-item">
          <span class="material-symbols-outlined">settings</span>
          <span class="nav-label">Cài đặt</span>
        </router-link>
        <router-link to="/" class="nav-item">
          <span class="material-symbols-outlined">home</span>
          <span class="nav-label">Về trang chủ</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="admin-profile">
          <img :src="authStore.avatarUrl" alt="Admin" class="admin-avatar">
          <div class="admin-info">
            <p class="admin-name">{{ authStore.user?.name }}</p>
            <p class="admin-role">Quản trị viên</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <button @click="isSidebarCollapsed = !isSidebarCollapsed" class="icon-btn">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <h1 class="page-title">Bảng điều khiển</h1>
        </div>
        <div class="topbar-right">
          <div class="search-box">
            <span class="material-symbols-outlined">search</span>
            <input type="text" placeholder="Tìm kiếm nhanh...">
          </div>
          <button class="icon-btn">
            <span class="material-symbols-outlined">notifications</span>
            <span class="dot"></span>
          </button>
        </div>
      </header>

      <div class="content-body">
        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon users">
              <span class="material-symbols-outlined">group</span>
            </div>
            <div class="stat-details">
              <h3>Người dùng</h3>
              <p class="stat-value">{{ stats.users }}</p>
              <p class="stat-change positive">+12% tháng này</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon posts">
              <span class="material-symbols-outlined">article</span>
            </div>
            <div class="stat-details">
              <h3>Tin đăng mới</h3>
              <p class="stat-value">{{ stats.posts }}</p>
              <p class="stat-change positive">+5.4% hôm nay</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon categories">
              <span class="material-symbols-outlined">category</span>
            </div>
            <div class="stat-details">
              <h3>Danh mục</h3>
              <p class="stat-value">{{ stats.categories }}</p>
              <p class="stat-change">Đang hoạt động</p>
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
                        <img :src="`https://ui-avatars.com/api/?name=${post.user_name}`" alt="">
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
                  <img :src="`https://ui-avatars.com/api/?name=${user.name}`" alt="">
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
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const isSidebarCollapsed = ref(false);
const pendingPosts = ref(3);

const stats = ref({
  users: '1,248',
  posts: '452',
  categories: '12',
  reports: '8'
});

const recentPosts = ref([
  { id: 1, user_name: 'Minh Tuấn', title: 'iPhone 13 Pro Max cũ 99%', price: 15500000, created_at: '2026-05-14T08:30:00Z' },
  { id: 2, user_name: 'Hải Yến', title: 'Tủ lạnh Samsung Inverter', price: 4200000, created_at: '2026-05-14T09:15:00Z' },
  { id: 3, user_name: 'Thành Nam', title: 'Xe máy Honda Vision 2021', price: 28000000, created_at: '2026-05-14T09:45:00Z' },
]);

const topUsers = ref([
  { id: 1, name: 'Nguyễn Văn A', email: 'vana@gmail.com', post_count: 24, rating: 4.8 },
  { id: 2, name: 'Trần Thị B', email: 'thib@gmail.com', post_count: 18, rating: 4.9 },
  { id: 3, name: 'Lê Văn C', email: 'vanc@gmail.com', post_count: 15, rating: 4.7 },
]);

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price);
const formatDate = (date) => new Date(date).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

onMounted(() => {
  // Logic to fetch actual stats from API would go here
});
</script>

<style scoped>
.admin-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
  color: var(--color-on-surface);
  font-family: 'Manrope', sans-serif;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  background-color: #020037;
  /* Dark theme for admin sidebar */
  color: white;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  z-index: 100;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 2rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  overflow: hidden;
}

.logo-icon {
  font-size: 2.5rem;
  color: var(--color-primary-fixed);
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 800;
  white-space: nowrap;
}

.sidebar.collapsed .logo-text {
  display: none;
}

.sidebar-nav {
  flex: 1;
  padding: 0 0.75rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  border-radius: 0.75rem;
  margin-bottom: 0.25rem;
  transition: all 0.2s;
  position: relative;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.active {
  background-color: var(--color-primary);
  color: white;
}

.nav-label {
  font-weight: 500;
  white-space: nowrap;
}

.sidebar.collapsed .nav-label,
.sidebar.collapsed .badge {
  display: none;
}

.badge {
  background: var(--color-error);
  color: white;
  font-size: 0.7rem;
  padding: 0.1rem 0.4rem;
  border-radius: 1rem;
  margin-left: auto;
}

.nav-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 1rem 0;
}

.sidebar-footer {
  padding: 1.5rem;
  background: rgba(0, 0, 0, 0.2);
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  overflow: hidden;
}

.admin-avatar {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  object-fit: cover;
}

.admin-info p {
  margin: 0;
  white-space: nowrap;
}

.admin-name {
  font-weight: 600;
  font-size: 0.9rem;
}

.admin-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.sidebar.collapsed .admin-info {
  display: none;
}

/* Main Content Styles */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

.topbar {
  height: 70px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  flex-shrink: 0;
}

.topbar-left,
.topbar-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.icon-btn {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  position: relative;
}

.icon-btn:hover {
  background: #f1f5f9;
  color: var(--color-primary);
}

.dot {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  background: var(--color-error);
  border-radius: 50%;
  border: 2px solid white;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.search-box {
  position: relative;
  width: 300px;
}

.search-box span {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.2rem;
}

.search-box input {
  width: 100%;
  background: #f1f5f9;
  border: 1px solid transparent;
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border-radius: 0.75rem;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.search-box input:focus {
  background: white;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px var(--color-primary-fixed);
  outline: none;
}

/* Dashboard Body */
.content-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

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

.stat-icon.categories {
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
  color: var(--color-primary);
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
  color: var(--color-error);
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

@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    height: 100vh;
    left: -280px;
  }

  .sidebar.collapsed {
    left: 0;
    width: 280px;
  }

  .search-box {
    display: none;
  }
}
</style>
