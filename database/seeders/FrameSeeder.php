<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Frame;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FrameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user (optional)
        $admin = User::where('is_admin', true)->first();
        
        // Get categories (optional)
        $cuteCategory = Category::where('slug', 'cute')->first();
        $classicCategory = Category::where('slug', 'classic')->first();
        $weddingCategory = Category::where('slug', 'wedding')->first();
        $birthdayCategory = Category::where('slug', 'birthday')->first();

        // ✅ CRITICAL: Ensure directories exist
        $this->ensureDirectoriesExist();

        // ✅ UPDATED: Frame data sesuai dengan file yang Anda punya
        $frames = [
            // Brown frames (2, 3, 4 photos)
            [
                'name' => 'Brown Frame 2 Photos',
                'description' => 'Classic brown frame for 2 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_brown2.png',
                'color_code' => 'brown',
                'photo_count' => 2,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Brown Frame 3 Photos',
                'description' => 'Classic brown frame for 3 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_brown3.png',
                'color_code' => 'brown',
                'photo_count' => 3,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Brown Frame 4 Photos',
                'description' => 'Classic brown frame for 4 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_brown4.png',
                'color_code' => 'brown',
                'photo_count' => 4,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],

            // Cream frames (2, 3, 4 photos)
            [
                'name' => 'Cream Frame 2 Photos',
                'description' => 'Elegant cream frame for 2 photos',
                'category_id' => $weddingCategory?->id,
                'image_path' => 'frames/4R_cream2.png',
                'color_code' => 'cream',
                'photo_count' => 2,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Cream Frame 3 Photos',
                'description' => 'Elegant cream frame for 3 photos',
                'category_id' => $weddingCategory?->id,
                'image_path' => 'frames/4R_cream3.png',
                'color_code' => 'cream',
                'photo_count' => 3,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Cream Frame 4 Photos',
                'description' => 'Elegant cream frame for 4 photos',
                'category_id' => $weddingCategory?->id,
                'image_path' => 'frames/4R_cream4.png',
                'color_code' => 'cream',
                'photo_count' => 4,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],

            // ✅ White frames (uncomment jika sudah ada filenya)
            [
                'name' => 'White Frame 2 Photos',
                'description' => 'Clean white frame for 2 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_white2.png',
                'color_code' => 'white',
                'photo_count' => 2,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'White Frame 3 Photos',
                'description' => 'Clean white frame for 3 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_white3.png',
                'color_code' => 'white',
                'photo_count' => 3,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'White Frame 4 Photos',
                'description' => 'Clean white frame for 4 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/4R_white4.png',
                'color_code' => 'white',
                'photo_count' => 4,
                'is_default' => true,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
        ];

        $this->command->info('Seeding frames...');

        // ✅ Insert frames ke database
        foreach ($frames as $frameData) {
            // Check if frame already exists
            $existing = Frame::where('image_path', $frameData['image_path'])->first();
            
            if ($existing) {
                $this->command->warn(" Frame already exists: {$frameData['name']}");
                continue;
            }

            Frame::create($frameData);
            $this->command->info("Created: {$frameData['name']}");
        }

        // ✅ Verify frame files exist
        $this->verifyFrameFiles($frames);

        $this->command->info('');
        $this->command->info('Frames seeded successfully!');
        $this->command->warn(' Make sure frame PNG files exist in storage/app/public/frames/');
    }

    /**
     * ✅ NEW: Ensure all required directories exist
     */
    private function ensureDirectoriesExist()
    {
        $directories = [
            storage_path('app/public/frames'),
            storage_path('app/public/photos'),
            storage_path('app/public/strips'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->command->info("Created directory: {$dir}");
            }
        }

        // ✅ Create symlink if not exists
        $symlinkTarget = public_path('storage');
        if (!File::exists($symlinkTarget)) {
            $this->command->warn('Storage symlink not found. Run: php artisan storage:link');
        }
    }

    /**
     * ✅ Verify frame files exist in storage
     */
    private function verifyFrameFiles(array $frames)
    {
        $this->command->info('');
        $this->command->info('Verifying frame files...');

        $storagePath = storage_path('app/public/frames');
        $missingFiles = [];

        foreach ($frames as $frame) {
            $filename = basename($frame['image_path']);
            $filePath = $storagePath . '/' . $filename;
            
            if (File::exists($filePath)) {
                $size = File::size($filePath);
                $sizeKB = round($size / 1024, 2);
                $this->command->info("{$filename} ({$sizeKB} KB)");
            } else {
                $this->command->error("Missing: {$filename}");
                $missingFiles[] = $filename;
            }
        }

        if (!empty($missingFiles)) {
            $this->command->error('');
            $this->command->error('Missing frame files:');
            foreach ($missingFiles as $file) {
                $this->command->error("   - {$file}");
            }
            $this->command->error('');
            $this->command->warn('Please upload these files to: storage/app/public/frames/');
            $this->command->warn('Or run: php artisan frames:create-placeholders');
        }
    }

    /**
     * ✅ OPTIONAL: Create placeholder frames if files don't exist
     * Uncomment untuk auto-generate placeholder
     */
    private function createPlaceholderFrames(array $frames)
    {
        $storagePath = storage_path('app/public/frames');

        foreach ($frames as $frame) {
            $filename = basename($frame['image_path']);
            $filePath = $storagePath . '/' . $filename;
            
            // Only create if doesn't exist
            if (!File::exists($filePath)) {
                $this->createFramePNG(
                    $filePath, 
                    $frame['color_code'],
                    $frame['photo_count']
                );
                $this->command->info("🎨 Created placeholder: {$filename}");
            }
        }
    }

    /**
     * Create a simple frame PNG placeholder
     */
    private function createFramePNG($filePath, $colorCode, $photoCount)
    {
        // Frame dimensions (4R photo size: 1200x1800px)
        $width = 1200;
        $height = 1800;
        
        // Create image
        $image = imagecreatetruecolor($width, $height);
        
        // Enable alpha blending for transparency
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        
        // Get frame color
        $frameColor = $this->getFrameColor($image, $colorCode);
        
        // Draw border
        $borderWidth = 40;
        
        // Outer border (top, bottom, left, right)
        imagefilledrectangle($image, 0, 0, $width, $borderWidth, $frameColor);
        imagefilledrectangle($image, 0, $height - $borderWidth, $width, $height, $frameColor);
        imagefilledrectangle($image, 0, 0, $borderWidth, $height, $frameColor);
        imagefilledrectangle($image, $width - $borderWidth, 0, $width, $height, $frameColor);
        
        // Calculate photo dividers
        if ($photoCount > 1) {
            $dividerHeight = 10;
            $photoHeight = ($height - ($borderWidth * 2) - ($dividerHeight * ($photoCount - 1))) / $photoCount;
            
            for ($i = 1; $i < $photoCount; $i++) {
                $y = $borderWidth + ($photoHeight * $i) + ($dividerHeight * ($i - 1));
                imagefilledrectangle($image, $borderWidth, $y, $width - $borderWidth, $y + $dividerHeight, $frameColor);
            }
        }
        
        // Save as PNG
        imagepng($image, $filePath);
        imagedestroy($image);
    }

    /**
     * Get frame color based on color code
     */
    private function getFrameColor($image, $colorCode)
    {
        switch ($colorCode) {
            case 'brown':
                return imagecolorallocate($image, 139, 69, 19);
            case 'cream':
                return imagecolorallocate($image, 245, 222, 179);
            case 'white':
                return imagecolorallocate($image, 255, 255, 255);
            default:
                return imagecolorallocate($image, 139, 69, 19);
        }
    }
}
