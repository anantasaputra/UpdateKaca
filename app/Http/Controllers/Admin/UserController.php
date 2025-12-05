<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $users = User::where('is_admin', false)
            ->withCount(['photoStrips', 'photos'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function toggleBlock(User $user)
    {
        if ($user->isAdmin()) {
            return back()
                ->withErrors(['error' => 'Cannot block admin users']);
        }

        $user->update([
            'is_blocked' => ! $user->is_blocked,
        ]);

        $status = $user->is_blocked ? 'blocked' : 'unblocked';

        return back()
            ->with('success', "User {$status} successfully");
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return back()
                ->withErrors(['error' => 'Cannot delete admin users']);
        }

        $user->delete();

        return back()
            ->with('success', 'User deleted successfully');
    }
}
