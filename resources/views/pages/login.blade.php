@extends('layouts.auth')

@section('title', 'Đăng nhập hệ thống - Chợ Đồ Cũ')

@section('content')
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <!-- Đã sửa class ở dòng dưới này thành text-3xl font-headline cho đồng bộ -->
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Đăng nhập
            </h1>
            <p class="text-body-lg text-on-surface-variant">Chào mừng bạn quay trở lại.</p>
        </div>

        <form id="login-form" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Địa chỉ Email</span>
                    <input id="login-email"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập email của bạn" required="" type="email" />
                </label>
                <label class="flex flex-col relative">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-label-md font-bold text-primary tracking-wide uppercase">Mật khẩu</span>
                        <a href="/forgot-password"
                            class="text-sm font-bold text-primary hover:text-secondary transition-colors" tabindex="-1">
                            Quên mật khẩu?
                        </a>
                    </div>
                    <input id="login-password"
                        class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none"
                        placeholder="Nhập mật khẩu" required="" type="password" />
                </label>
            </div>

            <div id="login-error" class="text-error text-sm hidden font-bold"></div>

            <button
                class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm hover:shadow-md transition-shadow flex items-center justify-center gap-2"
                type="submit">
                <span>Đăng nhập</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Chưa có tài khoản? <a class="font-bold text-primary hover:text-secondary transition-colors underline"
                href="/register">Đăng ký ngay</a>
        </p>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const errorDiv = document.getElementById('login-error');

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });
                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('access_token', data.access_token);
                    alert('Đăng nhập thành công!');
                    window.location.href = '/';
                } else {
                    errorDiv.classList.remove('hidden');
                    errorDiv.innerText = data.errors ? Object.values(data.errors)[0][0] : (data.error ||
                        'Đăng nhập thất bại.');
                }
            } catch (error) {
                errorDiv.classList.remove('hidden');
                errorDiv.innerText = 'Lỗi kết nối máy chủ.';
            }
        });
    </script>
@endsection