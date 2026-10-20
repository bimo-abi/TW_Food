@extends('admin.layouts.app')

@section('title', 'Konten Beranda - Admin TWFood')
@section('page-title', 'Konten Beranda')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Konten</div>
                <h2 class="page-title">Konten Beranda</h2>
                <div class="text-secondary mt-1">
                    Kelola informasi yang ditampilkan pada halaman Home TWFOOD.
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.konten-beranda.create') }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Konten
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
            <h3 class="card-title">Daftar Konten Beranda</h3>
        </div>

        @if ($konten->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-news d-block mb-3"
                   style="font-size: 3rem;"></i>

                <h3 class="mb-1">Belum Ada Konten Beranda</h3>

                <p class="text-secondary mb-3">
                    Tambahkan informasi yang akan ditampilkan pada halaman Home.
                </p>

                <a
                    href="{{ route('admin.konten-beranda.create') }}"
                    class="btn btn-primary"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Konten
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Kategori Konten</th>
                            <th>Status</th>
                            <th>Urutan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($konten as $item)
                            <tr>
                                <td>
                                    {{ $konten->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    @if ($item->gambar)
                                        <img
                                            src="{{ asset('storage/' . $item->gambar) }}"
                                            alt="{{ $item->judul }}"
                                            width="80"
                                            height="60"
                                            loading="lazy"
                                            style="object-fit: cover; border-radius: 6px;"
                                        >
                                    @else
                                        <div class="text-secondary">
                                            <i class="ti ti-photo me-1"></i>
                                            Tidak ada gambar
                                        </div>
                                    @endif
                                </td>

                                <td style="min-width: 200px; max-width: 300px;">
                                    <div class="fw-bold">
                                        {{ $item->judul }}
                                    </div>

                                    <div class="text-secondary small mt-1">
                                        {{ \Illuminate\Support\Str::limit($item->deskripsi ?? '', 60) }}
                                    </div>
                                </td>

                                <td>
                                    @switch($item->jenis_konten)
                                        @case('informasi_produk')
                                            Informasi Produk
                                            @break

                                        @case('informasi_bahan')
                                            Informasi Bahan
                                            @break

                                        @case('informasi_merek')
                                            Informasi Merek
                                            @break

                                        @case('acara')
                                            Acara
                                            @break

                                        @default
                                            {{ $item->kategori?->nama_kategori ?? 'Kategori tidak tersedia' }}
                                    @endswitch
                                </td>

                                <td>
                                    @if ($item->status_aktif)
                                        <span class="badge bg-success-lt">
                                            <i class="ti ti-check me-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-lt">
                                            <i class="ti ti-x me-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td>{{ $item->urutan }}</td>

                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2">
                                        <a
                                            href="{{ route('admin.konten-beranda.edit', $item->id_konten) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                        >
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.konten-beranda.destroy', $item->id_konten) }}"
                                            method="POST"
                                            class="d-inline-flex flex-shrink-0"
                                            onsubmit="return confirm('Yakin ingin menghapus konten ini?')"
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

            <div class="card-footer">
                {{ $konten->links() }}
            </div>
        @endif
    </div>
@endsection
