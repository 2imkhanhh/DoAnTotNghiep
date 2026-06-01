<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $now = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Tính % User (Hôm nay so với hôm qua)
        $usersToday = User::whereDate('created_at', $now)->count();
        $usersYesterday = User::whereDate('created_at', $yesterday)->count();
        $usersPercentChange = $usersYesterday > 0 ? round((($usersToday - $usersYesterday) / $usersYesterday) * 100, 1) : ($usersToday > 0 ? 100 : 0);

        // Tính % Tin đăng hiển thị (Hôm nay so với hôm qua)
        $postsToday = Post::where('status', 'active')->whereDate('created_at', $now)->count();
        $postsYesterday = Post::where('status', 'active')->whereDate('created_at', $yesterday)->count();
        $postsPercentChange = $postsYesterday > 0 ? round((($postsToday - $postsYesterday) / $postsYesterday) * 100, 1) : ($postsToday > 0 ? 100 : 0);

        // Tính % Đơn hàng thành công (Hôm nay so với hôm qua)
        $ordersToday = Order::where('status', 'delivered')->whereDate('created_at', $now)->count();
        $ordersYesterday = Order::where('status', 'delivered')->whereDate('created_at', $yesterday)->count();
        $ordersPercentChange = $ordersYesterday > 0 ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100, 1) : ($ordersToday > 0 ? 100 : 0);

        // Thống kê cơ bản
        $stats = [
            'users' => User::count(),
            'users_percent' => $usersPercentChange,
            'active_posts' => Post::where('status', 'active')->count(),
            'posts_percent' => $postsPercentChange,
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'orders_percent' => $ordersPercentChange,
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
                    'user_avatar' => $post->user ? $post->user->avatar : null,
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
                    'avatar' => $user->avatar,
                    'post_count' => $user->posts_count,
                    'rating' => $user->average_rating // Lấy số sao đánh giá thực tế
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
