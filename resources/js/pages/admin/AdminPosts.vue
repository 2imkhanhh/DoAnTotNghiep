<template>
  <AdminLayout title="Quản lý tin đăng">
    <div class="posts-container">
      <!-- Filters & Actions -->
      <div class="filters-bar">
        <div class="status-tabs">
          <button v-for="tab in statusTabs" :key="tab.value"
            :class="['tab-btn', { active: filterStatus === tab.value }]" @click="setFilterStatus(tab.value)">
            {{ tab.label }}
            <span v-if="tab.count !== null" class="count">{{ tab.count }}</span>
          </button>
        </div>

        <div class="search-box">
          <span class="material-symbols-outlined">search</span>
          <input type="text" v-model="searchQuery" @input="debounceSearch"
            placeholder="Tìm theo tiêu đề hoặc người đăng...">
        </div>
      </div>

      <!-- Posts Table -->
      <div class="table-card">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Hình ảnh</th>
              <th>Thông tin</th>
              <th>Giá</th>
              <th>Người đăng</th>
              <th>Trạng thái</th>
              <th>Ngày đăng</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="post in posts" :key="post.id">
              <td>
                <div class="post-thumb">
                  <img :src="getPrimaryImage(post)" :alt="post.title">
                </div>
              </td>
              <td>
                <div class="post-info">
                  <span class="category-tag">{{ post.category?.name }}</span>
                  <h4 class="title" @click="viewDetail(post)">{{ post.title }}</h4>
                  <p class="address">
                    <span class="material-symbols-outlined">location_on</span>
                    {{ (post.ward_name || post.province_name) ? `${post.ward_name ? post.ward_name + ', ' :
                      ''}${post.province_name || ''}` : (post.address || 'Đang cập nhật') }}
                  </p>
                </div>
              </td>
              <td>
                <span class="price">{{ formatPrice(post.price) }}đ</span>
              </td>
              <td>
                <div class="user-cell">
                  <span>{{ post.user?.name }}</span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', getStatusClass(post.status)]">
                  {{ getStatusText(post.status) }}
                </span>
              </td>
              <td class="text-secondary">{{ formatDate(post.created_at) }}</td>
              <td>
                <div class="action-btns">
                  <button v-if="post.status !== 1" class="btn-icon approve" @click="updateStatus(post, 1)"
                    title="Duyệt">
                    <span class="material-symbols-outlined">check_circle</span>
                  </button>
                  <button v-if="post.status !== 3" class="btn-icon reject" @click="openRejectModal(post)"
                    title="Từ chối">
                    <span class="material-symbols-outlined">block</span>
                  </button>
                  <button class="btn-icon view" @click="viewDetail(post)" title="Xem chi tiết">
                    <span class="material-symbols-outlined">visibility</span>
                  </button>
                  <button class="btn-icon delete" @click="confirmDelete(post)" title="Xóa">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="posts.length === 0">
              <td colspan="7" class="empty-state">
                <span class="material-symbols-outlined">description</span>
                <p>Không có tin đăng nào phù hợp với bộ lọc</p>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="pagination">
          <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <span class="page-info">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
          <button :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>

      <!-- Detail Modal -->
      <div v-if="showDetailModal" class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal-content post-detail-modal">
          <div class="modal-header">
            <h3>Chi tiết tin đăng</h3>
            <button @click="showDetailModal = false" class="close-btn">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div class="modal-body" v-if="selectedPost">
            <div class="post-detail-grid">
              <!-- Left: Images -->
              <div class="detail-images">
                <div class="main-img">
                  <img :src="selectedPost.images[activeImgIdx]?.image_path" alt="">

                  <!-- Navigation Arrows -->
                  <button v-if="selectedPost.images.length > 1" class="gallery-nav-btn prev-btn" @click="prevImage"
                    aria-label="Ảnh trước">
                    <span class="material-symbols-outlined">chevron_left</span>
                  </button>
                  <button v-if="selectedPost.images.length > 1" class="gallery-nav-btn next-btn" @click="nextImage"
                    aria-label="Ảnh sau">
                    <span class="material-symbols-outlined">chevron_right</span>
                  </button>
                </div>
                <div class="thumbs-list">
                  <img v-for="(img, idx) in selectedPost.images" :key="idx" :src="img.image_path"
                    :class="{ active: activeImgIdx === idx }" @click="activeImgIdx = idx">
                </div>
              </div>

              <!-- Right: Info -->
              <div class="detail-info">
                <span :class="['status-badge', getStatusClass(selectedPost.status)]"
                  style="align-self: flex-start; margin-bottom: 0.5rem;">
                  {{ getStatusText(selectedPost.status) }}
                </span>
                <h2 class="detail-title">{{ selectedPost.title }}</h2>
                <p class="detail-price">{{ formatPrice(selectedPost.price) }}đ</p>
                <p class="detail-address">
                  <span class="material-symbols-outlined">location_on</span>
                  {{ (selectedPost.ward_name || selectedPost.province_name) ? `${selectedPost.ward_name ?
                    selectedPost.ward_name + ', ' : ''}${selectedPost.province_name || ''}` : (selectedPost.address ||
                  'Đang cập nhật') }}
                </p>

                <div class="info-section">
                  <h5>Mô tả chi tiết</h5>
                  <p class="description">{{ selectedPost.description }}</p>
                </div>

                <div class="info-section" v-if="Object.keys(selectedPost.specifications || {}).length">
                  <h5>Thông số kỹ thuật</h5>
                  <div class="specs-grid">
                    <div v-for="(val, key) in selectedPost.specifications" :key="key" class="spec-item">
                      <span class="spec-key">{{ getAttributeName(selectedPost, key) }}:</span>
                      <span class="spec-val">{{ Array.isArray(val) ? val.join(', ') : val }}</span>
                    </div>
                  </div>
                </div>

                <div class="info-section user-box">
                  <h5>Người đăng</h5>
                  <div class="user-info-mini">
                    <img :src="getUserAvatar(selectedPost.user)" alt="">
                    <div>
                      <p class="name">{{ selectedPost.user?.name }}</p>
                      <p class="contact">SĐT: {{ selectedPost.phone || selectedPost.user?.phone || 'Chưa cập nhật' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button v-if="selectedPost?.status !== 1" class="btn-approve" @click="updateStatus(selectedPost, 1)">
              <span class="material-symbols-outlined">check_circle</span> Duyệt tin
            </button>
            <button v-if="selectedPost?.status !== 3" class="btn-reject" @click="openRejectModal(selectedPost)">
              <span class="material-symbols-outlined">block</span> Từ chối
            </button>
            <button class="btn-secondary" @click="showDetailModal = false">Đóng</button>
          </div>
        </div>
      </div>

      <!-- Simplified Reject Reason Modal -->
      <div v-if="showRejectModal" class="modal-overlay reject-modal-overlay">
        <div class="modal-content reject-modal-simple">
          <div class="modal-header-simple">
            <h3>Lý do từ chối</h3>
            <p>Vui lòng cho người dùng biết tại sao tin đăng này không được duyệt</p>
          </div>
          <div class="modal-body-simple">
            <textarea v-model="rejectionReason" placeholder="Nhập lý do tại đây..." rows="5"></textarea>
          </div>
          <div class="modal-footer-simple">
            <button class="btn-cancel" @click="showRejectModal = false">Để sau</button>
            <button class="btn-confirm" @click="confirmReject" :disabled="!rejectionReason.trim()">
              Gửi yêu cầu
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AdminLayout from '../../components/admin/AdminLayout.vue';

const posts = ref([]);
const filterStatus = ref(''); // Empty means 'All'
const searchQuery = ref('');
const loading = ref(false);
const showDetailModal = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');
const postToReject = ref(null);
const selectedPost = ref(null);
const activeImgIdx = ref(0);

const prevImage = () => {
  if (!selectedPost.value || !selectedPost.value.images || !selectedPost.value.images.length) return;
  if (activeImgIdx.value === 0) {
    activeImgIdx.value = selectedPost.value.images.length - 1;
  } else {
    activeImgIdx.value--;
  }
};

const nextImage = () => {
  if (!selectedPost.value || !selectedPost.value.images || !selectedPost.value.images.length) return;
  if (activeImgIdx.value === selectedPost.value.images.length - 1) {
    activeImgIdx.value = 0;
  } else {
    activeImgIdx.value++;
  }
};

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
});

