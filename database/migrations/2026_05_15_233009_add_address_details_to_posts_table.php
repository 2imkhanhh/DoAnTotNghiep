<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('province_id')->nullable()->after('address');
            $table->string('province_name')->nullable()->after('province_id');
            $table->integer('district_id')->nullable()->after('province_name');
            $table->string('district_name')->nullable()->after('district_id');
            $table->integer('ward_id')->nullable()->after('district_name');
            $table->string('ward_name')->nullable()->after('ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'province_name', 'district_id', 'district_name', 'ward_id', 'ward_name']);
        });
    }
};
