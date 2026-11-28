<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar produk aktif
     * beserta varian dan harga aktif.
     */
    public function index(Request $request)
    {
        $bolehLihatGrosir = $this->bolehLihatHargaGrosir($request);

        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) use ($bolehLihatGrosir) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) use ($bolehLihatGrosir) {
                                $query->where('status_aktif', true)
                                    ->when(!$bolehLihatGrosir, function ($harga) {
                                        $harga->where('jenis_harga', 'ecer');
                                    });
                            },
                        ]);
                },
            ])
            ->get();
        $this->tambahkanInfoCampaign($produk);

        return response()->json([
            'message' => 'Daftar produk berhasil diambil.',
            'data' => $produk,
        ], 200);
    }

    /**
     * Menampilkan detail satu produk aktif
     * beserta varian dan harga aktif.
     */
    public function show(Request $request, $id)
    {
        $bolehLihatGrosir = $this->bolehLihatHargaGrosir($request);

        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) use ($bolehLihatGrosir) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) use ($bolehLihatGrosir) {
                                $query->where('status_aktif', true)
                                    ->when(!$bolehLihatGrosir, function ($harga) {
                                        $harga->where('jenis_harga', 'ecer');
                                    });
                            },
                        ]);
                },
            ])
            ->find($id);

        if (!$produk) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $this->tambahkanInfoCampaign(collect([$produk]));

        return response()->json([
            'message' => 'Detail produk berhasil diambil.',
            'data' => $produk,
        ], 200);
    }


    /** Menempelkan satu Campaign aktif per varian untuk kebutuhan tampilan harga ecer. */
    private function tambahkanInfoCampaign($produkList): void
    {
        $idVarian = $produkList->flatMap(fn ($produk) => $produk->varian->pluck('id_varian'))->unique()->values();
        if ($idVarian->isEmpty()) return;

        $campaign = DB::table('promosi_varian as pv')
            ->join('promosi as p', 'p.id_promosi', '=', 'pv.id_promosi')
            ->whereIn('pv.id_varian', $idVarian)
            ->where('p.status_aktif', true)
            ->whereDate('p.tanggal_mulai', '<=', today())
            ->whereDate('p.tanggal_selesai', '>=', today())
            ->whereNotNull('pv.nilai_persen')
            ->select('pv.id_varian', 'p.id_promosi', 'p.nama_promosi', 'pv.nilai_persen', 'p.tanggal_mulai', 'p.tanggal_selesai')
            ->orderByDesc('p.id_promosi')->get()
            ->groupBy('id_varian')->map(fn ($rows) => $rows->first());

        foreach ($produkList as $produk) {
            foreach ($produk->varian as $varian) {
                $data = $campaign->get($varian->id_varian);
                $varian->setAttribute('campaign_aktif', $data ? [
                    'id_promosi' => (int) $data->id_promosi,
                    'nama_promosi' => $data->nama_promosi,
                    'nilai_persen' => (float) $data->nilai_persen,
                    'tanggal_mulai' => $data->tanggal_mulai,
                    'tanggal_selesai' => $data->tanggal_selesai,
                ] : null);
            }
        }
    }

    private function bolehLihatHargaGrosir(Request $request): bool
    {
        $pengguna = $request->user('sanctum');

        return $pengguna !== null
            && in_array($pengguna->jenis_pelanggan, ['toko', 'horeca'], true)
            && $pengguna->status_verifikasi === 'disetujui';
    }
}
