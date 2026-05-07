@extends('layouts.app')

@section('title', 'Chợ Đồ Cũ - Mua bán an toàn, tiện lợi')

@section('content')

    <div class="w-full max-w-7xl mx-auto space-y-12 pb-12">
        
        <!-- Hero Banner -->
        <section class="hero-banner mt-4">
            <h1>Nền tảng giao dịch đồ cũ an toàn & tiện lợi</h1>
            <p>Khám phá hàng ngàn món đồ chất lượng với giá siêu hời. Thương lượng trực tiếp qua chat.</p>
            <a href="#explore" class="inline-block bg-on-primary text-primary font-bold py-3 px-8 rounded-full shadow hover:bg-surface-container-low transition-colors text-lg">
                Khám phá ngay
            </a>
        </section>

        <!-- Danh mục nổi bật -->
        <section id="explore">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-on-surface">Danh mục nổi bật</h2>
                <a href="#" class="text-primary font-medium hover:underline">Xem tất cả</a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">smartphone</span>
                    </div>
                    <span class="font-bold text-center">Điện thoại</span>
                </a>
                
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">laptop_mac</span>
                    </div>
                    <span class="font-bold text-center">Máy tính</span>
                </a>
                
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">directions_bike</span>
                    </div>
                    <span class="font-bold text-center">Xe cộ</span>
                </a>
                
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">checkroom</span>
                    </div>
                    <span class="font-bold text-center">Thời trang</span>
                </a>
                
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">chair</span>
                    </div>
                    <span class="font-bold text-center">Nội thất</span>
                </a>
                
                <a href="#" class="category-item">
                    <div class="icon-wrapper">
                        <span class="material-symbols-outlined text-3xl">menu_book</span>
                    </div>
                    <span class="font-bold text-center">Sách báo</span>
                </a>
            </div>
        </section>

        <!-- Sản phẩm mới đăng -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-on-surface">Tin đăng mới nhất</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Product 1 -->
                <div class="product-card">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1598327105666-5b89351cb315?auto=format&fit=crop&q=80&w=600" alt="Iphone 13 Pro Max" class="product-image">
                        <span class="absolute top-2 right-2 bg-secondary text-on-secondary text-xs font-bold px-2 py-1 rounded">Đã kiểm duyệt</span>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">Iphone 13 Pro Max 256GB VN/A còn bảo hành dài</h3>
                        <p class="product-price">16.500.000 đ</p>
                        <div class="product-meta mt-auto">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 10 phút trước</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> Hà Nội</span>
                        </div>
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="product-card">
                    <img src="https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&q=80&w=600" alt="Laptop Dell XPS 15" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title">Laptop Dell XPS 15 9500 core i7 16GB RAM</h3>
                        <p class="product-price">22.000.000 đ</p>
                        <div class="product-meta mt-auto">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 1 giờ trước</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> TP.HCM</span>
                        </div>
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="product-card">
                    <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=600" alt="Giày Sneaker Nike Air" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title">Giày Sneaker Nike Air Jordan 1 auth size 42</h3>
                        <p class="product-price">2.100.000 đ</p>
                        <div class="product-meta mt-auto">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 3 giờ trước</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> Đà Nẵng</span>
                        </div>
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="product-card">
                    <img src="https://images.unsplash.com/photo-1550226891-ef816aed4a98?auto=format&fit=crop&q=80&w=600" alt="Sofa da cao cấp" class="product-image">
                    <div class="product-info">
                        <h3 class="product-title">Sofa góc da lộn màu ghi xám mới 95% thanh lý chuyển nhà</h3>
                        <p class="product-price">4.500.000 đ</p>
                        <div class="product-meta mt-auto">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> 5 giờ trước</span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> Hải Phòng</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-8">
                <button class="px-6 py-2 border-2 border-primary text-primary font-bold rounded-lg hover:bg-primary hover:text-on-primary transition-colors">
                    Xem thêm tin khác
                </button>
            </div>
        </section>

        <!-- Features -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 py-8 border-t border-outline-variant mt-12">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">chat</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Chat Realtime</h3>
                <p class="text-on-surface-variant">Thương lượng giá cả và trao đổi trực tiếp với người bán nhanh chóng, an toàn.</p>
            </div>
            
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">smart_toy</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Chatbot AI Hỗ trợ</h3>
                <p class="text-on-surface-variant">Giải đáp thắc mắc, hướng dẫn đăng tin và hỗ trợ người dùng 24/7 tự động.</p>
            </div>
            
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-tertiary-container text-on-tertiary-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">verified_user</span>
                </div>
                <h3 class="text-lg font-bold mb-2">Giao dịch An toàn</h3>
                <p class="text-on-surface-variant">Hệ thống xác thực người dùng, đánh giá uy tín giúp hạn chế tối đa lừa đảo.</p>
            </div>
        </section>
    </div>

    <!-- Floating Chatbot Button -->
    <div class="floating-chatbot" title="Chatbot Hỗ trợ">
        <span class="material-symbols-outlined">forum</span>
    </div>

@endsection
