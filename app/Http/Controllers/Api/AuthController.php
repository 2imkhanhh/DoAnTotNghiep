<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RefreshToken; // <-- Thêm Model mới
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // <-- Thêm thư viện tạo chuỗi
use Carbon\Carbon; // <-- Thêm thư viện xử lý thời gian
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'available_post_quota' => 3,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công. Vui lòng đăng nhập để tiếp tục.'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        // 1. Kiểm tra tài khoản và lấy Access Token(access token trong 60p)
        if (!$accessToken = $auth->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'error' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        $user = $auth->user();

        // 2. Tự nặn ra một chuỗi Refresh Token ngẫu nhiên (64 ký tự)
        $refreshTokenString = Str::random(64);

        // 3. Lưu Refresh Token vào Database (Sống 14 ngày)
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshTokenString,
            'expires_at' => Carbon::now()->addDays(14)
        ]);

        return $this->respondWithToken($accessToken, $refreshTokenString, 'Đăng nhập thành công');
    }

    public function me()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');
        return response()->json($auth->user());
        // user() là phương thức có sẵn của laravel dùng để lấy dữ liệu
    }

    public function logout(Request $request)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        // 1. Đưa Access Token hiện tại vào Blacklist của Tymon
        $auth->logout();

        // 2. Xóa Refresh Token trong Database (nếu Frontend có gửi lên)
        $refreshToken = $request->input('refresh_token');
        if ($refreshToken) {
            RefreshToken::where('token', $refreshToken)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công']);
    }

    public function refresh(Request $request)
    {
        $requestToken = $request->input('refresh_token');

        if (!$requestToken) {
            return response()->json(['success' => false, 'error' => 'Thiếu Refresh Token'], 400);
        }

        // 1. Tìm token trong DB
        $refreshToken = RefreshToken::where('token', $requestToken)->first();

        // 2. Kiểm tra xem Token có tồn tại và còn hạn không
        if (!$refreshToken || $refreshToken->expires_at < Carbon::now()) {
            return response()->json([
                'success' => false,
                'error' => 'Refresh token không hợp lệ hoặc đã hết hạn. Vui lòng đăng nhập lại.'
            ], 401);
        }

        $user = $refreshToken->user;

        // 3. Tạo Access Token mới cứng
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');
        $newAccessToken = $auth->login($user);

        // 4. Xoay vòng (Rotation): Tạo Refresh Token mới và đè lên cái cũ trong DB
        $newRefreshTokenString = Str::random(64);
        $refreshToken->update([
            'token' => $newRefreshTokenString,
            'expires_at' => Carbon::now()->addDays(14)
        ]);

        return $this->respondWithToken($newAccessToken, $newRefreshTokenString, 'Làm mới token thành công');
    }

    // Cập nhật hàm này để nhận thêm $refreshToken
    protected function respondWithToken($accessToken, $refreshToken, $message)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        return response()->json([
            'success' => true,
            'message' => $message,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken, // Trả về Refresh Token cho Frontend lưu
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60,
            'user' => $auth->user()
        ]);
    }
}
