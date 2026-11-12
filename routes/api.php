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

    // Pesanan
    Route::post('/pesanan', [PesananController::class, 'store']);

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
