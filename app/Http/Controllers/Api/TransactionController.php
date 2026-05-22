<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Transaction;
use App\Events\TransactionUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // Người bán bắt đầu giao dịch
    public function startTransaction(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'buyer_id' => 'required|exists:users,id',
            'conversation_id' => 'required'
        ]);

        $post = Post::findOrFail($request->post_id);

        if ($post->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện trên bài đăng này.'], 403);
        }

        // Kiểm tra xem bài đăng đã có giao dịch nào đang diễn ra hoặc đã xong chưa
        $existing = Transaction::where('post_id', $post->id)
            ->whereIn('status', ['trading', 'completed'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Bài đăng này đang có giao dịch khác hoặc đã được bán.'], 400);
        }

        $transaction = Transaction::create([
            'post_id' => $post->id,
            'seller_id' => Auth::id(),
            'buyer_id' => $request->buyer_id,
            'status' => 'trading'
        ]);

        $transaction->load(['seller', 'buyer', 'post']);

        broadcast(new TransactionUpdated($transaction, $request->conversation_id))->toOthers();

        return response()->json(['success' => true, 'data' => $transaction]);
    }

    // Người mua hoàn thành giao dịch
    public function completeTransaction(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không phải người mua trong giao dịch này.'], 403);
        }

        if ($transaction->status !== 'trading') {
            return response()->json(['success' => false, 'message' => 'Giao dịch không ở trạng thái có thể hoàn thành.'], 400);
        }

        $transaction->update(['status' => 'completed']);

        // Chuyển bài đăng thành đã bán (status 3 = sold)
        $post = Post::find($transaction->post_id);
        if ($post) {
            $post->update(['status' => 3]);
        }

        $transaction->load(['seller', 'buyer', 'post']);
        broadcast(new TransactionUpdated($transaction, $request->conversation_id))->toOthers();

        return response()->json(['success' => true, 'data' => $transaction]);
    }

    // Người bán hoặc người mua hủy giao dịch
    public function cancelTransaction(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->seller_id !== Auth::id() && $transaction->buyer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền hủy giao dịch này.'], 403);
        }

        if ($transaction->status !== 'trading') {
            return response()->json(['success' => false, 'message' => 'Không thể hủy giao dịch đã hoàn thành hoặc đã hủy.'], 400);
        }

        $transaction->update(['status' => 'cancelled']);

        $transaction->load(['seller', 'buyer', 'post']);
        broadcast(new TransactionUpdated($transaction, $request->conversation_id))->toOthers();

        return response()->json(['success' => true, 'data' => $transaction]);
    }
}
