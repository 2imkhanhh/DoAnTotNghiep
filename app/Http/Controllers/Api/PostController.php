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
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PostPendingApprovalNotification;
use App\Notifications\PostApprovedNotification;
use App\Notifications\PostRejectedNotification;

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
            'pending' => Post::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => Post::where('user_id', $user->id)->where('status', 'active')->count(),
            'sold' => Post::where('user_id', $user->id)->where('status', 'sold')->count(),
            'rejected' => Post::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'hidden' => Post::where('user_id', $user->id)->where('status', 'hidden')->count(),
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
            ->where('status', 'active');

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

        // Lọc theo từ khóa tìm kiếm
        $search = $request->get('search');
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
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
        $pendingCount = Post::where('status', 'pending')->count();

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
            // Kiểm tra xem người dùng hiện tại đã đặt hàng sản phẩm này chưa
            ->withExists(['orders as is_ordered' => function ($query) {
                $query->where('buyer_id', auth('api')->id())
                    ->whereIn('status', ['pending', 'confirmed', 'shipping']);
            }])
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        // Phân quyền xem tin đăng theo trạng thái
        $userId = auth('api')->id();

        if (in_array($post->status, ['sold', 'rejected', 'hidden']) && $post->user_id != $userId) {
            $statusMsg = $post->status == 'rejected' ? 'Sản phẩm đã bị từ chối duyệt' : 'Sản phẩm đã ẩn hoặc đã bán, bạn không có quyền xem';
            return response()->json(['success' => false, 'message' => $statusMsg], 403);
        }

        // Lấy tin đăng liên quan (cùng danh mục, trừ tin hiện tại)
        $relatedPosts = Post::with(['images', 'user'])
            // Thêm withExists kiểm tra yêu thích cho các bài viết liên quan bên dưới
            ->withExists(['favoritedBy as is_favorited' => function ($query) {
                $query->where('user_id', auth('api')->id());
            }])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'active')
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
        // 1. Bật khiên bảo vệ Database (Order)
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['user_id'] = auth('api')->id(); // Lấy ID người đang đăng nhập
            $data['slug'] = Str::slug($request->title) . '-' . time(); // Thêm time() để đảm bảo slug không bao giờ trùng

            // Nếu là admin thì duyệt luôn (status = 1), ngược lại là chờ duyệt (status = 0)
            if (auth('api')->user()->isAdmin()) {
                $data['status'] = 'active';
            } else {
                $data['status'] = 'pending';
            }

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

            // Gửi thông báo cho Admin nếu bài đăng cần duyệt
            if ($post->status === 'pending') {
                $admins = User::where('role', User::ROLE_ADMIN)->get();
                Notification::send($admins, new PostPendingApprovalNotification($post));
            }

            $message = auth('api')->user()->isAdmin()
                ? 'Đăng tin thành công!'
                : 'Đăng tin thành công! Tin của bạn đang chờ duyệt.';

            return response()->json([
                'success' => true,
                'message' => $message,
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

        // Chặn sửa tin đã bán
        if ($post->status == 'sold') {
            return response()->json(['success' => false, 'message' => 'Tin đã bán không thể chỉnh sửa'], 403);
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Tự động reset trạng thái về chờ duyệt khi sửa (nếu không phải admin)
            if (auth('api')->user()->isAdmin()) {
                $data['status'] = 'active';
            } else {
                $data['status'] = 'pending';
            }
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

            // Reset tất cả is_primary về false
            $post->images()->update(['is_primary' => false]);

            $primary_new_index = $request->get('primary_new_file_index', -1);
            $primary_image_id = $request->get('primary_image_id');

            // Xử lý hình ảnh mới (nếu có)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('images', $filename, 'public');

                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => '/storage/' . $path,
                        'is_primary' => ($primary_new_index != -1 && $primary_new_index == $index),
                    ]);
                }
            }

            // Set primary cho ảnh cũ nếu được chọn
            if ($primary_image_id) {
                $post->images()->where('id', $primary_image_id)->update(['is_primary' => true]);
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

            // Gửi thông báo cho Admin nếu bài đăng cần duyệt lại
            if ($post->status === 'pending') {
                $admins = User::where('role', User::ROLE_ADMIN)->get();
                Notification::send($admins, new PostPendingApprovalNotification($post));
            }

            $message = auth('api')->user()->isAdmin()
                ? 'Cập nhật tin đăng thành công!'
                : 'Cập nhật tin đăng thành công! Tin của bạn đang chờ duyệt lại.';

            return response()->json([
                'success' => true,
                'message' => $message,
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
            'status' => 'required|string|in:pending,active,sold,rejected,hidden',
            'reason' => 'nullable|string|max:255'
        ]);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tin đăng'], 404);
        }

        // Bổ sung phân quyền cho User thường (không phải admin)
        $user = auth('api')->user();
        if (!$user->isAdmin()) {
            // Chỉ được sửa tin của chính mình
            if ($post->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật trạng thái tin này'], 403);
            }

            // Không được tự ý duyệt tin (chuyển sang active từ pending/rejected)
            if ($request->status === 'active' && in_array($post->status, ['pending', 'rejected'])) {
                return response()->json(['success' => false, 'message' => 'Không thể tự duyệt hiển thị tin đăng đang chờ duyệt hoặc bị từ chối'], 403);
            }

            // User không được tự chuyển trạng thái về chờ duyệt hoặc từ chối
            if (in_array($request->status, ['pending', 'rejected'])) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền chuyển sang trạng thái này'], 403);
            }

            // Không được hiện lại tin nếu đang có đơn hàng giao dịch
            if ($request->status === 'active' && $post->status === 'hidden') {
                $hasActiveOrder = \App\Models\Order::where('post_id', $post->id)
                    ->whereIn('status', ['pending', 'confirmed', 'shipping', 'awaiting_payment'])
                    ->exists();
                if ($hasActiveOrder) {
                    return response()->json(['success' => false, 'message' => 'Không thể hiển lại tin do đơn hàng đang được xử lý'], 403);
                }
            }
        }

        $oldStatus = $post->status;

        $post->update([
            'status' => $request->status,
            'reject_reason' => $request->status === 'rejected' ? $request->reason : null
        ]);

        // Gửi thông báo cho người dùng
        if ($oldStatus !== $request->status) {
            if ($request->status === 'active' && $oldStatus === 'pending') {
                $post->user->notify(new PostApprovedNotification($post));
            } elseif ($request->status === 'rejected') {
                $post->user->notify(new PostRejectedNotification($post));
            }
        }

        $statusMap = [
            'pending' => 'đã chuyển về chờ duyệt',
            'active' => 'đã duyệt hiển thị',
            'sold' => 'đã đánh dấu là đã bán',
            'rejected' => 'đã từ chối',
            'hidden' => 'đã tạm ẩn'
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

        if ($post->status === 'sold') {
            return response()->json(['success' => false, 'message' => 'Không thể xóa tin đăng đã bán để bảo lưu lịch sử giao dịch'], 403);
        }

        // Xóa ảnh liên quan trong storage
        foreach ($post->images as $image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_path));
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa tin đăng thành công!']);
    }
}