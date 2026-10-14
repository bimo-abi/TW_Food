@extends('admin.layouts.app')

@section('title', 'Resep - Admin TWFood')
@section('page-title', 'Resep')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Produk</div>
                <h2 class="page-title">Resep</h2>
                <div class="text-secondary mt-1">
                    Kelola resep dan produk yang digunakan dalam resep TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.resep.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Resep
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
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
            <h3 class="card-title">Daftar Resep</h3>
        </div>

        @if ($resep->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-bowl d-block mb-3" style="font-size: 3rem;"></i>
                <h3 class="mb-1">Belum Ada Resep</h3>
                <p class="text-secondary mb-3">
                    Tambahkan resep untuk mulai mengelola informasi resep TWFood.
                </p>
                <a href="{{ route('admin.resep.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Resep
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Waktu Memasak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($resep as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    @if ($item->foto)
                                        <img
                                            src="{{ asset('storage/' . $item->foto) }}"
                                            alt="{{ $item->judul }}"
                                            width="80"
                                            height="80"
                                            class="rounded"
                                            style="object-fit: cover;"
                                        >
                                    @else
                                        <span class="text-secondary">Tidak ada foto</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-bold">{{ $item->judul }}</div>
                                </td>

                                <td style="min-width: 180px; max-width: 300px;">
                                    {{ $item->deskripsi ? \Illuminate\Support\Str::limit($item->deskripsi, 100) : '-' }}
                                </td>

                                <td>
                                    @if ($item->waktu_memasak)
                                        <span class="badge bg-azure-lt">
                                            <i class="ti ti-clock me-1"></i>
                                            {{ $item->waktu_memasak }} menit
                                        </span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2">
                                        <a
                                            href="{{ route('admin.resep.produk.index', $item->id_resep) }}"
                                            class="btn btn-sm btn-outline-info text-nowrap"
                                        >
                                            <i class="ti ti-package me-1"></i>
                                            Produk Resep
                                        </a>

                                        <a
                                            href="{{ route('admin.resep.edit', $item->id_resep) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                        >
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.resep.destroy', $item->id_resep) }}"
                                            method="POST"
                                            class="d-inline-flex shrink-0"
                                            onsubmit="return confirm('Yakin ingin menghapus resep ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                            >
                                                <i class="ti ti-trash me-1"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
