<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_strips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('frame_id')->nullable()->constrained()->onDelete('set null');
            $table->json('photo_data'); // Array of photo filenames
            $table->string('final_image_path');
            $table->integer('photo_count');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_strips');
    }
};