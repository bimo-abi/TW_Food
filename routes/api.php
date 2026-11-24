<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
use App\Http\Controllers\Api\MitraProdukController;
use App\Http\Controllers\Api\MitraVarianProdukController;
use App\Http\Controllers\Api\MitraReturController;
use App\Http\Controllers\RefundRiwayatController;

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

// Gambar produk disajikan melalui route API agar respons melewati middleware CORS.
Route::get('/media/products/{filename}', function (string $filename) {
    abort_unless(
        preg_match('/\\A[A-Za-z0-9][A-Za-z0-9._-]*\\.(?:png|jpe?g|webp)\\z/i', $filename) === 1,
        404
    );

    $relativePath = 'products/' . $filename;
    $disk = Storage::disk('public');

    abort_unless($disk->exists($relativePath), 404);

    $filePath = $disk->path($relativePath);

    return response()->file($filePath, [
        'Content-Type' => mime_content_type($filePath) ?: 'application/octet-stream',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('filename', '[A-Za-z0-9][A-Za-z0-9._-]*');

// Foto profil disajikan melalui API dengan nama file yang tervalidasi.
Route::get('/media/profiles/{filename}', function (string $filename) {
    abort_unless(
        preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\.(?:png|jpe?g|webp)\z/i', $filename) === 1,
        404
    );

    $relativePath = 'profiles/' . $filename;
    $disk = Storage::disk('public');
    abort_unless($disk->exists($relativePath), 404);

    $filePath = $disk->path($relativePath);
    return response()->file($filePath, [
        'Content-Type' => mime_content_type($filePath) ?: 'application/octet-stream',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('filename', '[A-Za-z0-9][A-Za-z0-9._-]*');

// Webhook Midtrans
// Tidak menggunakan auth:sanctum karena dipanggil oleh Midtrans.
Route::post(
    '/midtrans/notifikasi',
    [PembayaranController::class, 'notification']
)->middleware('throttle:60,1');

/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Autentikasi pengguna
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/photo', [AuthController::class, 'updateProfilePhoto']);

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
    Route::get('/retur/bukti/{id_bukti_retur}/preview', [RiwayatReturController::class, 'previewBukti'])->name('retur.bukti.preview');
    Route::get('/retur/bukti/{id_bukti_retur}', [RiwayatReturController::class, 'downloadBukti'])->name('retur.bukti');

    // Notifikasi akun yang sedang login
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllRead']);
    Route::patch('/notifikasi/{id_notifikasi}/baca', [NotifikasiController::class, 'markRead']);

    // Manajemen produk Flutter Mitra (controller memeriksa peran mitra aktif)
    Route::get('/mitra/produk', [MitraProdukController::class, 'index']);
    Route::get('/mitra/produk/{id_produk}', [MitraProdukController::class, 'show']);
    Route::post('/mitra/produk', [MitraProdukController::class, 'store']);
    Route::put('/mitra/produk/{id_produk}', [MitraProdukController::class, 'update']);
    Route::patch('/mitra/produk/{id_produk}/status', [MitraProdukController::class, 'toggleStatus']);

    // Manajemen varian dan harga Flutter Mitra
    Route::get('/mitra/produk/{id_produk}/varian', [MitraVarianProdukController::class, 'index']);
    Route::post('/mitra/produk/{id_produk}/varian', [MitraVarianProdukController::class, 'store']);
    Route::put('/mitra/varian/{id_varian}', [MitraVarianProdukController::class, 'update']);
    Route::patch('/mitra/varian/{id_varian}/status', [MitraVarianProdukController::class, 'toggleStatusVarian']);
    Route::patch('/mitra/varian/{id_varian}/stok', [MitraVarianProdukController::class, 'updateStok']);
    Route::patch('/mitra/varian/{id_varian}/pre-order', [MitraVarianProdukController::class, 'updatePreOrder']);
    Route::post('/mitra/varian/{id_varian}/harga', [MitraVarianProdukController::class, 'storeHarga']);
    Route::put('/mitra/harga/{id_daftar_harga}', [MitraVarianProdukController::class, 'updateHarga']);
    Route::patch('/mitra/harga/{id_daftar_harga}/status', [MitraVarianProdukController::class, 'toggleStatusHarga']);

    // Operasional Flutter Mitra (controller memeriksa peran mitra aktif)
    Route::get('/mitra/pesanan', [MitraPesananController::class, 'index']);
    Route::get('/mitra/pesanan/{id_pesanan}', [MitraPesananController::class, 'show']);
    Route::patch('/mitra/pesanan/{id_pesanan}/status', [MitraPesananController::class, 'updateStatus']);
    Route::put('/mitra/pesanan/{id_pesanan}/pengiriman', [MitraPesananController::class, 'updatePengiriman']);
    Route::get('/mitra/retur', [MitraReturController::class, 'index']);
    Route::get('/mitra/retur/{id_retur}', [MitraReturController::class, 'show']);
    Route::patch('/mitra/retur/{id_retur}/keputusan', [MitraReturController::class, 'updateKeputusan']);
    Route::patch('/mitra/retur/{id_retur}/instruksi-pengembalian', [MitraReturController::class, 'kirimInstruksiPengembalian']);
    Route::patch('/mitra/retur/{id_retur}/barang-diterima', [MitraReturController::class, 'konfirmasiBarangDiterima']);
    Route::patch('/mitra/retur/{id_retur}/pemeriksaan', [MitraReturController::class, 'periksaBarangRetur']);
    Route::post('/mitra/refund/{id_refund}/manual', [MitraReturController::class, 'catatRefundManual']);
    Route::get('/mitra/refund', [RefundRiwayatController::class, 'indexMitra'])->name('mitra.refund.index');
    Route::get('/mitra/refund/{id_refund}', [RefundRiwayatController::class, 'showMitra'])->name('mitra.refund.show');
    Route::get('/mitra/refund/percobaan/{id_percobaan_refund}/bukti', [RefundRiwayatController::class, 'downloadBuktiMitra'])->name('mitra.refund.bukti');

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
