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

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <a href="#produk" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Produk</div>
                            <div class="h1 mb-0">{{ $totalProduk }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-primary-lt avatar">
                                <i class="ti ti-package icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-4">
            <a href="#varian" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Varian</div>
                            <div class="h1 mb-0">{{ $totalVarian }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-azure-lt avatar">
                                <i class="ti ti-layers-intersect icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-4">
            <a href="#pesanan" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Pesanan</div>
                            <div class="h1 mb-0">{{ $totalPesanan }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-indigo-lt avatar">
                                <i class="ti ti-shopping-cart icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-4">
            <a href="#pesanan" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Pesanan Baru</div>
                            <div class="h1 mb-0">{{ $pesananBaru }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-orange-lt avatar">
                                <i class="ti ti-bell icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-4">
            <a href="#stok" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Stok</div>
                            <div class="h1 mb-0">{{ $totalStok }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-green-lt avatar">
                                <i class="ti ti-box icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-4">
            <a href="#pelanggan" class="card card-sm text-reset text-decoration-none">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Pelanggan</div>
                            <div class="h1 mb-0">{{ $totalPelanggan }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-purple-lt avatar">
                                <i class="ti ti-users icon"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- ===== Mockup cards (dummy data) ===== --}}
    @php
        // ===== DUMMY DATA (replace with controller values later) =====
        $dummyActivity = [
            'pengguna_online' => 12,
            'registrasi_baru' => 5,
            'pesanan_diproses' => 7,
        ];

        $dummyLowStock = [
            'total' => 11,
            'produk' => ['Kopi Susu Gula Aren', 'Teh Tarik Original', 'Roti Coklat Keju'],
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
                'data' => [
                    18200000,
                    16500000,
                    19800000,
                    21000000,
                    20100000,
                    22400000,
                    23900000,
                    21700000,
                    24300000,
                    25100000,
                    0,
                    0,
                ],
            ],
        ];
    @endphp

    <div class="row row-cards mb-3">
        {{-- Aktivitas --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="bg-green-lt avatar me-3">
                            <i class="ti ti-users icon"></i>
                        </span>
                        <div>
                            <div class="text-secondary">Pengguna Online</div>
                            <div class="h2 mb-0">{{ $dummyActivity['pengguna_online'] }}</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <span class="bg-blue-lt avatar me-3">
                            <i class="ti ti-user-plus icon"></i>
                        </span>
                        <div>
                            <div class="text-secondary">Registrasi Baru</div>
                            <div class="h2 mb-0">{{ $dummyActivity['registrasi_baru'] }}</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <span class="bg-orange-lt avatar me-3">
                            <i class="ti ti-bell-ringing icon"></i>
                        </span>
                        <div>
                            <div class="text-secondary">Pesanan Perlu Diproses</div>
                            <div class="h2 mb-0">{{ $dummyActivity['pesanan_diproses'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stok menipis --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="bg-red-lt avatar me-3">
                            <i class="ti ti-alert-triangle icon"></i>
                        </span>
                        <div>
                            <div class="text-secondary">Stok Menipis</div>
                            <div class="h2 mb-0">
                                {{ $stokMenipisCount }}
                                <span class="text-secondary h5">varian</span>
                            </div>
                        </div>
                    </div>

                    <ul class="list-unstyled mb-0">
                        @forelse ($stokMenipisList as $item)
                            <li class="d-flex align-items-center justify-content-between py-1">
                                <span>
                                    <i class="ti ti-point-filled text-red me-1"></i>
                                    {{ $item->produk->nama_produk ?? '-' }}
                                    <span class="text-secondary">({{ $item->nama_varian ?? '-' }})</span>
                                </span>
                                <span class="badge bg-red-lt">{{ $item->stok }}</span>
                            </li>
                        @empty
                            <li class="text-secondary py-1">Semua stok aman.</li>
                        @endforelse

                        @if ($stokMenipisCount > $stokMenipisList->count())
                            <li class="text-secondary py-1 ps-4">
                                +{{ $stokMenipisCount - $stokMenipisList->count() }} varian lainnya
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- Penjualan --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penjualan per Hari</h3>
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
        </div>
    </div>

    {{-- ===== Chart script (inline, no @push needed) ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sales = @json($dummySales);
            const canvas = document.getElementById('salesChart');
            const select = document.getElementById('salesRange');

            if (!canvas || !select) {
                console.error('Sales chart: canvas or select not found');
                return;
            }
            if (typeof Chart === 'undefined') {
                console.error('Sales chart: Chart.js failed to load');
                return;
            }

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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
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

    {{-- ===== Pesanan ===== --}}
    <div id="pesanan" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Daftar Pesanan</h3>
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
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $pesananList->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- ===== Stok ===== --}}
    <div id="stok" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Stok Varian (terkecil dulu)</h3>
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
                                <span class="badge {{ $item->stok <= 5 ? 'bg-red-lt' : 'bg-green-lt' }}">
                                    {{ $item->stok }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada data stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $stokList->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- ===== Produk ===== --}}
    <div id="produk" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Daftar Produk</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Jumlah Varian</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produkList as $produk)
                        <tr>
                            <td>{{ $produkList->firstItem() + $loop->index }}</td>
                            <td>{{ $produk->nama_produk }}</td>
                            <td>{{ $produk->varian_count }}</td>
                            <td>{{ $produk->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $produkList->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- ===== Varian ===== --}}
    <div id="varian" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Daftar Varian</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Nama Varian</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($varianList as $varian)
                        <tr>
                            <td>{{ $varianList->firstItem() + $loop->index }}</td>
                            <td>{{ $varian->produk->nama_produk ?? '-' }}</td>
                            <td>{{ $varian->nama_varian }}</td>
                            <td>
                                @forelse ($varian->daftarHarga as $harga)
                                    <div>
                                        {{ ucfirst($harga->jenis_harga) }}:
                                        Rp {{ number_format($harga->harga, 0, ',', '.') }}
                                    </div>
                                @empty
                                    <span class="text-secondary">-</span>
                                @endforelse
                            </td>
                            <td>{{ $varian->stok }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada varian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $varianList->links('pagination::bootstrap-5') }}
        </div>
    </div>



    {{-- ===== Pelanggan ===== --}}
    <div id="pelanggan" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Daftar Pelanggan</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Bergabung</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pelangganList as $pelanggan)
                        <tr>
                            <td>{{ $pelangganList->firstItem() + $loop->index }}</td>
                            <td>{{ $pelanggan->nama }}</td>
                            <td>{{ $pelanggan->email ?? '-' }}</td>
                            <td>{{ $pelanggan->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada pelanggan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            {{ $pelangganList->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
