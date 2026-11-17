@extends('admin.layouts.app')

@section('title', 'Laporan - TWFood')

@section('content')
    <div class="page-header">
        <h1>Laporan</h1>
        <p>Ringkasan pembayaran terverifikasi dan produk terlaris berdasarkan periode.</p>
    </div>

    <div class="content-card mb-3">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control"
                    value="{{ $tanggalMulai }}" required>
            </div>
            <div class="col-md-3">
                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control"
                    value="{{ $tanggalSelesai }}" min="{{ $tanggalMulai }}" required>
            </div>
            <div class="col-md-3">
                <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran</label>
                <select id="jenis_pembayaran" name="jenis_pembayaran" class="form-select">
                    <option value="">Semua jenis</option>
                    @foreach (['penuh' => 'Penuh', 'dp' => 'DP', 'pelunasan' => 'Pelunasan', 'refund' => 'Refund'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filter['jenis_pembayaran'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Terapkan</button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6">
            <div class="card"><div class="card-body">
                <div class="text-secondary">Total Pembayaran Diterima</div>
                <div class="h1 mb-0">Rp{{ number_format((int) $totalDiterima, 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-sm-6">
            <div class="card"><div class="card-body">
                <div class="text-secondary">Jumlah Transaksi Pembayaran</div>
                <div class="h1 mb-0">{{ number_format((int) $jumlahTransaksi, 0, ',', '.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="content-card mb-3">
        <h3 class="h3">Riwayat Pembayaran Berhasil</h3>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead><tr><th>Tanggal</th><th>ID Transaksi</th><th>Pesanan</th><th>Pelanggan</th><th>Jenis</th><th>Metode</th><th>Nominal</th></tr></thead>
                <tbody>
                    @forelse ($pembayaran as $item)
                        <tr>
                            <td>{{ $item->dibayar_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $item->id_transaksi ?: '-' }}</td>
                            <td>{{ $item->pesanan?->nomor_pesanan ?? '-' }}</td>
                            <td>{{ $item->pesanan?->pengguna?->nama ?? '-' }}</td>
                            <td>{{ ucfirst($item->jenis_pembayaran) }}</td>
                            <td>{{ $item->metode_pembayaran ?: '-' }}</td>
                            <td>Rp{{ number_format((int) $item->jumlah_pembayaran, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada pembayaran berhasil pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $pembayaran->links() }}</div>
    </div>

    <div class="content-card">
        <h3 class="h3">Produk Terlaris</h3>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead><tr><th>Produk</th><th>Jumlah Terjual</th></tr></thead>
                <tbody>
                    @forelse ($produkTerlaris as $produk)
                        <tr><td>{{ $produk->nama_produk_saat_pesan }}</td><td>{{ (int) $produk->total_terjual }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-secondary py-4">Belum ada data penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
