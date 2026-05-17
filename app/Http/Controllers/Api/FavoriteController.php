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

        // Chặn không cho phép tự thích bài viết của chính mình
        if ($post->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể yêu thích tin đăng của chính mình'
            ], 400);
        }

        // Kiểm tra xem bài viết đã được yêu thích chưa
        $isFavorited = $user->favoritePosts()->where('post_id', $postId)->exists();

        // Nếu chưa yêu thích (sẽ thêm mới sau khi toggle), kiểm tra giới hạn 50 tin
        if (!$isFavorited) {
            $favoritesCount = $user->favoritePosts()->count();
            if ($favoritesCount >= 50) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ có thể lưu tối đa 50 tin yêu thích'
                ], 400);
            }
        }

        // toggle() sẽ tự động: Nếu có rồi thì xóa, chưa có thì thêm vào
        $user->favoritePosts()->toggle($postId);

        // Kiểm tra lại trạng thái sau khi toggle
        $isFavoritedAfter = $user->favoritePosts()->where('post_id', $postId)->exists();

        return response()->json([
            'success' => true,
            'message' => $isFavoritedAfter ? 'Đã thêm vào mục yêu thích' : 'Đã bỏ yêu thích',
            'is_favorited' => $isFavoritedAfter
        ]);
    }

    // API Lấy danh sách tin đã yêu thích của user đang đăng nhập
    public function getFavorites(Request $request)
    {
        $user = $request->user();

        // Lấy danh sách kèm theo ảnh đại diện của bài viết và thông tin người đăng
        $favorites = $user->favoritePosts()
            ->with(['images', 'user'])
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }
}
