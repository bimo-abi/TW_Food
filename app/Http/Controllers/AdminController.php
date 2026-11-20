<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\VarianProduk;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private const BATAS_STOK_MENIPIS = 5;
    private const STOK_PER_HALAMAN = 5;
    private const STOK_PREVIEW = 3;
    private const PESANAN_TERBARU = 5;
    private const PRODUK_TERLARIS = 5;

    public function dashboard(Request $request)
    {
        $stokList = $this->stokMenipisQuery()
            ->paginate(self::STOK_PER_HALAMAN, ['*'], 'stok_page')
            ->withQueryString();

        // Request AJAX (pagination): kembalikan hanya tabel stok menipis,
        // query dashboard lainnya tidak perlu dijalankan.
        if ($request->ajax()) {
            return view('admin.partials.stok-menipis-table', compact('stokList'))->render();
        }

        return view('admin.dashboard', [
            'pesananBaru'       => Pesanan::where('status_pesanan', 'menunggu_pembayaran')->count(),
            'pelangganBaru'     => $this->pelangganBaru(),
            'pembayaranHariIni' => $this->pembayaranHariIni(),
            'stokMenipisCount'  => $stokList->total(),
            'stokMenipisList'   => $this->stokMenipisQuery()->limit(self::STOK_PREVIEW)->get(),
            'stokList'          => $stokList,
            'pesananList'       => $this->pesananTerbaru(),
            'produkTerlaris'    => $this->produkTerlaris(),
            'salesChart'        => $this->salesChart(),
        ]);
    }

    /* ---------------------------------------------------------------
     |  Query helpers
     | --------------------------------------------------------------- */

    private function stokMenipisQuery(): Builder
    {
        return VarianProduk::with('produk')
            ->where('stok', '<=', self::BATAS_STOK_MENIPIS)
            ->orderBy('stok');
    }

    /**
     * Hanya pembayaran yang telah diverifikasi sebagai paid yang dihitung.
     */
    private function pembayaranPaid(): Builder
    {
        return Pembayaran::query()->where('status_pembayaran', 'paid');
    }

    private function pelangganBaru(): int
    {
        return Pengguna::where('peran', 'pelanggan')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    private function pembayaranHariIni(): float|int
    {
        return $this->pembayaranPaid()
            ->whereDate('dibayar_pada', today())
            ->sum('jumlah_pembayaran');
    }

    private function pesananTerbaru()
    {
        return Pesanan::with('pengguna')
            ->latest('created_at')
            ->limit(self::PESANAN_TERBARU)
            ->get();
    }

    private function produkTerlaris()
    {
        return DetailPesanan::query()
            ->select('nama_produk_saat_pesan', DB::raw('SUM(detail_pesanan.jumlah) as total_terjual'))
            ->whereHas('pesanan.pembayaran', function ($query) {
                $query->where('status_pembayaran', 'paid');
            })
            ->groupBy('nama_produk_saat_pesan')
            ->orderByDesc('total_terjual')
            ->limit(self::PRODUK_TERLARIS)
            ->get();
    }

    /* ---------------------------------------------------------------
     |  Chart penjualan
     | --------------------------------------------------------------- */

    private function salesChart(): array
    {
        return [
            'minggu' => $this->salesSeries(
                $this->pembayaranPaid()->whereBetween('dibayar_pada', [now()->startOfWeek(), now()->endOfWeek()]),
                'DATE(dibayar_pada)',
                fn ($label) => Carbon::parse($label)->translatedFormat('D')
            ),
            'bulan' => $this->salesSeries(
                $this->pembayaranPaid()->whereBetween('dibayar_pada', [now()->startOfMonth(), now()->endOfMonth()]),
                'DATE(dibayar_pada)',
                fn ($label) => Carbon::parse($label)->format('d M')
            ),
            'tahun' => $this->salesSeries(
                $this->pembayaranPaid()->whereYear('dibayar_pada', now()->year),
                'MONTH(dibayar_pada)',
                // Tanggal 1 dipakai agar tidak overflow (mis. hari ini tanggal 31).
                fn ($label) => Carbon::create(now()->year, (int) $label, 1)->translatedFormat('M')
            ),
        ];
    }

    /**
     * Kelompokkan pembayaran per label lalu ubah ke format {labels, data} untuk Chart.js.
     */
    private function salesSeries(Builder $query, string $labelExpression, callable $formatLabel): array
    {
        $rows = $query
            ->selectRaw("{$labelExpression} as label, SUM(jumlah_pembayaran) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $formatLabel($row->label))->all(),
            'data'   => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }
}
