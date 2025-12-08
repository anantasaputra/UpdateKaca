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
        // Fix frame paths yang mungkin salah format
        DB::table('frames')
            ->where('image_path', 'NOT LIKE', 'frames/%')
            ->update([
                'image_path' => DB::raw("CONCAT('frames/', SUBSTRING_INDEX(image_path, '/', -1))")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