const statusTabs = ref([
  { label: 'Tất cả', value: '', count: null },
  { label: 'Chờ duyệt', value: '0', count: null },
  { label: 'Đang hiển thị', value: '1', count: null },
  { label: 'Đã bán', value: '2', count: null },
  { label: 'Bị từ chối', value: '3', count: null },
]);

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/posts', {
      params: {
        page,
        status: filterStatus.value,
        search: searchQuery.value,
        limit: 10
      }
    });
    posts.value = response.data.data.data;
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total,
      per_page: response.data.data.per_page
    };

    // Cập nhật số lượng Chờ duyệt cho tab
    if (response.data.counts && response.data.counts.pending !== undefined) {
      statusTabs.value[1].count = response.data.counts.pending;
    }
  } catch (error) {
    console.error('Lỗi khi tải tin đăng:', error);
  } finally {
    loading.value = false;
  }
};

const setFilterStatus = (status) => {
  filterStatus.value = status;
  pagination.value.current_page = 1;
  fetchPosts(1);
};

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchPosts(1);
  }, 500);
};

const changePage = (page) => {
  fetchPosts(page);
};

const viewDetail = (post) => {
  selectedPost.value = post;
  activeImgIdx.value = 0;
  showDetailModal.value = true;
};

const openRejectModal = (post) => {
  postToReject.value = post;
  rejectionReason.value = '';
  showRejectModal.value = true;
};

