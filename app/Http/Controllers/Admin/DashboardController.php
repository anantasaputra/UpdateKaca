<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Photo;
use App\Models\PhotoStrip;
use App\Models\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Statistik umum
        $stats = [
            'total_users' => User::count(),
            'total_photos' => Photo::count(),
            'total_strips' => PhotoStrip::count(),
            'total_frames' => Frame::count(),
            'active_users' => User::where('is_blocked', false)->count(),
            'blocked_users' => User::where('is_blocked', true)->count(),
            'saved_strips' => PhotoStrip::where('is_saved', true)->count(),
        ];

        // Recent activity
        $recentStrips = PhotoStrip::with(['user', 'frame'])
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::latest()
            ->take(10)
            ->get();

        // Photo strip stats by photo count
        $stripsByCount = PhotoStrip::select('photo_count', DB::raw('count(*) as total'))
            ->groupBy('photo_count')
            ->orderBy('photo_count')
            ->get();

        // Monthly stats (last 6 months)
        $monthlyStats = PhotoStrip::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        // Top users (most photo strips)
        $topUsers = User::withCount('photoStrips')
            ->orderBy('photo_strips_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentStrips',
            'recentUsers',
            'stripsByCount',
            'monthlyStats',
            'topUsers'
        ));
    }
}
