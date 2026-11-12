<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DaftarHarga;
use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\ProduksiPreOrder;
use App\Models\ReservasiStok;
use App\Models\RiwayatAktivitasTransaksi;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

class PesananController extends Controller
{
    /**
     * Membuat pesanan dari aplikasi Flutter.
     * Harga, stok, kelayakan pelanggan, dan total selalu dihitung backend.
     */
    public function store(Request $request)
    {
        $pengguna = $request->user();

        if (!$pengguna || !$pengguna->status_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif atau belum login.',
            ], 403);
        }

        $validated = $request->validate([
            'jenis_pemenuhan' => ['required', 'in:reguler,pre_order'],
            'jenis_pesanan' => ['required', 'in:pickup,delivery'],
            'id_alamat' => [
                'required_if:jenis_pesanan,delivery',
                'nullable',
                'integer',
                'exists:alamat,id_alamat',
            ],
            'id_outlet' => [
                'required_if:jenis_pesanan,pickup',
                'nullable',
                'integer',
                'exists:outlet,id_outlet',
            ],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id_varian' => [
                'required', 'integer', 'distinct', 'exists:varian_produk,id_varian',
            ],
            'items.*.id_daftar_harga' => [
                'required', 'integer', 'exists:daftar_harga,id_daftar_harga',
            ],
            'items.*.jumlah' => [
                'required', 'integer', 'min:1', 'max:65535',
            ],
        ]);

        // Validasi kepemilikan alamat dilakukan ulang di dalam transaksi
        // database agar snapshot yang disimpan berasal dari alamat pemilik pesanan.
        $hasil = DB::transaction(function () use ($validated, $pengguna) {
            $alamat = null;
            $outlet = null;

            if ($validated['jenis_pesanan'] === 'delivery') {
                $alamat = Alamat::where('id_alamat', $validated['id_alamat'])
                    ->where('id_pengguna', $pengguna->id_pengguna)
                    ->lockForUpdate()
                    ->first();

                if (!$alamat) {
                    throw ValidationException::withMessages([
                        'id_alamat' => ['Alamat tidak ditemukan atau bukan milik Anda.'],
                    ]);
                }
            }

            if ($validated['jenis_pesanan'] === 'pickup') {
                $outlet = Outlet::where('id_outlet', $validated['id_outlet'])
                    ->lockForUpdate()
                    ->first();

                if (!$outlet) {
                    throw ValidationException::withMessages([
                        'id_outlet' => ['Outlet yang dipilih tidak ditemukan.'],
                    ]);
                }
            }

            // Saluran pesanan harus mengikuti jenis pelanggan dari backend.
            $saluranPesanan = match ($pengguna->jenis_pelanggan) {
                'umum' => 'online',
                'toko' => 'toko',
                'horeca' => 'horeca',
                default => null,
            };

            if ($saluranPesanan === null) {
                throw ValidationException::withMessages([
                    'jenis_pelanggan' => ['Jenis pelanggan tidak valid untuk membuat pesanan.'],
                ]);
            }

            $isPreOrder = $validated['jenis_pemenuhan'] === 'pre_order';

            if ($isPreOrder && !in_array($pengguna->jenis_pelanggan, ['toko', 'horeca'], true)) {
                throw ValidationException::withMessages([
                    'jenis_pemenuhan' => ['Pre-order hanya tersedia untuk pelanggan Toko/Horeca.'],
                ]);
            }

            if ($isPreOrder && $pengguna->status_verifikasi !== 'disetujui') {
                throw ValidationException::withMessages([
                    'jenis_pemenuhan' => ['Pre-order hanya tersedia untuk pelanggan Toko/Horeca yang telah terverifikasi.'],
                ]);
            }

            $subtotalProduk = 0;
            $detailItems = [];
            $reservasiKadaluarsa = now()->addHours(24);

            foreach ($validated['items'] as $index => $item) {
                // Kunci varian agar dua checkout bersamaan tidak melewati
                // pemeriksaan ketersediaan stok yang sama.
                $varian = VarianProduk::where('id_varian', $item['id_varian'])
                    ->lockForUpdate()
                    ->first();

                if (!$varian || !$varian->status_aktif) {
                    throw ValidationException::withMessages([
                        "items.$index.id_varian" => ['Varian produk tidak aktif atau tidak ditemukan.'],
                    ]);
                }

                $produk = $varian->produk;
                if (!$produk || !$produk->status_aktif) {
                    throw ValidationException::withMessages([
                        "items.$index.id_varian" => ['Produk tidak aktif atau tidak ditemukan.'],
                    ]);
                }

                // Harga wajib aktif dan benar-benar milik varian yang dipilih.
                $daftarHarga = DaftarHarga::where('id_daftar_harga', $item['id_daftar_harga'])
                    ->where('id_varian', $varian->id_varian)
                    ->where('status_aktif', true)
                    ->lockForUpdate()
                    ->first();

                if (!$daftarHarga) {
                    throw ValidationException::withMessages([
                        "items.$index.id_daftar_harga" => ['Harga tidak aktif atau tidak sesuai dengan varian.'],
                    ]);
                }

                $jumlah = (int) $item['jumlah'];
                $minimalPembelian = (int) $daftarHarga->minimal_pembelian;

                if ($jumlah < $minimalPembelian) {
                    throw ValidationException::withMessages([
                        "items.$index.jumlah" => [
                            'Jumlah pembelian belum memenuhi minimum untuk jenis harga ' . $daftarHarga->jenis_harga . '.',
                        ],
                    ]);
                }

                if (
                    $daftarHarga->satuan_minimal !== null &&
                    $daftarHarga->satuan_minimal !== '' &&
                    $daftarHarga->satuan_minimal !== $varian->satuan_jual
                ) {
                    throw ValidationException::withMessages([
                        "items.$index.id_daftar_harga" => ['Satuan minimum harga tidak sesuai dengan satuan jual varian.'],
                    ]);
                }

                if ($isPreOrder) {
                    $tanggalMulai = $varian->tanggal_mulai_pre_order;
                    $tanggalSelesai = $varian->tanggal_selesai_pre_order;
                    $hariIni = today();

                    $periodeAktif = $tanggalMulai && $tanggalSelesai
                        && $hariIni->greaterThanOrEqualTo($tanggalMulai->copy()->startOfDay())
                        && $hariIni->lessThanOrEqualTo($tanggalSelesai->copy()->startOfDay());

                    if (!$varian->tersedia_pre_order || !$periodeAktif) {
                        throw ValidationException::withMessages([
                            "items.$index.id_varian" => [
                                'Pre-order tidak aktif atau berada di luar periode pemesanan.',
                            ],
                        ]);
                    }
                } else {
                    // Stok tersedia dikurangi reservasi aktif yang belum kedaluwarsa.
                    // Stok fisik tidak dikurangi saat checkout; reservasi dikonversi
                    // menjadi pengurangan stok setelah pembayaran diverifikasi.
                    $jumlahReservasiAktif = (int) ReservasiStok::where(
                        'id_varian',
                        $varian->id_varian
                    )
                        ->where('status_reservasi', 'aktif')
                        ->where('kadaluarsa_pada', '>', now())
                        ->sum('jumlah');

                    $stokTersedia = (int) $varian->stok - $jumlahReservasiAktif;

                    if ($jumlah > $stokTersedia) {
                        throw ValidationException::withMessages([
                            "items.$index.jumlah" => ['Stok tersedia tidak mencukupi setelah memperhitungkan reservasi pesanan lain.'],
                        ]);
                    }
                }

                $harga = (int) $daftarHarga->harga;
                $subtotalItem = $harga * $jumlah;

                if ($subtotalItem > 4294967295) {
                    throw ValidationException::withMessages([
                        "items.$index.jumlah" => ['Subtotal item melebihi kapasitas penyimpanan database.'],
                    ]);
                }

                $subtotalProduk += $subtotalItem;

                if ($subtotalProduk > 4294967295) {
                    throw ValidationException::withMessages([
                        'items' => ['Total pesanan melebihi kapasitas penyimpanan database.'],
                    ]);
                }

                $detailItems[] = [
                    'id_varian' => $varian->id_varian,
                    'nama_produk_saat_pesan' => $produk->nama_produk,
                    'nama_varian_saat_pesan' => $varian->nama_varian,
                    'harga_saat_pesan' => $harga,
                    'jenis_harga_saat_pesan' => $daftarHarga->jenis_harga,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotalItem,
                    'diskon_item' => 0,
                    'subtotal_setelah_diskon' => $subtotalItem,
                ];
            }

            // Diskon checkout belum dihitung dari modul promosi pada controller
            // yang tersedia. Nilai awal tetap nol, bukan menerima diskon dari client.
            $diskon = 0;
            $totalPesanan = $subtotalProduk - $diskon;

            $pesanan = Pesanan::create([
                'nomor_pesanan' => 'TW-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                'id_pengguna' => $pengguna->id_pengguna,
                'id_alamat' => $alamat?->id_alamat,
                'id_outlet' => $outlet?->id_outlet,
                'nama_penerima_saat_pesan' => $alamat?->nama_penerima,
                'nomor_telepon_saat_pesan' => $alamat?->nomor_telepon,
                'alamat_lengkap_saat_pesan' => $alamat?->alamat_lengkap,
                'kota_saat_pesan' => $alamat?->kota,
                'provinsi_saat_pesan' => $alamat?->provinsi,
                'kode_pos_saat_pesan' => $alamat?->kode_pos,
                'saluran_pesanan' => $saluranPesanan,
                'jenis_pesanan' => $validated['jenis_pesanan'],
                'jenis_pemenuhan' => $validated['jenis_pemenuhan'],
                'subtotal_produk' => $subtotalProduk,
                'diskon' => $diskon,
                'total_pesanan' => $totalPesanan,
                'status_pembayaran' => 'belum_dibayar',
                'status_pesanan' => 'menunggu_pembayaran',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($detailItems as $detail) {
                $detailPesanan = $pesanan->detailPesanan()->create($detail);

                if (!$isPreOrder) {
                    ReservasiStok::create([
                        'id_pesanan' => $pesanan->id_pesanan,
                        'id_detail_pesanan' => $detailPesanan->id_detail_pesanan,
                        'id_varian' => $detailPesanan->id_varian,
                        'jumlah' => $detailPesanan->jumlah,
                        'status_reservasi' => 'aktif',
                        'dipesan_pada' => now(),
                        'kadaluarsa_pada' => $reservasiKadaluarsa,
                    ]);
                } else {
                    // Catatan produksi dibuat menunggu pembayaran. Produksi baru
                    // boleh dimulai setelah pembayaran DP/penuh diverifikasi.
                    ProduksiPreOrder::create([
                        'id_detail_pesanan' => $detailPesanan->id_detail_pesanan,
                        'jumlah_produksi' => $detailPesanan->jumlah,
                        'status_produksi' => 'menunggu',
                    ]);
                }
            }

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $pengguna->id_pengguna,
                'jenis_aktor' => 'pelanggan',
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'pesanan_dibuat',
                'status_sebelum' => null,
                'status_sesudah' => 'menunggu_pembayaran',
                'deskripsi' => 'Pelanggan membuat pesanan ' . $pesanan->nomor_pesanan . '.',
                'created_at' => now(),
            ]);

            return $pesanan->load(['detailPesanan', 'reservasiStok']);
        }, 3);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat dan ketersediaan stok telah dicadangkan jika pesanan reguler.',
            'data' => $hasil,
        ], 201);
    }
}
