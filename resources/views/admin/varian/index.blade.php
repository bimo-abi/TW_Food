@extends('admin.layouts.app')

@section('title', 'Varian Produk - Admin TWFood')
@section('page-title', 'Varian Produk')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Manajemen</div>
                <h2 class="page-title">Varian Produk</h2>
                <div class="text-secondary mt-1">
                    Produk: <strong>{{ $produk->nama_produk }}</strong>
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.varian.create', $produk->id_produk) }}"
                   class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Varian
                </a>
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

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">
                Periksa kembali data berikut:
            </div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Varian Produk</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Varian</th>
                        <th>Berat</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Pre-Order</th>
                        <th>Jadwal Pre-Order</th>
                        <th>Estimasi Tersedia</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($varian as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="fw-bold">
                                    {{ $item->nama_varian }}
                                </span>
                            </td>

                            <td>
                                @if ($item->berat_gram !== null)
                                    {{ number_format($item->berat_gram, 0, ',', '.') }} gram
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ $item->satuan_jual }}</td>

                            <td>
                                {{ number_format($item->stok, 0, ',', '.') }}
                            </td>

                            <td>
                                @if ($item->tersedia_pre_order)
                                    <span class="badge bg-info-lt">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary-lt">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($item->tersedia_pre_order)
                                    <div class="text-nowrap">
                                        <div>
                                            <span class="text-secondary">Mulai:</span>
                                            {{ $item->tanggal_mulai_pre_order
                                                ? \Carbon\Carbon::parse($item->tanggal_mulai_pre_order)->format('d-m-Y')
                                                : '-' }}
                                        </div>

                                        <div>
                                            <span class="text-secondary">Selesai:</span>
                                            {{ $item->tanggal_selesai_pre_order
                                                ? \Carbon\Carbon::parse($item->tanggal_selesai_pre_order)->format('d-m-Y')
                                                : '-' }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                @if ($item->tersedia_pre_order && $item->estimasi_tersedia)
                                    {{ \Carbon\Carbon::parse($item->estimasi_tersedia)->format('d-m-Y') }}
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                @if ($item->status_aktif)
                                    <span class="badge bg-success-lt">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary-lt">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('admin.harga.index', $item->id_varian) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="ti ti-tags me-1"></i>Harga
                                    </a>

                                    <a href="{{ route('admin.varian.edit', $item->id_varian) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>Edit
                                    </a>

                                    <form action="{{ route('admin.varian.toggle-status', $item->id_varian) }}"
                                          method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-warning">
                                            @if ($item->status_aktif)
                                                <i class="ti ti-eye-off me-1"></i>Nonaktifkan
                                            @else
                                                <i class="ti ti-eye me-1"></i>Aktifkan
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.varian.destroy', $item->id_varian) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus varian ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10"
                                class="text-center text-secondary py-4">
                                <i class="ti ti-package-off d-block mb-2"
                                   style="font-size: 2rem;"></i>
                                Belum ada varian untuk produk ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.produk.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Produk
        </a>
    </div>
@endsection
