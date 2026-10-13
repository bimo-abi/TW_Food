
@extends('admin.layouts.app')

@section('title', 'Dashboard Admin - TWFood')

@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <div class="text-secondary mt-1">
                Selamat datang, <strong>{{ auth()->user()->nama }}</strong>.
            </div>
        </div>
    </div>

    {{-- STATISTIK --}}
    <div class="row row-cards mb-4">

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Total Produk</div>
                            <div class="h1 mb-0">{{ $totalProduk }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar">
                                <i class="ti ti-package fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Total Varian</div>
                            <div class="h1 mb-0">{{ $totalVarian }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-azure-lt text-azure avatar">
                                <i class="ti ti-layers-intersect fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Total Pesanan</div>
                            <div class="h1 mb-0">{{ $totalPesanan }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-indigo-lt text-indigo avatar">
                                <i class="ti ti-shopping-cart fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Pesanan Baru</div>
                            <div class="h1 mb-0">{{ $pesananBaru }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-warning-lt text-warning avatar">
                                <i class="ti ti-bell-ringing fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Total Stok</div>
                            <div class="h1 mb-0">{{ $totalStok }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-success-lt text-success avatar">
                                <i class="ti ti-box-seam fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary mb-1">Total Pelanggan</div>
                            <div class="h1 mb-0">{{ $totalPelanggan }}</div>
                        </div>
                        <div class="col-auto">
                            <span class="bg-purple-lt text-purple avatar">
                                <i class="ti ti-users fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- PESANAN TERBARU --}}
    <div class="card">
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
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pesananTerbaru as $pesanan)
                        <tr>
                            <td>{{ $pesanan->nomor_pesanan }}</td>
                            <td>{{ $pesanan->pengguna->nama ?? '-' }}</td>
                            <td>
                                Rp {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}
                            </td>
                            <td>{{ $pesanan->status_pembayaran }}</td>
                            <td>
                                <span class="badge bg-blue-lt">
                                    {{ $pesanan->status_pesanan }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
