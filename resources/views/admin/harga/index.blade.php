@extends('admin.layouts.app')

@section('title', 'Daftar Harga - Admin TWFood')
@section('page-title', 'Daftar Harga')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Manajemen Produk</div>
                <h2 class="page-title">Daftar Harga</h2>

                <div class="text-secondary mt-1">
                    Produk:
                    <strong>{{ $varian->produk->nama_produk }}</strong>
                </div>

                <div class="text-secondary">
                    Varian:
                    <strong>{{ $varian->nama_varian }}</strong>
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.harga.create', $varian->id_varian) }}"
                   class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Harga
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
            <div class="fw-bold mb-2">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Harga Varian</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Jenis Harga</th>
                        <th>Harga</th>
                        <th>Minimal Pembelian</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($harga as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if ($item->jenis_harga === 'ecer')
                                    <span class="badge bg-info-lt">Ecer</span>
                                @else
                                    <span class="badge bg-purple-lt">Grosir</span>
                                @endif
                            </td>

                            <td class="fw-bold">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ number_format($item->minimal_pembelian, 0, ',', '.') }}
                                {{ $item->satuan_minimal ?? '' }}
                            </td>

                            <td>
                                @if ($item->status_aktif)
                                    <span class="badge bg-success-lt">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Tidak Aktif</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('admin.harga.edit', $item->id_daftar_harga) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.harga.toggle-status', $item->id_daftar_harga) }}"
                                          method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-warning">
                                            @if ($item->status_aktif)
                                                <i class="ti ti-eye-off me-1"></i>
                                                Nonaktifkan
                                            @else
                                                <i class="ti ti-eye me-1"></i>
                                                Aktifkan
                                            @endif
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.harga.destroy', $item->id_daftar_harga) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus harga ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash me-1"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="text-center text-secondary py-4">
                                <i class="ti ti-tags d-block mb-2"
                                   style="font-size: 2rem;"></i>
                                Belum ada daftar harga untuk varian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.varian.index', $varian->id_produk) }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Varian
        </a>
    </div>
@endsection
