<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Get all banners for Admin panel
     */
    public function index()
    {
        $banners = Banner::orderBy('order', 'asc')->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $banners]);
    }

    /**
     * Get active banners for Home page
     */
    public function active()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $banners]);
    }

    /**
     * Store a new banner
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'link', 'description']);
        $data['is_active'] = $request->input('is_active', true);
        
        // Auto assign order to the end
        $maxOrder = Banner::max('order');
        $data['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '-banner-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('banners', $filename, 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        $banner = Banner::create($data);

        return response()->json(['success' => true, 'message' => 'Thêm banner thành công', 'data' => $banner], 201);
    }

    /**
     * Update an existing banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy banner'], 404);
        }

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'link', 'description']);
        
        if ($request->has('is_active')) {
            // When sent as form-data, boolean might be string "true" or "false"
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            $data['is_active'] = $isActive;
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_path));
            }

            $file = $request->file('image');
            $filename = time() . '-banner-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('banners', $filename, 'public');
            $data['image_path'] = '/storage/' . $path;
        }

        $banner->update($data);

        return response()->json(['success' => true, 'message' => 'Cập nhật banner thành công', 'data' => $banner]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy banner'], 404);
        }

        $banner->is_active = !$banner->is_active;
        $banner->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công', 'data' => $banner]);
    }

    /**
     * Delete a banner
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy banner'], 404);
        }

        // Delete image
        if ($banner->image_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image_path));
        }

        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Xoá banner thành công']);
    }

    /**
     * Update order of banners
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:banners,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($request->orders as $item) {
            Banner::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự thành công']);
    }
}
