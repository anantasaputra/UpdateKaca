<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;

class PhotoStripController extends Controller
{
    /**
     * Display a listing of photo strips
     */
    public function index(Request $request)
    {
        $query = PhotoStrip::with(['user', 'frame']);

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by photo count
        if ($request->filled('photo_count')) {
            $query->where('photo_count', $request->photo_count);
        }

        // Filter by saved status
        if ($request->filled('saved')) {
            if ($request->saved === 'yes') {
                $query->where('is_saved', true);
            } elseif ($request->saved === 'no') {
                $query->where('is_saved', false);
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
            ->paginate(30);

        return view('admin.photo-strips.index', compact('photoStrips'));
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
        $photoStrip->delete();

        return redirect()->route('admin.photo-strips.index')
            ->with('success', 'Photo strip berhasil dihapus.');
    }
}
