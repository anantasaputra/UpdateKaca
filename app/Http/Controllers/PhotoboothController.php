<?php

namespace App\Http\Controllers;

use App\Models\Frame;
use App\Models\Photo;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PhotoboothController extends Controller
{
    /**
     * ✅ FIXED: Display the photobooth interface with all active frames
     * Enhanced path handling with multiple fallback options
     */
    public function index()
    {
        // Get ALL active frames (both default and custom)
        $frames = Frame::where('is_active', true)
            ->with('category')
            ->orderBy('is_default', 'desc') // Default frames first
            ->orderBy('photo_count', 'asc')
            ->orderBy('color_code', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Group by photo count untuk display
        $framesByCount = $frames->groupBy('photo_count');

        // ✅ FIXED: Generate frame URLs dengan multiple fallback
        $framesJson = $frames->map(function($frame) {
            $basename = basename($frame->image_path);
            $imageUrl = null;
            
            // Try 1: asset() storage path (via symlink)
            $storagePath = 'storage/frames/' . $basename;
            if (file_exists(public_path($storagePath))) {
                $imageUrl = asset($storagePath);
            }
            // Try 2: public/frames direct
            elseif (file_exists(public_path('frames/' . $basename))) {
                $imageUrl = asset('frames/' . $basename);
            }
            // Try 3: Storage disk check
            elseif (Storage::disk('public')->exists($frame->image_path)) {
                $imageUrl = Storage::url($frame->image_path);
            }
            else {
                Log::error('Frame image not found in any location', [
                    'frame_id' => $frame->id,
                    'name' => $frame->name,
                    'path' => $frame->image_path,
                    'basename' => $basename,
                    'checked_paths' => [
                        'storage' => public_path($storagePath),
                        'public_frames' => public_path('frames/' . $basename),
                        'disk_exists' => Storage::disk('public')->exists($frame->image_path),
                    ]
                ]);
                $imageUrl = asset('images/placeholder-frame.png');
            }
            
            return [
                'id' => $frame->id,
                'name' => $frame->name,
                'photo_count' => $frame->photo_count,
                'color_code' => $frame->color_code,
                'image_path' => $imageUrl, // ✅ Validated URL
                'is_default' => $frame->is_default,
            ];
        });

        Log::info('Photobooth loaded', [
            'total_active_frames' => $frames->count(),
            'frames_by_count' => $framesByCount->map->count()->toArray(),
            'default_frames' => $frames->where('is_default', true)->count(),
            'custom_frames' => $frames->where('is_default', false)->count(),
            'frames_json_count' => $framesJson->count(),
        ]);

        return view('photobooth', compact('framesByCount', 'framesJson'));
    }

    /**
     * Upload photo from webcam/file
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // base64 image data
        ]);

        try {
            $imageData = $request->input('photo');
            
            // Extract image type from base64 string
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
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
            
            // Ensure photos directory exists
            if (!Storage::disk('public')->exists('photos')) {
                Storage::disk('public')->makeDirectory('photos');
            }
            
            // Save to storage
            Storage::disk('public')->put('photos/' . $filename, $imageData);

            // Create photo record
            $photo = Photo::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $filename,
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]);

            Log::info('Photo uploaded', [
                'photo_id' => $photo->id,
                'filename' => $filename,
            ]);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => Storage::url('photos/' . $filename),
                'photo_id' => $photo->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading photo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ UPDATED: Compose photo strip with selected frame
     * Simpan HANYA 1 RECORD per session
     */
    public function composeStrip(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // Single final canvas image
            'frame_id' => 'nullable|exists:frames,id',
            'photo_count' => 'required|integer|in:2,3,4',
        ]);

        try {
            $imageData = $request->input('image');
            $frameId = $request->input('frame_id');
            $photoCount = $request->input('photo_count');
            $userId = auth()->id();
            $guestSessionId = $userId ? null : session()->getId();

            // ✅ PENTING: Cek apakah user/guest sudah punya strip yang belum disimpan
            // Jika ada, hapus yang lama untuk menghindari duplikasi
            if ($userId) {
                $deleted = PhotoStrip::where('user_id', $userId)
                    ->where('is_saved', false)
                    ->where('photo_count', $photoCount)
                    ->get();
                
                // Delete old files
                foreach ($deleted as $oldStrip) {
                    if (Storage::disk('public')->exists($oldStrip->final_image_path)) {
                        Storage::disk('public')->delete($oldStrip->final_image_path);
                    }
                }
                
                // Delete records
                PhotoStrip::where('user_id', $userId)
                    ->where('is_saved', false)
                    ->where('photo_count', $photoCount)
                    ->delete();
                    
                Log::info('Deleted old unsaved strips for user', [
                    'user_id' => $userId,
                    'count' => $deleted->count()
                ]);
            } else {
                $deleted = PhotoStrip::where('guest_session_id', $guestSessionId)
                    ->where('is_saved', false)
                    ->where('photo_count', $photoCount)
                    ->get();
                
                // Delete old files
                foreach ($deleted as $oldStrip) {
                    if (Storage::disk('public')->exists($oldStrip->final_image_path)) {
                        Storage::disk('public')->delete($oldStrip->final_image_path);
                    }
                }
                
                // Delete records
                PhotoStrip::where('guest_session_id', $guestSessionId)
                    ->where('is_saved', false)
                    ->where('photo_count', $photoCount)
                    ->delete();
                    
                Log::info('Deleted old unsaved strips for guest', [
                    'session_id' => $guestSessionId,
                    'count' => $deleted->count()
                ]);
            }

            // Validate frame if provided
            $frame = null;
            if ($frameId) {
                $frame = Frame::where('id', $frameId)
                    ->where('is_active', true)
                    ->first();

                if (!$frame) {
                    return response()->json([
                        'error' => 'Selected frame is not available.'
                    ], 400);
                }

                Log::info('Using frame', [
                    'frame_id' => $frame->id,
                    'frame_name' => $frame->name,
                    'is_default' => $frame->is_default,
                    'image_path' => $frame->image_path,
                ]);
            }

            // Extract and decode base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            } else {
                return response()->json(['error' => 'Invalid image format'], 400);
            }
            
            $imageData = base64_decode($imageData);
            
            if ($imageData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 400);
            }

            // Generate unique strip filename
            $stripFilename = 'strip_' . time() . '_' . Str::random(10) . '.png';
            $stripPath = 'strips/' . $stripFilename;

            // Ensure strips directory exists
            if (!Storage::disk('public')->exists('strips')) {
                Storage::disk('public')->makeDirectory('strips');
            }

            // Save strip image
            $saved = Storage::disk('public')->put($stripPath, $imageData);

            if (!$saved) {
                throw new \Exception('Failed to save strip image');
            }

            // Increment frame usage if frame is used
            if ($frameId && $frame) {
                $frame->increment('usage_count');
                Log::info('Frame usage incremented', [
                    'frame_id' => $frame->id,
                    'new_usage_count' => $frame->usage_count,
                ]);
            }

            // ✅ Create HANYA 1 photo strip record per session
            $photoStrip = PhotoStrip::create([
                'user_id' => $userId,
                'guest_session_id' => $guestSessionId,
                'frame_id' => $frameId,
                'photo_data' => ['final_canvas'], // Metadata
                'final_image_path' => $stripPath,
                'photo_count' => $photoCount,
                'ip_address' => $request->ip(),
                'is_saved' => false, // Default false
            ]);

            Log::info('Photo strip created successfully (SINGLE RECORD)', [
                'strip_id' => $photoStrip->id,
                'frame_id' => $frameId,
                'photo_count' => $photoCount,
                'user_id' => $userId,
                'guest_session_id' => $guestSessionId,
                'is_saved' => false,
            ]);

            return response()->json([
                'success' => true,
                'strip_url' => Storage::url($stripPath),
                'strip_id' => $photoStrip->id,
                'download_url' => route('photobooth.download', $photoStrip->id),
                'is_authenticated' => auth()->check(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error composing strip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Update existing strip (untuk ganti frame/retake)
     */
    public function updateStrip(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|string',
            'frame_id' => 'nullable|exists:frames,id',
            'photo_count' => 'required|integer|in:2,3,4',
        ]);

        try {
            $strip = PhotoStrip::findOrFail($id);

            // Check ownership
            $userId = auth()->id();
            $guestSessionId = session()->getId();
            
            if ($strip->user_id && $strip->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            
            // Check guest session
            if (!$strip->user_id && $strip->guest_session_id !== $guestSessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            $imageData = $request->input('image');

            // Extract and decode base64 image
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            } else {
                return response()->json(['error' => 'Invalid image format'], 400);
            }
            
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 400);
            }

            // Delete old file
            if (Storage::disk('public')->exists($strip->final_image_path)) {
                Storage::disk('public')->delete($strip->final_image_path);
            }

            // Generate new filename
            $stripFilename = 'strip_' . time() . '_' . Str::random(10) . '.png';
            $stripPath = 'strips/' . $stripFilename;

            // Save new image
            Storage::disk('public')->put($stripPath, $imageData);

            // Update record
            $strip->update([
                'frame_id' => $request->input('frame_id'),
                'final_image_path' => $stripPath,
                'photo_count' => $request->input('photo_count'),
            ]);

            Log::info('Photo strip updated', [
                'strip_id' => $strip->id,
                'new_frame_id' => $request->input('frame_id'),
            ]);

            return response()->json([
                'success' => true,
                'strip_url' => Storage::url($stripPath),
                'strip_id' => $strip->id,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Photo strip tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating strip', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ UPDATED: Save strip to user profile
     */
    public function saveStrip(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu untuk menyimpan photo strip.'
            ], 401);
        }

        try {
            $strip = PhotoStrip::findOrFail($id);
            
            // Check ownership or guest session
            if ($strip->user_id && $strip->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }

            // ✅ Update strip: assign to user dan mark as saved
            $strip->update([
                'user_id' => auth()->id(),
                'guest_session_id' => null, // Clear guest session
                'is_saved' => true,
            ]);

            Log::info('Photo strip saved to user profile', [
                'strip_id' => $id,
                'user_id' => auth()->id(),
                'is_saved' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo strip berhasil disimpan ke profil Anda!',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Photo strip tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error saving strip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download photo strip
     */
    public function download($id)
    {
        try {
            $strip = PhotoStrip::findOrFail($id);

            $filePath = storage_path('app/public/' . $strip->final_image_path);

            if (!file_exists($filePath)) {
                Log::error('Strip file not found', [
                    'strip_id' => $id,
                    'path' => $filePath,
                ]);
                abort(404, 'File not found');
            }

            $filename = 'bingkiskaca_' . $strip->id . '_' . time() . '.png';

            Log::info('Photo strip downloaded', [
                'strip_id' => $id,
                'user_id' => auth()->id(),
                'filename' => $filename,
            ]);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'image/png',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Photo strip not found for download: ' . $id);
            abort(404, 'Photo strip not found');
        } catch (\Exception $e) {
            Log::error('Error downloading strip', [
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error downloading file');
        }
    }
}