const confirmReject = async () => {
  if (!rejectionReason.value.trim()) return;
  await updateStatus(postToReject.value, 3, rejectionReason.value);
  showRejectModal.value = false;
};

const updateStatus = async (post, status, reason = null) => {
  if (status !== 3) {
    const statusTexts = { 1: 'Duyệt tin', 2: 'Đánh dấu đã bán' };
    if (!confirm(`${statusTexts[status]} này?`)) return;
  }

  try {
    const response = await axios.put(`/api/posts/${post.id}/status`, {
      status,
      reason: reason
    });
    if (response.data.success) {
      post.status = status;
      post.reject_reason = reason;
      if (showDetailModal.value) showDetailModal.value = false;
    }
  } catch (error) {
    alert('Lỗi khi cập nhật trạng thái');
  }
};

const confirmDelete = async (post) => {
  if (!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tin đăng này?')) return;

  try {
    await axios.delete(`/api/posts/${post.id}`);
    fetchPosts(pagination.value.current_page);
  } catch (error) {
    alert('Lỗi khi xóa tin đăng');
  }
};

const getPrimaryImage = (post) => {
  if (!post.images || post.images.length === 0) return '/images/no-image.png';
  const primary = post.images.find(img => img.is_primary);
  return primary ? primary.image_path : post.images[0].image_path;
};

const getUserAvatar = (user) => {
  if (user?.avatar) {
    // Nếu avatar là link đầy đủ thì dùng luôn, nếu không thì nối thêm /storage/
    return user.avatar.startsWith('http') ? user.avatar : user.avatar;
  }
  return `https://ui-avatars.com/api/?name=${user?.name || 'User'}&background=random&color=fff`;
};

const getAttributeName = (post, key) => {
  if (!post.category?.attributes) return key;
  const attr = post.category.attributes.find(a => a.key === key);
  return attr ? attr.name : key;
};

const getStatusText = (status) => {
  const texts = {
    0: 'Chờ duyệt',
    1: 'Đang hiển thị',
    2: 'Đã bán',
    3: 'Bị từ chối'
  };
  return texts[status] || 'Không rõ';
};

const getStatusClass = (status) => {
  const classes = {
    0: 'pending',
    1: 'approved',
    2: 'sold',
    3: 'rejected'
  };
  return classes[status] || '';
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

.tab-btn .count {
  background: #e2e8f0;
  padding: 0.1rem 0.4rem;
  border-radius: 1rem;
  font-size: 0.75rem;
}

.search-box {
  position: relative;
  width: 350px;
}

.search-box span {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-box input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  outline: none;
  font-size: 0.9rem;
}

/* Table Styles */
.table-card {
  background: white;
  border-radius: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
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

.post-thumb img {
  width: 60px;
  height: 60px;
  border-radius: 0.75rem;
  object-fit: cover;
}

.post-info .category-tag {
  font-size: 0.7rem;
  background: #f1f5f9;
  padding: 0.1rem 0.5rem;
  border-radius: 2rem;
  color: #64748b;
}

.post-info .title {
  margin: 0.25rem 0;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  color: #1e293b;
}

.post-info .title:hover {
  color: #3b82f6;
}

.post-info .location {
  margin: 0;
  font-size: 0.8rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.post-info .location span {
  font-size: 1rem;
}

.price {
  font-weight: 800;
  color: #ef4444;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.user-cell img {
  width: 28px;
  height: 28px;
  border-radius: 50%;
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

.status-badge.sold {
  background: #e0f2fe;
  color: #0369a1;
}

.status-badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

.action-btns {
  display: flex;
  gap: 0.25rem;
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

.btn-icon.sold {
  color: #3b82f6;
}

.btn-icon.sold:hover {
  background: #eff6ff;
}

.btn-icon.reject {
  color: #f97316;
}

.btn-icon.reject:hover {
  background: #ffedd5;
}

.btn-icon.view {
  color: #3b82f6;
}

.btn-icon.view:hover {
  background: #eff6ff;
}

.btn-icon.delete {
  color: #ef4444;
}

.btn-icon.delete:hover {
  background: #fef2f2;
}

/* Pagination */
.pagination {
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  border-top: 1px solid #f1f5f9;
}

.pagination button {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.25rem;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.pagination button:hover:not(:disabled) {
  background: #f8fafc;
  color: #3b82f6;
  border-color: #3b82f6;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
}

/* Base Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  /* Căn giữa dọc */
  justify-content: center;
  /* Căn giữa ngang */
  z-index: 9999;
  padding: 2rem;
}

/* Modal Animation */
.modal-content {
  animation: modalPopup 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalPopup {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Post Detail Modal Specifics */
.post-detail-modal {
  background: white;
  width: 100%;
  max-width: 1000px;
  max-height: 90vh;
  border-radius: 1.5rem;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
  padding: 1.25rem 2rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.modal-body {
  padding: 2rem;
  overflow-y: auto;
  /* Cuộn nội dung bên trong nếu quá dài */
  flex: 1;
}

.post-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 2rem;
}

.detail-images .main-img {
  width: 100%;
  aspect-ratio: 4/3;
  border-radius: 1rem;
  overflow: hidden;
  margin-bottom: 1rem;
  background: #f1f5f9;
}

.detail-images .main-img img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.thumbs-list {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.thumbs-list img {
  width: 60px;
  height: 60px;
  border-radius: 0.5rem;
  object-fit: cover;
  cursor: pointer;
  border: 2px solid transparent;
}

.thumbs-list img.active {
  border-color: #3b82f6;
}

.detail-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.status-badge-floating {
  align-self: flex-start;
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.detail-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
}

.detail-price {
  font-size: 1.75rem;
  font-weight: 900;
  color: #ef4444;
  margin: 0;
}

.detail-address,
.address {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.detail-address span,
.address span {
  font-size: 1.1rem;
}

.info-section h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  color: #94a3b8;
  text-transform: uppercase;
}

.description {
  font-size: 0.95rem;
  color: #475569;
  line-height: 1.6;
  white-space: pre-line;
}

.specs-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  background: #f8fafc;
  padding: 1rem;
  border-radius: 1rem;
}

.spec-item {
  display: flex;
  flex-direction: column;
}

.spec-key {
  font-size: 0.75rem;
  color: #94a3b8;
}

.spec-val {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
}

.user-box {
  border-top: 1px solid #f1f5f9;
  padding-top: 1rem;
}

.user-info-mini {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info-mini img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
}

.user-info-mini .name {
  font-weight: 800;
  margin: 0;
}

.user-info-mini .contact {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.25rem 2rem;
  border-top: 1px solid #f1f5f9;
  background: #f8fafc;
}

.modal-header .close-btn {
  background: none;
  border: none;
  cursor: pointer !important;
  color: #94a3b8;
  transition: all 0.2s;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-header .close-btn span {
  cursor: pointer !important;
}

.modal-header .close-btn:hover {
  color: #ef4444;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 0.875rem 1.75rem;
  border-radius: 0.875rem;
  font-weight: 700;
  cursor: pointer !important;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.btn-approve {
  background: #16a34a;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-sold {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-reject {
  background: #f97316;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Simplified Reject Modal */
.reject-modal-overlay {
  z-index: 1100;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
}

.reject-modal-simple {
  background: white;
  width: 100%;
  max-width: 450px;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header-simple h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
}

.modal-header-simple p {
  margin: 0.5rem 0 1.5rem 0;
  font-size: 0.875rem;
  color: #64748b;
}

.modal-body-simple textarea {
  width: 100%;
  padding: 1.25rem;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  outline: none;
  font-family: inherit;
  font-size: 1rem;
  resize: none;
  transition: all 0.2s;
}

.modal-body-simple textarea:focus {
  background: white;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.modal-footer-simple {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-cancel {
  flex: 1;
  padding: 0.875rem;
  border: none;
  background: #f1f5f9;
  color: #475569;
  border-radius: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

.btn-confirm {
  flex: 2;
  padding: 0.875rem;
  border: none;
  background: #1e293b;
  color: white;
  border-radius: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-confirm:hover:not(:disabled) {
  background: #000;
  transform: translateY(-2px);
}

.btn-confirm:disabled {
  opacity: 0.3;
  cursor: not-allowed;
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

/* Detail Modal Gallery Navigation Arrows */
.main-img {
  position: relative;
}

.gallery-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(15, 23, 42, 0.6);
  /* Nền tối sang trọng (Dark Charcoal) */
  backdrop-filter: blur(8px);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0.7;
  /* Luôn hiển thị mờ để báo hiệu */
  z-index: 10;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.main-img:hover .gallery-nav-btn {
  opacity: 1;
}

.gallery-nav-btn:hover {
  background: rgba(15, 23, 42, 0.85);
  /* Tăng độ đậm khi hover */
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
}

.gallery-nav-btn:active {
  transform: translateY(-50%) scale(0.95);
}

.prev-btn {
  left: 1rem;
}

.next-btn {
  right: 1rem;
}

.gallery-nav-btn span {
  font-size: 26px;
  font-weight: 600;
}
</style>
