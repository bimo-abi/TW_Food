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

//login admin
Route::get('/admin/login', [LoginController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [LoginController::class, 'login'])
    ->name('admin.login.process');

Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->name('admin.logout');

//admin
Route::get('/admin', [AdminController::class, 'dashboard'])
    ->name('admin.dashboard')
    ->middleware('admin');

Route::get('/admin/pesanan', [AdminPesananController::class, 'index'])
    ->name('admin.pesanan.index')
    ->middleware('admin');

Route::get('/admin/pesanan/{id}', [AdminPesananController::class, 'show'])
    ->name('admin.pesanan.show')
    ->middleware('admin');

Route::put('/admin/pesanan/{id}/status', [AdminPesananController::class, 'update'])
    ->name('admin.pesanan.update-status')
    ->middleware('admin');

Route::put('/admin/pesanan/{id}/pengiriman', [AdminPesananController::class, 'updatePengiriman'])
    ->name('admin.pesanan.update-pengiriman')
    ->middleware('admin');

//admin produk
Route::get('/admin/produk', [AdminProdukController::class, 'index'])
    ->name('admin.produk.index')
    ->middleware('admin');

Route::get('/admin/produk/create', [AdminProdukController::class, 'create'])
    ->name('admin.produk.create')
    ->middleware('admin');

Route::post('/admin/produk', [AdminProdukController::class, 'store'])
    ->name('admin.produk.store')
    ->middleware('admin');

Route::get('/admin/produk/{id}/edit', [AdminProdukController::class, 'edit'])
    ->name('admin.produk.edit')
    ->middleware('admin');

Route::put('/admin/produk/{id}', [AdminProdukController::class, 'update'])
    ->name('admin.produk.update')
    ->middleware('admin');

Route::patch('/admin/produk/{id}/toggle-status', [AdminProdukController::class, 'toggleStatus'])
    ->name('admin.produk.toggle-status')
    ->middleware('admin');

Route::delete('/admin/produk/{id}', [AdminProdukController::class, 'destroy'])
    ->name('admin.produk.destroy')
    ->middleware('admin');

//varian produk
Route::get(
    '/admin/produk/{idProduk}/varian',
    [AdminVarianProdukController::class, 'index']
)
    ->name('admin.varian.index')
    ->middleware('admin');


Route::get(
    '/admin/produk/{idProduk}/varian/create',
    [AdminVarianProdukController::class, 'create']
)
    ->name('admin.varian.create')
    ->middleware('admin');


Route::post(
    '/admin/produk/{idProduk}/varian',
    [AdminVarianProdukController::class, 'store']
)
    ->name('admin.varian.store')
    ->middleware('admin');


Route::get(
    '/admin/varian/{idVarian}/edit',
    [AdminVarianProdukController::class, 'edit']
)
    ->name('admin.varian.edit')
    ->middleware('admin');


Route::put(
    '/admin/varian/{idVarian}',
    [AdminVarianProdukController::class, 'update']
)
    ->name('admin.varian.update')
    ->middleware('admin');


Route::patch(
    '/admin/varian/{idVarian}/toggle-status',
    [AdminVarianProdukController::class, 'toggleStatus']
)
    ->name('admin.varian.toggle-status')
    ->middleware('admin');


Route::delete(
    '/admin/varian/{idVarian}',
    [AdminVarianProdukController::class, 'destroy']
)
    ->name('admin.varian.destroy')
    ->middleware('admin');

//admin daftar harga
Route::get(
    '/admin/varian/{idVarian}/harga',
    [AdminDaftarHargaController::class, 'index']
)
    ->name('admin.harga.index')
    ->middleware('admin');


Route::get(
    '/admin/varian/{idVarian}/harga/create',
    [AdminDaftarHargaController::class, 'create']
)
    ->name('admin.harga.create')
    ->middleware('admin');


Route::post(
    '/admin/varian/{idVarian}/harga',
    [AdminDaftarHargaController::class, 'store']
)
    ->name('admin.harga.store')
    ->middleware('admin');


Route::get(
    '/admin/harga/{idHarga}/edit',
    [AdminDaftarHargaController::class, 'edit']
)
    ->name('admin.harga.edit')
    ->middleware('admin');


Route::put(
    '/admin/harga/{idHarga}',
    [AdminDaftarHargaController::class, 'update']
)
    ->name('admin.harga.update')
    ->middleware('admin');


Route::patch(
    '/admin/harga/{idHarga}/toggle-status',
    [AdminDaftarHargaController::class, 'toggleStatus']
)
    ->name('admin.harga.toggle-status')
    ->middleware('admin');


Route::delete(
    '/admin/harga/{idHarga}',
    [AdminDaftarHargaController::class, 'destroy']
)
    ->name('admin.harga.destroy')
    ->middleware('admin');

//admin stok dan pre order
Route::get(
    '/admin/varian/{idVarian}/stok',
    [AdminStokController::class, 'edit']
)
    ->name('admin.stok.edit')
    ->middleware('admin');


Route::put(
    '/admin/varian/{idVarian}/stok',
    [AdminStokController::class, 'update']
)
    ->name('admin.stok.update')
    ->middleware('admin');

//public website
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
