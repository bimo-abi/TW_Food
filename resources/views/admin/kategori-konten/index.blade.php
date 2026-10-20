@extends('admin.layouts.app')

@section('title', 'Kategori Konten - Admin TWFood')
@section('page-title', 'Kategori Konten')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Konten</div>
                <h2 class="page-title">Kategori Konten</h2>
                <div class="text-secondary mt-1">
                    Kelola kategori untuk Konten Beranda TWFOOD.
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.kategori-konten.create') }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Kategori
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
            <h3 class="card-title">Daftar Kategori Konten</h3>
        </div>

        @if ($kategori->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-folder-off d-block mb-3"
                   style="font-size: 3rem;"></i>

                <h3 class="mb-1">Belum Ada Kategori</h3>

                <p class="text-secondary mb-3">
                    Tambahkan kategori untuk mengelompokkan Konten Beranda.
                </p>

                <a
                    href="{{ route('admin.kategori-konten.create') }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Kategori
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Kategori</th>
                            <th>Slug</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($kategori as $item)
                            <tr>
                                <td>
                                    {{ $kategori->firstItem() + $loop->index }}
                                </td>

                                <td class="fw-bold">
                                    {{ $item->nama_kategori }}
                                </td>

                                <td>
                                    <code>{{ $item->slug }}</code>
                                </td>

                                <td style="min-width: 180px; max-width: 300px;">
                                    {{ $item->deskripsi ?: '-' }}
                                </td>

                                <td>
                                    @if ($item->status_aktif)
                                        <span class="badge bg-success-lt">
                                            <i class="ti ti-check me-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-lt">
                                            <i class="ti ti-minus me-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2">
                                        <a
                                            href="{{ route('admin.kategori-konten.edit', $item->id_kategori) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                        >
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.kategori-konten.destroy', $item->id_kategori) }}"
                                            method="POST"
                                            class="d-inline-flex flex-shrink-0"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?')"
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

            @if ($kategori->hasPages())
                <div class="card-footer">
                    {{ $kategori->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
