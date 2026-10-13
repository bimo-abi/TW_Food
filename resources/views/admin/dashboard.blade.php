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
        <div class="card card-sm">
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
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
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
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
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
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
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
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
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
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card card-sm">
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
        </div>
    </div>
</div>

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
                        <td>
                            {{ str_replace('_', ' ', $pesanan->status_pembayaran) }}
                        </td>
                        <td>
                            <span class="badge bg-azure-lt">
                                {{ str_replace('_', ' ', $pesanan->status_pesanan) }}
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
