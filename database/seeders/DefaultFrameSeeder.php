<?php

namespace Database\Seeders;

use App\Models\Frame;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DefaultFrameSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting default frames seeding...');

        // Ensure frames directory exists in storage
        if (!Storage::disk('public')->exists('frames')) {
            Storage::disk('public')->makeDirectory('frames');
            $this->command->info('Created frames directory in storage');
        }

        $defaultFrames = [
            // Brown Frames
            [
                'name' => 'Brown Classic 2 Photos',
                'description' => 'Default brown frame for 2 photos',
                'color_code' => 'brown',
                'photo_count' => 2,
                'source_file' => 'public/frames/4R_brown2.png',
            ],
            [
                'name' => 'Brown Classic 3 Photos',
                'description' => 'Default brown frame for 3 photos',
                'color_code' => 'brown',
                'photo_count' => 3,
                'source_file' => 'public/frames/4R_brown3.png',
            ],
            [
                'name' => 'Brown Classic 4 Photos',
                'description' => 'Default brown frame for 4 photos',
                'color_code' => 'brown',
                'photo_count' => 4,
                'source_file' => 'public/frames/4R_brown4.png',
            ],
            
            // Cream Frames
            [
                'name' => 'Cream Classic 2 Photos',
                'description' => 'Default cream frame for 2 photos',
                'color_code' => 'cream',
                'photo_count' => 2,
                'source_file' => 'public/frames/4R_cream2.png',
            ],
            [
                'name' => 'Cream Classic 3 Photos',
                'description' => 'Default cream frame for 3 photos',
                'color_code' => 'cream',
                'photo_count' => 3,
                'source_file' => 'public/frames/4R_cream3.png',
            ],
            [
                'name' => 'Cream Classic 4 Photos',
                'description' => 'Default cream frame for 4 photos',
                'color_code' => 'cream',
                'photo_count' => 4,
                'source_file' => 'public/frames/4R_cream4.png',
            ],
        ];

        $successCount = 0;
        $errorCount = 0;

        foreach ($defaultFrames as $frameData) {
            // Source path in public folder
            $sourcePath = base_path($frameData['source_file']);
            
            // Destination in storage/app/public/frames
            $filename = basename($frameData['source_file']);
            $destPath = 'frames/' . $filename;
            
            $this->command->info("Processing: {$frameData['name']}");
            
            try {
                // Check if source file exists
                if (File::exists($sourcePath)) {
                    // Copy to storage if not exists or update
                    $storagePath = storage_path('app/public/' . $destPath);
                    
                    File::copy($sourcePath, $storagePath);
                    $this->command->info("  Copied image to storage");
                    
                    // Create or update frame record
                    $frame = Frame::updateOrCreate(
                        [
                            'color_code' => $frameData['color_code'],
                            'photo_count' => $frameData['photo_count'],
                            'is_default' => true,
                        ],
                        [
                            'name' => $frameData['name'],
                            'description' => $frameData['description'],
                            'image_path' => $destPath,
                            'is_active' => true,
                            'is_default' => true,
                            'uploaded_by' => null,
                        ]
                    );
                    
                    $this->command->info("  Database record created/updated (ID: {$frame->id})");
                    $successCount++;
                    
                } else {
                    $this->command->warn("  Source file not found: {$sourcePath}");
                    $this->command->warn("  Please copy frame images to: public/frames/");
                    $errorCount++;
                }
                
            } catch (\Exception $e) {
                $this->command->error("  Error: " . $e->getMessage());
                Log::error('Error seeding frame', [
                    'frame' => $frameData['name'],
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        $this->command->info("\nSeeding Summary:");
        $this->command->info("  Success: {$successCount}");
        $this->command->info("  Errors: {$errorCount}");
        
        if ($successCount > 0) {
            $this->command->info("\nDefault frames seeded successfully!");
        }
        
        if ($errorCount > 0) {
            $this->command->warn("\nSome frames could not be seeded. Check logs.");
        }
    }
}
