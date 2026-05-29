<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Requests\Api\Profile\ChangePasswordRequest;

class ProfileController extends Controller
{
    // 1. Lấy thông tin hồ sơ
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();
        if ($user) {
            $user->loadCount(['followers', 'followings']);
        }
        
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
        $user = auth('api')->user();

        $data = $request->only([
            'name',
            'phone',
            'address',
            'province_id',
            'province_name',
            'ward_id',
            'ward_name'
        ]);

        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ nếu nó là file trong storage (thư mục images)
            if ($user->avatar && str_starts_with($user->avatar, '/storage/images/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }

            $file = $request->file('avatar');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('images', $filename, 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        // GỌI TRỰC TIẾP TỪ MODEL 
        User::where('id', $userId)->update($data);

        // Lấy lại thông tin user sau khi đã update để trả về cho Frontend
        $updatedUser = User::withCount(['followers', 'followings'])->find($userId);

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

    // 4. Lấy thông tin hồ sơ công khai của người bán
    public function showPublic($id)
    {
        $user = User::withCount(['followers', 'followings'])->findOrFail($id);

        $user->is_followed = false;
        if (auth('api')->check()) {
            /** @var \App\Models\User $currentUser */
            $currentUser = auth('api')->user();
            $user->is_followed = $currentUser->isFollowing($id);
        }

        // Đếm tổng số lượng
        $activeCount = Post::where('user_id', $id)->where('status', 'active')->count();
        $soldCount = Post::where('user_id', $id)->where('status', 'sold')->count();

        // Get posts based on status filter (default to active)
        $status = request('status', 'active');
        $posts = Post::with(['category', 'images'])
            ->where('user_id', $id)
            ->where('status', $status)
            ->latest()
            ->paginate(6);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'active_count' => $activeCount,
                'sold_count' => $soldCount,
                'posts' => $posts
            ]
        ]);
    }
}
