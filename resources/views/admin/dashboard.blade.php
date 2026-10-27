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
