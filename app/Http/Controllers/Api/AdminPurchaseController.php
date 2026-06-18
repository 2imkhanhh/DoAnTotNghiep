<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminPurchaseController extends Controller
{
    // Lấy danh sách yêu cầu mua gói
    public function index(Request $request)
    {
        $status = $request->get('status');
        
        $query = UserPurchase::with(['user', 'package'])->latest();
        
        if ($status) {
            $query->where('status', $status);
        }

        $purchases = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $purchases
        ]);
    }

    // Duyệt yêu cầu
    public function approve($id)
    {
        $purchase = UserPurchase::with('package', 'user')->findOrFail($id);

        if ($purchase->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Yêu cầu này không ở trạng thái chờ duyệt'], 400);
        }

        DB::beginTransaction();
        try {
            $purchase->update(['status' => 'active']);
            $user = $purchase->user;
            $package = $purchase->package;

            if ($package->type === 'vip') {
                // Cộng thêm ngày VIP
                $currentExpiry = $user->vip_expires_at ? Carbon::parse($user->vip_expires_at) : now();
                if ($currentExpiry->isPast()) {
                    $currentExpiry = now();
                }
                $user->vip_expires_at = $currentExpiry->addDays($package->duration_days);
                $user->save();
            } elseif ($package->type === 'post') {
                // Cộng lượt đăng tin
                $user->increment('available_post_quota', $package->post_quota);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Duyệt yêu cầu thành công, đã cộng quyền lợi cho User.',
                'data' => $purchase
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Từ chối yêu cầu
    public function reject($id)
    {
        $purchase = UserPurchase::findOrFail($id);

        if ($purchase->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Yêu cầu này không ở trạng thái chờ duyệt'], 400);
        }

        $purchase->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối yêu cầu.',
            'data' => $purchase
        ]);
    }
}
