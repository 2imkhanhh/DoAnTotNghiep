<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Người mua checkout (Tạo đơn hàng)
    public function checkout(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'shipping_name' => 'required|string',
            'shipping_phone' => ['required', 'string', 'regex:/^(84|0[3|5|7|8|9])+([0-9]{8})$/'],
            'shipping_address' => 'required|string',
            'shipping_province_id' => 'required',
            'shipping_ward_id' => 'required',
            'shipping_note' => 'nullable|string',
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->user_id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể tự mua bài đăng của chính mình.'], 403);
        }

        if ($post->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Bài đăng này không thể mua được (có thể đã bán hoặc đang giao dịch).'], 400);
        }

        // Kiểm tra xem người dùng đã đặt đơn hàng này chưa (tránh spam)
        $existingOrder = Order::where('post_id', $post->id)
            ->where('buyer_id', Auth::id())
            ->whereIn('status', ['pending', 'confirmed', 'shipping'])
            ->exists();

        if ($existingOrder) {
            return response()->json(['success' => false, 'message' => 'Bạn đã đặt mua sản phẩm này rồi, vui lòng chờ người bán xử lý.'], 400);
        }

        $Order = Order::create([
            'post_id' => $post->id,
            'seller_id' => $post->user_id,
            'buyer_id' => Auth::id(),
            'status' => 'pending',
            'shipping_name' => $request->shipping_name,
            'shipping_phone' => $request->shipping_phone,
            'shipping_address' => $request->shipping_address,
            'shipping_province_id' => $request->shipping_province_id,
            'shipping_ward_id' => $request->shipping_ward_id,
            'shipping_note' => $request->shipping_note,
            'total_price' => $post->price,
        ]);

        return response()->json(['success' => true, 'data' => $Order, 'message' => 'Đặt hàng thành công!']);
    }

    // Lấy danh sách đơn hàng người mua đã đặt
    public function buyerOrders(Request $request)
    {
        $userId = Auth::id();
        $status = $request->query('status');

        $query = Order::where('buyer_id', $userId)
            ->with(['seller', 'post.images', 'review']) // Load related info
            ->orderBy('created_at', 'desc');
            
        if ($status) {
            $query->where('status', $status);
        }

        $Orders = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $Orders
        ]);
    }

    // Người bán duyệt đơn (Chấp nhận)
    public function acceptOrder(Request $request, $id)
    {
        $Order = Order::findOrFail($id);

        if ($Order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện trên giao dịch này.'], 403);
        }

        if ($Order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Giao dịch không ở trạng thái chờ duyệt.'], 400);
        }

        // Kiểm tra xem bài đăng có giao dịch nào đang ở trạng thái shipping không
        $existing = Order::where('post_id', $Order->post_id)
            ->whereIn('status', ['confirmed', 'shipping', 'delivered'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm này đã được bán hoặc đang giao cho người khác.'], 400);
        }

        DB::transaction(function () use ($Order) {
            $Order->update(['status' => 'confirmed']);

            // Đổi trạng thái bài đăng thành Tạm ẩn (hidden) để ẩn khỏi trang chủ
            $post = Post::find($Order->post_id);
            if ($post) {
                $post->update(['status' => 'hidden']);
            }
            
            // Từ chối tất cả các giao dịch pending khác cho sản phẩm này
            Order::where('post_id', $Order->post_id)
                ->where('id', '!=', $Order->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        });

        return response()->json(['success' => true, 'message' => 'Đã duyệt đơn và chuyển sang Đã xác nhận.', 'data' => $Order]);
    }

    // Người bán bắt đầu giao hàng
    public function startShipping(Request $request, $id)
    {
        $Order = Order::findOrFail($id);

        if ($Order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền.'], 403);
        }

        if ($Order->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa được xác nhận.'], 400);
        }

        $Order->update(['status' => 'shipping']);

        return response()->json(['success' => true, 'message' => 'Đã chuyển sang Đang giao hàng.', 'data' => $Order]);
    }

    // Người bán từ chối đơn
    public function rejectOrder(Request $request, $id)
    {
        $Order = Order::findOrFail($id);

        if ($Order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền.'], 403);
        }

        if ($Order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể từ chối đơn đang chờ duyệt.'], 400);
        }

        $Order->update(['status' => 'rejected']);

        return response()->json(['success' => true, 'message' => 'Đã từ chối đơn hàng.', 'data' => $Order]);
    }

    // Người bán xác nhận đã giao hàng thành công
    public function deliverOrder(Request $request, $id)
    {
        $Order = Order::findOrFail($id);

        if ($Order->seller_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền.'], 403);
        }

        if ($Order->status !== 'shipping') {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa ở trạng thái đang giao.'], 400);
        }

        DB::transaction(function () use ($Order) {
            $Order->update(['status' => 'delivered']);

            // Đổi trạng thái bài đăng thành Đã bán (sold)
            $post = Post::find($Order->post_id);
            if ($post) {
                $post->update(['status' => 'sold']);
            }
        });

        return response()->json(['success' => true, 'message' => 'Đã xác nhận giao hàng thành công.', 'data' => $Order]);
    }

    // Người mua / Người bán hủy đơn (khi pending hoặc confirmed)
    public function cancelOrder(Request $request, $id)
    {
        $Order = Order::findOrFail($id);
        $userId = Auth::id();

        if ($Order->buyer_id !== $userId && $Order->seller_id !== $userId) {
            return response()->json(['success' => false, 'message' => 'Không có quyền hủy giao dịch này.'], 403);
        }

        if (!in_array($Order->status, ['pending', 'confirmed'])) {
            return response()->json(['success' => false, 'message' => 'Không thể hủy đơn hàng đang giao hoặc đã giao.'], 400);
        }

        $wasConfirmed = $Order->status === 'confirmed';

        DB::transaction(function () use ($Order, $wasConfirmed) {
            $Order->update(['status' => 'cancelled']);

            // Khôi phục bài đăng về hiển thị nếu đã từng tạm ẩn
            if ($wasConfirmed) {
                $post = Post::find($Order->post_id);
                if ($post && $post->status === 'hidden') {
                    $post->update(['status' => 'active']);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng thành công.', 'data' => $Order]);
    }
}
