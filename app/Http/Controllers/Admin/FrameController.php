<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Frame;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FrameController extends Controller
{
    /**
     * Display a listing of frames
     */
    public function index()
    {
        $frames = Frame::with('category')
            ->withCount('photoStrips')
            ->latest()
            ->paginate(20);

        return view('admin.frames.index', compact('frames'));
    }

    /**
     * Show the form for creating a new frame
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.create', compact('categories'));
    }

    /**
     * Store a newly created frame
     */
    public function store(Request $request)
    {
        // Debug: Log incoming request
        Log::info('Frame store request received', [
            'all_data' => $request->all(),
            'has_file' => $request->hasFile('image_path'),
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'image_path' => 'required|image|mimes:jpeg,png,jpg|max:5120',
                'color_code' => 'required|string|in:brown,cream,white',
                'photo_count' => 'required|integer|in:2,3,4',
                'is_active' => 'nullable|boolean',
            ]);

            Log::info('Validation passed', $validated);

            // Upload frame image
            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');
                
                // Ensure frames directory exists
                if (!Storage::disk('public')->exists('frames')) {
                    Storage::disk('public')->makeDirectory('frames');
                }
                
                $filename = 'frame_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('frames', $filename, 'public');
                $validated['image_path'] = $path;

                Log::info('Image uploaded', ['path' => $path]);
            } else {
                Log::error('No file uploaded');
                return back()->withErrors(['image_path' => 'Please upload a frame image.'])->withInput();
            }

            // Handle checkbox
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['uploaded_by'] = auth()->id();

            Log::info('Creating frame', $validated);

            $frame = Frame::create($validated);

            Log::info('Frame created successfully', ['frame_id' => $frame->id]);

            return redirect()->route('admin.frames.index')
                ->with('success', 'Frame "' . $frame->name . '" berhasil ditambahkan! Sekarang tersedia di photobooth.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Error creating frame', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the frame
     */
    public function edit(Frame $frame)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.frames.edit', compact('frame', 'categories'));
    }

    /**
     * Update the specified frame
     */
    public function update(Request $request, Frame $frame)
    {
        Log::info('Frame update request received', [
            'frame_id' => $frame->id,
            'request_data' => $request->except('image_path'),
            'has_new_file' => $request->hasFile('image_path'),
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'nullable|exists:categories,id',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
                'color_code' => 'required|string|in:brown,cream,white',
                'photo_count' => 'required|integer|in:2,3,4',
                'is_active' => 'nullable|boolean',
            ]);

            // Upload new image if provided
            if ($request->hasFile('image_path')) {
                // Delete old image
                if ($frame->image_path && Storage::disk('public')->exists($frame->image_path)) {
                    $deleted = Storage::disk('public')->delete($frame->image_path);
                    Log::info('Old image deleted', [
                        'path' => $frame->image_path,
                        'success' => $deleted
                    ]);
                }
                
                $file = $request->file('image_path');
                $filename = 'frame_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('frames', $filename, 'public');
                $validated['image_path'] = $path;

                Log::info('New image uploaded', ['path' => $path]);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;

            $frame->update($validated);

            Log::info('Frame updated successfully', ['frame_id' => $frame->id]);

            return redirect()->route('admin.frames.index')
                ->with('success', 'Frame "' . $frame->name . '" berhasil diupdate!');

        } catch (\Exception $e) {
            Log::error('Error updating frame', [
                'frame_id' => $frame->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified frame (ENHANCED VERSION)
     */
    public function destroy(Frame $frame)
    {
        try {
            Log::info('Attempting to delete frame', [
                'frame_id' => $frame->id,
                'frame_name' => $frame->name,
                'image_path' => $frame->image_path,
                'photo_strips_count' => $frame->photoStrips()->count(),
            ]);

            // Check if frame is being used
            $usageCount = $frame->photoStrips()->count();
            if ($usageCount > 0) {
                Log::warning('Frame deletion blocked - still in use', [
                    'frame_id' => $frame->id,
                    'usage_count' => $usageCount,
                ]);

                return back()->with('error', "Cannot delete frame '{$frame->name}' because it is being used in {$usageCount} photo strips!");
            }

            // Store frame info before deletion
            $frameName = $frame->name;
            $imagePath = $frame->image_path;

            // Try to delete image file
            if ($imagePath) {
                Log::info('Attempting to delete image file', [
                    'path' => $imagePath,
                    'full_path' => storage_path('app/public/' . $imagePath),
                ]);
                
                if (Storage::disk('public')->exists($imagePath)) {
                    $deleted = Storage::disk('public')->delete($imagePath);
                    
                    if ($deleted) {
                        Log::info('Image file deleted successfully', ['path' => $imagePath]);
                    } else {
                        Log::warning('Failed to delete image file', [
                            'path' => $imagePath,
                            'exists_after' => Storage::disk('public')->exists($imagePath),
                        ]);
                    }
                } else {
                    Log::warning('Image file not found', [
                        'path' => $imagePath,
                        'attempted_full_path' => storage_path('app/public/' . $imagePath),
                    ]);
                }
            }

            // Delete frame record from database
            $frame->delete();

            Log::info('Frame deleted successfully', [
                'frame_name' => $frameName,
                'had_image' => !empty($imagePath),
            ]);

            return redirect()->route('admin.frames.index')
                ->with('success', "Frame '{$frameName}' berhasil dihapus!");

        } catch (\Exception $e) {
            Log::error('Error deleting frame', [
                'frame_id' => $frame->id ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Toggle frame active status
     */
    public function toggle(Frame $frame)
    {
        try {
            $previousStatus = $frame->is_active;
            $frame->update(['is_active' => !$frame->is_active]);

            $status = $frame->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Frame status toggled', [
                'frame_id' => $frame->id,
                'frame_name' => $frame->name,
                'previous_status' => $previousStatus,
                'new_status' => $frame->is_active,
            ]);

            return back()->with('success', "Frame '{$frame->name}' berhasil {$status}.");

        } catch (\Exception $e) {
            Log::error('Error toggling frame', [
                'frame_id' => $frame->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Force delete frame with image (admin override)
     * Use with caution - this will delete even if frame is in use
     */
    public function forceDestroy(Frame $frame)
    {
        try {
            Log::warning('FORCE DELETE initiated', [
                'frame_id' => $frame->id,
                'frame_name' => $frame->name,
                'usage_count' => $frame->photoStrips()->count(),
                'admin_user' => auth()->user()->email,
            ]);

            $frameName = $frame->name;
            $imagePath = $frame->image_path;

            // Force delete image
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info('Image force deleted', ['path' => $imagePath]);
            }

            // Force delete frame (will cascade to photo_strips if configured)
            $frame->delete();

            Log::warning('Frame force deleted', ['frame_name' => $frameName]);

            return redirect()->route('admin.frames.index')
                ->with('warning', "Frame '{$frameName}' telah dihapus secara paksa!");

        } catch (\Exception $e) {
            Log::error('Error force deleting frame', [
                'frame_id' => $frame->id,
                'message' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
