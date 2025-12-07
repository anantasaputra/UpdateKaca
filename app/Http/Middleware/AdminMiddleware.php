<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check if user is admin
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Admin access only.');
        }

        // Check if user is blocked
        if (auth()->user()->is_blocked) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah diblokir.');
        }

        return $next($request);
    }
}
