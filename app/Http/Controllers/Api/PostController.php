<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\Post\StorePostRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
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
}
