<template>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
      <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon">shield_person</span>
        <span class="logo-text">Admin Panel</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" class="nav-item">
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
    <div class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <button @click="isSidebarCollapsed = !isSidebarCollapsed" class="icon-btn">
            <span class="material-symbols-outlined">menu</span>
          </button>
          <h1 class="page-title">{{ title }}</h1>
        </div>
        <div class="topbar-right">
          <div class="search-box">
            <span class="material-symbols-outlined">search</span>
            <input type="text" placeholder="Tìm kiếm...">
          </div>
          <button class="icon-btn">
            <span class="material-symbols-outlined">notifications</span>
            <span class="dot"></span>
          </button>
        </div>
      </header>

      <div class="content-body">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../../stores/auth';

defineProps({
  title: {
    type: String,
    default: 'Bảng điều khiển'
  }
});

const authStore = useAuthStore();
const isSidebarCollapsed = ref(false);
</script>

<style scoped>
.admin-container {
  display: flex;
  height: 100vh;
  background-color: #f8fafc;
  color: #1e293b;
  font-family: 'Manrope', sans-serif;
  overflow: hidden;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  background-color: #020037;
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
  color: #93c5fd;
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
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.router-link-active {
  background-color: #3b82f6;
  color: white;
}

.nav-label {
  font-weight: 500;
  white-space: nowrap;
}

.sidebar.collapsed .nav-label {
  display: none;
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

/* Main Content */
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

.topbar-left, .topbar-right {
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
  transition: all 0.2s;
}

.icon-btn:hover {
  background: #f1f5f9;
  color: #3b82f6;
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
}

.content-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
  scrollbar-gutter: stable;
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
}
</style>
