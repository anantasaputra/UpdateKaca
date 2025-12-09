<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FrameController as AdminFrameController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\Admin\PhotoStripController as AdminPhotoStripController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoboothController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/help', [HomeController::class, 'help'])->name('help');

/*
|--------------------------------------------------------------------------
| Photobooth Routes
|--------------------------------------------------------------------------
| ✅ UPDATED: Added update-strip route for retake/frame change
*/

Route::get('/photobooth', [PhotoboothController::class, 'index'])->name('photobooth');
Route::post('/photobooth/upload', [PhotoboothController::class, 'uploadPhoto'])->name('photobooth.upload');

// ✅ UPDATED: Main compose route (simpan 1 record)
Route::post('/photobooth/compose', [PhotoboothController::class, 'composeStrip'])->name('photobooth.compose');

// ✅ NEW: Update existing strip (untuk retake/ganti frame tanpa buat record baru)
Route::post('/photobooth/update-strip/{id}', [PhotoboothController::class, 'updateStrip'])->name('photobooth.update-strip');

// Download strip (public access)
Route::get('/photobooth/download/{id}', [PhotoboothController::class, 'download'])->name('photobooth.download');

/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated Users)
|--------------------------------------------------------------------------
| ✅ UPDATED: Consolidated save-strip routes
*/

