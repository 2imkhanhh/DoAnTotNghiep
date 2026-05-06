@extends('layouts.auth')

@section('title', 'Quên mật khẩu - Chợ Đồ Cũ')

@section('content')
    <div class="bg-surface-container-low rounded-xl p-8 sm:p-12 relative shadow-[0_32px_64px_rgba(28,27,31,0.06)]">
        <div class="mb-10">
            <h1 class="text-3xl font-headline text-primary font-extrabold tracking-[-0.02em] leading-tight mb-2">Quên mật khẩu</h1>
            <p class="text-body-lg text-on-surface-variant">Đừng lo lắng! Vui lòng nhập email của bạn, chúng tôi sẽ gửi liên kết khôi phục.</p>
        </div>

        <form id="forgot-password-form" class="flex flex-col gap-6">
            <div class="flex flex-col gap-4">
                <label class="flex flex-col">
                    <span class="text-label-md font-bold text-primary mb-2 tracking-wide uppercase">Địa chỉ Email đã đăng ký</span>
                    <input id="forgot-email" class="w-full h-14 bg-surface-container-highest border-none rounded-lg px-4 py-3 focus:ring-0 focus:outline-none" placeholder="Nhập email của bạn" required="" type="email"/>
                </label>
            </div>
            
            <div id="status-message" class="text-sm hidden font-bold p-4 rounded-lg"></div>

            <button id="submit-btn" class="w-full h-14 mt-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-lg rounded-lg shadow-sm flex items-center justify-center gap-2" type="submit">
                <span>Gửi liên kết khôi phục</span>
                <span class="material-symbols-outlined">mark_email_read</span>
            </button>
        </form>
        
        <p class="mt-10 text-center text-body-md text-on-surface-variant">
            Nhớ ra mật khẩu rồi? <a class="font-bold text-primary hover:text-secondary transition-colors underline" href="/login">Đăng nhập</a>
        </p>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('forgot-password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('forgot-email').value;
            const statusDiv = document.getElementById('status-message');
            const btn = document.getElementById('submit-btn');

            btn.innerHTML = '<span>Đang gửi...</span><span class="material-symbols-outlined animate-spin">sync</span>';
            btn.style.opacity = '0.7';
            btn.disabled = true;

            try {
                const response = await fetch('/api/forgot-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();
                statusDiv.classList.remove('hidden', 'bg-error-container', 'text-error', 'bg-[#d1e7dd]', 'text-[#0f5132]');
                
                if (response.ok) {
                    statusDiv.classList.add('bg-[#d1e7dd]', 'text-[#0f5132]');
                    statusDiv.innerText = 'Thành công! Vui lòng kiểm tra hòm thư Email của bạn.';
                    document.getElementById('forgot-email').value = '';
                } else {
                    statusDiv.classList.add('bg-error-container', 'text-error');
                    statusDiv.innerText = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Có lỗi xảy ra.');
                }
            } catch (error) {
                statusDiv.classList.remove('hidden');
                statusDiv.classList.add('bg-error-container', 'text-error');
                statusDiv.innerText = 'Không thể kết nối đến máy chủ.';
            } finally {
                btn.innerHTML = '<span>Gửi liên kết khôi phục</span><span class="material-symbols-outlined">mark_email_read</span>';
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        });
    </script>
@endsection