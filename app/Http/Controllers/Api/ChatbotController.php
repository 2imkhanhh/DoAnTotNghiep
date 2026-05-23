<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\ChatbotSession;
use App\Models\ChatbotMessage;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string'
        ]);

        $userMessage = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['status' => 'error', 'message' => 'Chưa cấu hình GEMINI_API_KEY'], 500);
        }

        // 1. XỬ LÝ SESSION VÀ LƯU DATABASE
        $userId = auth('api')->id(); // Lấy ID người dùng (nếu có đăng nhập)
        $sessionIdInput = $request->input('session_id');
        $session = null;

        if ($sessionIdInput) {
            $session = ChatbotSession::where('session_id', $sessionIdInput)->first();
            // Cập nhật user_id nếu trước đó là khách vãng lai, giờ họ đã đăng nhập
            if ($session && !$session->user_id && $userId) {
                $session->update(['user_id' => $userId]);
            }
        }

        // Nếu không có session cũ hoặc không tìm thấy -> Tạo mới
        if (!$session) {
            $session = ChatbotSession::create([
                'session_id' => (string) Str::uuid(),
                'user_id' => $userId
            ]);
        }

        // Lưu câu hỏi của User vào Database
        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'user',
            'content' => $userMessage
        ]);

        // 2. LẤY LỊCH SỬ CHAT (Tối đa 10 tin nhắn gần nhất để tiết kiệm token)
        // Lưu ý: Phải xếp theo thứ tự cũ đến mới (ASC)
        $historyMessages = ChatbotMessage::where('chatbot_session_id', $session->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $contents = [];
        foreach ($historyMessages as $msg) {
            $contents[] = [
                'role' => $msg->role, // 'user' hoặc 'model'
                'parts' => [['text' => $msg->content]]
            ];
        }

        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        // 3. CHUẨN BỊ DATA GỬI LÊN GEMINI
        $requestData = [
            'contents' => $contents,
            'systemInstruction' => [
                'role' => 'system',
                'parts' => [
                    ['text' => 'Bạn là trợ lý ảo thân thiện của website bán đồ cũ 2Hand. HÃY LUÔN SỬ DỤNG hàm search_posts nếu khách có nhu cầu mua/tìm sản phẩm. Khi có kết quả từ hàm, hãy tóm tắt lịch sự. Nếu khách hỏi những thứ không liên quan đến đồ cũ, hãy từ chối khéo léo.']
                ]
            ],
            'tools' => [
                [
                    'functionDeclarations' => [
                        [
                            'name' => 'search_posts',
                            'description' => 'Tìm kiếm bài đăng đồ cũ trong cơ sở dữ liệu dựa trên từ khoá và mức giá.',
                            'parameters' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'keyword' => ['type' => 'STRING', 'description' => 'Từ khoá sản phẩm'],
                                    'max_price' => ['type' => 'NUMBER', 'description' => 'Mức giá tối đa (VNĐ). Vd: 10 triệu -> 10000000']
                                ],
                                'required' => ['keyword']
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'maxOutputTokens' => 800,
            ]
        ];

        try {
            // GỌI API LẦN 1
            $response1 = Http::withHeaders(['Content-Type' => 'application/json'])->post($apiUrl, $requestData);

            if (!$response1->successful()) {
                Log::error('Gemini API Error: ' . $response1->body());
                return response()->json(['status' => 'error', 'message' => 'Lỗi kết nối tới AI'], 500);
            }

            $result1 = $response1->json();
            $parts = $result1['candidates'][0]['content']['parts'] ?? [];

            $functionCall = null;
            foreach ($parts as $part) {
                if (isset($part['functionCall'])) {
                    $functionCall = $part['functionCall'];
                    break;
                }
            }

            $botFinalReply = '';
            $productsData = null;

            // NẾU KHÔNG CÓ GỌI HÀM (Chat bình thường)
            if (!$functionCall) {
                $botFinalReply = $parts[0]['text'] ?? 'Xin lỗi, tôi không hiểu ý bạn.';
            }
            // NẾU CÓ GỌI HÀM
            else {
                $args = $functionCall['args'];
                $keyword = $args['keyword'] ?? '';
                $maxPrice = $args['max_price'] ?? null;

                // Query DB
                $query = Post::where('status', 1)->where('title', 'like', "%{$keyword}%");
                if ($maxPrice) {
                    $query->where('price', '<=', $maxPrice);
                }
                // Lấy thêm nhiều trường dữ liệu để AI có thể tư vấn chi tiết hơn
                $productsData = $query->with('user:id,name')->take(5)->get([
                    'id',
                    'user_id',
                    'title',
                    'price',
                    'slug',
                    'description',
                    'specifications',
                    'province_name',
                    'ward_name'
                ])->toArray();

                $functionResult = [
                    'products' => $productsData,
                    'count' => count($productsData)
                ];

                // GỌI API LẦN 2 (Truyền kết quả DB về cho AI)
                $requestData2 = $requestData;
                $requestData2['contents'][] = [
                    'role' => 'model',
                    'parts' => [['functionCall' => $functionCall]]
                ];
                $requestData2['contents'][] = [
                    'role' => 'function',
                    'parts' => [['functionResponse' => ['name' => 'search_posts', 'response' => $functionResult]]]
                ];

                $response2 = Http::withHeaders(['Content-Type' => 'application/json'])->post($apiUrl, $requestData2);
                if ($response2->successful()) {
                    $result2 = $response2->json();
                    $botFinalReply = $result2['candidates'][0]['content']['parts'][0]['text'] ?? 'Đã có lỗi khi tóm tắt sản phẩm.';
                } else {
                    $botFinalReply = "Xin lỗi, đã xảy ra lỗi khi tìm kiếm sản phẩm.";
                }
            }

            // 4. LƯU CÂU TRẢ LỜI CỦA BOT VÀO DATABASE
            ChatbotMessage::create([
                'chatbot_session_id' => $session->id,
                'role' => 'model',
                'content' => $botFinalReply,
                'data' => $productsData // Lưu lại mảng data nếu có
            ]);

            return response()->json([
                'status' => 'success',
                'reply' => $botFinalReply,
                'data' => $productsData,
                'session_id' => $session->session_id // Trả về chìa khoá cho Frontend
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}