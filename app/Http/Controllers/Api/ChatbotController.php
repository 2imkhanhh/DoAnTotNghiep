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
            $text = $msg->content;

            // Nhồi thêm thông tin chi tiết sản phẩm vào bộ nhớ của AI để nó tự tư vấn tiếp mà không cần tìm kiếm lại
            if ($msg->role === 'model' && $msg->data) {
                $dataArr = is_string($msg->data) ? json_decode($msg->data, true) : $msg->data;
                if (!empty($dataArr)) {
                    // Lọc bớt các trường không cần thiết để tiết kiệm token
                    $memoryData = array_map(function ($p) {
                        return [
                            'title' => $p['title'],
                            'price' => $p['price'],
                            'description' => $p['description'] ?? '',
                            'specifications' => $p['specifications'] ?? ''
                        ];
                    }, $dataArr);
                    $text .= "\n\n[Dữ liệu ẩn trong bộ nhớ: " . json_encode($memoryData, JSON_UNESCAPED_UNICODE) . "]";
                }
            }

            $contents[] = [
                'role' => $msg->role, // 'user' hoặc 'model'
                'parts' => [['text' => $text]]
            ];
        }

        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        // 3. CHUẨN BỊ DATA GỬI LÊN GEMINI
        $requestData = [
            'contents' => $contents,
            'systemInstruction' => [
                'role' => 'system',
                'parts' => [
                    ['text' => 'Bạn là trợ lý ảo thân thiện của website bán đồ cũ Chợ Đồ Cũ UTT. 
                                Dưới đây là các thông tin chung của sàn giao dịch để bạn dùng khi khách hỏi:
                                - Địa chỉ cơ sở chính: Số 54 Triều Khúc, Thanh Xuân, Hà Nội (Trường Đại học Công nghệ Giao thông Vận tải).
                                - Cách đăng bán sản phẩm: Người dùng cần Đăng nhập -> Chọn nút "Đăng tin" -> Điền thông tin, hình ảnh và giá cả -> Chờ quản trị viên duyệt bài.
                                - Hình thức giao dịch: Nền tảng chỉ đóng vai trò kết nối người mua và người bán. Hai bên tự liên hệ để thương lượng cách xem hàng và thanh toán trực tiếp.

                                Quy tắc xử lý:
                                1. Khi khách CẦN TÌM MỚI một sản phẩm, HÃY LUÔN SỬ DỤNG hàm search_posts. Khi có kết quả, CHỈ giới thiệu các sản phẩm ĐÚNG loại khách cần (VD: khách tìm laptop tuyệt đối không đưa iPad vào).
                                2. Khi khách HỎI THÊM CHI TIẾT về sản phẩm bạn vừa giới thiệu, KHÔNG CẦN GỌI HÀM search_posts nữa, hãy đọc phần [Dữ liệu ẩn trong bộ nhớ] ở câu trả lời trước của bạn để lấy thông tin tư vấn cho khách.
                                3. Luôn trả lời ngắn gọn, thân thiện. Hãy dựa vào "các thông tin chung" ở trên để hướng dẫn khách khi họ hỏi về quy định hay cách hoạt động của sàn. Nếu khách hỏi ngoài lề (làm thơ, tính toán, thông tin xã hội...), hãy từ chối khéo léo.']
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
                                    'keyword' => ['type' => 'STRING', 'description' => 'Từ khoá cốt lõi ngắn gọn nhất để tìm sản phẩm (vd: "máy tính", "điện thoại"). TUYỆT ĐỐI KHÔNG chứa các từ hỏi như "có", "không", "nào", "bộ", "chiếc".'],
                                    'max_price' => ['type' => 'NUMBER', 'description' => 'Mức giá tối đa (VNĐ). Vd: 10 triệu -> 10000000'],
                                    'location' => ['type' => 'STRING', 'description' => 'Địa điểm, tên tỉnh thành, quận huyện khách muốn tìm (vd: "Nghệ An", "Hà Nội"). Trả về chuỗi rỗng nếu khách không nhắc đến địa điểm.']
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
                return response()->json([
                    'status' => 'success',
                    'reply' => 'Hệ thống AI đã hết lượt dùng trong ngày. Vui lòng tạo một API Key mới (từ nick Google khác) và dán vào file .env nhé!',
                    'session_id' => $session->session_id
                ]);
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
                $keyword = trim($args['keyword'] ?? '');
                $maxPrice = $args['max_price'] ?? null;
                $location = trim($args['location'] ?? '');

                // Query DB: Tách từ khóa để tìm linh hoạt hơn (VD: "loa jbl" -> tìm cả "loa" và "jbl")
                $query = Post::where('status', 'active');
                $words = array_filter(explode(' ', $keyword));
                if (!empty($words)) {
                    $query->where(function ($q) use ($words) {
                        foreach ($words as $word) {
                            $q->where(function ($subQ) use ($word) {
                                $subQ->where('title', 'like', "%{$word}%")
                                    ->orWhereHas('category', function ($catQuery) use ($word) {
                                        $catQuery->where('name', 'like', "%{$word}%");
                                    });
                            });
                        }
                    });
                }
                if ($maxPrice) {
                    $query->where('price', '<=', $maxPrice);
                }
                if ($location) {
                    $query->where(function ($q) use ($location) {
                        $q->where('province_name', 'like', "%{$location}%")
                          ->orWhere('ward_name', 'like', "%{$location}%");
                    });
                }
                
                // Lấy thêm nhiều trường dữ liệu để AI có thể tư vấn chi tiết hơn
                $productsData = $query->with(['user:id,name', 'images'])->take(5)->get([
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
                    Log::error('Gemini API Error Vòng 2: ' . $response2->body());
                    $botFinalReply = 'Hệ thống AI đang quá tải do vượt quá giới hạn API. Vui lòng đợi khoảng 1 phút rồi thử lại nhé!';
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
            return response()->json(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $userId = auth('api')->id();
        $sessionIdInput = $request->input('session_id');

        $session = null;
        if ($sessionIdInput) {
            $session = ChatbotSession::where('session_id', $sessionIdInput)->first();
        }

        // Khôi phục session theo user nếu ở local storage bị mất
        if (!$session && $userId) {
            $session = ChatbotSession::where('user_id', $userId)->latest()->first();
        }

        if (!$session) {
            return response()->json(['status' => 'success', 'messages' => [], 'session_id' => null]);
        }

        $messages = $session->messages()->orderBy('created_at', 'asc')->get();
        $formatted = [];
        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg->role,
                'content' => $msg->content,
                'data' => is_string($msg->data) ? json_decode($msg->data, true) : $msg->data,
                'hideAvatar' => false
            ];
        }

        return response()->json([
            'status' => 'success',
            'messages' => $formatted,
            'session_id' => $session->session_id
        ]);
    }

    public function reset(Request $request)
    {
        $userId = auth('api')->id();
        $sessionIdInput = $request->input('session_id');

        // Nếu có session_id, tiến hành xoá phiên chat hiện tại
        if ($sessionIdInput) {
            $oldSession = ChatbotSession::where('session_id', $sessionIdInput)->first();
            if ($oldSession) {
                $oldSession->messages()->delete();
                $oldSession->delete();
            }
        }

        // Đảm bảo xoá luôn TẤT CẢ các phiên chat cũ nếu người dùng ĐÃ ĐĂNG NHẬP
        // Tránh việc History lấy lại session_id cũ từ user_id
        if ($userId) {
            $userSessions = ChatbotSession::where('user_id', $userId)->get();
            foreach ($userSessions as $s) {
                $s->messages()->delete();
                $s->delete();
            }
        }

        // Tạo phiên làm việc mới
        $newSession = ChatbotSession::create([
            'session_id' => (string) Str::uuid(),
            'user_id' => $userId
        ]);

        return response()->json([
            'status' => 'success',
            'session_id' => $newSession->session_id
        ]);
    }
}