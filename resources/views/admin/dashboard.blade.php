@extends('admin.layouts.app')

@section('title', 'Dashboard Admin - TWFood')
@section('page-title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
    {{-- ===== Banner sambutan ===== --}}
    <div class="dash-hero d-print-none mb-4">
        <div class="dash-hero-isi">
            <div class="dash-hero-sub">Ringkasan &middot; {{ now()->locale('id')->translatedFormat('l, j F Y') }}</div>
            <h2 class="dash-hero-title">Halo, {{ auth()->user()->nama }}!</h2>
            <div class="dash-hero-text">Berikut ringkasan toko TWFood hari ini.</div>
        </div>
        <a href="{{ route('admin.pesanan.index') }}" class="dash-hero-btn">Lihat Pesanan</a>
    </div>

    {{-- ===== KPI ===== --}}
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="#pesanan" class="card card-sm kpi kpi-oranye text-reset text-decoration-none">
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
            <div class="card card-sm kpi kpi-hijau">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Pembayaran Diterima Hari Ini</div>
                            <div class="h1 mb-0">Rp {{ number_format($pembayaranHariIni, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-green-lt avatar"><i class="ti ti-cash icon"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="#lowstocktable" class="card card-sm kpi kpi-merah text-reset text-decoration-none">
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
            <div class="card card-sm kpi kpi-ungu">
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
                    <h3 class="card-title">
                        <span class="judul-ikon ikon-merah"><i class="ti ti-alert-triangle"></i></span>
                        Stok Menipis
                    </h3>
                    <div class="card-actions">
                        <a href="#lowstocktable" class="lihat-semua">Lihat semua &rarr;</a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($stokMenipisList->isEmpty())
                        <div class="dash-kosong-tengah">
                            <i class="ti ti-circle-check ikon-hijau"></i>
                            <div class="judul">Semua stok aman</div>
                            <div class="sub">Belum ada varian yang stoknya menipis.</div>
                        </div>
                    @else
                        <ul class="dash-list">
                            @foreach ($stokMenipisList as $item)
                                <li class="dash-item">
                                    <div class="dash-item-info">
                                        <div class="dash-item-nama">{{ $item->produk->nama_produk ?? '-' }}</div>
                                        <div class="dash-item-sub">{{ $item->nama_varian ?? '-' }}</div>
                                    </div>
                                    @if ((int) $item->stok <= 0)
                                        <span class="stok-pil stok-habis">Habis</span>
                                    @else
                                        <span class="stok-pil stok-sisa">Sisa {{ $item->stok }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($stokMenipisCount > $stokMenipisList->count())
                            <div class="dash-lainnya">
                                +{{ $stokMenipisCount - $stokMenipisList->count() }} varian lainnya
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="judul-ikon ikon-oranye"><i class="ti ti-trophy"></i></span>
                        Produk Terlaris
                    </h3>
                </div>
                <div class="card-body">
                    @if ($produkTerlaris->isEmpty())
                        <div class="dash-kosong-tengah">
                            <i class="ti ti-chart-bar ikon-oranye-teks"></i>
                            <div class="judul">Belum ada penjualan</div>
                            <div class="sub">Pesanan yang pembayarannya berhasil akan muncul di sini.</div>
                        </div>
                    @else
                        <ul class="dash-list">
                            @foreach ($produkTerlaris as $produk)
                                <li class="dash-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="rank rank-{{ $loop->iteration }}">{{ $loop->iteration }}</span>
                                        <div class="dash-item-nama">{{ $produk->nama_produk_saat_pesan }}</div>
                                    </div>
                                    <span class="terjual-pil">{{ (int) $produk->total_terjual }} terjual</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
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
            <div class="sales-wrap">
                <canvas id="salesChart"></canvas>
                <div id="salesEmpty" class="sales-empty" hidden>
                    <i class="ti ti-chart-line"></i>
                    <div>Belum ada penjualan pada periode ini</div>
                </div>
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
                        <th class="text-end">Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesananList as $pesanan)
                        <tr>
                            <td class="fw-semibold">{{ $pesanan->nomor_pesanan }}</td>
                            <td>{{ $pesanan->pengguna->nama ?? '-' }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge text-capitalize {{ $pesanan->status_pembayaran === 'lunas' ? 'bg-green-lt' : 'bg-yellow-lt' }}">
                                    {{ str_replace('_', ' ', $pesanan->status_pembayaran) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-azure-lt text-capitalize">
                                    {{ str_replace('_', ' ', $pesanan->status_pesanan) }}
                                </span>
                            </td>
                            <td class="text-secondary">{{ $pesanan->created_at->locale('id')->translatedFormat('j M Y') }}</td>
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
    {{-- ===== Tabel stok menipis (dituju dari card) ===== --}}
    <div id="lowstocktable" class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Stok Menipis (stok ≤ 5)</h3>
        </div>
        <div id="lowstock-wrapper">
            @include('admin.partials.stok-menipis-table')
        </div>
    </div>

    {{-- ===== Chart script (dummy) ===== --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sales = @json($salesChart);
            const canvas = document.getElementById('salesChart');
            const select = document.getElementById('salesRange');
            if (!canvas || !select || typeof Chart === 'undefined') return;

            Chart.defaults.font.family = "'Plus Jakarta Sans', Arial, sans-serif";
            Chart.defaults.color = '#6b7280';

            const rupiahSingkat = v => {
                v = Number(v);
                if (v >= 1000000) return 'Rp ' + (v / 1000000).toLocaleString('id-ID') + ' jt';
                if (v >= 1000) return 'Rp ' + (v / 1000).toLocaleString('id-ID') + ' rb';
                return 'Rp ' + v.toLocaleString('id-ID');
            };

            const chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: [],
                        backgroundColor: '#E31E24',
                        hoverBackgroundColor: '#F7941D',
                        borderRadius: 8,
                        maxBarThickness: 46,
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
                            suggestedMax: 100000,
                            border: { display: false },
                            grid: { color: '#eef0f3', borderDash: [4, 4] },
                            ticks: {
                                maxTicksLimit: 6,
                                callback: value => rupiahSingkat(value),
                            },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });

            function render(range) {
                chart.data.labels = sales[range].labels;
                chart.data.datasets[0].data = sales[range].data;
                chart.update();

                const kosong = sales[range].data.every(v => Number(v) === 0);
                document.getElementById('salesEmpty').hidden = !kosong;
            }

            select.addEventListener('change', e => render(e.target.value));
            render(select.value);
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('click', '#lowstock-wrapper .pagination a', function (e) {
            e.preventDefault();

            const url = $(this).attr('href');
            const $wrapper = $('#lowstock-wrapper');

            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function () {
                    $wrapper.css('opacity', 0.5);
                },
                success: function (html) {
                    $wrapper.html(html);
                    window.history.pushState({}, '', url); // optional: keeps URL in sync
                },
                error: function () {
                    alert('Gagal memuat data stok.');
                },
                complete: function () {
                    $wrapper.css('opacity', 1);
                }
            });
        });
    });
</script>
@endsection