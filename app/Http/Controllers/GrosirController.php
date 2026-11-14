<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Carbon\Carbon;

class GrosirController extends Controller
{
    /**
     * Halaman Harga Grosir (public).
     * Datanya sama dengan halaman Produk: produk aktif + varian aktif + harga aktif.
     * Nanti di view hanya produk yang punya harga grosir yang ditampilkan.
     */
    public function index()
    {
        $produk = Produk::where('status_aktif', true)
            ->with([
                'varian' => function ($query) {
                    $query->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($harga) {
                                $harga->where('status_aktif', true);
                            }
                        ]);
                }
            ])
            ->orderBy('id_produk')
            ->get();

        // Tanggal harga terbaru = tanggal update paling baru di tabel daftar_harga
        $terbaru = $produk->flatMap(fn ($p) => $p->varian)
            ->flatMap(fn ($v) => $v->daftarHarga)
            ->max('updated_at');

        $tanggalHarga = $terbaru
            ? Carbon::parse($terbaru)->locale('id')->translatedFormat('j F Y')
            : null;

        return view('grosir.index', compact('produk', 'tanggalHarga'));
    }
}