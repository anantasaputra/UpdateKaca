<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    // HAPUS middleware() dari constructor

    /**
     * Display a listing of photos
     */
    public function index(Request $request)
    {
        $query = Photo::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $photos = $query->latest()->paginate(30);

        return view('admin.photos.index', compact('photos'));
    }

    /**
     * Approve a photo
     */
    public function approve(Photo $photo)
    {
        $photo->update(['is_approved' => true]);

        return back()->with('success', 'Photo berhasil diapprove.');
    }

    /**
     * Reject a photo
     */
    public function reject(Photo $photo)
    {
        $photo->update(['is_approved' => false]);

        return back()->with('success', 'Photo berhasil direject.');
    }

    /**
     * Remove the specified photo
     */
    public function destroy(Photo $photo)
    {
        if ($photo->file_path) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $photo->delete();

        return redirect()->route('admin.photos.index')
            ->with('success', 'Photo berhasil dihapus.');
    }
}
