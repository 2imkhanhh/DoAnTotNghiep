<template>
    <div class="w-full max-w-7xl mx-auto space-y-12 pb-12">

        <!-- Hero Slider -->
        <section class="relative w-full h-[400px] mt-4 rounded-2xl overflow-hidden shadow-2xl group">
            <!-- Slider Wrapper -->
            <div class="flex w-full h-full transition-transform duration-700 ease-in-out"
                :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                <!-- Dynamic Slides -->
                <div v-for="(banner, index) in banners" :key="banner.id" class="min-w-full h-full relative">
                    <img :src="banner.image_path" class="w-full h-full object-cover"
                        :alt="banner.title || 'Banner'">
                    <div v-if="banner.title || banner.description || banner.link"
                        class="absolute inset-0 bg-linear-to-r from-black/80 to-transparent flex flex-col justify-center px-10 sm:px-20">
                        <h1 v-if="banner.title" class="text-white text-3xl sm:text-5xl font-extrabold mb-4 max-w-xl leading-tight">
                            {{ banner.title }}
                        </h1>
                        <p v-if="banner.description" class="text-gray-200 text-lg mb-8 max-w-md">
                            {{ banner.description }}
                        </p>
                        <router-link v-if="banner.link && banner.link.startsWith('/')" :to="banner.link"
                            class="inline-block bg-primary text-on-primary font-bold py-3 px-8 rounded-full shadow hover:bg-primary-container hover:text-on-primary-container transition-colors w-max">
                            Khám phá ngay
                        </router-link>
                        <a v-else-if="banner.link" :href="banner.link" target="_blank" rel="noopener noreferrer"
                            class="inline-block bg-primary text-on-primary font-bold py-3 px-8 rounded-full shadow hover:bg-primary-container hover:text-on-primary-container transition-colors w-max">
                            Khám phá ngay
                        </a>
                    </div>
                </div>

                <!-- Fallback Slide nếu không có banner nào -->
                <div v-if="banners.length === 0" class="min-w-full h-full relative">
                    <img src="/images/banners/banner1.jpg" class="w-full h-full object-cover"
                        alt="Banner Săn đồ công nghệ">
                    <div
                        class="absolute inset-0 bg-linear-to-r from-black/80 to-transparent flex flex-col justify-center px-10 sm:px-20">
                        <h1 class="text-white text-3xl sm:text-5xl font-extrabold mb-4 max-w-xl leading-tight">Săn đồ
                            công nghệ<br>Giá siêu hời</h1>
                        <p class="text-gray-200 text-lg mb-8 max-w-md">Khám phá hàng ngàn điện thoại, laptop cũ chất
                            lượng cao được kiểm duyệt kỹ càng.</p>
                        <a href="#explore"
                            class="inline-block bg-primary text-on-primary font-bold py-3 px-8 rounded-full shadow hover:bg-primary-container hover:text-on-primary-container transition-colors w-max">
                            Khám phá ngay
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slider Controls -->
            <button @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>

            <!-- Slider Indicators -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <div v-for="index in totalSlides" :key="index" @click="goToSlide(index - 1)"
                    :class="['w-3 h-3 rounded-full cursor-pointer transition-colors', currentSlide === index - 1 ? 'bg-white' : 'bg-white/50']">
                </div>
            </div>
        </section>

        <section id="explore">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-on-surface">
                    {{ showAllCategories ? 'Tất cả danh mục' : 'Danh mục nổi bật' }}
                </h2>
                <button @click.prevent="toggleShowAllCategories" class="text-primary font-medium hover:underline bg-transparent border-none cursor-pointer p-0">
                    {{ showAllCategories ? 'Thu gọn' : 'Xem tất cả' }}
                </button>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                <router-link v-for="cat in categories" :key="cat.id" :to="`/category/${cat.slug}`" class="category-item">
                    <div class="icon-wrapper">
                        <img v-if="cat.icon" :src="cat.icon" :alt="cat.name" class="w-11 h-11 object-contain">
                        <span v-else class="material-symbols-outlined text-3xl">category</span>
                    </div>
                    <span class="font-bold text-center">{{ cat.name }}</span>
                </router-link>
            </div>
        </section>

        <!-- Sản phẩm mới đăng -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-on-surface">Tin đăng mới nhất</h2>
            </div>

            <div v-if="posts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="post in posts" :key="post.id"
                    @click="router.push(`/post/${post.slug}`)"
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col group cursor-pointer">
                    <router-link :to="`/post/${post.slug}`" @click.stop class="relative h-48 w-full overflow-hidden block">
                        <img :src="getPrimaryImage(post)" :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Favorite Button -->
                        <button v-if="!authStore.isLoggedIn || post.user_id !== authStore.user?.id" @click.prevent.stop="toggleFavorite(post.id)" 
                            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center cursor-pointer transition-all duration-300 hover:scale-125 z-10 active:scale-95 group/heart">
                            <!-- 1. Ruột Đỏ (Nằm dưới, chỉ hiện khi yêu thích) -->
                            <span :class="['material-symbols-outlined text-[22px] text-error font-variation-fill transition-all duration-300 absolute', 
                                isFavorite(post.id) ? 'opacity-100 scale-100' : 'opacity-0 scale-0']">
                                favorite
                            </span>
                            
                            <!-- 2. Viền Trắng (Nằm trên cùng, luôn hiện để giữ đường viền) -->
                            <span class="material-symbols-outlined text-[26px] text-white absolute drop-shadow-[0_2px_2px_rgba(0,0,0,0.5)]">
                                favorite
                            </span>
                        </button>

                        <span v-if="post.status === 'sold'"
                            class="absolute top-2 left-2 bg-black/60 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm backdrop-blur-sm">
                            Đã bán
                        </span>
                    </router-link>
                    <div class="p-4 flex flex-col grow">
                        <router-link :to="`/post/${post.slug}`" @click.stop
                            class="font-bold text-on-surface line-clamp-2 mb-2 hover:text-primary transition-colors h-12">
                            {{ post.title }}
                        </router-link>
                        <p class="text-error font-bold text-lg mb-4">{{ formatPrice(post.price) }} đ</p>
                        <div class="mt-auto flex items-center justify-between text-[11px] text-on-surface-variant">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                {{ formatTime(post.created_at) }}
                            </span>
                            <span class="flex items-start gap-1">
                                <span class="material-symbols-outlined text-sm mt-0.5">location_on</span>
                                <div class="flex flex-col leading-tight">
                                    <span>{{ post.ward_name }}</span>
                                    <span>{{ post.province_name }}</span>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="py-12 text-center text-on-surface-variant bg-surface-container-low rounded-2xl border-2 border-dashed border-outline-variant">
                <span class="material-symbols-outlined text-4xl mb-2">inventory_2</span>
                <p>Hiện chưa có tin đăng nào được hiển thị.</p>
            </div>

            <div v-if="posts.length > 0" class="flex justify-center items-center gap-4 mt-8">
                <!-- Nút Xem thêm tin khác -->
                <button
                    v-if="hasMore"
                    @click="loadMorePosts"
                    :disabled="loadingMore"
                    class="btn-load-more">
                    {{ loadingMore ? 'Đang tải...' : 'Xem thêm tin khác' }}
                </button>

                <!-- Nút Ẩn bớt -->
                <button
                    v-if="currentPage > 1"
                    @click="collapsePosts"
                    class="btn-collapse">
                    Ẩn bớt tin
                </button>
            </div>
        </section>

        <!-- Features -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 py-8 border-t border-outline-variant mt-12">
            <div class="flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">chat</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Chat Realtime</h3>
                <p class="text-on-surface-variant">Thương lượng giá cả và trao đổi trực tiếp với người bán nhanh chóng,
                    an toàn.</p>
            </div>

            <div class="flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">smart_toy</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Chatbot AI Hỗ trợ</h3>
                <p class="text-on-surface-variant">Giải đáp thắc mắc, hướng dẫn đăng tin và hỗ trợ người dùng 24/7 tự
                    động.</p>
            </div>

            <div class="flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">verified_user</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Giao dịch An toàn</h3>
                <p class="text-on-surface-variant">Hệ thống xác thực người dùng, đánh giá uy tín giúp hạn chế tối đa lừa
                    đảo.</p>
            </div>
        </section>
    </div>

