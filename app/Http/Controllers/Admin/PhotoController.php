<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $photos = Photo::with('user')
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.photos.index', compact('photos', 'status'));
    }

    public function approve(Photo $photo)
    {
        $photo->approve();

        return back()
            ->with('success', 'Photo approved successfully');
    }

    public function reject(Photo $photo)
    {
        $photo->reject();

        return back()
            ->with('success', 'Photo rejected successfully');
    }

    public function destroy(Photo $photo)
    {
        $photo->delete();

        return back()
            ->with('success', 'Photo deleted successfully');
    }
}
