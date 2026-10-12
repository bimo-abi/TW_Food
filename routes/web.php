<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminProdukController;
use App\Http\Controllers\AdminVarianProdukController;
use App\Http\Controllers\AdminDaftarHargaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\AdminPesananController;
use App\Http\Controllers\AdminResepController;
use App\Http\Controllers\AdminResepProdukController;
use App\Http\Controllers\AdminOutletController;
use App\Http\Controllers\AdminJamOperasionalOutletController;
use App\Http\Controllers\AdminPengaturanMitraController;
use App\Http\Controllers\AdminKontenBerandaController;
use App\Http\Controllers\AdminKategoriKontenController;

//Dashboard
Route::get('/', [HomeController::class, 'index'])->name('home');

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

//admin resep

Route::get(
    '/admin/resep',
    [AdminResepController::class, 'index']
)
    ->name('admin.resep.index')
    ->middleware('admin');


Route::get(
    '/admin/resep/create',
    [AdminResepController::class, 'create']
)
    ->name('admin.resep.create')
    ->middleware('admin');


Route::post(
    '/admin/resep',
    [AdminResepController::class, 'store']
)
    ->name('admin.resep.store')
    ->middleware('admin');


Route::get(
    '/admin/resep/{id}/edit',
    [AdminResepController::class, 'edit']
)
    ->name('admin.resep.edit')
    ->middleware('admin');


Route::put(
    '/admin/resep/{id}',
    [AdminResepController::class, 'update']
)
    ->name('admin.resep.update')
    ->middleware('admin');


Route::delete(
    '/admin/resep/{id}',
    [AdminResepController::class, 'destroy']
)
    ->name('admin.resep.destroy')
    ->middleware('admin');

// admin resep produk
Route::get(
    '/admin/resep/{idResep}/produk',
    [AdminResepProdukController::class, 'index']
)
    ->name('admin.resep.produk.index')
    ->middleware('admin');


Route::post(
    '/admin/resep/{idResep}/produk',
    [AdminResepProdukController::class, 'store']
)
    ->name('admin.resep.produk.store')
    ->middleware('admin');


Route::delete(
    '/admin/resep-produk/{idResepProduk}',
    [AdminResepProdukController::class, 'destroy']
)
    ->name('admin.resep.produk.destroy')
    ->middleware('admin');

//admin outlet
Route::get(
    '/admin/outlet',
    [AdminOutletController::class, 'index']
)
    ->name('admin.outlet.index')
    ->middleware('admin');


Route::get(
    '/admin/outlet/create',
    [AdminOutletController::class, 'create']
)
    ->name('admin.outlet.create')
    ->middleware('admin');


Route::post(
    '/admin/outlet',
    [AdminOutletController::class, 'store']
)
    ->name('admin.outlet.store')
    ->middleware('admin');


Route::get(
    '/admin/outlet/{idOutlet}/edit',
    [AdminOutletController::class, 'edit']
)
    ->name('admin.outlet.edit')
    ->middleware('admin');


Route::put(
    '/admin/outlet/{idOutlet}',
    [AdminOutletController::class, 'update']
)
    ->name('admin.outlet.update')
    ->middleware('admin');


Route::delete(
    '/admin/outlet/{idOutlet}',
    [AdminOutletController::class, 'destroy']
)
    ->name('admin.outlet.destroy')
    ->middleware('admin');

//admin jam operasional outlet
Route::get(
    '/admin/outlet/{idOutlet}/jam-operasional',
    [AdminJamOperasionalOutletController::class, 'index']
)
    ->name('admin.jam-operasional.index')
    ->middleware('admin');


Route::get(
    '/admin/outlet/{idOutlet}/jam-operasional/create',
    [AdminJamOperasionalOutletController::class, 'create']
)
    ->name('admin.jam-operasional.create')
    ->middleware('admin');


Route::post(
    '/admin/outlet/{idOutlet}/jam-operasional',
    [AdminJamOperasionalOutletController::class, 'store']
)
    ->name('admin.jam-operasional.store')
    ->middleware('admin');


