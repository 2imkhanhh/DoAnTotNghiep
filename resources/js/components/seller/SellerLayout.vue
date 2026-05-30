<template>
  <div class="seller-container">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
      <div class="sidebar-header">
        <span class="material-symbols-outlined logo-icon text-primary">storefront</span>
        <span class="logo-text">Cửa hàng</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/seller-center/dashboard" class="nav-item">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="nav-label">Dashboard</span>
        </router-link>
        <router-link to="/seller-center/posts" class="nav-item">
          <span class="material-symbols-outlined">inventory_2</span>
          <span class="nav-label">Quản lý tin đăng</span>
        </router-link>
        <router-link to="/seller-center/post/create" class="nav-item">
          <span class="material-symbols-outlined">add_circle</span>
          <span class="nav-label">Đăng tin mới</span>
        </router-link>
        <router-link to="/seller-center/orders" class="nav-item">
          <span class="material-symbols-outlined">receipt_long</span>
          <span class="nav-label">Quản lý đơn hàng</span>
        </router-link>
        <div class="nav-divider"></div>
        <router-link to="/" class="nav-item">
          <span class="material-symbols-outlined">home</span>
          <span class="nav-label">Trang chủ</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="seller-profile">
          <img :src="authStore.avatarUrl" alt="Seller" class="seller-avatar">
          <div class="seller-info">
            <p class="seller-name">{{ authStore.user?.name }}</p>
            <p class="seller-role">Người bán</p>
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
    default: 'Cửa hàng'
  }
});

const authStore = useAuthStore();
const isSidebarCollapsed = ref(false);
</script>

<style scoped>
.seller-container {
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
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  overflow: hidden;
  border-bottom: 1px solid #e2e8f0;
}

.logo-icon {
  font-size: 2.5rem;
  color: #93c5fd;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 800;
  white-space: nowrap;
  color: white;
}

.sidebar.collapsed .logo-text {
  display: none;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0.75rem;
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
  font-weight: 600;
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

.seller-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.seller-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.seller-info p {
  margin: 0;
  white-space: nowrap;
}

.seller-name {
  font-weight: 600;
  font-size: 0.9rem;
}

.seller-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.sidebar.collapsed .seller-info {
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
  transition: all 0.2s;
  position: relative;
}

.icon-btn:hover {
  background: var(--color-primary-fixed);
  color: var(--color-on-primary-fixed);
}

.dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
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
