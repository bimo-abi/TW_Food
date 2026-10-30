@extends('admin.layouts.app')

@section('title', 'Dashboard Admin - TWFood')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Ringkasan</div>
                <h2 class="page-title">Dashboard TWFood</h2>
                <div class="text-secondary mt-1">
                    Selamat datang,
                    <strong>{{ auth()->user()->nama }}</strong>.
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Dummy data (ganti saat transaksi sudah final) ===== --}}
    @php
        $dummyPenjualanHariIni = 1250000;

        $dummyProdukTerlaris = [
            ['nama' => 'Kopi Susu Gula Aren', 'terjual' => 48],
            ['nama' => 'Teh Tarik Original', 'terjual' => 35],
            ['nama' => 'Roti Coklat Keju', 'terjual' => 22],
        ];

        $dummySales = [
            'minggu' => [
                'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'data' => [420000, 380000, 510000, 465000, 620000, 780000, 690000],
            ],
            'bulan' => [
                'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                'data' => [2850000, 3120000, 2640000, 3390000],
            ],
            'tahun' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'data' => [18200000, 16500000, 19800000, 21000000, 20100000, 22400000,
                    23900000, 21700000, 24300000, 25100000, 0, 0],
            ],
        ];
    @endphp

    {{-- ===== KPI ===== --}}
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="#pesanan" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Pesanan Perlu Diproses</div>
                            <div class="h1 mb-0">{{ $pesananBaru }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-orange-lt avatar"><i class="ti ti-bell-ringing icon"></i></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Penjualan Hari Ini</div>
                            <div class="h1 mb-0">Rp {{ number_format($dummyPenjualanHariIni, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-green-lt avatar"><i class="ti ti-cash icon"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="#lowstocktable" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Stok Menipis</div>
                            <div class="h1 mb-0">{{ $stokMenipisCount }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-red-lt avatar"><i class="ti ti-alert-triangle icon"></i></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Pelanggan Baru (7 hari)</div>
                            <div class="h1 mb-0">{{ $pelangganBaru }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-purple-lt avatar"><i class="ti ti-user-plus icon"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Stok menipis + Produk terlaris ===== --}}
    <div class="row row-cards mb-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Stok Menipis</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @forelse ($stokMenipisList as $item)
                            <li class="d-flex align-items-center justify-content-between py-1">
                                <span>
                                    {{ $item->produk->nama_produk ?? '-' }}
                                    <span class="text-secondary">({{ $item->nama_varian ?? '-' }})</span>
                                </span>
                                <span class="badge bg-red-lt">{{ $item->stok }}</span>
                            </li>
                        @empty
                            <li class="text-secondary py-1">Semua stok aman.</li>
                        @endforelse

                        @if ($stokMenipisCount > $stokMenipisList->count())
                            <li class="py-1 ps-0">
                                <a href="#lowstocktable">
                                    +{{ $stokMenipisCount - $stokMenipisList->count() }} varian lainnya
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Produk Terlaris</h3>
                    <div class="card-actions text-secondary small">Data contoh</div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach ($dummyProdukTerlaris as $produk)
                            <li class="d-flex align-items-center justify-content-between py-1">
                                <span>{{ $produk['nama'] }}</span>
                                <span class="text-secondary">{{ $produk['terjual'] }} terjual</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Chart penjualan (dummy) ===== --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Penjualan</h3>
            <div class="card-actions">
                <select id="salesRange" class="form-select form-select-sm w-auto">
                    <option value="minggu" selected>Per Minggu</option>
                    <option value="bulan">Per Bulan</option>
                    <option value="tahun">Per Tahun</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== Pesanan terbaru (top 5) ===== --}}
    <div id="pesanan" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Pesanan Terbaru</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesananList as $pesanan)
                        <tr>
                            <td>{{ $pesanan->nomor_pesanan }}</td>
                            <td>{{ $pesanan->pengguna->nama ?? '-' }}</td>
                            <td>Rp {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}</td>
                            <td>{{ str_replace('_', ' ', $pesanan->status_pembayaran) }}</td>
                            <td>
                                <span class="badge bg-azure-lt">
                                    {{ str_replace('_', ' ', $pesanan->status_pesanan) }}
                                </span>
                            </td>
                            <td>{{ $pesanan->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.pesanan.index') }}">Lihat semua pesanan</a>
        </div>
    </div>

    {{-- ===== Tabel stok menipis (dituju dari card) ===== --}}
    <div id="lowstocktable" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Stok Menipis (stok ≤ 5)</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Varian</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stokList as $item)
                        <tr>
                            <td>{{ $stokList->firstItem() + $loop->index }}</td>
                            <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                            <td>{{ $item->nama_varian ?? '-' }}</td>
                            <td>
                                <span class="badge bg-red-lt">{{ $item->stok }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Semua stok aman.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $stokList->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- ===== Chart script (dummy) ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sales = @json($dummySales);
            const canvas = document.getElementById('salesChart');
            const select = document.getElementById('salesRange');
            if (!canvas || !select || typeof Chart === 'undefined') return;

            const chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: [],
                        backgroundColor: '#206bc4',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'Rp ' + Number(value).toLocaleString('id-ID'),
                            },
                        },
                    },
                },
            });

            function render(range) {
                chart.data.labels = sales[range].labels;
                chart.data.datasets[0].data = sales[range].data;
                chart.update();
            }

            select.addEventListener('change', e => render(e.target.value));
            render(select.value);
        });
    </script>
@endsection
