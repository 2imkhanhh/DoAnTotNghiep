<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServicePackage;
use App\Models\UserPurchase;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    // Lấy danh sách các gói đang bán
    public function index()
    {
        $packages = ServicePackage::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    // Lịch sử mua gói của User hiện tại
    public function myPurchases()
    {
        $user = Auth::user();
        $purchases = UserPurchase::with('package')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);
            
        return response()->json([
            'success' => true,
            'data' => $purchases
        ]);
    }

    // Đăng ký mua gói
    public function buy(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:service_packages,id'
        ]);

        $package = ServicePackage::find($request->package_id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy gói dịch vụ.'
            ], 404);
        }

        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Gói dịch vụ này hiện đã ngừng bán.'
            ], 400);
        }

        // Tạo yêu cầu mua gói (trạng thái pending)
        $purchase = UserPurchase::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'status' => 'pending',
            'price_paid' => $package->price,
        ]);

        // Gửi thông báo cho Admin
        $admins = \App\Models\User::where('role', 1)->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\PackagePurchasePendingNotification($purchase));

        // Lấy thông tin ngân hàng của Admin (role = 1)
        $admin = \App\Models\User::where('role', 1)->first();
        $adminBank = [
            'bank_name' => $admin->bank_name ?: 'MBBank',
            'bank_account_no' => $admin->bank_account_no ?: '0123456789',
            'bank_account_name' => $admin->bank_account_name ?: 'ADMIN WEB',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo yêu cầu mua gói. Vui lòng chuyển khoản để được admin duyệt.',
            'data' => [
                'purchase' => $purchase->load('package'),
                'admin_bank' => $adminBank
            ]
        ]);
    }

    // Hủy yêu cầu mua gói (chỉ khi đang ở trạng thái pending)
    public function cancel($id)
    {
        $purchase = UserPurchase::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hoặc yêu cầu không thể hủy.'
            ], 400);
        }

        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy yêu cầu mua gói dịch vụ.'
        ]);
    }

    // --- ADMIN METHODS ---

    public function adminIndex()
    {
        $packages = ServicePackage::all();
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vip,post',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'post_quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $package = ServicePackage::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo gói dịch vụ thành công.',
            'data' => $package
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vip,post',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'post_quota' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $package = ServicePackage::findOrFail($id);

        $hasPurchases = UserPurchase::where('package_id', $id)->exists();

        if ($hasPurchases) {
            // Thay vì sửa trực tiếp làm ảnh hưởng đến người mua trước đó, 
            // ta ẩn gói cũ đi và tạo ra một gói mới hoàn toàn với dữ liệu cập nhật
            $package->update(['is_active' => false]);
            
            $newPackage = ServicePackage::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Gói dịch vụ đã có người mua nên hệ thống tự động tạo phiên bản mới (gói cũ được ẩn đi để bảo toàn lịch sử).',
                'data' => $newPackage
            ]);
        }

        $package->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thông tin gói dịch vụ.',
            'data' => $package
        ]);
    }

    public function toggleActive($id)
    {
        $package = ServicePackage::findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);

        return response()->json([
            'success' => true,
            'message' => $package->is_active ? 'Đã hiện gói dịch vụ.' : 'Đã ẩn gói dịch vụ.'
        ]);
    }
}
