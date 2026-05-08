<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Requests\Api\Profile\ChangePasswordRequest;

class ProfileController extends Controller
{
    // 1. Lấy thông tin hồ sơ
    public function show()
    {
        $user = auth('api')->user();
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    // 2. Cập nhật hồ sơ cá nhân
    public function update(UpdateProfileRequest $request)
    {
        // Lấy ID của user đang đăng nhập
        $userId = auth('api')->id();

        // GỌI TRỰC TIẾP TỪ MODEL 
        User::where('id', $userId)->update($request->only(['name', 'phone', 'address', 'avatar']));

        // Lấy lại thông tin user sau khi đã update để trả về cho Frontend
        $updatedUser = User::find($userId);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $updatedUser
        ]);
    }

    // 3. Đổi mật khẩu
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['Mật khẩu hiện tại không đúng.']]
            ], 400);
        }

        // GỌI TRỰC TIẾP TỪ MODEL:
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }
}
