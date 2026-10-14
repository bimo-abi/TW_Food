@extends('admin.layouts.app')

@section('title', 'Pesanan - Admin TWFood')
@section('page-title', 'Pesanan')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Transaksi</div>
                <h2 class="page-title">Manajemen Pesanan</h2>
                <div class="text-secondary mt-1">
                    Daftar pesanan pelanggan TWFood.
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pesanan</h3>
        </div>

        @if ($pesanan->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-clipboard-off d-block mb-3"
                   style="font-size: 3rem;"></i>

                <h3 class="mb-1">Belum Ada Pesanan</h3>

                <p class="text-secondary mb-0">
                    Pesanan pelanggan akan ditampilkan di halaman ini.
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Pengiriman</th>
                            <th>Status Pesanan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($pesanan as $item)
                            <tr>
                                <td>
                                    <span class="fw-bold">
                                        {{ $item->nomor_pesanan }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->pengguna->nama ?? '-' }}
                                </td>

                                <td class="text-nowrap">
                                    Rp {{ number_format($item->total_pesanan, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ ucwords(str_replace('_', ' ', $item->status_pembayaran)) }}
                                </td>

                                <td>
                                    @if ($item->jenis_pesanan === 'pickup')
                                        <span class="badge bg-secondary-lt">
                                            Pickup
                                        </span>
                                    @elseif ($item->jenis_pesanan === 'delivery')
                                        @if ($item->kurir === 'gosend')
                                            <div class="fw-bold">GoSend</div>

                                            @if ($item->tautan_pelacakan)
                                                <span class="badge bg-success-lt mt-1">
                                                    Tracking tersedia
                                                </span>
                                            @else
                                                <span class="badge bg-warning-lt mt-1">
                                                    Tracking belum diisi
                                                </span>
                                            @endif

                                        @elseif ($item->kurir === 'jnt')
                                            <div class="fw-bold">J&T</div>

                                            @if ($item->nomor_resi)
                                                <span class="badge bg-success-lt mt-1">
                                                    Resi tersedia
                                                </span>
                                            @else
                                                <span class="badge bg-warning-lt mt-1">
                                                    Resi belum diisi
                                                </span>
                                            @endif

                                        @else
                                            Delivery
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @php
                                        $status = $item->status_pesanan;

                                        $statusClass = match ($status) {
                                            'selesai',
                                            'diterima',
                                            'pesanan_diambil' => 'bg-success-lt',

                                            'dibatalkan' => 'bg-danger-lt',

                                            'sedang_diproses',
                                            'pesanan_siap',
                                            'menunggu_pengiriman',
                                            'dalam_pengiriman',
                                            'siap_diambil' => 'bg-info-lt',

                                            default => 'bg-secondary-lt',
                                        };
                                    @endphp

                                    <span class="badge {{ $statusClass }}">
                                        {{ ucwords(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.pesanan.show', $item->id_pesanan) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye me-1"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
