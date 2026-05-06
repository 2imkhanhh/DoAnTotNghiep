<header class="w-full p-4 sm:p-6 flex justify-between items-center z-10">
    <a href="/" class="flex items-center gap-2 text-primary hover:text-primary-container transition-colors p-2 rounded-full hover:bg-surface-container-low">
        <span class="material-symbols-outlined text-3xl">storefront</span>
        <span class="font-bold text-xl tracking-tight hidden sm:inline">Chợ Đồ Cũ</span>
    </a>
    
    <div class="flex items-center gap-4">
        <!-- Chỉ hiển thị nút Đăng nhập nếu KHÔNG phải đang ở trang /login -->
        @if(!request()->is('login'))
            <a href="/login" class="font-bold text-primary hover:text-secondary transition-colors underline">Đăng nhập</a>
        @endif
        
        <!-- Chỉ hiển thị nút Đăng ký nếu KHÔNG phải đang ở trang /register -->
        @if(!request()->is('register'))
            <a href="/register" class="px-4 py-2 bg-primary text-on-primary font-bold rounded-lg shadow-sm hover:shadow-md transition-shadow">Đăng ký</a>
        @endif
    </div>
</header>