<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request, $postId)
    {
        // Kiểm tra bài viết có tồn tại không
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bài viết'], 404);
        }

        $user = $request->user();

        // toggle() sẽ tự động: Nếu có rồi thì xóa, chưa có thì thêm vào
        $user->favoritePosts()->toggle($postId);

        // Kiểm tra lại trạng thái sau khi toggle
        $isFavorited = $user->favoritePosts()->where('post_id', $postId)->exists();

        return response()->json([
            'success' => true,
            'message' => $isFavorited ? 'Đã thêm vào mục yêu thích' : 'Đã bỏ yêu thích',
            'is_favorited' => $isFavorited
        ]);
    }

    // API Lấy danh sách tin đã yêu thích của user đang đăng nhập
    public function getFavorites(Request $request)
    {
        $user = $request->user();

        // Lấy danh sách kèm theo ảnh đại diện của bài viết
        $favorites = $user->favoritePosts()->with('images')->orderBy('favorites.created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }
}
