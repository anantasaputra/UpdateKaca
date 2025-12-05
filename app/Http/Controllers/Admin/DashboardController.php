<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Frame;
use App\Models\Photo;
use App\Models\PhotoStrip;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $stats = [
            'total_users' => User::where('is_admin', false)->count(),
            'total_frames' => Frame::count(),
            'total_categories' => Category::count(),
            'total_strips' => PhotoStrip::count(),
            'pending_photos' => Photo::pending()->count(),
            'recent_strips' => PhotoStrip::with(['user', 'frame'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}