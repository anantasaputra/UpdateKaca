<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageLinks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'storage:fix-links';

    /**
     * The console command description.
     */
    protected $description = 'Fix storage symlinks and create necessary directories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing storage links and directories...');
        
        // 1. Remove old symlink if exists
        $publicStorage = public_path('storage');
        if (File::exists($publicStorage)) {
            if (is_link($publicStorage)) {
                File::delete($publicStorage);
                $this->info('✅ Removed old storage symlink');
            } else {
                $this->warn('⚠️  public/storage exists but is not a symlink!');
            }
        }
        
        // 2. Create new symlink
        $target = storage_path('app/public');
        if (!File::exists($target)) {
            File::makeDirectory($target, 0755, true);
            $this->info('✅ Created storage/app/public directory');
        }
        
        try {
            File::link($target, $publicStorage);
            $this->info('✅ Created storage symlink: public/storage -> storage/app/public');
        } catch (\Exception $e) {
            $this->error('❌ Failed to create symlink: ' . $e->getMessage());
            $this->warn('💡 Try running: ln -s ' . $target . ' ' . $publicStorage);
        }
        
        // 3. Create required directories
        $directories = [
            storage_path('app/public/frames'),
            storage_path('app/public/photos'),
            storage_path('app/public/strips'),
        ];
        
        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->info("✅ Created directory: {$dir}");
            } else {
                $this->comment("📁 Directory already exists: {$dir}");
            }
        }
        
        // 4. Verify everything
        $this->info('');
        $this->info('🔍 Verifying setup...');
        
        if (is_link($publicStorage)) {
            $this->info('✅ Symlink exists: public/storage');
        } else {
            $this->error('❌ Symlink not created properly');
        }
        
        foreach ($directories as $dir) {
            if (File::exists($dir) && File::isWritable($dir)) {
                $this->info('✅ Directory OK: ' . basename($dir));
            } else {
                $this->error('❌ Directory issue: ' . basename($dir));
            }
        }
        
        $this->info('');
        $this->info('🎉 Storage links fixed successfully!');
        $this->warn('⚠️  Next steps:');
        $this->warn('   1. Copy your frame files to: storage/app/public/frames/');
        $this->warn('   2. Run: php artisan migrate:fresh --seed');
        
        return 0;
    }
}
