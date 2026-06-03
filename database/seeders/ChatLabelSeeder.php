<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLabels = [
            [
                'name' => 'Khách mới',
                'color_code' => '#ef4444', // Red
                'is_default' => true,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hẹn gặp',
                'color_code' => '#3b82f6', // Blue
                'is_default' => true,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tin rác / Bỏ qua',
                'color_code' => '#1e293b', // Slate/Black
                'is_default' => true,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert using DB facade to avoid mass assignment issues if any
        DB::table('chat_labels')->insert($defaultLabels);
    }
}
