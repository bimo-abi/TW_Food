<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReturController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\OutletController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API TWFOOD berhasil terhubung.'
    ]);
});

Route::post('/pesanan/{id_pesanan}/retur', [ReturController::class, 'store']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])
    ->middleware('auth:sanctum');
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);
Route::get('/resep', [ResepController::class, 'index']);
Route::get('/resep/{id}', [ResepController::class, 'show']);
Route::get('/outlet', [OutletController::class, 'index']);
