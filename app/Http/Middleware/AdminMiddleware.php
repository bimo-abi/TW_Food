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
        // Pengguna harus memiliki sesi login yang valid.
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $pengguna = Auth::user();

        // TWFOOD saat ini menggunakan peran mitra untuk akses Admin Website.
        if ($pengguna->peran !== 'mitra') {
            abort(403, 'Anda tidak memiliki akses ke halaman admin.');
        }

        // Akun mitra yang dinonaktifkan tidak boleh mengakses halaman maupun
        // aksi Admin Website, termasuk pengelolaan retur dan pencatatan refund.
        if (!$pengguna->status_aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'Akun Anda sedang dinonaktifkan.']);
        }

        return $next($request);
    }
}
