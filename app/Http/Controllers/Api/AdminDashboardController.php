<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function stats(Request $request)
    {
        // Thống kê cơ bản
        $stats = [
            'users' => User::count(),
            'active_posts' => Post::where('status', 'active')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'reports' => 0 // Có thể tích hợp bảng Report sau
        ];

        // Tin đăng chờ duyệt
        $recentPosts = Post::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'user_name' => $post->user ? $post->user->name : 'N/A',
                    'title' => $post->title,
                    'price' => $post->price,
                    'created_at' => $post->created_at,
                ];
            });

        // Top người dùng tích cực (nhiều bài đăng)
        $topUsers = User::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'post_count' => $user->posts_count,
                    'rating' => 5.0 // Có thể tích hợp rating thực tế sau
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recentPosts' => $recentPosts,
                'topUsers' => $topUsers,
            ]
        ]);
    }
}
