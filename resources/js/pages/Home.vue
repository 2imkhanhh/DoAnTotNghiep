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

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Product 1 -->
                <div
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="relative h-48 w-full">
                        <img src="https://images.unsplash.com/photo-1598327105666-5b89351cb315?auto=format&fit=crop&q=80&w=600"
                            alt="Iphone 13 Pro Max" class="w-full h-full object-cover">
                        <span
                            class="absolute top-2 right-2 bg-secondary text-on-secondary text-xs font-bold px-2 py-1 rounded">Đã
                            kiểm duyệt</span>
                    </div>
                    <div class="p-4 flex flex-col grow">
                        <h3 class="font-bold text-on-surface line-clamp-2 mb-2">Iphone 13 Pro Max 256GB VN/A còn bảo
                            hành dài</h3>
                        <p class="text-error font-bold text-lg mb-4">16.500.000 đ</p>
                        <div class="mt-auto flex items-center justify-between text-xs text-on-surface-variant">
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">schedule</span> 10 phút trước</span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">location_on</span> Hà Nội</span>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="relative h-48 w-full">
                        <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&q=80&w=600"
                            alt="Laptop Dell XPS 15" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex flex-col grow">
                        <h3 class="font-bold text-on-surface line-clamp-2 mb-2">Laptop Dell XPS 15 9500 core i7 16GB RAM
                        </h3>
                        <p class="text-error font-bold text-lg mb-4">22.000.000 đ</p>
                        <div class="mt-auto flex items-center justify-between text-xs text-on-surface-variant">
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">schedule</span> 1 giờ trước</span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">location_on</span> TP.HCM</span>
                        </div>
                    </div>
                </div>

                <!-- Product 3 -->
                <div
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="relative h-48 w-full">
                        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=600"
                            alt="Giày Sneaker Nike Air" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex flex-col grow">
                        <h3 class="font-bold text-on-surface line-clamp-2 mb-2">Giày Sneaker Nike Air Jordan 1 auth size
                            42</h3>
                        <p class="text-error font-bold text-lg mb-4">2.100.000 đ</p>
                        <div class="mt-auto flex items-center justify-between text-xs text-on-surface-variant">
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">schedule</span> 3 giờ trước</span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">location_on</span> Đà Nẵng</span>
                        </div>
                    </div>
                </div>

                <!-- Product 4 -->
                <div
                    class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="relative h-48 w-full">
                        <img src="https://images.unsplash.com/photo-1550226891-ef816aed4a98?auto=format&fit=crop&q=80&w=600"
                            alt="Sofa da cao cấp" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 flex flex-col grow">
                        <h3 class="font-bold text-on-surface line-clamp-2 mb-2">Sofa góc da lộn màu ghi xám mới 95%
                            thanh lý chuyển nhà</h3>
                        <p class="text-error font-bold text-lg mb-4">4.500.000 đ</p>
                        <div class="mt-auto flex items-center justify-between text-xs text-on-surface-variant">
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">schedule</span> 5 giờ trước</span>
                            <span class="flex items-center gap-1"><span
                                    class="material-symbols-outlined text-sm">location_on</span> Hải Phòng</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-8">
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

const currentSlide = ref(0);
const totalSlides = 3;
let slideInterval = null;
const categories = ref([]);

const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/categories/featured');
        categories.value = response.data.data;
    } catch (error) {
        console.error('Lỗi khi lấy danh mục nổi bật:', error);
    }
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
});

onUnmounted(() => {
    clearInterval(slideInterval);
});
</script>

