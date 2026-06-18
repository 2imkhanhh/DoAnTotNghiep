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

        $package = ServicePackage::where('id', $request->package_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Tạo yêu cầu mua gói (trạng thái pending)
        $purchase = UserPurchase::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'status' => 'pending',
            'price_paid' => $package->price,
        ]);

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
}
