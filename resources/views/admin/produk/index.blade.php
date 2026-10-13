@extends('admin.layouts.app')

@section('title', 'Produk - Admin TWFood')
@section('page-title', 'Produk')

@section('content')
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Manajemen</div>
                <h2 class="page-title">Produk</h2>
                <div class="text-secondary mt-1">
                    Kelola produk TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.produk.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Produk
                </a>
            </div>
        </div>
    </div>

    {{-- Pesan sukses --}}
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan error --}}
    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Error validasi --}}
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
            <h3 class="card-title">Daftar Produk</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table produk-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Foto</th>
                        <th class="produk-name">Nama Produk</th>
                        <th class="produk-description">Deskripsi</th>
                        <th>Status</th>
                        <th class="produk-action-column">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($produk as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if ($item->foto_produk)
                                    <img
                                        src="{{ asset('storage/' . $item->foto_produk) }}"
                                        alt="{{ $item->nama_produk }}"
                                        width="64"
                                        height="64"
                                        class="rounded"
                                        style="object-fit: cover;"
                                    >
                                @else
                                    <span class="text-secondary">Tidak ada foto</span>
                                @endif
                            </td>

                            <td class="produk-name">
                                <span class="fw-bold">
                                    {{ $item->nama_produk }}
                                </span>
                            </td>

                            <td class="produk-description">
                                {{ $item->deskripsi ? Str::limit($item->deskripsi, 80) : '-' }}
                            </td>

                            <td>
                                @if ($item->status_aktif)
                                    <span class="badge bg-success-lt">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Tidak Aktif</span>
                                @endif
                            </td>

                            <td class="produk-action-column">
                                <div class="produk-actions">

                                    <a
                                        href="{{ route('admin.produk.edit', $item->id_produk) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="ti ti-edit me-1"></i>
                                        Edit
                                    </a>

                                    <a
                                        href="{{ route('admin.varian.index', $item->id_produk) }}"
                                        class="btn btn-sm btn-outline-info"
                                    >
                                        <i class="ti ti-layers-intersect me-1"></i>
                                        Varian
                                    </a>

                                    <form
                                        action="{{ route('admin.produk.toggle-status', $item->id_produk) }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            @if ($item->status_aktif)
                                                <i class="ti ti-eye-off me-1"></i>
                                                Nonaktifkan
                                            @else
                                                <i class="ti ti-eye me-1"></i>
                                                Aktifkan
                                            @endif
                                        </button>
                                    </form>

                                    <form
                                        action="{{ route('admin.produk.destroy', $item->id_produk) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            <i class="ti ti-trash me-1"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                <i class="ti ti-package-off d-block mb-2"
                                   style="font-size: 2rem;"></i>
                                Belum ada produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
