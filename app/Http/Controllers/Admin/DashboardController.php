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

        // BARU: Statistik Frame - Yang Paling Sering & Tidak Pernah Digunakan
        $frameStats = Frame::leftJoin('photo_strips', 'frames.id', '=', 'photo_strips.frame_id')
            ->select(
                'frames.id',
                'frames.name',
                'frames.color_code',
                'frames.photo_count',
                'frames.is_active',
                'frames.is_default',
                'frames.image_path',
                DB::raw('COUNT(photo_strips.id) as usage_count')
            )
            ->groupBy(
                'frames.id',
                'frames.name',
                'frames.color_code',
                'frames.photo_count',
                'frames.is_active',
                'frames.is_default',
                'frames.image_path'
            )
            ->orderBy('usage_count', 'desc')
            ->get();

        // BARU: Top 5 Frame Paling Populer
        $topFrames = $frameStats->take(5);

        // BARU: Frame yang Tidak Pernah Digunakan
        $unusedFrames = $frameStats->where('usage_count', 0);

        // BARU: Frame Statistics Summary
        $frameStatsSummary = [
            'total_frames' => $frameStats->count(),
            'active_frames' => $frameStats->where('is_active', true)->count(),
            'inactive_frames' => $frameStats->where('is_active', false)->count(),
            'default_frames' => $frameStats->where('is_default', true)->count(),
            'custom_frames' => $frameStats->where('is_default', false)->count(),
            'unused_frames' => $unusedFrames->count(),
            'most_used_frame' => $frameStats->first(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentStrips',
            'recentUsers',
            'stripsByCount',
            'monthlyStats',
            'topUsers',
            'topFrames',           // BARU
            'unusedFrames',        // BARU
            'frameStatsSummary'    // BARU
        ));
    }
}