</template>

<script setup>
import { toast, confirmDialog } from '../utils/alert';

import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const currentSlide = ref(0);
const banners = ref([]);
const totalSlides = computed(() => Math.max(1, banners.value.length));
let slideInterval = null;
const showAllCategories = ref(false);
const allCategories = ref([]);
const featuredCategories = ref([]);

const categories = computed(() => {
    if (showAllCategories.value) {
        const flat = [];
        for (const parent of allCategories.value) {
            flat.push(parent);
            if (parent.children && parent.children.length) {
                for (const child of parent.children) {
                    flat.push(child);
                }
            }
        }
        return flat;
    }
    return featuredCategories.value;
});
const posts = ref([]);
const favoriteIds = ref([]); // Danh sách ID các tin đã yêu thích
const currentPage = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

const isFavorite = (postId) => {
    return favoriteIds.value.includes(Number(postId));
};

const fetchFavorites = async () => {
    if (!authStore.isLoggedIn) return;
    try {
        const response = await axios.get('/api/user/favorites');
        // Vì Backend dùng paginate nên data sẽ nằm trong response.data.data.data
        if (response.data.success) {
            favoriteIds.value = response.data.data.data.map(p => p.id);
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách yêu thích:', error);
    }
};

const toggleFavorite = async (postId) => {
    if (!authStore.isLoggedIn) {
        toast('Vui lòng đăng nhập để sử dụng tính năng này', 'info');
        return;
    }

    try {
        const response = await axios.post(`/api/posts/${postId}/favorite`);
        if (response.data.success) {
            if (response.data.is_favorited) {
                favoriteIds.value.push(Number(postId));
            } else {
                favoriteIds.value = favoriteIds.value.filter(id => id !== Number(postId));
            }
        }
    } catch (error) {
        console.error('Lỗi khi thực hiện yêu thích:', error);
        const msg = error.response?.data?.message || 'Đã xảy ra lỗi khi thực hiện yêu thích';
        toast(msg, 'info');
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/categories/featured');
        featuredCategories.value = response.data.data;
    } catch (error) {
        console.error('Lỗi khi lấy danh mục nổi bật:', error);
    }
};

const fetchBanners = async () => {
    try {
        const response = await axios.get('/api/banners/active');
        if (response.data.success) {
            banners.value = response.data.data;
        }
    } catch (error) {
        console.error('Lỗi khi lấy banner:', error);
    }
};

const toggleShowAllCategories = async () => {
    if (showAllCategories.value) {
        showAllCategories.value = false;
    } else {
        if (allCategories.value.length === 0) {
            try {
                const response = await axios.get('/api/categories');
                allCategories.value = response.data.data;
            } catch (error) {
                console.error('Lỗi khi lấy tất cả danh mục:', error);
            }
        }
        showAllCategories.value = true;
    }
};

const fetchPosts = async () => {
    try {
        const response = await axios.get('/api/posts?limit=8&page=1');
        if (response.data.success) {
            posts.value = response.data.data.data;
            currentPage.value = response.data.data.current_page;
            hasMore.value = response.data.data.current_page < response.data.data.last_page;
        }
    } catch (error) {
        console.error('Lỗi khi lấy tin đăng:', error);
    }
};

const loadMorePosts = async () => {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    try {
        const nextPage = currentPage.value + 1;
        const response = await axios.get(`/api/posts?limit=8&page=${nextPage}`);
        if (response.data.success) {
            posts.value = [...posts.value, ...response.data.data.data];
            currentPage.value = response.data.data.current_page;
            hasMore.value = response.data.data.current_page < response.data.data.last_page;
        }
    } catch (error) {
        console.error('Lỗi khi tải thêm tin đăng:', error);
    } finally {
        loadingMore.value = false;
    }
};

const collapsePosts = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        posts.value = posts.value.slice(0, currentPage.value * 8);
        hasMore.value = true; // Chắc chắn còn trang tiếp theo để tải lại
    }
};

