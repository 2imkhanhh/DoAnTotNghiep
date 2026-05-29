<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * Lấy danh sách các cuộc hội thoại của user đang đăng nhập.
     */
    public function index()
    {
        $userId = auth()->id();

        $conversations = Conversation::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->with(['buyer', 'seller', 'post.images', 'post.orders', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $data = $conversations->map(function ($conversation) use ($userId) {
            // Xác định ai là đối phương trong cuộc trò chuyện
            $partner = $conversation->buyer_id === $userId ? $conversation->seller : $conversation->buyer;

            // Tính số tin nhắn chưa đọc gửi đến user hiện tại
            $unreadCount = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->count();

            // Định dạng bài viết liên quan
            $post = null;
            if ($conversation->post) {
                $primaryImage = $conversation->post->images->first();
                $post = [
                    'id' => $conversation->post->id,
                    'title' => $conversation->post->title,
                    'slug' => $conversation->post->slug,
                    'price' => $conversation->post->price,
                    'user_id' => $conversation->post->user_id,
                    'status' => $conversation->post->status,
                    'image' => $primaryImage ? $primaryImage->image_path : null,
                    'orders' => $conversation->post->orders,
                ];
            }

            return [
                'id' => $conversation->id,
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'avatar' => $partner->avatar ? $partner->avatar : "https://ui-avatars.com/api/?name=" . urlencode($partner->name) . "&background=020037&color=fff",
                ],
                'post' => $post,
                'latest_message' => $conversation->latestMessage ? [
                    'id' => $conversation->latestMessage->id,
                    'message_text' => $conversation->latestMessage->message_text,
                    'sender_id' => $conversation->latestMessage->sender_id,
                    'is_read' => $conversation->latestMessage->is_read,
                    'created_at' => $conversation->latestMessage->created_at,
                ] : null,
                'unread_messages_count' => $unreadCount,
                'updated_at' => $conversation->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Khởi tạo hoặc tìm cuộc hội thoại giữa người mua và người bán về một bài viết.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        $userId = auth()->id();
        $postId = $request->post_id;

        $post = Post::findOrFail($postId);
        $sellerId = $post->user_id;

        // Không được tự nhắn tin cho chính mình
        if ($userId === $sellerId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự nhắn tin cho chính mình về bài đăng này.'
            ], 400);
        }

        // Tìm xem cuộc trò chuyện giữa 2 người này đã tồn tại chưa (gộp chat theo User chuẩn Chợ Tốt)
        $conversation = Conversation::where(function ($query) use ($userId, $sellerId) {
                $query->where('buyer_id', $userId)->where('seller_id', $sellerId);
            })
            ->orWhere(function ($query) use ($userId, $sellerId) {
                $query->where('buyer_id', $sellerId)->where('seller_id', $userId);
            })
            ->first();

        // Nếu chưa tồn tại cuộc trò chuyện nào giữa 2 người này, tạo mới
        if (!$conversation) {
            $conversation = Conversation::create([
                'buyer_id' => $userId,
                'seller_id' => $sellerId
                // Không lưu post_id vào conversation nữa
            ]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'message' => 'Cuộc trò chuyện đã sẵn sàng.'
        ]);
    }

    /**
     * Tải lịch sử tin nhắn trong một cuộc hội thoại.
     */
    public function messages($id)
    {
        $userId = auth()->id();
        $conversation = Conversation::findOrFail($id);

        // Kiểm tra xem user có quyền tham gia cuộc hội thoại không
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào cuộc hội thoại này.'
            ], 403);
        }

        // Lấy toàn bộ tin nhắn sắp xếp từ cũ đến mới kèm thông tin sản phẩm đính kèm
        $messages = Message::where('conversation_id', $id)
            ->with(['sender', 'post.images', 'post.orders.review'])
            ->orderBy('created_at', 'asc')
            ->get();

        $data = $messages->map(function ($message) {
            $postData = null;
            if ($message->post) {
                $primaryImage = $message->post->images->first();
                $postData = [
                    'id' => $message->post->id,
                    'title' => $message->post->title,
                    'slug' => $message->post->slug,
                    'price' => $message->post->price,
                    'user_id' => $message->post->user_id,
                    'status' => $message->post->status,
                    'image' => $primaryImage ? $primaryImage->image_path : null,
                    'orders' => $message->post->orders,
                ];
            }

            return [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'message_text' => $message->message_text,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
                'post' => $postData
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Gửi tin nhắn mới trong một cuộc hội thoại.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message_text' => 'required|string',
            'post_id' => 'nullable|exists:posts,id',
        ]);

        $userId = auth()->id();
        $conversation = Conversation::findOrFail($id);

        // Kiểm tra quyền
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền gửi tin nhắn trong cuộc hội thoại này.'
            ], 403);
        }

        // Tạo tin nhắn mới, có thể mang theo widget sản phẩm đính kèm
        $message = Message::create([
            'conversation_id' => $id,
            'sender_id' => $userId,
            'message_text' => $request->message_text,
            'is_read' => false,
            'post_id' => $request->has('post_id') ? $request->post_id : null
        ]);

        // Cập nhật lại thời gian updated_at của Conversation để đẩy lên đầu danh sách
        $conversation->touch();

        // Tải các thông tin liên quan để broadcast
        $message->load(['sender', 'post.images', 'post.orders.review']);

        // Phát sự kiện broadcast qua WebSockets
        broadcast(new MessageSent($message))->toOthers();

        // Định dạng dữ liệu bài viết mới nếu có
        $postData = null;
        if ($message->post) {
            $primaryImage = $message->post->images->first();
            $postData = [
                'id' => $message->post->id,
                'title' => $message->post->title,
                'slug' => $message->post->slug,
                'price' => $message->post->price,
                'user_id' => $message->post->user_id,
                'status' => $message->post->status,
                'image' => $primaryImage ? $primaryImage->image_path : null,
                'orders' => $message->post->orders,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_name' => auth()->user()->name,
                'message_text' => $message->message_text,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
                'post' => $postData
            ],
            // attached_post được trả về cho các client tương thích ngược
            'attached_post' => $postData
        ]);
    }

    /**
     * Đánh dấu toàn bộ tin nhắn trong cuộc trò chuyện là đã đọc.
     */
    public function markAsRead($id)
    {
        $userId = auth()->id();
        $conversation = Conversation::findOrFail($id);

        // Kiểm tra quyền
        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập.'
            ], 403);
        }

        // Cập nhật các tin nhắn được gửi bởi người kia thành đã đọc
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đã đọc.'
        ]);
    }

    /**
     * Lấy danh sách các giao dịch chung giữa 2 người dùng của cuộc hội thoại này
     */
    public function activeOrders($id)
    {
        $userId = auth()->id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->buyer_id !== $userId && $conversation->seller_id !== $userId) {
            return response()->json(['success' => false], 403);
        }

        $partnerId = $conversation->buyer_id === $userId ? $conversation->seller_id : $conversation->buyer_id;

        $orders = \App\Models\Order::where(function($q) use ($userId, $partnerId) {
                $q->where(function($query) use ($userId, $partnerId) {
                    $query->where('buyer_id', $userId)->where('seller_id', $partnerId);
                })->orWhere(function($query) use ($userId, $partnerId) {
                    $query->where('buyer_id', $partnerId)->where('seller_id', $userId);
                });
            })
            ->whereIn('status', ['requested', 'trading', 'completed'])
            ->with(['post.images', 'review'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->unique('post_id')
            ->values(); // Đảm bảo trả về mảng chuẩn (indexed array) cho Vue

        $data = $orders->map(function ($order) {
            $primaryImage = $order->post->images->first();
            return [
                'id' => $order->id,
                'status' => $order->status,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'review' => $order->review,
                'post' => [
                    'id' => $order->post->id,
                    'title' => $order->post->title,
                    'slug' => $order->post->slug,
                    'price' => $order->post->price,
                    'image' => $primaryImage ? $primaryImage->image_path : null,
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
