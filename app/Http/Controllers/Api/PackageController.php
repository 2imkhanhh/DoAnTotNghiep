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
            'package_id' => 'required|exists:service_packages,id',
            'payment_method' => 'nullable|in:manual,payos'
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

        $paymentMethod = $request->payment_method ?? 'manual';
        $orderCode = intval(substr(strval(time()), -6) . rand(1000, 9999)); // Generate random int for orderCode

        // Tạo yêu cầu mua gói (trạng thái pending)
        $purchase = UserPurchase::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'status' => 'pending',
            'price_paid' => $package->price,
            'payment_method' => $paymentMethod,
            'payos_order_code' => $paymentMethod === 'payos' ? $orderCode : null,
        ]);

        if ($paymentMethod === 'payos') {
            try {
                $payOS = new \PayOS\PayOS(
                    env('PAYOS_CLIENT_ID'),
                    env('PAYOS_API_KEY'),
                    env('PAYOS_CHECKSUM_KEY')
                );

                $data = [
                    "orderCode" => $orderCode,
                    "amount" => intval($package->price),
                    "description" => "MUA GOI " . $purchase->id,
                    "returnUrl" => env('APP_URL') . "/seller-center/packages?payos=success",
                    "cancelUrl" => env('APP_URL') . "/seller-center/packages?payos=cancel"
                ];

                $response = $payOS->createPaymentLink($data);

                $purchase->update([
                    'checkout_url' => $response['checkoutUrl']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tạo link thanh toán PayOS thành công.',
                    'data' => [
                        'purchase' => $purchase->load('package'),
                        'checkout_url' => $response['checkoutUrl'],
                        'payos_data' => $response // Chứa bin, accountNumber, accountName, etc.
                    ]
                ]);
            } catch (\Exception $e) {
                $purchase->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi tạo link thanh toán PayOS: ' . $e->getMessage()
                ], 500);
            }
        }

        // --- Luồng Manual ---
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

    // Webhook PayOS
    public function payosWebhook(Request $request)
    {
        try {
            $payOS = new \PayOS\PayOS(
                env('PAYOS_CLIENT_ID'),
                env('PAYOS_API_KEY'),
                env('PAYOS_CHECKSUM_KEY')
            );
            
            $body = json_decode($request->getContent(), true);
            $webhookData = $payOS->verifyPaymentWebhookData($body);

            // Tùy theo cách PayOS trả về, webhookData có thể là object hoặc array
            $orderCode = is_array($webhookData) ? $webhookData['orderCode'] : $webhookData->orderCode;
            
            $purchase = UserPurchase::where('payos_order_code', $orderCode)
                                    ->where('status', 'pending')
                                    ->first();
                                    
            if ($purchase) {
                // Cập nhật trạng thái và cộng quyền lợi
                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    $purchase->update(['status' => 'active']);
                    $user = $purchase->user;
                    $package = $purchase->package;

                    if ($package->type === 'vip') {
                        $currentExpiry = $user->vip_expires_at ? \Carbon\Carbon::parse($user->vip_expires_at) : now();
                        if ($currentExpiry->isPast()) {
                            $currentExpiry = now();
                        }
                        $user->vip_expires_at = $currentExpiry->addDays($package->duration_days);
                        $user->save();
                    } elseif ($package->type === 'post') {
                        $user->increment('available_post_quota', $package->post_quota);
                    }

                    \Illuminate\Support\Facades\DB::commit();

                    $user->notify(new \App\Notifications\PackagePurchaseApprovedNotification($purchase));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    \Illuminate\Support\Facades\Log::error('Error processing payos webhook inner: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'error' => 0,
                'message' => 'Ok',
                'data' => $webhookData
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PayOS Webhook Error: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Verify thanh toán chủ động (Fallback cho Localhost hoặc khi Webhook bị miss)
    public function verifyPayosOrder(Request $request)
    {
        $request->validate([
            'orderCode' => 'required'
        ]);

        $orderCode = $request->orderCode;
        $purchase = UserPurchase::where('payos_order_code', $orderCode)
                                ->where('status', 'pending')
                                ->first();

        if (!$purchase) {
            return response()->json(['success' => true, 'message' => 'Đơn hàng đã được xử lý hoặc không tồn tại.']);
        }

        try {
            $payOS = new \PayOS\PayOS(
                env('PAYOS_CLIENT_ID'),
                env('PAYOS_API_KEY'),
                env('PAYOS_CHECKSUM_KEY')
            );

            // Gọi API PayOS để lấy thông tin đơn hàng
            $paymentInfo = $payOS->getPaymentLinkInformation($orderCode);

            if (isset($paymentInfo['status']) && $paymentInfo['status'] === 'PAID') {
                \Illuminate\Support\Facades\DB::beginTransaction();
                try {
                    $purchase->update(['status' => 'active']);
                    $user = $purchase->user;
                    $package = $purchase->package;

                    if ($package->type === 'vip') {
                        $currentExpiry = $user->vip_expires_at ? \Carbon\Carbon::parse($user->vip_expires_at) : now();
                        if ($currentExpiry->isPast()) {
                            $currentExpiry = now();
                        }
                        $user->vip_expires_at = $currentExpiry->addDays($package->duration_days);
                        $user->save();
                    } elseif ($package->type === 'post') {
                        $user->increment('available_post_quota', $package->post_quota);
                    }

                    \Illuminate\Support\Facades\DB::commit();
                    $user->notify(new \App\Notifications\PackagePurchaseApprovedNotification($purchase));

                    return response()->json(['success' => true, 'message' => 'Đã xác nhận thanh toán.']);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], 500);
                }
            }

            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa được thanh toán.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối PayOS: ' . $e->getMessage()], 500);
        }
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

        // Nếu là đơn PayOS, gửi request hủy sang PayOS
        if ($purchase->payment_method === 'payos' && $purchase->payos_order_code) {
            try {
                $payOS = new \PayOS\PayOS(
                    env('PAYOS_CLIENT_ID'),
                    env('PAYOS_API_KEY'),
                    env('PAYOS_CHECKSUM_KEY')
                );
                $payOS->cancelPaymentLink(intval($purchase->payos_order_code), 'Người dùng hủy thanh toán');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Lỗi khi huỷ PayOS order: ' . $e->getMessage());
            }
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
