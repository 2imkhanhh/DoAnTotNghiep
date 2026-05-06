@extends('layouts.auth')

@section('title', 'Tạo mật khẩu mới - Chợ Đồ Cũ')

@section('content')
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Tạo mật khẩu mới</h1>
            <p id="email-display" class="text-body-lg text-on-surface-variant">Đang tải thông tin...</p>
        </div>

        <form id="reset-password-form" class="flex flex-col gap-6 hidden">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Mật khẩu mới</span>
                    <input id="new-password" class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" placeholder="Nhập mật khẩu mới" required="" type="password"/>
                </label>
                <label class="flex flex-col relative">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Xác nhận mật khẩu mới</span>
                    <input id="confirm-password" class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" placeholder="Nhập lại mật khẩu mới" required="" type="password"/>
                </label>
            </div>
            
            <div id="reset-error" class="text-error text-sm hidden font-bold"></div>

            <button class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm flex items-center justify-center gap-2" type="submit">
                <span>Cập nhật mật khẩu</span>
                <span class="material-symbols-outlined">lock_reset</span>
            </button>
        </form>

        <div id="invalid-link-msg" class="hidden text-center">
            <span class="material-symbols-outlined text-error text-5xl mb-2">error</span>
            <p class="text-error font-bold mb-4">Đường dẫn không hợp lệ hoặc thiếu thông tin.</p>
            <a href="/forgot-password" class="text-primary underline">Quay lại trang quên mật khẩu</a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');

        const form = document.getElementById('reset-password-form');
        const invalidMsg = document.getElementById('invalid-link-msg');
        const emailDisplay = document.getElementById('email-display');

        if (token && email) {
            form.classList.remove('hidden');
            emailDisplay.innerHTML = `Cho tài khoản: <b class="text-primary">${email}</b>`;
        } else {
            invalidMsg.classList.remove('hidden');
            emailDisplay.innerText = '';
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const password = document.getElementById('new-password').value;
            const password_confirmation = document.getElementById('confirm-password').value;
            const errorDiv = document.getElementById('reset-error');

            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        email: email,             
                        token: token,             
                        password: password,       
                        password_confirmation: password_confirmation
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Đổi mật khẩu thành công! Bạn sẽ được chuyển hướng về trang Đăng nhập.');
                    window.location.href = '/login'; 
                } else {
                    errorDiv.classList.remove('hidden');
                    errorDiv.innerText = data.errors ? Object.values(data.errors)[0][0] : (data.error || 'Đổi mật khẩu thất bại.');
                }
            } catch (error) {
                errorDiv.classList.remove('hidden');
                errorDiv.innerText = 'Không thể kết nối đến máy chủ.';
            }
        });
    </script>
@endsection