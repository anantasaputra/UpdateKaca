<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PhotoStripController extends Controller
{
    /**
     * ✅ UPDATED: Display a listing of photo strips
     * Hanya tampilkan strip yang "complete" (bukan temporary/duplicate)
     */
    public function index(Request $request)
    {
        $query = PhotoStrip::with(['user', 'frame']);

        // ✅ OPTIONAL: Filter hanya yang disimpan ke profil (exclude guest temporary)
        // Uncomment jika hanya ingin lihat yang user save
        // $query->where('is_saved', true);

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter by photo count
        if ($request->filled('photo_count')) {
            $query->where('photo_count', $request->photo_count);
        }

        // ✅ UPDATED: Filter by saved status
        if ($request->filled('saved')) {
            if ($request->saved === 'yes') {
                $query->where('is_saved', true);
            } elseif ($request->saved === 'no') {
                $query->where('is_saved', false);
            }
        }

        // ✅ NEW: Filter by user type
        if ($request->filled('user_type')) {
            if ($request->user_type === 'registered') {
                $query->whereNotNull('user_id');
            } elseif ($request->user_type === 'guest') {
                $query->whereNull('user_id');
            }
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $photoStrips = $query->latest()
            ->paginate(30)
            ->withQueryString(); // ✅ Maintain filter params in pagination

        // ✅ Statistics
        $stats = [
            'total' => PhotoStrip::count(),
            'saved' => PhotoStrip::where('is_saved', true)->count(),
            'unsaved' => PhotoStrip::where('is_saved', false)->count(),
            'with_users' => PhotoStrip::whereNotNull('user_id')->count(),
            'guests' => PhotoStrip::whereNull('user_id')->count(),
        ];

        return view('admin.photo-strips.index', compact('photoStrips', 'stats'));
    }

    /**
     * ✅ NEW: Riwayat khusus guest
     */
    public function guestHistory(Request $request)
    {
        $query = PhotoStrip::with('frame')
            ->whereNull('user_id')
            ->whereNotNull('guest_session_id');

        // Filter by photo count
        if ($request->filled('photo_count')) {
            $query->where('photo_count', $request->photo_count);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $guestStrips = $query->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.photo-strips.guest-history', compact('guestStrips'));
    }

    /**
     * ✅ FIXED: Display the specified photo strip
     * Menggunakan parameter ID biasa dengan manual find
     */
    public function show($id)
    {
        // Find photo strip by ID with relationships
        $photoStrip = PhotoStrip::with(['user', 'frame'])->find($id);

        // Check if photo strip exists
        if (!$photoStrip) {
            Log::warning('Photo strip not found', [
                'id' => $id,
                'url' => request()->url(),
            ]);

            return redirect()->route('admin.photo-strips.index')
                ->with('error', 'Photo strip tidak ditemukan.');
        }

        return view('admin.photo-strips.show', compact('photoStrip'));
    }

    /**
     * ✅ FIXED: Remove the specified photo strip
     * Menggunakan parameter ID biasa untuk menghindari konflik route model binding
     */
    public function destroy($id)
    {
        try {
            // Find photo strip by ID
            $photoStrip = PhotoStrip::find($id);

            if (!$photoStrip) {
                Log::warning('Photo strip not found for deletion', [
                    'id' => $id,
                    'url' => request()->url(),
                ]);
                
                return redirect()->route('admin.photo-strips.index')
                    ->with('error', 'Photo strip tidak ditemukan.');
            }

            Log::info('Attempting to delete photo strip', [
                'id' => $photoStrip->id,
                'user_id' => $photoStrip->user_id,
                'user' => $photoStrip->user ? $photoStrip->user->name : 'Guest',
                'image_path' => $photoStrip->final_image_path,
                'ip_address' => $photoStrip->ip_address,
            ]);

            // Store info before deletion
            $stripId = $photoStrip->id;
            $imagePath = $photoStrip->final_image_path;

            // Delete image file if exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                $deleted = Storage::disk('public')->delete($imagePath);
                
                if ($deleted) {
                    Log::info('Image file deleted successfully', [
                        'path' => $imagePath,
                    ]);
                } else {
                    Log::warning('Failed to delete image file', [
                        'path' => $imagePath,
                    ]);
                }
            } else {
                Log::info('No image file to delete or file not found', [
                    'path' => $imagePath,
                ]);
            }

            // Delete database record
            $photoStrip->delete();

            Log::info('Photo strip deleted successfully', [
                'id' => $stripId,
            ]);

            return redirect()->route('admin.photo-strips.index')
                ->with('success', "Photo strip #{$stripId} berhasil dihapus!");

        } catch (\Exception $e) {
            Log::error('Error deleting photo strip', [
                'id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('admin.photo-strips.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Bulk delete unsaved guest strips (cleanup)
     */
    public function cleanupGuests()
    {
        try {
            Log::info('Starting guest strips cleanup', [
                'admin_user' => auth()->user()->email,
            ]);

            $deleted = PhotoStrip::whereNull('user_id')
                ->where('is_saved', false)
                ->where('created_at', '<', now()->subDays(7)) // Older than 7 days
                ->delete();

            Log::info('Guest strips cleanup completed', [
                'deleted_count' => $deleted,
                'admin_user' => auth()->user()->email,
            ]);

            if ($deleted > 0) {
                return redirect()->back()
                    ->with('success', "Berhasil menghapus {$deleted} strip guest yang lama.");
            } else {
                return redirect()->back()
                    ->with('info', 'Tidak ada strip guest yang perlu dibersihkan.');
            }

        } catch (\Exception $e) {
            Log::error('Error cleaning up guest strips', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membersihkan data: ' . $e->getMessage());
        }
    }
}
