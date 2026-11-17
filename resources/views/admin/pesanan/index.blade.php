@extends('admin.layouts.app')

@section('title', 'Manajemen Pesanan - Admin TWFood')
@section('page-title', 'Manajemen Pesanan')

@section('content')
    @php
        $labelJenis = $jenisPemenuhan === 'pre_order' ? 'Pre-order' : 'Reguler';
    @endphp

    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Transaksi</div>
                <h2 class="page-title">Pesanan {{ $labelJenis }}</h2>
                <div class="text-secondary mt-1">
                    Kelola pesanan {{ strtolower($labelJenis) }} pelanggan TWFood.
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => 'reguler']) }}"
               class="card card-link {{ $jenisPemenuhan === 'reguler' ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="subheader">Pesanan Reguler</div>
                    <div class="h1 mb-0">{{ number_format($jumlahReguler) }}</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => 'pre_order']) }}"
               class="card card-link {{ $jenisPemenuhan === 'pre_order' ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="subheader">Pesanan Pre-order</div>
                    <div class="h1 mb-0">{{ number_format($jumlahPreOrder) }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Pesanan {{ $labelJenis }}</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pesanan.index') }}">
                <input type="hidden" name="jenis_pemenuhan" value="{{ $jenisPemenuhan }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="q">Nomor Pesanan / Nama Pelanggan</label>
                        <input class="form-control" id="q" name="q" value="{{ $filter['q'] ?? '' }}"
                               placeholder="Cari pesanan atau pelanggan">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="status_pesanan">Status Pesanan</label>
                        <select class="form-select" id="status_pesanan" name="status_pesanan">
                            <option value="">Semua status</option>
                            @foreach ([
                                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                'sedang_diproses' => 'Sedang Diproses',
                                'siap_diambil' => 'Siap Diambil',
                                'siap_dikirim' => 'Siap Dikirim',
                                'dalam_pengiriman' => 'Dalam Pengiriman',
                                'diterima' => 'Diterima',
                                'dibatalkan' => 'Dibatalkan',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(($filter['status_pesanan'] ?? '') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="status_pembayaran">Status Pembayaran</label>
                        <select class="form-select" id="status_pembayaran" name="status_pembayaran">
                            <option value="">Semua pembayaran</option>
                            @foreach ([
                                'belum_dibayar' => 'Belum Dibayar',
                                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                'dp_dibayar' => 'DP Dibayar',
                                'sebagian_dibayar' => 'Sebagian Dibayar',
                                'lunas' => 'Lunas',
                                'gagal' => 'Gagal',
                                'kadaluarsa' => 'Kadaluarsa',
                                'dibatalkan' => 'Dibatalkan',
                                'dikembalikan' => 'Dikembalikan',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected(($filter['status_pembayaran'] ?? '') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="saluran_pesanan">Jenis Pelanggan</label>
                        <select class="form-select" id="saluran_pesanan" name="saluran_pesanan">
                            <option value="">Semua jenis pelanggan</option>
                            @foreach (['online' => 'Umum / Retail', 'toko' => 'Toko', 'horeca' => 'Horeca'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filter['saluran_pesanan'] ?? '') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="jenis_pesanan">Pemenuhan</label>
                        <select class="form-select" id="jenis_pesanan" name="jenis_pesanan">
                            <option value="">Pickup dan Delivery</option>
                            <option value="pickup" @selected(($filter['jenis_pesanan'] ?? '') === 'pickup')>Pickup</option>
                            <option value="delivery" @selected(($filter['jenis_pesanan'] ?? '') === 'delivery')>Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                               value="{{ $filter['tanggal_mulai'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai"
                               value="{{ $filter['tanggal_selesai'] ?? '' }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => $jenisPemenuhan]) }}"
                       class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pesanan {{ $labelJenis }}</h3>
            <div class="card-actions text-secondary">
                {{ $pesanan->total() }} pesanan
            </div>
        </div>

        @if ($pesanan->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-clipboard-off d-block mb-3" style="font-size: 3rem;"></i>
                <h3 class="mb-1">Belum Ada Pesanan {{ $labelJenis }}</h3>
                <p class="text-secondary mb-0">Pesanan yang sesuai dengan filter akan ditampilkan di sini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Jenis Pelanggan</th>
                            <th>Pemenuhan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status Pesanan</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pesanan as $item)
                            @php
                                $statusClass = match ($item->status_pesanan) {
                                    'diterima' => 'bg-success-lt',
                                    'dibatalkan' => 'bg-danger-lt',
                                    'sedang_diproses', 'siap_diambil', 'siap_dikirim', 'dalam_pengiriman' => 'bg-info-lt',
                                    default => 'bg-secondary-lt',
                                };
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ $item->nomor_pesanan }}</td>
                                <td>{{ $item->pengguna->nama ?? $item->nama_penerima_saat_pesan ?? '-' }}</td>
                                <td>
                                    @switch($item->saluran_pesanan)
                                        @case('online') Umum / Retail @break
                                        @case('toko') Toko @break
                                        @case('horeca') Horeca @break
                                        @default -
                                    @endswitch
                                </td>
                                <td>
                                    @if ($item->jenis_pesanan === 'pickup')
                                        <span class="badge bg-secondary-lt">Pickup</span>
                                    @elseif ($item->jenis_pesanan === 'delivery')
                                        <span class="badge bg-info-lt">Delivery</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-nowrap">Rp {{ number_format($item->total_pesanan, 0, ',', '.') }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $item->status_pembayaran)) }}</td>
                                <td><span class="badge {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status_pesanan)) }}</span></td>
                                <td class="text-nowrap">{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.pesanan.show', $item->id_pesanan) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="text-secondary small">
                    Menampilkan {{ $pesanan->firstItem() }}–{{ $pesanan->lastItem() }} dari {{ $pesanan->total() }} pesanan
                </div>
                {{ $pesanan->links() }}
            </div>
        @endif
    </div>
@endsection
