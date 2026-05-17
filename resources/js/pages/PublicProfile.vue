<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Sidebar Trái: Thông tin người bán -->
      <aside class="w-full md:w-80 shrink-0">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm sticky top-24">
          <div class="p-6 border-b border-outline-variant text-center">
            <!-- Avatar -->
            <div class="relative w-24 h-24 mx-auto mb-4">
              <img
                :src="seller.avatar || 'https://ui-avatars.com/api/?name=' + (seller.name || 'Seller') + '&background=020037&color=fff'"
                alt="Avatar" class="w-full h-full rounded-full object-cover border-4 border-primary-fixed shadow-sm">
            </div>

            <h2 class="font-extrabold text-xl text-on-surface truncate">{{ seller.name || 'Người bán' }}</h2>
            <div class="flex items-center justify-center gap-1 mt-1 text-amber-500">
              <span class="material-symbols-outlined font-variation-fill text-lg">star</span>
              <span class="text-sm font-bold text-on-surface">{{ seller.rating || '5.0' }}</span>
              <span class="text-xs text-on-surface-variant">({{ seller.reviews_count || 20 }} đánh giá)</span>
            </div>

            <!-- Nút tương tác chính -->
            <div class="grid grid-cols-2 gap-3 mt-6">
              <button @click="toggleFollow" :class="['py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 cursor-pointer border',
                isFollowing
                  ? 'bg-surface-container-high border-outline-variant text-on-surface'
                  : 'bg-primary border-primary text-on-primary shadow-md hover:shadow-primary/20']">
                <span class="material-symbols-outlined text-sm">{{ isFollowing ? 'done' : 'person_add' }}</span>
                <span>{{ isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}</span>
              </button>

              <a :href="'tel:' + (seller.phone || '')"
                class="py-2.5 px-4 bg-success border border-success text-white rounded-xl text-sm font-bold shadow-md hover:shadow-success/20 transition-all flex items-center justify-center gap-2 text-decoration-none">
                <span class="material-symbols-outlined text-sm">call</span>
                <span>Gọi điện</span>
              </a>
            </div>
          </div>

          <!-- Thông tin chi tiết liên hệ -->
          <div class="p-6 space-y-4 border-b border-outline-variant bg-surface-container-low/20">
            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">location_on</span>
              <div class="text-sm text-left">
                <p class="font-bold text-on-surface">Địa chỉ</p>
                <p class="text-on-surface-variant mt-0.5 leading-relaxed">
                  {{ seller.address || seller.ward_name ? `${seller.address || ''} ${seller.ward_name || ''},
                  ${seller.province_name || ''}` : 'Đang cập nhật' }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-on-surface-variant text-lg mt-0.5">mail</span>
              <div class="text-sm text-left">
                <p class="font-bold text-on-surface">Email liên hệ</p>
                <p class="text-on-surface-variant mt-0.5 truncate w-48 sm:w-56">{{ seller.email || 'Đang ẩn' }}</p>
              </div>
            </div>
          </div>

          <!-- Thống kê của người bán -->
          <div
            class="p-6 grid grid-cols-2 gap-4 divide-x divide-outline-variant text-center bg-surface-container-low/40">
            <div class="flex flex-col items-center justify-center">
              <span class="text-2xl font-black text-on-surface">{{ seller.sold_count || 0 }}</span>
              <span class="text-[11px] font-bold text-on-surface-variant uppercase mt-1 tracking-wider">Đã bán</span>
            </div>
            <div class="flex flex-col items-center justify-center pl-4">
              <span class="text-2xl font-black text-on-surface">{{ seller.reviews_count || 20 }}</span>
              <span class="text-[11px] font-bold text-on-surface-variant uppercase mt-1 tracking-wider">Đánh giá</span>
            </div>
          </div>
        </div>
      </aside>

      <!-- Cột Phải: Danh sách tin đăng của người bán -->
      <main class="grow">
        <div
          class="bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm overflow-hidden min-h-[600px]">
          <!-- Tab Navigation -->
          <div class="border-b border-outline-variant flex bg-surface-container-low/10">
            <button @click="activeTab = 'posts'"
              :class="['px-6 py-4 font-bold text-sm border-b-2 transition-all cursor-pointer flex items-center gap-2',
                activeTab === 'posts' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface']">
              <span class="material-symbols-outlined text-lg">grid_on</span>
              <span>Tin đang đăng ({{ mockPosts.length }})</span>
            </button>
            <button @click="activeTab = 'reviews'"
              :class="['px-6 py-4 font-bold text-sm border-b-2 transition-all cursor-pointer flex items-center gap-2',
                activeTab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface']">
              <span class="material-symbols-outlined text-lg">star</span>
              <span>Đánh giá từ người mua ({{ mockReviews.length }})</span>
            </button>
          </div>

          <!-- Nội dung Tab: Danh sách tin đăng -->
          <div class="p-6" v-if="activeTab === 'posts'">
            <div v-if="mockPosts.length === 0" class="text-center py-20 text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl mb-3 opacity-40">inventory_2</span>
              <p class="font-bold text-lg">Người dùng chưa đăng tin bán nào</p>
              <p class="text-sm mt-1">Các tin đăng được phê duyệt của người bán sẽ xuất hiện ở đây.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Tin đăng Card -->
              <div v-for="item in mockPosts" :key="item.id" @click="goToPost(item.slug)"
                class="bg-surface-container rounded-2xl overflow-hidden border border-outline-variant hover:shadow-md transition-all cursor-pointer flex flex-col group">
                <div class="relative aspect-4/3 bg-surface-container-high overflow-hidden shrink-0">
                  <img :src="item.image" :alt="item.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  <span
                    class="absolute top-3 left-3 bg-primary/95 text-on-primary text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {{ item.category }}
                  </span>
                </div>
                <div class="p-4 flex-1 flex flex-col justify-between">
                  <div>
                    <h4
                      class="font-extrabold text-on-surface line-clamp-2 leading-snug group-hover:text-primary transition-colors"
                      :title="item.title">
                      {{ item.title }}
                    </h4>
                    <p class="text-error font-black text-lg mt-2">{{ formatPrice(item.price) }}đ</p>
                  </div>

                  <div
                    class="mt-4 pt-3 border-t border-outline-variant flex items-center justify-between text-xs text-on-surface-variant">
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">schedule</span>
                      {{ item.time }}
                    </span>
                    <span class="flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">location_on</span>
                      {{ item.location }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Nội dung Tab: Danh sách Đánh giá -->
          <div class="p-6 space-y-6" v-else>
            <div v-if="mockReviews.length === 0" class="text-center py-20 text-on-surface-variant">
              <span class="material-symbols-outlined text-5xl mb-3 opacity-40">rate_review</span>
              <p class="font-bold text-lg">Chưa có đánh giá nào cho người bán này</p>
            </div>

            <div v-else v-for="rev in mockReviews" :key="rev.id"
              class="border-b border-outline-variant last:border-0 pb-6 last:pb-0">
              <div class="flex items-start gap-4">
                <img :src="rev.reviewer_avatar"
                  class="w-10 h-10 rounded-full object-cover shrink-0 border border-outline-variant" />
                <div class="flex-1 text-left">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                    <h4 class="font-bold text-on-surface text-sm sm:text-base">{{ rev.reviewer_name }}</h4>
                    <span class="text-xs text-on-surface-variant">{{ rev.date }}</span>
                  </div>

                  <div class="flex items-center gap-1 text-amber-500 mb-2">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-sm font-variation-fill">
                      {{ star <= rev.rating ? 'star' : 'star_outline' }} </span>
                        <span class="text-xs text-on-surface-variant ml-2 font-medium">Mua hàng:
                          <span class="text-primary font-bold">{{ rev.post_title }}</span>
                        </span>
                  </div>

                  <p class="text-on-surface text-sm leading-relaxed">
                    {{ rev.comment }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const seller = ref({});
const isFollowing = ref(false);
const activeTab = ref('posts');

const toggleFollow = () => {
  isFollowing.value = !isFollowing.value;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price);
};

const goToPost = (slug) => {
  router.push(`/post/${slug}`);
};

// Mock dữ liệu tin đăng của người bán để giao diện cực kỳ phong phú và chân thực
const mockPosts = ref([
  {
    id: 1,
    title: 'iPhone 13 Pro Max 256GB Gold Bản Quốc Tế Zin Đét',
    price: 15500000,
    category: 'Điện thoại',
    image: 'https://images.unsplash.com/photo-1632661676196-a300659a1ba0?q=80&w=400&h=300&fit=crop',
    time: '2 giờ trước',
    location: 'Quận 1, TP. HCM',
    slug: 'iphone-13-pro-max-256gb-gold'
  },
  {
    id: 2,
    title: 'MacBook Air M1 8G/256G Gray Đẹp Không Tỳ Vết Pin 95%',
    price: 13900000,
    category: 'Laptop',
    image: 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?q=80&w=400&h=300&fit=crop',
    time: '1 ngày trước',
    location: 'Bình Thạnh, TP. HCM',
    slug: 'macbook-air-m1-8g-256g-gray'
  },
  {
    id: 3,
    title: 'Tai nghe Chống Ồn Sony WH-1000XM4 Như Mới Fullbox',
    price: 3800000,
    category: 'Phụ kiện',
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=400&h=300&fit=crop',
    time: '3 ngày trước',
    location: 'Quận 3, TP. HCM',
    slug: 'tai-nghe-sony-wh-1000xm4'
  }
]);

// Mock đánh giá chất lượng người bán từ khách hàng
const mockReviews = ref([
  {
    id: 1,
    reviewer_name: 'Nguyễn Văn Hùng',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Nguyen+Van+Hung&background=3b82f6&color=fff',
    rating: 5,
    date: '12/05/2026',
    post_title: 'iPhone 13 Pro Max 256GB Gold',
    comment: 'Điện thoại dùng rất tốt, pin còn 92% đúng như mô tả. Người bán uy tín hỗ trợ ship nhanh, đóng gói rất cẩn thận 3 lớp chống sốc.'
  },
  {
    id: 2,
    reviewer_name: 'Trần Thị Lan',
    reviewer_avatar: 'https://ui-avatars.com/api/?name=Tran+Thi+Lan&background=10b981&color=fff',
    rating: 5,
    date: '08/05/2026',
    post_title: 'MacBook Air M1 8GB/256GB Gray',
    comment: 'Máy dùng siêu mượt, màn hình đẹp không vết trầy. Giao dịch nhanh chóng tại nhà, anh chủ siêu nhiệt tình test máy từ A-Z giúp mình.'
  }
]);

onMounted(() => {
  // Lấy thông tin người bán được truyền qua state của router
  if (history.state && history.state.seller) {
    seller.value = history.state.seller;
  } else {
    // Trường hợp dự phòng nếu load trực tiếp link không qua click bài viết
    seller.value = {
      name: 'Người bán uy tín',
      rating: '5.0',
      sold_count: 5,
      reviews_count: 20,
      email: 'seller@example.com',
      phone: '0901234567',
      address: 'Quận 1',
      province_name: 'TP. Hồ Chí Minh',
      ward_name: 'Phường Bến Nghé'
    };
  }

  window.scrollTo(0, 0);
});
</script>

<style scoped>
.text-decoration-none {
  text-decoration: none;
}

.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
