<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan frame double strip mode
        $frames = [
            [
                'name' => 'Brown 2-Strip Double',
                'image_path' => 'storage/app/public/frames/frame-brown-2strip-double.png',
                'photo_count' => 2,
                'color_code' => 'brown',
                'is_active' => true,
                'is_default' => true,
                'is_double_strip' => true,
                'category_id' => null,
                'usage_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brown 3-Strip Double',
                'image_path' => 'storage/app/public/frames/frame-brown-3strip-double.png',
                'photo_count' => 3,
                'color_code' => 'brown',
                'is_active' => true,
                'is_default' => true,
                'is_double_strip' => true,
                'category_id' => null,
                'usage_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Brown 4-Strip Double',
                'image_path' => 'storage/app/public/frames/frame-brown-4strip-double.png',
                'photo_count' => 4,
                'color_code' => 'brown',
                'is_active' => true,
                'is_default' => true,
                'is_double_strip' => true,
                'category_id' => null,
                'usage_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('frames')->insert($frames);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('frames')
            ->where('is_double_strip', true)
            ->delete();
    }
};
