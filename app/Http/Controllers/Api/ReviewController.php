<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Lấy danh sách reviews của 1 user
    public function index($userId)
    {
        $reviews = Review::where('reviewed_user_id', $userId)
            ->with(['reviewer' => function($q) {
                $q->select('id', 'name', 'avatar');
            }, 'Order.post.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    // Tạo đánh giá mới (người mua đánh giá người bán)
    public function store(Request $request, $userId)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $Order = Order::findOrFail($request->order_id);

        // Đảm bảo đúng người mua
        if ($Order->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Chỉ người mua mới được quyền đánh giá.'], 403);
        }

        // Đảm bảo đúng người bán
        if ($Order->seller_id != $userId) {
            return response()->json(['success' => false, 'message' => 'ID người bán không hợp lệ.'], 400);
        }

        // Đảm bảo giao dịch đã hoàn thành
        if ($Order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Chưa thể đánh giá vì giao dịch chưa hoàn thành.'], 400);
        }

        // Đảm bảo chưa review lần nào cho giao dịch này
        $existing = Review::where('order_id', $Order->id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Bạn đã đánh giá giao dịch này rồi.'], 400);
        }

        $review = Review::create([
            'order_id' => $Order->id,
            'reviewer_id' => Auth::id(),
            'reviewed_user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'data' => $review]);
    }

    // Chỉnh sửa đánh giá (trong 24h)
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review = Review::findOrFail($id);

        if ($review->reviewer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        // Kiểm tra trong vòng 24h
        if ($review->created_at->diffInHours(now()) > 24) {
            return response()->json(['success' => false, 'message' => 'Đã quá 24 giờ, bạn không thể chỉnh sửa đánh giá nữa.'], 400);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'data' => $review]);
    }
}
