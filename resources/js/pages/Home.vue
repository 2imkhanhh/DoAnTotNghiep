<template>
    <div class="w-full max-w-7xl mx-auto space-y-12 pb-12">

        <!-- Hero Slider -->
        <section class="relative w-full h-[400px] mt-4 rounded-2xl overflow-hidden shadow-2xl group">
            <!-- Slider Wrapper -->
            <div class="flex w-full h-full transition-transform duration-700 ease-in-out"
                :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                <!-- Slide 1 -->
                <div class="min-w-full h-full relative">
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
                <!-- Slide 2 -->
                <div class="min-w-full h-full relative">
                    <img src="/images/banners/banner2.jpg" class="w-full h-full object-cover" alt="Banner Thời trang">
                    <div
                        class="absolute inset-0 bg-linear-to-r from-black/80 to-transparent flex flex-col justify-center px-10 sm:px-20">
                        <h1 class="text-white text-3xl sm:text-5xl font-extrabold mb-4 max-w-xl leading-tight">Thời
                            trang phong cách<br>Thanh lý tủ đồ</h1>
                        <p class="text-gray-200 text-lg mb-8 max-w-md">Mua bán quần áo, phụ kiện đồ cũ thời trang với
                            giá cực tốt. Bảo vệ môi trường.</p>
                        <a href="#explore"
                            class="inline-block bg-primary text-on-primary font-bold py-3 px-8 rounded-full shadow hover:bg-primary-container hover:text-on-primary-container transition-colors w-max">
                            Xem bộ sưu tập
                        </a>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="min-w-full h-full relative">
                    <img src="/images/banners/banner3.jpg" class="w-full h-full object-cover" alt="Banner Nội thất">
                    <div
                        class="absolute inset-0 bg-linear-to-r from-black/80 to-transparent flex flex-col justify-center px-10 sm:px-20">
                        <h1 class="text-white text-3xl sm:text-5xl font-extrabold mb-4 max-w-xl leading-tight">Nội thất
                            gia đình<br>Tiết kiệm chi phí</h1>
                        <p class="text-gray-200 text-lg mb-8 max-w-md">Sang nhượng bàn ghế, tủ giường đồ cũ cực đẹp cho
                            không gian của bạn.</p>
                        <a href="#explore"
                            class="inline-block bg-primary text-on-primary font-bold py-3 px-8 rounded-full shadow hover:bg-primary-container hover:text-on-primary-container transition-colors w-max">
                            Trang trí nhà cửa
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slider Controls -->
            <button @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/40 text-white rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>

            <!-- Slider Indicators -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <div v-for="index in 3" :key="index" @click="goToSlide(index - 1)"
                    :class="['w-3 h-3 rounded-full cursor-pointer transition-colors', currentSlide === index - 1 ? 'bg-white' : 'bg-white/50']">
                </div>
            </div>
        </section>

        <!-- Danh mục nổi bật -->
        <section id="explore">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-on-surface">Danh mục nổi bật</h2>
                <a href="#" class="text-primary font-medium hover:underline">Xem tất cả</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <router-link v-for="cat in categories" :key="cat.id" :to="`/category/${cat.slug}`" class="category-item">
                    <div class="icon-wrapper">
                        <img v-if="cat.icon" :src="cat.icon" :alt="cat.name" class="w-8 h-8 object-contain">
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
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col group">
                    <router-link :to="`/post/${post.slug}`" class="relative h-48 w-full overflow-hidden block">
                        <img :src="getPrimaryImage(post)" :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        
                        <!-- Favorite Button -->
                        <button v-if="!authStore.isLoggedIn || post.user_id !== authStore.user?.id" @click.prevent="toggleFavorite(post.id)" 
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

                        <span v-if="post.status === 2"
                            class="absolute top-2 left-2 bg-black/60 text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm backdrop-blur-sm">
                            Đã bán
                        </span>
                    </router-link>
                    <div class="p-4 flex flex-col grow">
                        <router-link :to="`/post/${post.slug}`"
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

            <div v-if="posts.length > 0" class="text-center mt-8">
                <button
                    class="px-6 py-2 border-2 border-primary text-primary font-bold rounded-lg hover:bg-primary hover:text-on-primary transition-colors">
                    Xem thêm tin khác
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

    <!-- Floating Chatbot Button -->
    <div class="fixed bottom-6 right-6 w-14 h-14 bg-primary text-on-primary rounded-full flex items-center justify-center shadow-lg cursor-pointer hover:bg-primary-container hover:text-on-primary-container transition-colors z-50"
        title="Chatbot Hỗ trợ">
        <span class="material-symbols-outlined">forum</span>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const currentSlide = ref(0);
const totalSlides = 3;
let slideInterval = null;
const categories = ref([]);
const posts = ref([]);
const favoriteIds = ref([]); // Danh sách ID các tin đã yêu thích

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
        alert('Vui lòng đăng nhập để sử dụng tính năng này');
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
        alert(msg);
    }
};

const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/categories/featured');
        categories.value = response.data.data;
    } catch (error) {
        console.error('Lỗi khi lấy danh mục nổi bật:', error);
    }
};

const fetchPosts = async () => {
    try {
        const response = await axios.get('/api/posts?limit=8');
        // Vì Backend dùng paginate nên data sẽ nằm trong response.data.data.data
        posts.value = response.data.data.data;
    } catch (error) {
        console.error('Lỗi khi lấy tin đăng:', error);
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
    currentSlide.value = (currentSlide.value + 1) % totalSlides;
    resetAutoSlide();
};

const prevSlide = () => {
    currentSlide.value = (currentSlide.value - 1 + totalSlides) % totalSlides;
    resetAutoSlide();
};

const goToSlide = (index) => {
    currentSlide.value = index;
    resetAutoSlide();
};



const startAutoSlide = () => {
    slideInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % totalSlides;
    }, 5000);
};

const resetAutoSlide = () => {
    clearInterval(slideInterval);
    startAutoSlide();
};

onMounted(() => {
    startAutoSlide();
    fetchCategories();
    fetchPosts();
    fetchFavorites();
});

onUnmounted(() => {
    clearInterval(slideInterval);
});
</script>

