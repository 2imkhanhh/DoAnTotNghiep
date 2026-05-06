@extends('layouts.auth')

@section('title', 'Đăng ký tài khoản - Chợ Đồ Cũ')

@section('content')
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Tạo tài khoản</h1>
            <p class="text-body-lg text-on-surface-variant">Bắt đầu hành trình sưu tầm những món đồ độc bản của riêng bạn.</p>
        </div>

        <form id="register-form" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Họ và tên</span>
                    <input class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" id="fullname" placeholder="Nhập họ và tên" required="" type="text"/>
                </label>
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Email</span>
                    <input class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" id="email" placeholder="Địa chỉ email của bạn" required="" type="email"/>
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Mật khẩu</span>
                    <input class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" id="password" placeholder="Tạo mật khẩu an toàn" required="" type="password"/>
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Xác nhận mật khẩu</span>
                    <input class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" id="confirm_password" placeholder="Nhập lại mật khẩu" required="" type="password"/>
                </label>
            </div>
            
            <div id="error-message" class="text-error text-sm hidden font-bold"></div>

            <button class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center justify-center gap-2" type="submit">
                <span>Tạo tài khoản</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>
        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Đã có tài khoản? <a class="font-bold text-primary hover:text-secondary transition-colors underline" href="/login">Đăng nhập ngay</a>
        </p>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const name = document.getElementById('fullname').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('error-message');

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ name, email, password, password_confirmation })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Đăng ký thành công! Vui lòng đăng nhập.');
                    window.location.href = '/login'; 
                } else {
                    errorDiv.classList.remove('hidden');
                    errorDiv.innerText = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Có lỗi xảy ra.');
                }
            } catch (error) {
                errorDiv.classList.remove('hidden');
                errorDiv.innerText = 'Không thể kết nối đến máy chủ.';
            }
        });
    </script>
@endsection