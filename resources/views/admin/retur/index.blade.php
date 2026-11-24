@extends('admin.layouts.app')

@section('title', 'Retur - TWFood')
@section('page-title', 'Retur')

@section('content')
    @php
        $kelasRetur = fn ($st) => match ($st) {
            'menunggu_persetujuan' => 'bg-yellow-lt',
            'disetujui', 'selesai' => 'bg-success-lt',
            'menunggu_pengembalian_barang' => 'bg-orange-lt',
            'barang_diterima' => 'bg-azure-lt',
            'sedang_diperiksa' => 'bg-purple-lt',
            'ditolak' => 'bg-danger-lt',
            default => 'bg-secondary-lt',
        };
        $kelasRefund = fn ($st) => match ($st) {
            'berhasil', 'selesai', 'dicatat_manual', 'dikembalikan' => 'bg-success-lt',
            'gagal', 'ditolak' => 'bg-danger-lt',
            'menunggu', 'diproses', 'menunggu_refund' => 'bg-yellow-lt',
            default => 'bg-secondary-lt',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">
            Pantau pengajuan retur pelanggan dan status pengembalian dananya.
        </div>

        <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-history me-1"></i>
            Riwayat Refund
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pengajuan Retur</h3>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.retur.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="q" class="form-label">Cari retur, pesanan, atau pelanggan</label>
                    <input
                        id="q"
                        name="q"
                        type="search"
                        class="form-control"
                        value="{{ $filter['q'] ?? '' }}"
                        placeholder="Nomor retur / pesanan / nama pelanggan"
                    >
                </div>
                <div class="col-12 col-md-7 col-lg-4">
                    <label for="status_retur" class="form-label">Status retur</label>
                    <select id="status_retur" name="status_retur" class="form-select">
                        <option value="">Semua status</option>
                        @foreach ([
                            'menunggu_persetujuan' => 'Menunggu Persetujuan',
                            'disetujui' => 'Disetujui',
                            'menunggu_pengembalian_barang' => 'Menunggu Pengembalian Barang',
                            'barang_diterima' => 'Barang Diterima',
                            'sedang_diperiksa' => 'Sedang Diperiksa',
                            'selesai' => 'Selesai',
                            'ditolak' => 'Ditolak',
                            'dibatalkan' => 'Dibatalkan',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(($filter['status_retur'] ?? '') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-5 col-lg-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ti ti-search me-1"></i>Cari
                        </button>
                        <a href="{{ route('admin.retur.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        @if ($retur->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-package-export d-block mb-3" style="font-size: 2.5rem; color: #F7941D;"></i>
                <h3 class="h3 mb-1">Belum Ada Pengajuan Retur</h3>
                <p class="text-secondary mb-0">
                    Belum ada pengajuan retur yang sesuai dengan pencarian atau filter yang dipilih.
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>Nomor Retur</th>
                            <th>Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Diajukan</th>
                            <th>Status Retur</th>
                            <th>Status Refund</th>
                            <th class="text-end">Nominal Refund</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($retur as $item)
                            @php
                                $statusRefund = $item->refund->pluck('status_refund')->unique();
                                $labelRefund = $statusRefund->isEmpty()
                                    ? 'Belum tersedia'
                                    : ($statusRefund->count() === 1 ? str_replace('_', ' ', $statusRefund->first()) : 'Beberapa status');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-nowrap">{{ $item->nomor_retur }}</div>
                                </td>
                                <td class="text-nowrap">{{ $item->pesanan?->nomor_pesanan ?? '-' }}</td>
                                <td>{{ $item->pesanan?->pengguna?->nama ?? '-' }}</td>
                                <td class="text-nowrap">
                                    @if ($item->diajukan_pada)
                                        <div>{{ $item->diajukan_pada->locale('id')->translatedFormat('j M Y') }}</div>
                                        <div class="text-secondary small">{{ $item->diajukan_pada->format('H:i') }} WIB</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $kelasRetur($item->status_retur) }}">
                                        {{ ucwords(str_replace('_', ' ', $item->status_retur)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusRefund->count() === 1 ? $kelasRefund($statusRefund->first()) : 'bg-secondary-lt' }}">
                                        {{ ucwords($labelRefund) }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((int) $item->nominal_pengembalian, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.retur.show', $item->id_retur) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($retur->hasPages())
                <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="text-secondary small">
                        Menampilkan {{ $retur->firstItem() }}–{{ $retur->lastItem() }} dari {{ $retur->total() }} pengajuan
                    </div>
                    {{ $retur->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection