<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FrameController as AdminFrameController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoboothController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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
*/

Route::get('/photobooth', [PhotoboothController::class, 'index'])->name('photobooth');
Route::post('/photobooth/upload', [PhotoboothController::class, 'uploadPhoto'])->name('photobooth.upload');
Route::post('/photobooth/compose', [PhotoboothController::class, 'composeStrip'])->name('photobooth.compose');
Route::get('/photobooth/download/{id}', [PhotoboothController::class, 'download'])->name('photobooth.download');

/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/strips/{id}', [ProfileController::class, 'deleteStrip'])->name('profile.strips.delete');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Frames Management
    Route::resource('frames', AdminFrameController::class);

    // Categories Management
    Route::resource('categories', CategoryController::class);

    // Photos Moderation
    Route::get('photos', [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::post('photos/{photo}/approve', [AdminPhotoController::class, 'approve'])->name('photos.approve');
    Route::post('photos/{photo}/reject', [AdminPhotoController::class, 'reject'])->name('photos.reject');
    Route::delete('photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');

    // Users Management
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

// use Illuminate\Support\Facades\RateLimiter;

// Route::post('/photobooth/upload', [PhotoboothController::class, 'uploadPhoto'])
//     ->middleware('throttle:10,1') // 10 requests per minute
//     ->name('photobooth.upload');

// Route::post('/photobooth/compose', [PhotoboothController::class, 'composeStrip'])
//     ->middleware('throttle:5,1') // 5 requests per minute
//     ->name('photobooth.compose');