Route::get(
    '/admin/jam-operasional/{idJamOperasional}/edit',
    [AdminJamOperasionalOutletController::class, 'edit']
)
    ->name('admin.jam-operasional.edit')
    ->middleware('admin');


Route::put(
    '/admin/jam-operasional/{idJamOperasional}',
    [AdminJamOperasionalOutletController::class, 'update']
)
    ->name('admin.jam-operasional.update')
    ->middleware('admin');


Route::delete(
    '/admin/jam-operasional/{idJamOperasional}',
    [AdminJamOperasionalOutletController::class, 'destroy']
)
    ->name('admin.jam-operasional.destroy')
    ->middleware('admin');

    // Admin Pengaturan Mitra
Route::get(
    '/admin/pengaturan-mitra',
    [AdminPengaturanMitraController::class, 'index']
)
    ->name('admin.pengaturan-mitra.index')
    ->middleware('admin');

Route::put(
    '/admin/pengaturan-mitra',
    [AdminPengaturanMitraController::class, 'update']
)
    ->name('admin.pengaturan-mitra.update')
    ->middleware('admin');

// Admin Konten Beranda
Route::get(
    '/admin/konten-beranda',
    [AdminKontenBerandaController::class, 'index']
)->name('admin.konten-beranda.index')
 ->middleware('admin');

Route::get(
    '/admin/konten-beranda/create',
    [AdminKontenBerandaController::class, 'create']
)->name('admin.konten-beranda.create')
 ->middleware('admin');

Route::post(
    '/admin/konten-beranda',
    [AdminKontenBerandaController::class, 'store']
)->name('admin.konten-beranda.store')
 ->middleware('admin');

Route::get(
    '/admin/konten-beranda/{id}/edit',
    [AdminKontenBerandaController::class, 'edit']
)->name('admin.konten-beranda.edit')
 ->middleware('admin');

Route::put(
    '/admin/konten-beranda/{id}',
    [AdminKontenBerandaController::class, 'update']
)->name('admin.konten-beranda.update')
 ->middleware('admin');

Route::delete(
    '/admin/konten-beranda/{id}',
    [AdminKontenBerandaController::class, 'destroy']
)->name('admin.konten-beranda.destroy')
 ->middleware('admin');

// Admin Kategori Konten
Route::get('/admin/kategori-konten', [AdminKategoriKontenController::class, 'index'])
    ->name('admin.kategori-konten.index')
    ->middleware('admin');

Route::get('/admin/kategori-konten/create', [AdminKategoriKontenController::class, 'create'])
    ->name('admin.kategori-konten.create')
    ->middleware('admin');

Route::post('/admin/kategori-konten', [AdminKategoriKontenController::class, 'store'])
    ->name('admin.kategori-konten.store')
    ->middleware('admin');

Route::get('/admin/kategori-konten/{id}/edit', [AdminKategoriKontenController::class, 'edit'])
    ->name('admin.kategori-konten.edit')
    ->middleware('admin');

Route::put('/admin/kategori-konten/{id}', [AdminKategoriKontenController::class, 'update'])
    ->name('admin.kategori-konten.update')
    ->middleware('admin');

Route::delete('/admin/kategori-konten/{id}', [AdminKategoriKontenController::class, 'destroy'])
    ->name('admin.kategori-konten.destroy')
    ->middleware('admin');

//public website
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/produk', [ProdukController::class, 'index'])
    ->name('produk.public');

Route::get('/produk/{id}', [ProdukController::class, 'show'])
    ->name('produk.detail');

Route::get('/resep', [ResepController::class, 'index'])
    ->name('resep.public');

Route::get('/resep/{id}', [ResepController::class, 'show'])
    ->name('resep.detail');

Route::get('/outlet', [OutletController::class, 'index'])
    ->name('outlet.public');

Route::get('/kontak', [KontakController::class, 'index'])
    ->name('kontak.public');