const getPrimaryImage = (post) => {
    if (post.images && post.images.length > 0) {
        const primary = post.images.find(img => img.is_primary);
        return primary ? primary.image_path : post.images[0].image_path;
    }
    return 'https://via.placeholder.com/400x300?text=No+Image';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN').format(price);
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds

    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
    if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;
    
    return date.toLocaleDateString('vi-VN');
};

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % totalSlides.value;
    resetAutoSlide();
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + totalSlides.value) % totalSlides.value;
    resetAutoSlide();
};

const goToSlide = (index) => {
    currentSlide.value = index;
    resetAutoSlide();
};



const startAutoSlide = () => {
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % totalSlides.value;
    }, 5000);
};

const resetAutoSlide = () => {
    clearInterval(slideInterval);
    startAutoSlide();
};

onMounted(() => {
    fetchBanners().then(() => {
        startAutoSlide();
    });
    fetchCategories();
    fetchPosts();
    fetchFavorites();
});

onUnmounted(() => {
    clearInterval(slideInterval);
});
</script>

<style scoped>
.btn-load-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 2rem;
  border: 2px solid var(--color-primary, #020037);
  background: transparent;
  color: var(--color-primary, #020037);
  font-weight: 700;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-load-more:hover:not(:disabled) {
  background: var(--color-primary, #020037);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(2, 0, 55, 0.25);
  transform: translateY(-2px);
}

.btn-load-more:active:not(:disabled) {
  transform: translateY(0);
}

.btn-load-more:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-collapse {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 2rem;
  border: 2px solid var(--color-primary, #020037);
  background: transparent;
  color: var(--color-primary, #020037);
  font-weight: 700;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-collapse:hover {
  background: var(--color-primary, #020037);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(2, 0, 55, 0.25);
  transform: translateY(-2px);
}

.btn-collapse:active {
  transform: translateY(0);
}
</style>


