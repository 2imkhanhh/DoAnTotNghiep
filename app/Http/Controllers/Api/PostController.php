<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Post\StorePostRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 8);
        $category_id = $request->get('category_id');
        
        $query = Post::with(['images', 'category', 'user'])
            ->where('status', 1)
            ->latest();

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $posts = $query->paginate($limit);

        return response()->json(['success' => true, 'data' => $posts]);
    }

    public function show($slug)
    {
        $post = Post::with(['images', 'category.attributes', 'user'])
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        // Lấy tin đăng liên quan (cùng danh mục, trừ tin hiện tại)
        $relatedPosts = Post::with(['images', 'user'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 1)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $post,
            'related' => $relatedPosts
        ]);
    }

    public function store(StorePostRequest $request)
    {
        // 1. Bật khiên bảo vệ Database (Transaction)
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['user_id'] = auth('api')->id(); // Lấy ID người đang đăng nhập
            $data['slug'] = Str::slug($request->title) . '-' . time(); // Thêm time() để đảm bảo slug không bao giờ trùng

            // 2. Xử lý cái cột JSON (specifications)
            // Vì Frontend gửi qua form-data nên nó là 1 chuỗi string, ta phải dịch nó sang Array cho Laravel hiểu
            if ($request->has('specifications') && $request->specifications != null) {
                $data['specifications'] = json_decode($request->specifications, true);
            }

            // 3. Lưu thông tin vào bảng `posts`
            $post = Post::create($data);

            // 4. Xử lý lưu HÀNG LOẠT hình ảnh
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    // Tạo tên file độc nhất
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Lưu ảnh vào thư mục 'storage/app/public/images'
                    $path = $file->storeAs('images', $filename, 'public');

                    // Lưu vào bảng `post_images`
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => '/storage/' . $path,
                        'is_primary' => $index === 0 ? true : false, // Ảnh đầu tiên tải lên mặc định là ảnh bìa
                    ]);
                }
            }

            // 5. Nếu mọi thứ thành công không lỗi lầm gì -> Lưu chốt vào Database
            DB::commit();

            // Load lại tin đăng kèm theo ảnh vừa tạo để trả về cho Frontend
            $post->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Đăng tin thành công! Tin của bạn đang chờ duyệt.',
                'data' => $post
            ], 201);
        } catch (\Exception $e) {
            // 6. Nếu có LỖI (vd: đang lưu ảnh thứ 3 thì đứt mạng) -> Hủy bỏ toàn bộ, không lưu post nữa
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đăng tin, vui lòng thử lại!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- Admin Methods ---

    // Lấy danh sách tin đăng cho Admin (không giới hạn status=1)
    public function adminIndex(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $query = Post::with(['images', 'category.attributes', 'user'])->latest();

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%");
                  });
            });
        }

        $posts = $query->paginate($limit);

        return response()->json(['success' => true, 'data' => $posts]);
    }

    // Cập nhật trạng thái tin đăng (Duyệt/Từ chối/Đã bán)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3', // 0: Chờ, 1: Duyệt, 2: Đã bán, 3: Từ chối
            'reason' => 'nullable|string|max:255'
        ]);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        $post->update([
            'status' => $request->status,
            'reject_reason' => $request->status == 3 ? $request->reason : null
        ]);

        $statusMap = [
            0 => 'đã chuyển về chờ duyệt',
            1 => 'đã duyệt hiển thị',
            2 => 'đã đánh dấu là đã bán',
            3 => 'đã từ chối'
        ];

        return response()->json([
            'success' => true,
            'message' => "Tin đăng " . ($statusMap[$request->status] ?? 'đã cập nhật') . " thành công!",
            'data' => $post
        ]);
    }

    // Xóa tin đăng
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        // Xóa ảnh liên quan trong storage
        foreach ($post->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_path));
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa tin đăng thành công!']);
    }
}
