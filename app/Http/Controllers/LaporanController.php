<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanFilterRequest;
use App\Models\Outlet;
use App\Services\Laporan\LaporanService;
use Illuminate\View\View;

class LaporanController extends Controller
{
    private const REPORTS = [
        'penjualan' => 'Penjualan',
        'keuangan' => 'Keuangan',
        'transaksi' => 'Transaksi',
        'produk-stok' => 'Produk & Stok',
        'pelanggan' => 'Pelanggan',
        'retur-refund' => 'Retur & Refund',
        'produksi' => 'Produksi',
    ];

    public function index(LaporanFilterRequest $request, LaporanService $service): View
    {
        $filters = $request->filters();
        return view('admin.laporan.index', [
            'jenis' => 'ringkasan',
            'judul' => 'Ringkasan Bisnis',
            'reports' => self::REPORTS,
            'filters' => $filters,
            'outlets' => Outlet::query()->orderBy('nama_outlet')->get(['id_outlet', 'nama_outlet']),
            'summary' => $service->dashboard($filters),
            'rows' => collect(),
            'orders' => collect(),
            'secondaryRows' => collect(),
            'outstandingRows' => collect(),
        ]);
    }

    public function show(string $jenis, LaporanFilterRequest $request, LaporanService $service): View
    {
        abort_unless(isset(self::REPORTS[$jenis]), 404);
        $filters = $request->filters();
        $data = match ($jenis) {
            'penjualan' => $service->sales($filters),
            'keuangan' => [
                'rows' => $service->payments($filters),
                'secondaryRows' => $service->manualFinancialEntries($filters),
                'outstandingRows' => $service->outstandingOrders($filters),
            ],
            'transaksi' => ['rows' => $service->transactions($filters)],
            'produk-stok' => ['rows' => $service->products($filters)],
            'pelanggan' => ['rows' => $service->customers($filters)],
            'retur-refund' => [
                'rows' => $service->returns($filters),
                'secondaryRows' => $service->refunds($filters),
            ],
            'produksi' => ['rows' => $service->production($filters)],
        };

        return view('admin.laporan.index', array_merge([
            'jenis' => $jenis,
            'judul' => self::REPORTS[$jenis],
            'reports' => self::REPORTS,
            'filters' => $filters,
            'outlets' => Outlet::query()->orderBy('nama_outlet')->get(['id_outlet', 'nama_outlet']),
            'summary' => $service->dashboard($filters),
            'rows' => $data['rows'] ?? $data['products'] ?? collect(),
            'orders' => $data['orders'] ?? collect(),
            'secondaryRows' => $data['secondaryRows'] ?? collect(),
        ], $data));
    }
}
