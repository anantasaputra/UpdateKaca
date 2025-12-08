<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;

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
     * Display the specified photo strip
     */
    public function show(PhotoStrip $photoStrip)
    {
        $photoStrip->load(['user', 'frame']);

        return view('admin.photo-strips.show', compact('photoStrip'));
    }

    /**
     * Remove the specified photo strip
     */
    public function destroy(PhotoStrip $photoStrip)
    {
        $photoStrip->delete(); // File akan otomatis terhapus via model boot

        return redirect()->back()
            ->with('success', 'Photo strip berhasil dihapus.');
    }

    /**
     * ✅ NEW: Bulk delete unsaved guest strips (cleanup)
     */
    public function cleanupGuests()
    {
        $deleted = PhotoStrip::whereNull('user_id')
            ->where('is_saved', false)
            ->where('created_at', '<', now()->subDays(7)) // Older than 7 days
            ->delete();

        return redirect()->back()
            ->with('success', "Berhasil menghapus {$deleted} strip guest yang lama.");
    }
}
