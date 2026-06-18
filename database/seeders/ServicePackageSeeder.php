<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServicePackage;

class ServicePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServicePackage::insert([
            [
                'name' => 'Gói VIP 1 Tháng',
                'type' => 'vip',
                'price' => 200000,
                'duration_days' => 30,
                'post_quota' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói 1 tin đăng',
                'type' => 'post',
                'price' => 5000,
                'duration_days' => null,
                'post_quota' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói 5 tin đăng',
                'type' => 'post',
                'price' => 20000,
                'duration_days' => null,
                'post_quota' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói 10 tin đăng',
                'type' => 'post',
                'price' => 30000,
                'duration_days' => null,
                'post_quota' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
