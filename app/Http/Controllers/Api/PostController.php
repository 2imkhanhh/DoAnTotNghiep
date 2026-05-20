<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Post\StorePostRequest;
use App\Http\Requests\Api\Post\UpdatePostRequest;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Lấy danh sách tin đăng của chính người dùng đang đăng nhập
     */
    public function userPosts(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');

        $query = Post::with(['images', 'category'])
            // Thêm withExists kiểm tra yêu thích
            ->withExists(['favoritedBy as is_favorited' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }])
            ->where('user_id', $user->id)
            ->latest();

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $posts = $query->paginate(10);

        // Lấy số lượng theo từng trạng thái cho User
        $counts = [
            'all' => Post::where('user_id', $user->id)->count(),
            'pending' => Post::where('user_id', $user->id)->where('status', 0)->count(),
            'approved' => Post::where('user_id', $user->id)->where('status', 1)->count(),
            'sold' => Post::where('user_id', $user->id)->where('status', 2)->count(),
            'rejected' => Post::where('user_id', $user->id)->where('status', 3)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $posts,
            'counts' => $counts
        ]);
    }

    public function index(Request $request)
    {
        //sử dụng cú pháp ES6 để gộp mảng khi phân trang
        /*
            Giả sử mảng cũ là: A = [1, 2]
            Mảng mới lấy về là: B = [3, 4]
            Nếu bạn viết A = B thì mảng cũ sẽ bị đè mất và chỉ còn lại [3, 4].
            Nhưng khi viết A = [...A, ...B], nó sẽ "rải" các phần tử ra thành A = [1, 2, 3, 4]. 
            Nhờ đó, người dùng vẫn giữ nguyên các tin đã xem ở phía trên và xem thêm tin mới được nối dài xuống dưới.
        */

        $limit = $request->get('limit', 8);
        $category_id = $request->get('category_id');
        $province_name = $request->get('province_name');
        $price_min = $request->get('price_min');
        $price_max = $request->get('price_max');
        $sort = $request->get('sort', 'latest');

        $query = Post::with(['images', 'category', 'user'])
            // Thêm withExists kiểm tra yêu thích
            ->withExists(['favoritedBy as is_favorited' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }])
            ->where('status', 1);

        if ($category_id) {
            $category = \App\Models\Category::with('children.children')->find($category_id);
            if ($category) {
                $categoryIds = [$category->id];
                foreach ($category->children as $child) {
                    $categoryIds[] = $child->id;
                    if ($child->children) {
                        foreach ($child->children as $grandchild) {
                            $categoryIds[] = $grandchild->id;
                        }
                    }
                }
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $category_id);
            }
        }

        // Lọc theo tỉnh thành (Khu vực)
        if ($province_name) {
            $query->where('province_name', $province_name);
        }

        // Lọc theo khoảng giá
        if ($price_min !== null && $price_min !== '') {
            $query->where('price', '>=', $price_min);
        }
        if ($price_max !== null && $price_max !== '') {
            $query->where('price', '<=', $price_max);
        }

        // Lọc theo thuộc tính danh mục (specifications JSON)
        $specs = $request->get('specs', []);
        if (!empty($specs) && is_array($specs)) {
            foreach ($specs as $key => $value) {
                if ($value !== null && $value !== '') {
                    $query->whereJsonContains("specifications->{$key}", $value);
                }
            }
        }

        // Sắp xếp
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $posts = $query->paginate($limit);

        return response()->json(['success' => true, 'data' => $posts]);
    }

    public function adminIndex(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');

        $query = Post::with(['images', 'category.attributes', 'user'])
            ->latest();

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $posts = $query->paginate($request->get('limit', 10));

        // Lấy số lượng chờ duyệt cho Admin
        $pendingCount = Post::where('status', 0)->count();

        return response()->json([
            'success' => true,
            'data' => $posts,
            'counts' => [
                'pending' => $pendingCount
            ]
        ]);
    }

    public function show($slug)
    {
        $post = Post::with(['images', 'category.attributes', 'user'])
            // Thêm withExists kiểm tra yêu thích cho bài viết chính
            ->withExists(['favoritedBy as is_favorited' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }])
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        // Lấy tin đăng liên quan (cùng danh mục, trừ tin hiện tại)
        $relatedPosts = Post::with(['images', 'user'])
            // Thêm withExists kiểm tra yêu thích cho các bài viết liên quan bên dưới
            ->withExists(['favoritedBy as is_favorited' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }])
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

    /**
     * Lấy thông tin rút gọn của bài đăng theo ID phục vụ đính kèm chat.
     */
    public function showById($id)
    {
        $post = Post::with(['images'])->find($id);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        $primaryImage = $post->images->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'price' => $post->price,
                'status' => $post->status,
                'image' => $primaryImage ? $primaryImage->image_path : null,
            ]
        ]);
    }

    public function edit($id)
    {
        $post = Post::with(['images', 'category'])
            ->where('id', $id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post
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

    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::where('id', $id)->where('user_id', auth('api')->id())->first();

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng hoặc bạn không có quyền sửa'], 404);
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Tự động reset trạng thái về chờ duyệt khi sửa
            $data['status'] = 0;
            $data['reject_reason'] = null;

            // Cập nhật slug nếu tiêu đề thay đổi
            if ($post->title !== $request->title) {
                $data['slug'] = Str::slug($request->title) . '-' . time();
            }

            if ($request->has('specifications') && $request->specifications != null) {
                $data['specifications'] = json_decode($request->specifications, true);
            }

            $post->update($data);

            // 1. Nhận danh sách ID ảnh cũ còn giữ lại từ Frontend
            $remainingIds = [];
            if ($request->has('remaining_images')) {
                $remainingIds = json_decode($request->remaining_images, true);
                if (!is_array($remainingIds)) {
                    $remainingIds = [];
                }
            }

            // 2. Lấy ra những ảnh cũ của post mà KHÔNG nằm trong danh sách giữ lại để xóa đi
            $imagesToDelete = $post->images()->whereNotIn('id', $remainingIds)->get();
            foreach ($imagesToDelete as $img) {
                // Xóa tệp vật lý trong storage
                Storage::disk('public')->delete(str_replace('/storage/', '', $img->image_path));
                // Xóa bản ghi trong database
                $img->delete();
            }

            // Xử lý hình ảnh mới (nếu có)
            if ($request->hasFile('images')) {
                // Kiểm tra xem đã có ảnh bìa chưa
                $hasPrimary = $post->images()->where('is_primary', true)->exists();

                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('images', $filename, 'public');

                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => '/storage/' . $path,
                        // Nếu chưa có ảnh bìa và đây là ảnh đầu tiên trong đống mới -> làm ảnh bìa
                        'is_primary' => (!$hasPrimary && $index === 0) ? true : false,
                    ]);
                }
            }

            // 3. Đảm bảo luôn có đúng 1 ảnh làm ảnh bìa (is_primary = true)
            $allImages = $post->images()->get();
            if ($allImages->isNotEmpty()) {
                $hasPrimary = $allImages->where('is_primary', true)->isNotEmpty();
                if (!$hasPrimary) {
                    // Nếu chưa có ảnh bìa nào (do ảnh bìa cũ bị xóa), đặt ảnh đầu tiên làm ảnh bìa
                    $allImages->first()->update(['is_primary' => true]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tin đăng thành công! Tin của bạn đang chờ duyệt lại.',
                'data' => $post->load('images')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật tin đăng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- Admin Methods ---

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
