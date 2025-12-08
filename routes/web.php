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
| ✅ UPDATED: Added guest history and cleanup routes
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Dashboard
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
    */
    Route::resource('frames', AdminFrameController::class)->except(['show']);
    Route::post('/frames/{frame}/toggle', [AdminFrameController::class, 'toggle'])->name('frames.toggle');
    Route::post('/frames/restore-defaults', [AdminFrameController::class, 'restoreDefaults'])->name('frames.restore-defaults');
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
    | ✅ UPDATED: Added guest history and cleanup routes
    */
    // Main photo strips index (all strips)
    Route::get('/photo-strips', [AdminPhotoStripController::class, 'index'])->name('photo-strips.index');
    
    // ✅ NEW: Guest history (strips from non-authenticated users)
    Route::get('/photo-strips/guests', [AdminPhotoStripController::class, 'guestHistory'])->name('photo-strips.guests');
    
    // ✅ NEW: Cleanup old guest strips (older than 7 days)
    Route::post('/photo-strips/cleanup-guests', [AdminPhotoStripController::class, 'cleanupGuests'])->name('photo-strips.cleanup-guests');
    
    // Show single photo strip
    Route::get('/photo-strips/{photoStrip}', [AdminPhotoStripController::class, 'show'])->name('photo-strips.show');
    
    // Delete photo strip
    Route::delete('/photo-strips/{photoStrip}', [AdminPhotoStripController::class, 'destroy'])->name('photo-strips.destroy');
});

/*
|--------------------------------------------------------------------------
| Debug Routes (Development Only)
|--------------------------------------------------------------------------
| ⚠️ REMOVE IN PRODUCTION
*/

if (config('app.debug')) {
    
    // ✅ UPDATED: Debug single frame details
    Route::get('/debug/frame/{frame}', function (App\Models\Frame $frame) {
        return [
            'id' => $frame->id,
            'name' => $frame->name,
            'is_default' => $frame->is_default,
            'photo_strips_count' => $frame->photoStrips()->count(),
            'can_delete' => $frame->canBeDeleted(),
            
            // Path info
            'image_path' => $frame->image_path,
            'image_url' => $frame->image_url,
            'full_path' => $frame->full_path,
            
            // Existence checks
            'image_exists' => $frame->imageExists(),
            'storage_exists' => Storage::disk('public')->exists($frame->image_path),
            'public_exists' => file_exists(public_path(str_replace('public/', '', $frame->image_path))),
            
            // URLs
            'storage_url' => Storage::url($frame->image_path),
            'asset_url' => asset(str_replace('public/', '', $frame->image_path)),
        ];
    })->middleware(['auth', 'admin']);
    
    // ✅ NEW: Debug all frames (HTML view)
    Route::get('/debug/frame-paths', function () {
        $frames = App\Models\Frame::all();
        return view('debug-frames', ['frames' => $frames]);
    });
    
    // ✅ NEW: Debug all frames (JSON)
    Route::get('/debug/frame-json', function () {
        $frames = App\Models\Frame::all();
        
        return response()->json([
            'total_frames' => $frames->count(),
            'frames' => $frames->map(function($frame) {
                return [
                    'id' => $frame->id,
                    'name' => $frame->name,
                    'color_code' => $frame->color_code,
                    'photo_count' => $frame->photo_count,
                    'is_active' => $frame->is_active,
                    'is_default' => $frame->is_default,
                    
                    // Path info
                    'image_path' => $frame->image_path,
                    'image_url' => $frame->image_url,
                    'full_path' => $frame->full_path,
                    
                    // Existence checks
                    'exists' => $frame->imageExists(),
                    'storage_exists' => Storage::disk('public')->exists($frame->image_path),
                    'public_exists' => file_exists(public_path(str_replace('public/', '', $frame->image_path))),
                    
                    // URLs for testing
                    'storage_url' => Storage::url($frame->image_path),
                    'asset_url' => asset(str_replace('public/', '', $frame->image_path)),
                ];
            })
        ], JSON_PRETTY_PRINT);
    });
    
    // ✅ Debug photo strips
    Route::get('/debug/photo-strips', function () {
        return response()->json([
            'total_strips' => App\Models\PhotoStrip::count(),
            'saved_strips' => App\Models\PhotoStrip::where('is_saved', true)->count(),
            'unsaved_strips' => App\Models\PhotoStrip::where('is_saved', false)->count(),
            'user_strips' => App\Models\PhotoStrip::whereNotNull('user_id')->count(),
            'guest_strips' => App\Models\PhotoStrip::whereNull('user_id')->count(),
            'recent_strips' => App\Models\PhotoStrip::with(['user', 'frame'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function($strip) {
                    return [
                        'id' => $strip->id,
                        'user' => $strip->user ? $strip->user->name : 'Guest',
                        'guest_session' => $strip->guest_session_id ? substr($strip->guest_session_id, 0, 8) : null,
                        'frame' => $strip->frame ? $strip->frame->name : 'No Frame',
                        'photo_count' => $strip->photo_count,
                        'is_saved' => $strip->is_saved,
                        'created_at' => $strip->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ], JSON_PRETTY_PRINT);
    })->middleware(['auth', 'admin']);
    
    // ✅ Debug session info
    Route::get('/debug/session', function () {
        return response()->json([
            'session_id' => session()->getId(),
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'user' => auth()->user(),
        ], JSON_PRETTY_PRINT);
    });
    
    // ✅ NEW: Test specific frame file
    Route::get('/test-frame/{filename}', function($filename) {
        $path = 'frames/' . $filename;
        
        // Check various locations
        $checks = [
            'storage_exists' => Storage::disk('public')->exists($path),
            'storage_full_path' => storage_path('app/public/' . $path),
            'storage_file_exists' => file_exists(storage_path('app/public/' . $path)),
            
            'public_full_path' => public_path($path),
            'public_file_exists' => file_exists(public_path($path)),
            
            'storage_url' => Storage::url($path),
            'asset_url' => asset($path),
            'asset_storage_url' => asset('storage/' . $path),
        ];
        
        return response()->json($checks, JSON_PRETTY_PRINT);
    });
    
    // ✅ NEW: Quick fix - Update all frame paths
    Route::get('/debug/fix-frame-paths', function() {
        $frames = App\Models\Frame::all();
        $updated = 0;
        
        foreach ($frames as $frame) {
            // If path doesn't start with 'frames/', fix it
            if (!str_starts_with($frame->image_path, 'frames/')) {
                $filename = basename($frame->image_path);
                $frame->image_path = 'frames/' . $filename;
                $frame->save();
                $updated++;
            }
        }
        
        return response()->json([
            'message' => 'Frame paths updated',
            'total_frames' => $frames->count(),
            'updated_frames' => $updated,
            'frames' => $frames->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'image_path' => $f->image_path,
                'exists' => $f->imageExists(),
            ])
        ], JSON_PRETTY_PRINT);
    });
}

/*
|--------------------------------------------------------------------------
| Auth Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
