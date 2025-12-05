<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Frame;
use App\Models\Photo;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PhotoboothController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with(['activeFrames' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('photobooth', compact('categories'));
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // base64 image data
        ]);

        try {
            // Decode base64 image
            $imageData = $request->input('photo');
            
            // Remove data:image/png;base64, prefix if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
            } else {
                return response()->json(['error' => 'Invalid image format'], 400);
            }

            // Decode base64
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 400);
            }

            // Generate unique filename
            $filename = 'photo_' . time() . '_' . Str::random(10) . '.' . $type;

            // Save to storage
            Storage::disk('public')->put('photos/' . $filename, $imageData);

            // Save to database
            $photo = Photo::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $filename,
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => Storage::url('photos/' . $filename),
                'photo_id' => $photo->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function composeStrip(Request $request)
    {
        $request->validate([
            'photos' => 'required|array|min:2|max:4',
            'photos.*' => 'required|string', // base64 or filename
            'frame_id' => 'nullable|exists:frames,id',
            'photo_count' => 'required|integer|min:2|max:4',
        ]);

        try {
            $photos = $request->input('photos');
            $frameId = $request->input('frame_id');
            $photoCount = $request->input('photo_count');

            // Load frame if provided
            $frame = null;
            $frameImage = null;
            if ($frameId) {
                $frame = Frame::findOrFail($frameId);
                $frameImage = Image::read(storage_path('app/public/frames/' . $frame->filename));
                
                // Increment frame usage count
                $frame->incrementUsage();
            }

            // Strip dimensions (portrait orientation for photobooth)
            $photoWidth = 800;
            $photoHeight = 600;
            $stripWidth = $photoWidth;
            $stripHeight = $photoHeight * $photoCount;

            // Create blank canvas
            $strip = Image::create($stripWidth, $stripHeight)
                ->fill('ffffff');

            // Process each photo
            foreach ($photos as $index => $photoData) {
                // Decode base64 if needed
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $photoData = base64_decode($photoData);
                    $photoImage = Image::read($photoData);
                } else {
                    // Assume it's a filename
                    $photoImage = Image::read(storage_path('app/public/photos/' . $photoData));
                }

                // Resize photo to fit
                $photoImage->cover($photoWidth, $photoHeight);

                // Calculate position (vertical stacking)
                $yPosition = $index * $photoHeight;

                // Place photo on strip
                $strip->place($photoImage, 'top-left', 0, $yPosition);

                // Overlay frame if exists
                if ($frameImage) {
                    $frameResized = clone $frameImage;
                    $frameResized->resize($photoWidth, $photoHeight);
                    $strip->place($frameResized, 'top-left', 0, $yPosition);
                }
            }

            // Generate unique filename for strip
            $stripFilename = 'strip_' . time() . '_' . Str::random(10) . '.png';
            $stripPath = 'strips/' . $stripFilename;

            // Save strip
            Storage::disk('public')->put($stripPath, $strip->toPng());

            // Save to database
            $photoStrip = PhotoStrip::create([
                'user_id' => auth()->id(),
                'frame_id' => $frameId,
                'photo_data' => $photos,
                'final_image_path' => $stripPath,
                'photo_count' => $photoCount,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'strip_url' => Storage::url($stripPath),
                'strip_id' => $photoStrip->id,
                'download_url' => route('photobooth.download', $photoStrip->id),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        $strip = PhotoStrip::findOrFail($id);

        // Check authorization (optional - allow public download or restrict to owner)
        // if ($strip->user_id && $strip->user_id !== auth()->id()) {
        //     abort(403);
        // }

        $filePath = storage_path('app/public/' . $strip->final_image_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, 'bingkiskaca_' . $strip->id . '.png');
    }
}