Route::middleware('auth')->group(function () {
    // Profile management
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Photo strips management
    Route::delete('/profile/strips/{id}', [ProfileController::class, 'deleteStrip'])->name('profile.strips.delete');
    
    // ✅ UPDATED: Save strip to profile (requires authentication)
    // Route untuk menyimpan strip ke profil user yang sudah login
    Route::post('/photobooth/save/{id}', [PhotoboothController::class, 'saveStrip'])->name('photobooth.save');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by auth + admin middleware)
|--------------------------------------------------------------------------
| ✅ UPDATED: Added guest history, cleanup routes, and frame statistics
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // ✅ UPDATED: Dashboard with frame statistics
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Users Management
    |--------------------------------------------------------------------------
    */
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
    Route::post('/users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    /*
    |--------------------------------------------------------------------------
    | Categories Management
    |--------------------------------------------------------------------------
    */
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

    /*
    |--------------------------------------------------------------------------
    | Frames Management
    |--------------------------------------------------------------------------
    | ✅ UPDATED: Enhanced frame management with better delete protection
    */
    Route::resource('frames', AdminFrameController::class)->except(['show']);
    
    // Toggle frame active/inactive status
    Route::post('/frames/{frame}/toggle', [AdminFrameController::class, 'toggle'])->name('frames.toggle');
    
    // Restore default frames (if accidentally deleted or modified)
    Route::post('/frames/restore-defaults', [AdminFrameController::class, 'restoreDefaults'])->name('frames.restore-defaults');
    
    // Force delete frame (admin override - use with caution)
    Route::delete('/frames/{frame}/force', [AdminFrameController::class, 'forceDestroy'])->name('frames.force-destroy');

    /*
    |--------------------------------------------------------------------------
    | Photos Moderation
    |--------------------------------------------------------------------------
    */
    Route::get('/photos', [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::post('/photos/{photo}/approve', [AdminPhotoController::class, 'approve'])->name('photos.approve');
    Route::post('/photos/{photo}/reject', [AdminPhotoController::class, 'reject'])->name('photos.reject');
    Route::delete('/photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');

    /*
    |--------------------------------------------------------------------------
    | Photo Strips Management
    |--------------------------------------------------------------------------
    | ✅ FIXED: Menggunakan parameter {id} biasa untuk menghindari konflik
    */

    // Main photo strips index (all strips)
    Route::get('/photo-strips', [AdminPhotoStripController::class, 'index'])->name('photo-strips.index');

    // ✅ NEW: Guest history (strips from non-authenticated users)
    Route::get('/photo-strips/guests', [AdminPhotoStripController::class, 'guestHistory'])->name('photo-strips.guests');

    // ✅ NEW: Cleanup old guest strips (older than 7 days)
    Route::post('/photo-strips/cleanup-guests', [AdminPhotoStripController::class, 'cleanupGuests'])->name('photo-strips.cleanup-guests');

    // ✅ FIXED: Show single photo strip (parameter {id} biasa, bukan route model binding)
    Route::get('/photo-strips/{id}', [AdminPhotoStripController::class, 'show'])->name('photo-strips.show');

    // ✅ FIXED: Delete photo strip (parameter {id} biasa, bukan route model binding)
    Route::delete('/photo-strips/{id}', [AdminPhotoStripController::class, 'destroy'])->name('photo-strips.destroy');

});

/*
|--------------------------------------------------------------------------
| Debug Routes (Development Only)
|--------------------------------------------------------------------------
| ⚠️ REMOVE IN PRODUCTION
| 
| Routes ini untuk debugging dan testing.
| Pastikan untuk menghapus atau menonaktifkan di production!
*/

if (config('app.debug')) {
    
    /*
    |--------------------------------------------------------------------------
    | Frame Debugging Routes
    |--------------------------------------------------------------------------
    */
    
    // ✅ Debug single frame details (JSON)
    Route::get('/debug/frame/{frame}', function (App\Models\Frame $frame) {
        return response()->json([
            'frame_info' => [
                'id' => $frame->id,
                'name' => $frame->name,
                'is_default' => $frame->is_default,
                'is_active' => $frame->is_active,
                'color_code' => $frame->color_code,
                'photo_count' => $frame->photo_count,
            ],
            
            'usage_statistics' => [
                'photo_strips_count' => $frame->photoStrips()->count(),
                'can_be_deleted' => $frame->canBeDeleted(),
                'can_be_edited' => $frame->canBeEdited(),
            ],
            
            'path_information' => [
                'image_path' => $frame->image_path,
                'image_url' => $frame->image_url,
                'full_path' => $frame->full_path,
            ],
            
            'existence_checks' => [
                'image_exists' => $frame->imageExists(),
                'storage_disk_exists' => Storage::disk('public')->exists($frame->image_path),
                'public_path_exists' => file_exists(public_path('storage/' . $frame->image_path)),
            ],
            
            'url_variations' => [
                'storage_url' => Storage::url($frame->image_path),
                'asset_url' => asset('storage/' . $frame->image_path),
            ],
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin'])->name('debug.frame.show');
    
    // ✅ Debug all frames (HTML view)
    Route::get('/debug/frame-paths', function () {
        $frames = App\Models\Frame::withCount('photoStrips')->get();
        
        return view('debug.frames', [
            'frames' => $frames,
            'title' => 'Frame Paths Debug',
        ]);
    })->middleware(['auth', 'admin'])->name('debug.frames.html');
    
    // ✅ Debug all frames (JSON)
    Route::get('/debug/frame-json', function () {
        $frames = App\Models\Frame::withCount('photoStrips')->get();
        
        return response()->json([
            'summary' => [
                'total_frames' => $frames->count(),
                'active_frames' => $frames->where('is_active', true)->count(),
                'default_frames' => $frames->where('is_default', true)->count(),
                'custom_frames' => $frames->where('is_default', false)->count(),
            ],
            'frames' => $frames->map(function($frame) {
                return [
                    'id' => $frame->id,
                    'name' => $frame->name,
                    'color_code' => $frame->color_code,
                    'photo_count' => $frame->photo_count,
                    'is_active' => $frame->is_active,
                    'is_default' => $frame->is_default,
                    'usage_count' => $frame->photo_strips_count,
                    
                    // Path info
                    'image_path' => $frame->image_path,
                    'image_url' => $frame->image_url,
                    'full_path' => $frame->full_path,
                    
                    // Existence checks
                    'exists' => $frame->imageExists(),
                    'storage_exists' => Storage::disk('public')->exists($frame->image_path),
                    
                    // Permissions
                    'can_delete' => $frame->canBeDeleted(),
                    'can_edit' => $frame->canBeEdited(),
                    
                    // URLs for testing
                    'storage_url' => Storage::url($frame->image_path),
                    'asset_url' => asset('storage/' . $frame->image_path),
                ];
            })
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin'])->name('debug.frames.json');
    
    /*
    |--------------------------------------------------------------------------
    | Photo Strips Debugging Routes
    |--------------------------------------------------------------------------
    */
    
    // ✅ Debug photo strips statistics
    Route::get('/debug/photo-strips', function () {
        return response()->json([
            'statistics' => [
                'total_strips' => App\Models\PhotoStrip::count(),
                'saved_strips' => App\Models\PhotoStrip::where('is_saved', true)->count(),
                'unsaved_strips' => App\Models\PhotoStrip::where('is_saved', false)->count(),
                'user_strips' => App\Models\PhotoStrip::whereNotNull('user_id')->count(),
                'guest_strips' => App\Models\PhotoStrip::whereNull('user_id')->count(),
            ],
            'recent_strips' => App\Models\PhotoStrip::with(['user', 'frame'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function($strip) {
                    return [
                        'id' => $strip->id,
                        'user' => $strip->user ? $strip->user->name : 'Guest',
                        'user_id' => $strip->user_id,
                        'guest_session' => $strip->guest_session_id ? substr($strip->guest_session_id, 0, 12) : null,
                        'frame' => $strip->frame ? $strip->frame->name : 'No Frame',
                        'frame_id' => $strip->frame_id,
                        'photo_count' => $strip->photo_count,
                        'is_saved' => $strip->is_saved,
                        'ip_address' => $strip->ip_address,
                        'created_at' => $strip->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $strip->updated_at->format('Y-m-d H:i:s'),
                    ];
                }),
            'strips_by_frame' => App\Models\Frame::withCount('photoStrips')
                ->orderBy('photo_strips_count', 'desc')
                ->get()
                ->map(function($frame) {
                    return [
                        'frame_id' => $frame->id,
                        'frame_name' => $frame->name,
                        'usage_count' => $frame->photo_strips_count,
                    ];
                }),
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin'])->name('debug.photo-strips');
    
    /*
    |--------------------------------------------------------------------------
    | Session & Authentication Debugging
    |--------------------------------------------------------------------------
    */
    
    // ✅ Debug session info
    Route::get('/debug/session', function () {
        return response()->json([
            'session' => [
                'session_id' => session()->getId(),
                'csrf_token' => csrf_token(),
            ],
            'authentication' => [
                'is_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'is_admin' => auth()->check() && auth()->user()->is_admin,
            ],
            'user_details' => auth()->check() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'is_admin' => auth()->user()->is_admin,
                'is_blocked' => auth()->user()->is_blocked,
                'created_at' => auth()->user()->created_at->format('Y-m-d H:i:s'),
            ] : null,
        ], JSON_PRETTY_PRINT);
    })->name('debug.session');
    
    /*
    |--------------------------------------------------------------------------
    | File & Storage Testing Routes
    |--------------------------------------------------------------------------
    */
    
    // ✅ Test specific frame file
    Route::get('/test-frame/{filename}', function($filename) {
        $path = 'frames/' . $filename;
        
        // Check various locations
        $checks = [
            'requested_file' => $filename,
            'storage_path' => $path,
            
            'storage_disk' => [
                'exists' => Storage::disk('public')->exists($path),
                'full_path' => storage_path('app/public/' . $path),
                'file_exists' => file_exists(storage_path('app/public/' . $path)),
                'url' => Storage::url($path),
            ],
            
            'public_directory' => [
                'full_path' => public_path('storage/' . $path),
                'file_exists' => file_exists(public_path('storage/' . $path)),
                'asset_url' => asset('storage/' . $path),
            ],
            
            'direct_public' => [
                'full_path' => public_path('frames/' . $filename),
                'file_exists' => file_exists(public_path('frames/' . $filename)),
                'asset_url' => asset('frames/' . $filename),
            ],
        ];
        
        return response()->json($checks, JSON_PRETTY_PRINT);
    })->name('debug.test-frame');
    
    // ✅ Storage link status check
    Route::get('/debug/storage-link', function() {
        $storageLinkPath = public_path('storage');
        $targetPath = storage_path('app/public');
        
        return response()->json([
            'storage_link' => [
                'path' => $storageLinkPath,
                'exists' => file_exists($storageLinkPath),
                'is_link' => is_link($storageLinkPath),
                'target' => is_link($storageLinkPath) ? readlink($storageLinkPath) : null,
                'correct_target' => $targetPath,
            ],
            'recommendation' => !file_exists($storageLinkPath) || !is_link($storageLinkPath) 
                ? 'Run: php artisan storage:link' 
                : 'Storage link is properly configured',
        ], JSON_PRETTY_PRINT);
    })->name('debug.storage-link');
    
    /*
    |--------------------------------------------------------------------------
    | Database Maintenance Routes
    |--------------------------------------------------------------------------
    */
    
    // ✅ Fix frame paths (update database paths to correct format)
    Route::get('/debug/fix-frame-paths', function() {
        $frames = App\Models\Frame::all();
        $updated = 0;
        $errors = [];
        
        foreach ($frames as $frame) {
            try {
                // If path doesn't start with 'frames/', fix it
                if ($frame->image_path && !str_starts_with($frame->image_path, 'frames/')) {
                    $oldPath = $frame->image_path;
                    $filename = basename($frame->image_path);
                    $frame->image_path = 'frames/' . $filename;
                    $frame->save();
                    $updated++;
                    
                    \Log::info("Fixed frame path", [
                        'frame_id' => $frame->id,
                        'old_path' => $oldPath,
                        'new_path' => $frame->image_path,
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'frame_id' => $frame->id,
                    'frame_name' => $frame->name,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Frame paths updated',
            'summary' => [
                'total_frames' => $frames->count(),
                'updated_frames' => $updated,
                'errors_count' => count($errors),
            ],
            'errors' => $errors,
            'frames' => $frames->fresh()->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'image_path' => $f->image_path,
                'exists' => $f->imageExists(),
                'can_delete' => $f->canBeDeleted(),
            ]),
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin'])->name('debug.fix-frame-paths');
    
    // ✅ Cleanup orphaned photo strips (strips without valid images)
    Route::get('/debug/cleanup-orphaned-strips', function() {
        $orphanedStrips = App\Models\PhotoStrip::get()->filter(function($strip) {
            return $strip->final_image_path && !Storage::disk('public')->exists($strip->final_image_path);
        });
        
        return response()->json([
            'orphaned_strips_count' => $orphanedStrips->count(),
            'orphaned_strips' => $orphanedStrips->map(fn($s) => [
                'id' => $s->id,
                'user' => $s->user ? $s->user->name : 'Guest',
                'image_path' => $s->final_image_path,
                'created_at' => $s->created_at->format('Y-m-d H:i:s'),
            ]),
            'warning' => 'These strips have database records but missing image files',
            'suggestion' => 'You may want to delete these records manually',
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin'])->name('debug.cleanup-orphaned');
}

/*
|--------------------------------------------------------------------------
| Auth Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
