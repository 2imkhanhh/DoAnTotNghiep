<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
    // Lấy toàn bộ danh mục GỐC kèm theo các danh mục CON bên trong
    public function index()
    {
        // with('children') sẽ tự động lồng mảng danh mục con vào bên trong danh mục cha
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    // Thêm mới danh mục
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);

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
}
