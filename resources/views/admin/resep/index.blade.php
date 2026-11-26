@extends('admin.layouts.app')

@section('title', 'Resep - Admin TWFood')
@section('page-title', 'Resep')

@push('styles')
    <style>
        .resep-thumb { width: 64px; height: 64px; border-radius: 12px; object-fit: cover; border: 1px solid #eceff3; }
        .resep-thumb-kosong {
            width: 64px; height: 64px; border-radius: 12px; background: #f3f4f7; color: #b5bac4;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .resep-deskripsi {
            max-width: 320px; color: #6b7280; font-size: 13px; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">Kelola resep dan produk yang digunakan dalam resep TWFood.</div>
        <a href="{{ route('admin.resep.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Tambah Resep
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
        @if ($resep->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-bowl d-block mb-3" style="font-size: 2.6rem; color: #F7941D;"></i>
                <h3 class="mb-1">Belum ada resep</h3>
                <p class="text-secondary mb-3">Tambahkan resep untuk mulai mengelola informasi resep TWFood.</p>
                <a href="{{ route('admin.resep.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Tambah Resep
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th>Foto</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Waktu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resep as $item)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->judul }}" class="resep-thumb">
                                    @else
                                        <span class="resep-thumb-kosong"><i class="ti ti-photo"></i></span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $item->judul }}</td>
                                <td>
                                    <div class="resep-deskripsi">
                                        {{ $item->deskripsi ? trim(html_entity_decode(strip_tags($item->deskripsi))) : '-' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($item->waktu_memasak)
                                        <span class="badge bg-azure-lt text-nowrap">
                                            <i class="ti ti-clock me-1"></i>{{ $item->waktu_memasak }} menit
                                        </span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-end gap-2">
                                        <a href="{{ route('admin.resep.produk.index', $item->id_resep) }}"
                                            class="btn btn-sm btn-outline-secondary text-nowrap">
                                            <i class="ti ti-package me-1"></i>Produk
                                        </a>
                                        <a href="{{ route('admin.resep.edit', $item->id_resep) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap">
                                            <i class="ti ti-edit me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('admin.resep.destroy', $item->id_resep) }}" method="POST"
                                            class="d-inline-flex"
                                            onsubmit="return confirm('Yakin ingin menghapus resep ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"
                                                title="Hapus" aria-label="Hapus">
                                                <i class="ti ti-trash"></i>
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