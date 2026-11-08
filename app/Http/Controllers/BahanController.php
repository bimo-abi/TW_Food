<?php

namespace App\Http\Controllers;

class BahanController extends Controller
{

    public function index()
    {
        $daftarBahan = [
            [
                'nama'      => 'Kaldu Jamur Tiram dan Shitake',
                'kategori'  => 'Bumbu',
                'foto'      => 'kaldu-jamur-tiram-shitake.jpg',
                'bahan'     => ['Ekstrak jamur tiram', 'Ekstrak jamur Shitake', 'Seledri', 'Soya', 'Bawang bombay', 'Bawang merah', 'Bawang Putih', 'Merica', 'Garam'],
                'ket_bahan' => null,
                'simpan'    => '±14 bulan',
                'catatan'   => 'Tanpa penambahan MSG dan pengawet.',
            ],
            [
                'nama'      => 'Kaldu Jamur Tiram MPASI',
                'kategori'  => 'Bumbu',
                'foto'      => 'kaldu-jamur-tiram-mpasi.jpg',
                'bahan'     => ['Ekstrak jamur tiram', 'Udang'],
                'ket_bahan' => null,
                'simpan'    => 'Lihat label kemasan',
                'catatan'   => 'Protein lebih tinggi, cocok untuk MPASI. Bahan lainnya lihat label kemasan.',
            ],
            [
                'nama'      => 'Kecap Jamur Tiram',
                'kategori'  => 'Bumbu',
                'foto'      => 'kecap-jamur-tiram.jpg',
                'bahan'     => ['Fermentasi jamur tiram', 'Rempah asli Indonesia', 'Gula merah', 'Wijen'],
                'ket_bahan' => null,
                'simpan'    => '±14 bulan',
                'catatan'   => 'Tanpa pewarna dan pengawet kimia. Varian manis dan pedas.',
            ],
            [
                'nama'      => 'Jamur Tiram Krispi',
                'kategori'  => 'Camilan',
                'foto'      => 'jamur-tiram-krispi.jpg',
                'bahan'     => [],
                'ket_bahan' => 'Daftar bahan belum tercantum di katalog. Lihat label kemasan.',
                'simpan'    => '±14 bulan',
                'catatan'   => 'Digoreng dengan sedikit minyak. Varian rasa: Original, Keju, Barbeque, Spicy.',
            ],
            [
                'nama'      => 'Stik Jantung Pisang',
                'kategori'  => 'Camilan',
                'foto'      => 'stik-jantung-pisang.jpg',
                'bahan'     => ['Jantung pisang', 'Tepung beras', 'Rempah', 'Kaldu jamur'],
                'ket_bahan' => null,
                'simpan'    => '±14 bulan',
                'catatan'   => 'Varian rasa: keju, pedas, bawang putih.',
            ],
            [
                'nama'      => 'Abon Jamur Tiram',
                'kategori'  => 'Camilan',
                'foto'      => 'abon-jamur-tiram.jpg',
                'bahan'     => ['Jamur tiram', 'Jamur Shitake'],
                'ket_bahan' => null,
                'simpan'    => '±14 bulan',
                'catatan'   => 'Vegan-friendly. Bahan lainnya lihat label kemasan.',
            ],
            [
                'nama'      => 'Kerupuk Jamur Tiram',
                'kategori'  => 'Camilan',
                'foto'      => 'kerupuk-jamur-tiram.jpg',
                'bahan'     => ['Jamur tiram', 'Tepung tapioka', 'Rempah'],
                'ket_bahan' => null,
                'simpan'    => '±14 bulan',
                'catatan'   => 'Varian: Jamur Original, Jamur & Wortel, Jamur & Daun Kelor.',
            ],
            [
                'nama'      => 'Mie Instan Jamur Tiram',
                'kategori'  => 'Mie Instan',
                'foto'      => 'mie-instan-jamur-tiram.jpg',
                'bahan'     => ['Tepung tapioka', 'Tepung jamur', 'Bumbu daun kelor segar', 'Telur', 'Bumbu pelengkap'],
                'ket_bahan' => null,
                'simpan'    => '±18 bulan',
                'catatan'   => 'Diperkaya telur sebagai tambahan protein hewani.',
            ],
        ];

        return view('bahan.index', compact('daftarBahan'));
    }
}