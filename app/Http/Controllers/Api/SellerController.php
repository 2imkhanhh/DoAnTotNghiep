<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Order;

class SellerController extends Controller
{
    /**
     * Get statistics for the seller dashboard.
     */
    public function dashboardStats(Request $request)
    {
        $userId = Auth::id();

        // 1. Post statistics
        $totalPosts = Post::where('user_id', $userId)->count();
        $activePosts = Post::where('user_id', $userId)->where('status', 'active')->count(); // 1: Đang hiển thị
        $pendingPosts = Post::where('user_id', $userId)->where('status', 'pending')->count(); // 0: Chờ duyệt
        $soldPosts = Post::where('user_id', $userId)->where('status', 'sold')->count(); // 2: Đã bán

        // 2. Order statistics (where user is the seller)
        $totalOrders = Order::where('seller_id', $userId)->count();
        $completedOrders = Order::where('seller_id', $userId)->where('status', 'delivered')->count();
        $tradingOrders = Order::where('seller_id', $userId)->where('status', 'shipping')->count();
        $requestedOrders = Order::where('seller_id', $userId)->where('status', 'pending')->count();

        // 3. Estimated Revenue (Sum of completed Orders' post price)
        $revenue = Order::where('seller_id', $userId)
            ->where('Orders.status', 'delivered')
            ->join('posts', 'Orders.post_id', '=', 'posts.id')
            ->sum('posts.price');

        return response()->json([
            'success' => true,
            'data' => [
                'posts' => [
                    'total' => $totalPosts,
                    'active' => $activePosts,
                    'pending' => $pendingPosts,
                    'sold' => $soldPosts,
                ],
                'Orders' => [
                    'total' => $totalOrders,
                    'completed' => $completedOrders,
                    'trading' => $tradingOrders,
                    'requested' => $requestedOrders,
                ],
                'revenue' => $revenue
            ]
        ]);
    }

    /**
     * Get list of Orders where user is seller.
     */
    public function Orders(Request $request)
    {
        $userId = Auth::id();
        $status = $request->query('status');

        $query = Order::where('seller_id', $userId)
            ->with(['buyer', 'post.images']) // Load related info
            ->orderBy('created_at', 'desc');
            
        if ($status) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        }

        $Orders = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $Orders
        ]);
    }
}

