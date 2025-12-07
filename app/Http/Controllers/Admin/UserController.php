<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($request->status === 'active') {
                $query->where('is_blocked', false);
            }
        }

        // Filter by role
        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'user') {
                $query->where('is_admin', false);
            }
        }

        $users = $query->withCount('photoStrips')
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['photoStrips' => function($query) {
            $query->latest()->take(20);
        }]);

        $stats = [
            'total_strips' => $user->photoStrips()->count(),
            'saved_strips' => $user->photoStrips()->where('is_saved', true)->count(),
            'total_photos' => $user->photos()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Block a user
     */
    public function block(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Tidak dapat memblokir admin.');
        }

        $user->update(['is_blocked' => true]);

        return back()->with('success', "User {$user->name} berhasil diblokir.");
    }

    /**
     * Unblock a user
     */
    public function unblock(User $user)
    {
        $user->update(['is_blocked' => false]);

        return back()->with('success', "User {$user->name} berhasil di-unblock.");
    }

    /**
     * Toggle block status (untuk kompatibilitas dengan route lama)
     */
    public function toggleBlock(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Tidak dapat memblokir admin.');
        }

        $user->update(['is_blocked' => !$user->is_blocked]);

        $status = $user->is_blocked ? 'diblokir' : 'di-unblock';
        
        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Tidak dapat menghapus admin.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} berhasil dihapus.");
    }
}
