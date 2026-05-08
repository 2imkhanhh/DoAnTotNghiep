<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\ResetPassword;
use App\Http\Requests\Api\Auth\SendResetLinkRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;

class ResetPasswordController extends Controller
{
    // 1. Hàm nhận Email và gửi link
    public function sendResetLink(SendResetLinkRequest $request)
    {
        // Tạo token ngẫu nhiên
        $token = Str::random(60);

        // Lưu vào bảng password_reset_tokens của Laravel
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email
        Mail::to($request->email)->send(new ResetPassword($token, $request->email));

        return response()->json([
            'success' => true,
            'message' => 'Vui lòng kiểm tra hòm thư Email để đặt lại mật khẩu.'
        ]);
    }

    // 2. Hàm nhận thông tin từ Frontend để cập nhật mật khẩu mới
    public function resetPassword(ResetPasswordRequest $request)
    {

        // Kiểm tra xem token và email có khớp trong DB không
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'error' => 'Mã xác thực không hợp lệ hoặc đã hết hạn.'
            ], 400);
        }

        // Cập nhật mật khẩu mới cho User
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Xóa token vừa dùng để không cho dùng lại lần 2
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.'
        ]);
    }
}
