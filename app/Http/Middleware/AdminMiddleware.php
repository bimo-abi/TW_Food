<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        // Cek apakah pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        // Cek apakah pengguna memiliki peran sebagai mitra
        if (Auth::user()->peran !== 'mitra') {
            abort(403, 'Anda tidak memiliki akses ke halaman admin.');
        }

        return $next($request);
    }
}
