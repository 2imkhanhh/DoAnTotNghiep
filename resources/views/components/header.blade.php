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

                @if (!request()->is('login'))
                    <a href="/login" class="font-bold text-primary hover:text-secondary transition-colors underline hidden sm:block">Đăng nhập</a>
                @endif

                @if (!request()->is('register'))
                    <a href="/register" class="px-4 py-2 bg-primary text-on-primary font-bold rounded-lg shadow-sm hover:shadow-md transition-shadow">Đăng ký</a>
                @endif
                
                <!-- Mobile menu button -->
                <button class="md:hidden p-2 text-on-surface hover:bg-surface-container rounded-full">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </div>
    </div>
</header>
