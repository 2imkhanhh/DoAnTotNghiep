<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;
use App\Http\Requests\Api\Category\StoreCategoryAttributeRequest;
use App\Http\Requests\Api\Category\UpdateCategoryAttributeRequest;
use App\Models\CategoryAttribute;

class CategoryController extends Controller
{
    // Lấy toàn bộ danh mục GỐC kèm theo các danh mục CON bên trong (Hỗ trợ 3 cấp)
    public function index()
    {
        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function getFeaturedCategories()
    {
        // Lấy danh mục được đánh dấu là nổi bật và đang hoạt động
        $categories = Category::where('is_featured', true)
            ->where('is_active', true)
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }

    // Thêm mới danh mục
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);

        // Kiểm tra tối đa 10 danh mục nổi bật ở Backend
        if (!empty($data['is_featured'])) {
            $featuredCount = Category::where('is_featured', true)->count();
            if ($featuredCount >= 8) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được phép thiết lập tối đa 8 danh mục nổi bật! Hãy tắt nổi bật của danh mục khác trước.'
                ], 422);
            }
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = time() . '-' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $filename, 'public');
            $data['icon'] = '/storage/' . $path;
        }

        $category = Category::create($data);

        return response()->json(['success' => true, 'message' => 'Tạo danh mục thành công!', 'data' => $category], 201);
    }

    // Cập nhật danh mục
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);

        $data = $request->validated();
        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        // Kiểm tra tối đa 8 danh mục nổi bật ở Backend khi cập nhật
        if (isset($data['is_featured']) && $data['is_featured'] && !$category->is_featured) {
            $featuredCount = Category::where('is_featured', true)->count();
            if ($featuredCount >= 8) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ được phép thiết lập tối đa 8 danh mục nổi bật! Hãy tắt nổi bật của danh mục khác trước.'
                ], 422);
            }
        }

        if ($request->hasFile('icon')) {
            // Xóa ảnh cũ đi cho nhẹ server
            if ($category->icon) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $category->icon));
            }
            $file = $request->file('icon');
            $filename = time() . '-' . $file->getClientOriginalName();
            $path = $file->storeAs('images', $filename, 'public');
            $data['icon'] = '/storage/' . $path;
        }

        $category->update($data);

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!', 'data' => $category]);
    }

    // Xóa danh mục
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);

        // Xóa luôn file ảnh trong ổ cứng
        if ($category->icon) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $category->icon));
        }
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa danh mục!']);
    }

    // Lấy danh sách các trường thông tin chi tiết của danh mục
    public function getAttributes($id)
    {
        $attributes = CategoryAttribute::where('category_id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }

    public function storeAttribute(StoreCategoryAttributeRequest $request, $id)
    {
        // 1. Kiểm tra danh mục
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);
        }

        // 2. Lấy dữ liệu ĐÃ ĐƯỢC KIỂM DUYỆT SẠCH SẼ từ file Request
        $data = $request->validated();

        // 3. Lưu vào Database
        $attribute = $category->attributes()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm thuộc tính thành công cho danh mục!',
            'data' => $attribute
        ], 201);
    }

    public function updateAttribute(UpdateCategoryAttributeRequest $request, $id, $attribute_id)
    {
        // 1. Kiểm tra danh mục có tồn tại không
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);
        }

        // 2. Tìm thuộc tính thuộc về danh mục này
        $attribute = CategoryAttribute::where('category_id', $id)
            ->where('id', $attribute_id)
            ->first();

        if (!$attribute) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thuộc tính cần sửa trong danh mục này'], 404);
        }

        // 3. Lấy dữ liệu đã validate và cập nhật
        $data = $request->validated();
        $attribute->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thuộc tính thành công!',
            'data' => $attribute
        ]);
    }

    // Xóa thuộc tính của danh mục
    public function destroyAttribute($id, $attribute_id)
    {
        // 1. Kiểm tra danh mục
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy danh mục'], 404);
        }

        // 2. Tìm thuộc tính
        $attribute = CategoryAttribute::where('category_id', $id)
            ->where('id', $attribute_id)
            ->first();

        if (!$attribute) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thuộc tính cần xóa'], 404);
        }

        // 3. Xóa khỏi DB
        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thuộc tính thành công!'
        ]);
    }
}
