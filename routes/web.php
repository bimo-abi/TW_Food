<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\AdminVarianProdukController;
use App\Http\Controllers\AdminDaftarHargaController;
use App\Http\Controllers\AdminStokController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\AdminPesananController;

//login admin (tanpa middleware)
Route::get('/admin/login', [LoginController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [LoginController::class, 'login'])
    ->name('admin.login.process');

Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->name('admin.logout');


//pakai middleware
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

    // Dashboard  -> URL: /admin | nama: admin.dashboard.home
    Route::get('/', [AdminController::class, 'dashboard'])
        ->name('dashboard.home');

    // Pesanan
    Route::get('/pesanan', [AdminPesananController::class, 'index'])
        ->name('pesanan.index');

    Route::get('/pesanan/{id}', [AdminPesananController::class, 'show'])
        ->name('pesanan.show');

    Route::put('/pesanan/{id}/status', [AdminPesananController::class, 'update'])
        ->name('pesanan.update-status');

    Route::put('/pesanan/{id}/pengiriman', [AdminPesananController::class, 'updatePengiriman'])
        ->name('pesanan.update-pengiriman');

    // Produk
    Route::get('/produk', [AdminProdukController::class, 'index'])
        ->name('produk.index');

    Route::get('/produk/create', [AdminProdukController::class, 'create'])
        ->name('produk.create');

    Route::post('/produk', [AdminProdukController::class, 'store'])
        ->name('produk.store');

    Route::get('/produk/{id}/edit', [AdminProdukController::class, 'edit'])
        ->name('produk.edit');

    Route::put('/produk/{id}', [AdminProdukController::class, 'update'])
        ->name('produk.update');

    Route::patch('/produk/{id}/toggle-status', [AdminProdukController::class, 'toggleStatus'])
        ->name('produk.toggle-status');

    Route::delete('/produk/{id}', [AdminProdukController::class, 'destroy'])
        ->name('produk.destroy');

    // Varian produk
    Route::get('/produk/{idProduk}/varian', [AdminVarianProdukController::class, 'index'])
        ->name('varian.index');

    Route::get('/produk/{idProduk}/varian/create', [AdminVarianProdukController::class, 'create'])
        ->name('varian.create');

    Route::post('/produk/{idProduk}/varian', [AdminVarianProdukController::class, 'store'])
        ->name('varian.store');

    Route::get('/varian/{idVarian}/edit', [AdminVarianProdukController::class, 'edit'])
        ->name('varian.edit');

    Route::put('/varian/{idVarian}', [AdminVarianProdukController::class, 'update'])
        ->name('varian.update');

    Route::patch('/varian/{idVarian}/toggle-status', [AdminVarianProdukController::class, 'toggleStatus'])
        ->name('varian.toggle-status');

    Route::delete('/varian/{idVarian}', [AdminVarianProdukController::class, 'destroy'])
        ->name('varian.destroy');

    // Daftar harga
    Route::get('/varian/{idVarian}/harga', [AdminDaftarHargaController::class, 'index'])
        ->name('harga.index');

    Route::get('/varian/{idVarian}/harga/create', [AdminDaftarHargaController::class, 'create'])
        ->name('harga.create');

    Route::post('/varian/{idVarian}/harga', [AdminDaftarHargaController::class, 'store'])
        ->name('harga.store');

    Route::get('/harga/{idHarga}/edit', [AdminDaftarHargaController::class, 'edit'])
        ->name('harga.edit');

    Route::put('/harga/{idHarga}', [AdminDaftarHargaController::class, 'update'])
        ->name('harga.update');

    Route::patch('/harga/{idHarga}/toggle-status', [AdminDaftarHargaController::class, 'toggleStatus'])
        ->name('harga.toggle-status');

    Route::delete('/harga/{idHarga}', [AdminDaftarHargaController::class, 'destroy'])
        ->name('harga.destroy');

    // Stok dan pre order
    Route::get('/varian/{idVarian}/stok', [AdminStokController::class, 'edit'])
        ->name('stok.edit');

    Route::put('/varian/{idVarian}/stok', [AdminStokController::class, 'update'])
        ->name('stok.update');
});

//website publik
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/produk', [ProdukController::class, 'index'])
    ->name('produk.public');

Route::get('/resep', [ResepController::class, 'index'])
    ->name('resep.public');

Route::get('/resep/{id}', [ResepController::class, 'show'])
    ->name('resep.detail');

Route::get('/outlet', [OutletController::class, 'index'])
    ->name('outlet.public');

Route::get('/kontak', [KontakController::class, 'index'])
    ->name('kontak.public');