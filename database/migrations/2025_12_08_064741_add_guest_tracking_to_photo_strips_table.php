<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('photo_strips', function (Blueprint $table) {
            // ✅ Tambahkan kolom guest_session_id jika belum ada
            if (!Schema::hasColumn('photo_strips', 'guest_session_id')) {
                $table->string('guest_session_id')->nullable()->after('user_id');
            }
            
            // ✅ Tambahkan kolom is_saved jika belum ada
            if (!Schema::hasColumn('photo_strips', 'is_saved')) {
                $table->boolean('is_saved')->default(false)->after('ip_address');
            }
        });

        // ✅ Tambahkan index HANYA jika belum ada
        $this->createIndexIfNotExists('photo_strips', 'guest_session_id');
        $this->createIndexIfNotExists('photo_strips', 'is_saved');
    }

    public function down()
    {
        Schema::table('photo_strips', function (Blueprint $table) {
            // Drop index jika ada
            $this->dropIndexIfExists('photo_strips', 'guest_session_id');
            $this->dropIndexIfExists('photo_strips', 'is_saved');
            
            // Drop columns
            if (Schema::hasColumn('photo_strips', 'guest_session_id')) {
                $table->dropColumn('guest_session_id');
            }
            if (Schema::hasColumn('photo_strips', 'is_saved')) {
                $table->dropColumn('is_saved');
            }
        });
    }

    /**
     * ✅ FIXED: Create index jika belum ada (tanpa $this->command)
     */
    private function createIndexIfNotExists($table, $column)
    {
        $indexName = "{$table}_{$column}_index";
        
        // Cek apakah index sudah ada
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        
        if (empty($indexes)) {
            DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$column})");
            // ✅ HAPUS $this->command, gunakan echo atau skip
            echo "Index {$indexName} created\n";
        } else {
            // ✅ HAPUS $this->command
            echo "Index {$indexName} already exists, skipping...\n";
        }
    }

    /**
     * ✅ FIXED: Drop index jika ada
     */
    private function dropIndexIfExists($table, $column)
    {
        $indexName = "{$table}_{$column}_index";
        
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        
        if (!empty($indexes)) {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$indexName}");
            echo "Index {$indexName} dropped\n";
        }
    }
};
