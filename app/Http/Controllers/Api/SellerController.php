<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Transaction;

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
        $activePosts = Post::where('user_id', $userId)->where('status', 1)->count(); // 1: Đang hiển thị
        $pendingPosts = Post::where('user_id', $userId)->where('status', 0)->count(); // 0: Chờ duyệt
        $soldPosts = Post::where('user_id', $userId)->where('status', 2)->count(); // 2: Đã bán

        // 2. Transaction statistics (where user is the seller)
        $totalTransactions = Transaction::where('seller_id', $userId)->count();
        $completedTransactions = Transaction::where('seller_id', $userId)->where('status', 'completed')->count();
        $tradingTransactions = Transaction::where('seller_id', $userId)->where('status', 'trading')->count();
        $requestedTransactions = Transaction::where('seller_id', $userId)->where('status', 'requested')->count();

        // 3. Estimated Revenue (Sum of completed transactions' post price)
        $revenue = Transaction::where('seller_id', $userId)
            ->where('transactions.status', 'completed')
            ->join('posts', 'transactions.post_id', '=', 'posts.id')
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
                'transactions' => [
                    'total' => $totalTransactions,
                    'completed' => $completedTransactions,
                    'trading' => $tradingTransactions,
                    'requested' => $requestedTransactions,
                ],
                'revenue' => $revenue
            ]
        ]);
    }

    /**
     * Get list of transactions where user is seller.
     */
    public function transactions(Request $request)
    {
        $userId = Auth::id();
        $status = $request->query('status');

        $query = Transaction::where('seller_id', $userId)
            ->with(['buyer', 'post.images']) // Load related info
            ->orderBy('created_at', 'desc');
            
        if ($status && in_array($status, ['requested', 'trading', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        $transactions = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
