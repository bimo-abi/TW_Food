@extends('admin.layouts.app')

@section('title', 'Kategori Konten')
@section('page-title', 'Kategori Konten')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">Kategori Konten</h2>
            <p class="text-secondary mb-0">
                Kelola kategori untuk Konten Beranda TWFOOD.
            </p>
        </div>

        <a href="{{ route('admin.kategori-konten.create') }}"
           class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>
            Tambah Kategori
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Daftar Kategori</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3" style="width: 70px;">No.</th>
                            <th>Nama Kategori</th>
                            <th>Slug</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th class="text-center" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kategori as $item)
                            <tr>
                                <td class="px-3">
                                    {{ $kategori->firstItem() + $loop->index }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $item->nama_kategori }}
                                </td>

                                <td>
                                    <code>{{ $item->slug }}</code>
                                </td>

                                <td>
                                    {{ $item->deskripsi ?: '-' }}
                                </td>

                                <td>
                                    @if ($item->status_aktif)
                                        <span class="badge bg-success-subtle text-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.kategori-konten.edit', $item->id_kategori) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.kategori-konten.destroy', $item->id_kategori) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?');"
                                        >
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
                                <td colspan="6" class="text-center py-5">
                                    <i class="ti ti-folder-off fs-1 text-secondary"></i>
                                    <p class="text-secondary mb-0 mt-2">
                                        Belum ada kategori konten.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($kategori->hasPages())
            <div class="card-footer bg-white">
                {{ $kategori->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
