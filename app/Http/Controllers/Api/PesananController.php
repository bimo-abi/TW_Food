<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DaftarHarga;
use App\Models\Outlet;
use App\Models\Pesanan;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    /**
     * Membuat pesanan baru dari aplikasi Flutter.
     */
    public function store(Request $request)
    {
        $pengguna = $request->user();

        // Pastikan akun pelanggan aktif.
        if (!$pengguna || !$pengguna->status_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif atau belum login.',
            ], 403);
        }

        // Validasi data yang dikirim oleh Flutter.
        $validated = $request->validate([
            'jenis_pemenuhan' => [
                'required',
                'in:reguler,pre_order',
            ],
            'jenis_pesanan' => [
                'required',
                'in:pickup,delivery',
            ],
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
            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.id_varian' => [
                'required',
                'integer',
                'distinct',
                'exists:varian_produk,id_varian',
            ],
            'items.*.id_daftar_harga' => [
                'required',
                'integer',
                'exists:daftar_harga,id_daftar_harga',
            ],
            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],
        ]);

        // Pastikan pelanggan hanya menggunakan alamat miliknya.
        if ($validated['jenis_pesanan'] === 'delivery') {
            $alamatMilikPelanggan = Alamat::where(
                'id_alamat',
                $validated['id_alamat']
            )
                ->where('id_pengguna', $pengguna->id_pengguna)
                ->exists();

            if (!$alamatMilikPelanggan) {
                throw ValidationException::withMessages([
                    'id_alamat' => [
                        'Alamat tidak ditemukan atau bukan milik Anda.',
                    ],
                ]);
            }
        }

        // Validasi outlet untuk pesanan pickup.
        if ($validated['jenis_pesanan'] === 'pickup') {
            $outletAda = Outlet::where(
                'id_outlet',
                $validated['id_outlet']
            )->exists();

            if (!$outletAda) {
                throw ValidationException::withMessages([
                    'id_outlet' => [
                        'Outlet yang dipilih tidak ditemukan.',
                    ],
                ]);
            }
        }

        // Tentukan saluran pesanan dari data backend.
        $saluranPesanan = match ($pengguna->jenis_pelanggan) {
            'umum' => 'online',
            'toko', 'horeca' => 'toko',
            default => null,
        };

        if (!$saluranPesanan) {
            return response()->json([
                'message' => 'Jenis pelanggan tidak valid.',
            ], 422);
        }

        // Simpan pesanan dan detail dalam satu transaksi database.
        $pesanan = DB::transaction(function () use (
            $validated,
            $pengguna,
            $saluranPesanan
        ) {
            $subtotalProduk = 0;
            $detailItems = [];

            foreach ($validated['items'] as $item) {
                // Kunci varian selama proses pemeriksaan.
                $varian = VarianProduk::where(
                    'id_varian',
                    $item['id_varian']
                )
                    ->lockForUpdate()
                    ->first();

                if (!$varian || !$varian->status_aktif) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Terdapat varian produk yang tidak aktif.',
                        ],
                    ]);
                }

                // Produk induk juga harus aktif.
                $produkAktif = $varian->produk()
                    ->where('status_aktif', true)
                    ->exists();

                if (!$produkAktif) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Terdapat produk yang tidak aktif.',
                        ],
                    ]);
                }

                // Harga harus aktif dan milik varian yang dipilih.
                $daftarHarga = DaftarHarga::where(
                    'id_daftar_harga',
                    $item['id_daftar_harga']
                )
                    ->where('id_varian', $varian->id_varian)
                    ->where('status_aktif', true)
                    ->first();

                if (!$daftarHarga) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Harga tidak aktif atau tidak sesuai dengan varian.',
                        ],
                    ]);
                }

                // Periksa minimum pembelian untuk harga yang dipilih.
                if (
                    $item['jumlah'] <
                    $daftarHarga->minimal_pembelian
                ) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Jumlah pembelian belum memenuhi minimum harga.',
                        ],
                    ]);
                }

                // Jika satuan minimum ditentukan, satuannya harus sesuai.
                if (
                    $daftarHarga->satuan_minimal !== null &&
                    $daftarHarga->satuan_minimal !== '' &&
                    $daftarHarga->satuan_minimal !== $varian->satuan_jual
                ) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Satuan minimum harga tidak sesuai dengan satuan jual varian.',
                        ],
                    ]);
                }

                // Periksa jenis pemenuhan dan kelayakan stok.
                if ($validated['jenis_pemenuhan'] === 'reguler') {
                    // Pesanan reguler wajib tersedia pada stok reguler.
                    if ($item['jumlah'] > $varian->stok) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'Stok reguler tidak mencukupi untuk pesanan ini.',
                            ],
                        ]);
                    }

                    // Cadangkan stok reguler di dalam transaksi database.
                    $varian->stok -= (int) $item['jumlah'];
                    $varian->save();
                } else {
                    // Pre-order hanya untuk pelanggan Toko/Horeca.
                    $jenisPelangganDiizinkan = in_array(
                        $pengguna->jenis_pelanggan,
                        ['toko', 'horeca'],
                        true
                    );

                    $statusVerifikasiDisetujui =
                        $pengguna->status_verifikasi === 'disetujui';

                    $hariIni = now()->startOfDay();

                    $periodePreOrderBerlaku =
                        $varian->tanggal_mulai_pre_order !== null &&
                        $varian->tanggal_selesai_pre_order !== null &&
                        $hariIni->betweenIncluded(
                            $varian->tanggal_mulai_pre_order,
                            $varian->tanggal_selesai_pre_order
                        );

                    $preOrderDiizinkan =
                        $jenisPelangganDiizinkan &&
                        $statusVerifikasiDisetujui &&
                        $varian->tersedia_pre_order &&
                        $periodePreOrderBerlaku;

                    if (!$preOrderDiizinkan) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'Pre-order hanya tersedia untuk pelanggan Toko/Horeca terverifikasi pada periode yang berlaku.',
                            ],
                        ]);
                    }
                }
                // Harga diambil dari database, bukan dari Flutter.
                $harga = (int) $daftarHarga->harga;
                $jumlah = (int) $item['jumlah'];
                $subtotal = $harga * $jumlah;

                $subtotalProduk += $subtotal;

                $detailItems[] = [
                    'id_varian' => $varian->id_varian,
                    'nama_produk_saat_pesan' =>
                    $varian->produk->nama_produk,
                    'nama_varian_saat_pesan' =>
                    $varian->nama_varian,
                    'harga_saat_pesan' => $harga,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal,
                ];
            }

            // Pastikan total tidak melebihi kapasitas kolom database.
            if ($subtotalProduk > 4294967295) {
                throw ValidationException::withMessages([
                    'items' => [
                        'Total pesanan melebihi batas nominal yang didukung.',
                    ],
                ]);
            }

            // Diskon belum diterapkan pada tahap checkout awal.
            $diskon = 0;
            $totalPesanan = $subtotalProduk - $diskon;

            $kodePesanan = 'TW-' .
                now()->format('YmdHis') .
                '-' .
                Str::upper(Str::random(6));

            $pesanan = Pesanan::create([
                'nomor_pesanan' => $kodePesanan,
                'id_pengguna' => $pengguna->id_pengguna,
                'id_alamat' => $validated['jenis_pesanan'] === 'delivery'
                    ? $validated['id_alamat']
                    : null,
                'id_outlet' => $validated['jenis_pesanan'] === 'pickup'
                    ? $validated['id_outlet']
                    : null,
                'saluran_pesanan' => $saluranPesanan,
                'jenis_pesanan' => $validated['jenis_pesanan'],
                'jenis_pemenuhan' => $validated['jenis_pemenuhan'],
                'subtotal_produk' => $subtotalProduk,
                'diskon' => $diskon,
                'total_pesanan' => $totalPesanan,
                'status_pembayaran' => 'belum_dibayar',
                'status_pesanan' => 'pesanan_diterima',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Simpan setiap barang sebagai detail pesanan.
            foreach ($detailItems as $detail) {
                $pesanan->detailPesanan()->create($detail);
            }

            return $pesanan->load('detailPesanan');
        });

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $pesanan,
        ], 201);
    }
}
