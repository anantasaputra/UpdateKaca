<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update all frame paths from .jpg to .png
        DB::table('frames')
            ->where('image_path', 'LIKE', '%.jpg')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, '.jpg', '.png')")
            ]);
    }

    public function down()
    {
        // Rollback: change back to .jpg
        DB::table('frames')
            ->where('image_path', 'LIKE', '%.png')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, '.png', '.jpg')")
            ]);
    }
};
