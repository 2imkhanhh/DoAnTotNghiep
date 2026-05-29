<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // Lấy danh sách người dùng cho Admin
    public function index(Request $request)
    {
        $query = User::query();

        // Tìm kiếm theo tên hoặc email
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Eager load các relation dùng cho thuộc tính appended (tránh lỗi N+1)
        $query->with(['posts', 'receivedReviews'])->withCount('posts');

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['success' => true, 'data' => $users]);
    }

    // Nâng quyền / Hạ quyền
    public function toggleRole($id)
    {
        $user = User::findOrFail($id);
        
        // Không cho phép Admin tự hạ quyền chính mình
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Không thể tự thay đổi quyền của chính mình.'], 403);
        }

        $user->role = $user->role === 1 ? 0 : 1;
        $user->save();

        return response()->json(['success' => true, 'data' => $user, 'message' => 'Thay đổi quyền thành công.']);
    }

    // Khóa / Mở khóa tài khoản
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Không cho phép Admin tự khóa chính mình
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.'], 403);
        }

        $user->status = $user->status === 1 ? 0 : 1;
        $user->save();

        return response()->json(['success' => true, 'data' => $user, 'message' => 'Thay đổi trạng thái tài khoản thành công.']);
    }
}

