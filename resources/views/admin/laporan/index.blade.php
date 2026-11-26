@extends('admin.layouts.app')

@section('title', 'Laporan - TWFood')
@section('page-title', 'Laporan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
    @php
        $kelasJenis = fn ($j) => match ($j) {
            'penuh' => 'bg-success-lt',
            'dp' => 'bg-orange-lt',
            'pelunasan' => 'bg-azure-lt',
            'refund' => 'bg-danger-lt',
            default => 'bg-secondary-lt',
        };
    @endphp

    <div class="text-secondary mb-4">
        Ringkasan pembayaran terverifikasi dan produk terlaris berdasarkan periode.
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="form-laporan" method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
                        value="{{ $tanggalMulai }}" required>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_selesai" class="form-label">Tanggal selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control"
                        value="{{ $tanggalSelesai }}" min="{{ $tanggalMulai }}" required>
                </div>
                <div class="col-md-3">
                    <label for="jenis_pembayaran" class="form-label">Jenis pembayaran</label>
                    <select id="jenis_pembayaran" name="jenis_pembayaran" class="form-select">
                        <option value="">Semua jenis</option>
                        @foreach (['penuh' => 'Penuh', 'dp' => 'DP', 'pelunasan' => 'Pelunasan', 'refund' => 'Refund'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filter['jenis_pembayaran'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="ti ti-filter me-1"></i>Terapkan
                    </button>
                </div>
            </form>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                <span class="text-secondary small me-1">Pilih cepat:</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-rentang="hari">Hari ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-rentang="7">7 hari terakhir</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-rentang="bulan">Bulan ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-rentang="tahun">Tahun ini</button>
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan ===== --}}
    <div class="row row-cards mb-4">
        <div class="col-sm-6">
            <div class="card card-sm kpi kpi-hijau">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Pembayaran Diterima</div>
                            <div class="h1 mb-0">Rp {{ number_format((int) $totalDiterima, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-green-lt avatar"><i class="ti ti-cash icon"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card card-sm kpi kpi-oranye">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Jumlah Transaksi Pembayaran</div>
                            <div class="h1 mb-0">{{ number_format((int) $jumlahTransaksi, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-orange-lt avatar"><i class="ti ti-receipt icon"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Riwayat pembayaran ===== --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <span class="judul-ikon ikon-merah"><i class="ti ti-credit-card"></i></span>
                Riwayat Pembayaran Berhasil
            </h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>ID Transaksi</th>
                        <th>Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Jenis</th>
                        <th>Metode</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembayaran as $item)
                        <tr>
                            <td class="text-nowrap">
                                @if ($item->dibayar_pada)
                                    <div>{{ $item->dibayar_pada->locale('id')->translatedFormat('j M Y') }}</div>
                                    <div class="text-secondary small">{{ $item->dibayar_pada->format('H:i') }} WIB</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-secondary">{{ $item->id_transaksi ?: '-' }}</td>
                            <td class="fw-semibold">{{ $item->pesanan?->nomor_pesanan ?? '-' }}</td>
                            <td>{{ $item->pesanan?->pengguna?->nama ?? '-' }}</td>
                            <td><span class="badge {{ $kelasJenis($item->jenis_pembayaran) }}">{{ ucfirst($item->jenis_pembayaran) }}</span></td>
                            <td class="text-uppercase">{{ $item->metode_pembayaran ?: '-' }}</td>
                            <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((int) $item->jumlah_pembayaran, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="ti ti-receipt-off d-block mb-2" style="font-size: 2.4rem; color: #F7941D;"></i>
                                <div class="fw-bold text-dark">Tidak ada pembayaran berhasil</div>
                                <div class="small">Coba ubah rentang tanggal atau jenis pembayaran.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pembayaran->hasPages())
            <div class="card-footer">{{ $pembayaran->links() }}</div>
        @endif
    </div>

    {{-- ===== Produk terlaris ===== --}}
    <div class="card">
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
                    <div class="judul">Belum ada data penjualan</div>
                    <div class="sub">Tidak ada produk terjual pada periode ini.</div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('form-laporan');
        var mulai = document.getElementById('tanggal_mulai');
        var selesai = document.getElementById('tanggal_selesai');
        var fmt = function (d) { return d.toLocaleDateString('sv-SE'); }; // YYYY-MM-DD

        document.querySelectorAll('[data-rentang]').forEach(function (b) {
            b.addEventListener('click', function () {
                var hariIni = new Date();
                var awal = new Date();
                var r = b.dataset.rentang;
                if (r === '7') awal.setDate(hariIni.getDate() - 6);
                if (r === 'bulan') awal = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);
                if (r === 'tahun') awal = new Date(hariIni.getFullYear(), 0, 1);
                mulai.value = fmt(awal);
                selesai.min = fmt(awal);
                selesai.value = fmt(hariIni);
                form.requestSubmit();
            });
        });
        mulai.addEventListener('change', function () { selesai.min = mulai.value; });
    });
    </script>
@endsection