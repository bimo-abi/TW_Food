<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    // Menampilkan halaman form login / daftar
    public function showLogin()
    {
        return view('auth.login');
    }

    // Memproses data login yang dikirimkan
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Arahkan ke dashboard admin
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Memproses pendaftaran akun admin
    public function register(Request $request)
    {
        // Buang spasi dan tanda strip, jadi "0812-3456-7890" tetap diterima
        $request->merge([
            'nomor_telepon' => preg_replace('/[\s\-]/', '', (string) $request->nomor_telepon),
        ]);

        $data = $request->validateWithBag('register', [
            'nama'          => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'unique:pengguna,email'],
            'nomor_telepon' => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'kata_sandi'    => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'confirmed',
            ],
            'kode_daftar'   => ['required', Rule::in([config('app.kode_daftar_admin')])],
        ], [
            'nama.required'          => 'Nama lengkap wajib diisi.',
            'email.required'         => 'Email wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'email.unique'           => 'Email ini sudah terdaftar.',
            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
            'nomor_telepon.regex'    => 'Nomor telepon harus diawali 08, contoh: 081234567890.',
            'kata_sandi.required'    => 'Kata sandi wajib diisi.',
            'kata_sandi.min'         => 'Kata sandi minimal 8 karakter.',
            'kata_sandi.regex'       => 'Kata sandi harus mengandung huruf besar, huruf kecil, dan angka.',
            'kata_sandi.confirmed'   => 'Ulangi kata sandi tidak sama.',
            'kode_daftar.required'   => 'Kode pendaftaran wajib diisi.',
            'kode_daftar.in'         => 'Kode pendaftaran tidak valid.',
        ]);

        Pengguna::create([
            'nama'          => $data['nama'],
            'email'         => $data['email'],
            'nomor_telepon' => $data['nomor_telepon'],
            'kata_sandi'    => Hash::make($data['kata_sandi']),
            'peran'         => 'mitra',   // dikunci di server, bukan dari form
            'status_aktif'  => true,
        ]);

        return redirect()
            ->route('admin.login')
            ->with('success', 'Akun berhasil dibuat. Silakan login.');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}