<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatLabel;
use App\Models\ConversationChatLabel;
use Illuminate\Support\Facades\Auth;

class ChatLabelController extends Controller
{
    /**
     * Get all labels (default + user custom)
     */
    public function index()
    {
        $userId = Auth::id();
        
        $labels = ChatLabel::withCount(['conversationLabels' => function($q) use ($userId) {
            $q->where('user_id', $userId);
        }])
        ->where(function($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        })
        ->orderBy('is_default', 'desc')
        ->orderBy('id', 'asc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $labels
        ]);
    }

    /**
     * Create a new custom label
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'color_code' => 'required|string|max:20'
        ]);

        $label = ChatLabel::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color_code' => $request->color_code,
            'is_default' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo nhãn thành công',
            'data' => $label
        ]);
    }

    /**
     * Update a custom label
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'color_code' => 'required|string|max:20'
        ]);

        $label = ChatLabel::find($id);

        if (!$label) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhãn'], 404);
        }

        if ($label->is_default || $label->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền sửa nhãn này'], 403);
        }

        $label->update([
            'name' => $request->name,
            'color_code' => $request->color_code
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật nhãn thành công',
            'data' => $label
        ]);
    }

    /**
     * Delete a custom label
     */
    public function destroy($id)
    {
        $label = ChatLabel::find($id);

        if (!$label) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhãn'], 404);
        }

        if ($label->is_default || $label->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa nhãn này'], 403);
        }

        $label->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa nhãn thành công'
        ]);
    }

    /**
     * Update labels for a specific conversation
     */
    public function updateConversationLabels(Request $request, $conversationId)
    {
        $request->validate([
            'label_ids' => 'present|array',
            'label_ids.*' => 'integer|exists:chat_labels,id'
        ]);

        $userId = Auth::id();

        // Xóa hết các nhãn cũ của user này trong conversation này
        ConversationChatLabel::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->delete();

        // Gắn các nhãn mới
        $newLabels = [];
        foreach ($request->label_ids as $labelId) {
            $newLabels[] = [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'chat_label_id' => $labelId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($newLabels)) {
            ConversationChatLabel::insert($newLabels);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phân loại thành công'
        ]);
    }
}
