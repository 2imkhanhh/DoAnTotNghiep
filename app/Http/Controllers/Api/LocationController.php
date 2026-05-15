<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Lấy danh sách Tỉnh/Thành phố
     */
    public function getProvinces()
    {
        try {
            $response = Http::get('https://provinces.open-api.vn/api/v2/p/');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tải danh sách tỉnh thành'], 500);
        }
    }

    /**
     * Lấy danh sách Phường/Xã theo mã Tỉnh (Cấu trúc 2 cấp mới)
     */
    public function getWards($provinceCode)
    {
        try {
            $response = Http::get("https://provinces.open-api.vn/api/v2/p/{$provinceCode}?depth=2");
            $data = $response->json();
            return response()->json($data['wards'] ?? []);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể tải danh sách phường xã'], 500);
        }
    }
}
