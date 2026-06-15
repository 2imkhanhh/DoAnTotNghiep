<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // ================= CHART DATA =================
        $trendPeriod = $request->query('trend_period', '7days');
        $categoryPeriod = $request->query('category_period', '7days');
        $orderPeriod = $request->query('order_period', '7days');

        [$trendStart, $trendEnd, $trendGroup] = $this->getTimeBounds($trendPeriod);
        [$catStart, $catEnd, $catGroup] = $this->getTimeBounds($categoryPeriod);
        [$orderStart, $orderEnd, $orderGroup] = $this->getTimeBounds($orderPeriod);

        // 1. Dữ liệu xu hướng (Line Chart)

        $trendData = [
            'labels' => [],
            'users' => [],
            'posts' => [],
            'orders' => []
        ];

        if ($trendGroup === 'day') {
            $currentDate = clone $trendStart;
            $daysList = [];
            while ($currentDate <= Carbon::today()) {
                $dateStr = $currentDate->format('Y-m-d');
                $daysList[$dateStr] = [
                    'label' => $currentDate->format('d/m'),
                    'users' => 0,
                    'posts' => 0,
                    'orders' => 0
                ];
                $currentDate->addDay();
            }

            $usersRaw = User::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')->pluck('count', 'date')->toArray();
            $postsRaw = Post::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')->pluck('count', 'date')->toArray();
            $ordersRaw = Order::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')->pluck('count', 'date')->toArray();

            foreach ($daysList as $dateStr => $data) {
                $trendData['labels'][] = $data['label'];
                $trendData['users'][] = $usersRaw[$dateStr] ?? 0;
                $trendData['posts'][] = $postsRaw[$dateStr] ?? 0;
                $trendData['orders'][] = $ordersRaw[$dateStr] ?? 0;
            }
        } else {
            // this_year, group by month
            $currentMonth = clone $trendStart;
            $monthsList = [];
            while ($currentMonth <= Carbon::today()) {
                $monthStr = $currentMonth->format('Y-m');
                $monthsList[$monthStr] = [
                    'label' => 'Tháng ' . $currentMonth->format('n'),
                ];
                $currentMonth->addMonth();
            }

            $usersRaw = User::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')->pluck('count', 'month')->toArray();
            $postsRaw = Post::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')->pluck('count', 'month')->toArray();
            $ordersRaw = Order::whereBetween('created_at', [$trendStart, $trendEnd])
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                ->groupBy('month')->pluck('count', 'month')->toArray();

            foreach ($monthsList as $monthStr => $data) {
                $trendData['labels'][] = $data['label'];
                $trendData['users'][] = $usersRaw[$monthStr] ?? 0;
                $trendData['posts'][] = $postsRaw[$monthStr] ?? 0;
                $trendData['orders'][] = $ordersRaw[$monthStr] ?? 0;
            }
        }

        // 2. Dữ liệu cơ cấu danh mục (Doughnut Chart)
        $categories = Category::withCount(['posts' => function ($q) use ($catStart, $catEnd) {
            $q->whereBetween('created_at', [$catStart, $catEnd]);
        }])->having('posts_count', '>', 0)->get();

        $categoryData = [
            'labels' => $categories->pluck('name')->toArray(),
            'data' => $categories->pluck('posts_count')->toArray(),
        ];

        // 3. Dữ liệu trạng thái đơn hàng (Bar Chart)
        $orderStatuses = Order::whereBetween('created_at', [$orderStart, $orderEnd])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $orderStatusData = [
            'pending' => $orderStatuses['pending'] ?? 0,
            'confirmed' => $orderStatuses['confirmed'] ?? 0,
            'shipping' => $orderStatuses['shipping'] ?? 0,
            'delivered' => $orderStatuses['delivered'] ?? 0,
            'cancelled' => ($orderStatuses['cancelled'] ?? 0) + ($orderStatuses['rejected'] ?? 0),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recentPosts' => $recentPosts,
                'topUsers' => $topUsers,
                'charts' => [
                    'trend' => $trendData,
                    'category' => $categoryData,
                    'orderStatus' => $orderStatusData
                ]
            ]
        ]);
    }

    private function getTimeBounds($period)
    {
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today()->endOfDay();
        $groupBy = 'day';

        if ($period === '30days') {
            $startDate = Carbon::today()->subDays(29);
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
        } elseif ($period === 'this_year') {
            $startDate = Carbon::now()->startOfYear();
            $groupBy = 'month';
        }

        return [$startDate, $endDate, $groupBy];
    }

    public function sidebarStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending_posts' => Post::where('status', 'pending')->count()
            ]
        ]);
    }
}
