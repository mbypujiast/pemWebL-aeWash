<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Pemakaian di route:  ->middleware('role:admin,staff')
     * Admin selalu lolos (bisa akses semua route).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login -> arahkan ke halaman login
        if (! $user) {
            return redirect()->route('login');
        }

        // Admin boleh mengakses semua route
        if ($user->isAdmin() || $user->hasRole(...$roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
