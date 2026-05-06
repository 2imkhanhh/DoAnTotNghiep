<!DOCTYPE html>
<html>
<head>
    <title>Khôi phục mật khẩu</title>
</head>
<body>
    <h2>Xin chào!</h2>
    <p>Bạn nhận được email này vì chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.</p>
    
    <!-- Lưu ý: Đường link này phải trỏ về URL của FRONTEND (Ví dụ: React/Vue/HTML đang chạy ở port 3000) -->
    @php
        $resetUrl = "http://127.0.0.1:8000/reset-password?token=" . $token . "&email=" . $email;
    @endphp

    <a href="{{ $resetUrl }}" style="padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
        Bấm vào đây để đặt lại mật khẩu
    </a>

    <p>Liên kết này sẽ hết hạn trong 60 phút.</p>
    <p>Nếu bạn không yêu cầu đổi mật khẩu, vui lòng bỏ qua email này.</p>
</body>
</html>