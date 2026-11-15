<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\ReturController;
use App\Http\Controllers\Api\RiwayatPesananController;
use App\Http\Controllers\Api\StatusPembayaranController;
use App\Http\Controllers\Api\RiwayatReturController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\MitraPesananController;
use App\Http\Controllers\Api\MitraReturController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/

// Tes koneksi API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API TWFOOD berhasil terhubung.',
    ]);
});

// Autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Data publik
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);

Route::get('/resep', [ResepController::class, 'index']);
Route::get('/resep/{id}', [ResepController::class, 'show']);

Route::get('/outlet', [OutletController::class, 'index']);

// Webhook Midtrans
// Tidak menggunakan auth:sanctum karena dipanggil oleh Midtrans.
Route::post(
    '/midtrans/notifikasi',
    [PembayaranController::class, 'notification']
);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Autentikasi pengguna
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Alamat pengguna
    Route::get('/alamat', [AlamatController::class, 'index']);
    Route::post('/alamat', [AlamatController::class, 'store']);
    Route::put('/alamat/{idAlamat}', [AlamatController::class, 'update']);
    Route::delete('/alamat/{idAlamat}', [AlamatController::class, 'destroy']);

    // Pesanan pelanggan
    Route::post('/pesanan', [PesananController::class, 'store']);
    Route::get('/pesanan', [RiwayatPesananController::class, 'index']);
    Route::get('/pesanan/{id_pesanan}', [RiwayatPesananController::class, 'show']);
    Route::get('/pesanan/{id_pesanan}/pembayaran', [StatusPembayaranController::class, 'show']);
    Route::get('/retur', [RiwayatReturController::class, 'index']);
    Route::get('/retur/{id_retur}', [RiwayatReturController::class, 'show']);

    // Notifikasi akun yang sedang login
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllRead']);
    Route::patch('/notifikasi/{id_notifikasi}/baca', [NotifikasiController::class, 'markRead']);

    // Operasional Flutter Mitra (controller memeriksa peran mitra aktif)
    Route::get('/mitra/pesanan', [MitraPesananController::class, 'index']);
    Route::get('/mitra/pesanan/{id_pesanan}', [MitraPesananController::class, 'show']);
    Route::patch('/mitra/pesanan/{id_pesanan}/status', [MitraPesananController::class, 'updateStatus']);
    Route::put('/mitra/pesanan/{id_pesanan}/pengiriman', [MitraPesananController::class, 'updatePengiriman']);
    Route::get('/mitra/retur', [MitraReturController::class, 'index']);
    Route::get('/mitra/retur/{id_retur}', [MitraReturController::class, 'show']);

    // Pembayaran pesanan
    Route::post(
        '/pesanan/{id_pesanan}/pembayaran',
        [PembayaranController::class, 'store']
    );

    // Pengajuan retur
    Route::post(
        '/pesanan/{id_pesanan}/retur',
        [ReturController::class, 'store']
    );
});
