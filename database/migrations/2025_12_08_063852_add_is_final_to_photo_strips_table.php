<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photo_strips', function (Blueprint $table) {
            $table->boolean('is_final')->default(false)->after('frame_id');
        });
    }

    public function down()
    {
        Schema::table('photo_strips', function (Blueprint $table) {
            $table->dropColumn('is_final');
        });
    }
};
