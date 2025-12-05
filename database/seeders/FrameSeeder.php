<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Frame;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FrameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('is_admin', true)->first();
        
        // Get categories
        $cuteCategory = Category::where('slug', 'cute')->first();
        $minimalisCategory = Category::where('slug', 'minimalis')->first();
        $formalCategory = Category::where('slug', 'formal')->first();
        $coupleCategory = Category::where('slug', 'couple')->first();

        // Create sample frames
        // NOTE: You need to create actual PNG frame files in storage/app/public/frames/
        // For now, we'll create placeholder entries
        
        $frames = [
            [
                'name' => 'Hearts Border',
                'filename' => 'frame_hearts_border.png',
                'category_id' => $cuteCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Cute Stars',
                'filename' => 'frame_cute_stars.png',
                'category_id' => $cuteCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Simple Black',
                'filename' => 'frame_simple_black.png',
                'category_id' => $minimalisCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Clean White',
                'filename' => 'frame_clean_white.png',
                'category_id' => $minimalisCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Professional Gold',
                'filename' => 'frame_professional_gold.png',
                'category_id' => $formalCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Classic Silver',
                'filename' => 'frame_classic_silver.png',
                'category_id' => $formalCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Love Hearts',
                'filename' => 'frame_love_hearts.png',
                'category_id' => $coupleCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
            [
                'name' => 'Together Forever',
                'filename' => 'frame_together_forever.png',
                'category_id' => $coupleCategory->id,
                'is_active' => true,
                'uploaded_by' => $admin->id,
            ],
        ];

        foreach ($frames as $frameData) {
            Frame::create($frameData);
        }

        // Create placeholder PNG files
        $this->createPlaceholderFrames($frames);
    }

    /**
     * Create placeholder frame files
     */
    private function createPlaceholderFrames(array $frames)
    {
        $storagePath = storage_path('app/public/frames');
        
        // Ensure directory exists
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        foreach ($frames as $frame) {
            $filePath = $storagePath . '/' . $frame['filename'];
            
            // Only create if doesn't exist
            if (!File::exists($filePath)) {
                // Create a simple transparent PNG placeholder
                // In production, replace these with actual frame images
                $this->createTransparentPNG($filePath, 800, 600);
            }
        }
    }

    /**
     * Create a transparent PNG placeholder
     */
    private function createTransparentPNG($filePath, $width, $height)
    {
        // Create image
        $image = imagecreatetruecolor($width, $height);
        
        // Enable alpha blending
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        
        // Draw a simple border as frame
        $brown = imagecolorallocate($image, 82, 37, 4); // #522504
        $borderWidth = 20;
        
        // Top border
        imagefilledrectangle($image, 0, 0, $width, $borderWidth, $brown);
        // Bottom border
        imagefilledrectangle($image, 0, $height - $borderWidth, $width, $height, $brown);
        // Left border
        imagefilledrectangle($image, 0, 0, $borderWidth, $height, $brown);
        // Right border
        imagefilledrectangle($image, $width - $borderWidth, 0, $width, $height, $brown);
        
        // Save as PNG
        imagepng($image, $filePath);
        imagedestroy($image);
    }
}