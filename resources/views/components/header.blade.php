<header class="w-full bg-surface-container-lowest border-b border-outline-variant sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2 text-primary hover:text-primary-container transition-colors">
                    <span class="material-symbols-outlined text-3xl">storefront</span>
                    <span class="font-bold text-2xl tracking-tight hidden sm:block">Chợ Đồ Cũ</span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 max-w-2xl px-4 ml-8 hidden md:block">
                <div class="relative w-full">
                    <input type="text" class="w-full bg-surface-container border border-outline-variant text-on-surface rounded-full pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all" placeholder="Tìm kiếm điện thoại, laptop, xe cộ...">
                    <button class="absolute right-0 top-0 mt-2 mr-3 text-on-surface-variant hover:text-primary">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>
            </div>

            <!-- Right Navigation -->
            <div class="flex items-center gap-2 sm:gap-4 ml-auto">
                <a href="#" class="p-2 text-on-surface hover:text-primary hover:bg-surface-container rounded-full transition-colors hidden sm:block" title="Tin nhắn">
                    <div class="relative">
                        <span class="material-symbols-outlined">chat</span>
                        <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-error text-[10px] text-on-error font-bold">3</span>
                    </div>
                </a>

                <a href="#" class="p-2 text-on-surface hover:text-primary hover:bg-surface-container rounded-full transition-colors hidden sm:block" title="Thông báo">
                    <span class="material-symbols-outlined">notifications</span>
                </a>

                <a href="#" class="hidden sm:flex items-center gap-1 font-bold text-primary hover:text-primary-container px-3 py-2 rounded-lg hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined">add_circle</span>
                    Đăng tin
                </a>

                <div class="h-6 w-px bg-outline-variant hidden sm:block mx-2"></div>

                <!-- Auth Buttons (Guest) -->
                <div id="auth-buttons" class="flex items-center gap-2">
                    @if (!request()->is('login'))
                        <a href="/login" class="hidden sm:block px-4 py-2 text-primary font-bold hover:bg-surface-container-low rounded-full transition-colors">
                            Đăng nhập
                        </a>
                    @endif

                    @if (!request()->is('register'))
                        <a href="/register" class="px-5 py-2 bg-primary text-on-primary font-bold rounded-full shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                            Đăng ký
                        </a>
                    @endif
                </div>

                <!-- User Profile (Logged in) -->
                <div id="user-profile" class="hidden items-center gap-2">
                    <div class="relative group cursor-pointer">
                        <div class="flex items-center gap-2 p-1 pr-3 rounded-full border border-outline-variant hover:bg-surface-container-low transition-colors">
                            <img src="https://ui-avatars.com/api/?name=User&background=020037&color=fff" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                            <span class="font-bold text-sm hidden sm:block text-on-surface">Tài khoản</span>
                            <span class="material-symbols-outlined text-sm text-on-surface-variant group-hover:text-primary transition-colors">expand_more</span>
                        </div>
                        
                        <!-- Dropdown menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-surface-container-lowest rounded-xl shadow-lg border border-outline-variant opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <div class="py-2">
                                <a href="#" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Trang cá nhân</a>
                                <a href="#" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Quản lý tin đăng</a>
                                <a href="#" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low hover:text-primary">Cài đặt</a>
                                <div class="border-t border-outline-variant my-1"></div>
                                <a href="#" onclick="logout()" class="block px-4 py-2 text-sm text-error hover:bg-error-container font-bold">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile menu button -->
                <button class="md:hidden p-2 text-on-surface hover:bg-surface-container rounded-full">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('access_token');
        const authButtons = document.getElementById('auth-buttons');
        const userProfile = document.getElementById('user-profile');

        if (token) {
            // Logged in
            if (authButtons) {
                authButtons.classList.add('hidden');
                authButtons.classList.remove('flex');
            }
            if (userProfile) {
                userProfile.classList.remove('hidden');
                userProfile.classList.add('flex');
            }
        } else {
            // Guest
            if (authButtons) {
                authButtons.classList.remove('hidden');
                authButtons.classList.add('flex');
            }
            if (userProfile) {
                userProfile.classList.add('hidden');
                userProfile.classList.remove('flex');
            }
        }
    });

    function logout() {
        localStorage.removeItem('access_token');
        window.location.href = '/login';
    }
</script>
