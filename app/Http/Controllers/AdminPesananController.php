<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class AdminPesananController extends Controller
{
    /**
     * Menampilkan daftar pesanan.
     */
    public function index()
    {
        $pesanan = Pesanan::with('pengguna')
            ->latest('created_at')
            ->get();

        return view('admin.pesanan.index', compact('pesanan'));
    }

    /**
     * Menampilkan detail pesanan.
     */
    public function show($id)
    {
        $pesanan = Pesanan::with([
            'pengguna',
            'alamat',
            'outlet',
            'detailPesanan.varian.produk',
            'pembayaran',
            'retur'
        ])->findOrFail($id);

        return view('admin.pesanan.show', compact('pesanan'));
    }

    /**
     * Memperbarui status pesanan.
     */
    public function update(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $request->validate([
            'status_pesanan' => 'required|in:pesanan_diterima,sedang_diproses,pesanan_siap,menunggu_pengiriman,dalam_pengiriman,diterima,siap_diambil,pesanan_diambil,selesai,dibatalkan',
        ]);
        $statusBaru = $request->status_pesanan;
        $statusSekarang = $pesanan->status_pesanan;

        // Nomor resi wajib diisi sebelum pesanan mulai dikirim
        // Validasi informasi tracking sebelum pesanan mulai dikirim
        if (
            $statusSekarang === 'menunggu_pengiriman' &&
            $statusBaru === 'dalam_pengiriman' &&
            $pesanan->jenis_pesanan === 'delivery'
        ) {
            // GoSend menggunakan tautan tracking
            if (
                $pesanan->kurir === 'gosend' &&
                empty($pesanan->tautan_pelacakan)
            ) {
                return back()->withErrors([
                    'status_pesanan' =>
                    'Tautan tracking wajib diisi sebelum pesanan GoSend mulai dikirim.'
                ]);
            }

            // J&T menggunakan nomor resi
            if (
                $pesanan->kurir === 'jnt' &&
                empty($pesanan->nomor_resi)
            ) {
                return back()->withErrors([
                    'status_pesanan' =>
                    'Nomor resi wajib diisi sebelum pesanan J&T mulai dikirim.'
                ]);
            }

            // Kurir harus valid
            if (!in_array($pesanan->kurir, ['gosend', 'jnt'])) {
                return back()->withErrors([
                    'status_pesanan' =>
                    'Kurir pesanan belum ditentukan dengan benar.'
                ]);
            }
        }

        // Alur status delivery
        $alurDelivery = [
            'pesanan_diterima' => ['sedang_diproses', 'dibatalkan'],
            'sedang_diproses' => ['pesanan_siap', 'dibatalkan'],
            'pesanan_siap' => ['menunggu_pengiriman', 'dibatalkan'],
            'menunggu_pengiriman' => ['dalam_pengiriman', 'dibatalkan'],
            'dalam_pengiriman' => ['diterima', 'dibatalkan'],
            'diterima' => ['selesai'],
            'selesai' => [],
            'dibatalkan' => [],
        ];

        // Alur status pickup
        $alurPickup = [
            'pesanan_diterima' => ['sedang_diproses', 'dibatalkan'],
            'sedang_diproses' => ['pesanan_siap', 'dibatalkan'],
            'pesanan_siap' => ['siap_diambil', 'dibatalkan'],
            'siap_diambil' => ['pesanan_diambil', 'dibatalkan'],
            'pesanan_diambil' => ['selesai'],
            'selesai' => [],
            'dibatalkan' => [],
        ];

        if ($pesanan->jenis_pesanan === 'delivery') {
            $alur = $alurDelivery;
        } else {
            $alur = $alurPickup;
        }

        // Jika status baru bukan status yang diperbolehkan
        if (!in_array($statusBaru, $alur[$statusSekarang] ?? [])) {
            return back()->withErrors([
                'status_pesanan' => 'Perubahan status pesanan tidak sesuai dengan alur pesanan.'
            ]);
        }

        $pesanan->status_pesanan = $statusBaru;
        $pesanan->save();

        return redirect()
            ->route('admin.pesanan.show', $pesanan->id_pesanan)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updatePengiriman(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        // Hanya pesanan delivery yang boleh memiliki informasi pengiriman
        if ($pesanan->jenis_pesanan !== 'delivery') {
            return back()->withErrors([
                'pengiriman' => 'Informasi pengiriman hanya dapat diisi untuk pesanan delivery.'
            ]);
        }

        // Kurir harus sudah ditentukan oleh sistem
        if (!in_array($pesanan->kurir, ['gosend', 'jnt'])) {
            return back()->withErrors([
                'pengiriman' => 'Kurir pesanan belum ditentukan dengan benar.'
            ]);
        }

        $data = $request->validate([
            'nomor_resi' => 'nullable|string|max:100',
            'tautan_pelacakan' => 'nullable|url|max:255',
        ]);

        $pesanan->nomor_resi = $data['nomor_resi'] ?? null;
        $pesanan->tautan_pelacakan = $data['tautan_pelacakan'] ?? null;
        $pesanan->save();

        return redirect()
            ->route('admin.pesanan.show', $pesanan->id_pesanan)
            ->with('success', 'Informasi pengiriman berhasil disimpan.');
    }
}
