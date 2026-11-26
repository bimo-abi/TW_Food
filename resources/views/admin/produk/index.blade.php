@extends('admin.layouts.app')

@section('title', 'Produk - Admin TWFood')
@section('page-title', 'Produk')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Admin/admin-produk.css') }}">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 d-print-none">
        <div class="text-secondary">Kelola produk TWFood: foto, deskripsi, varian, dan status.</div>

        <a href="{{ route('admin.produk.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>
            Tambah Produk
        </a>
    </div>

    {{-- Pesan sukses & error --}}
    @foreach (['success' => ['success', 'check-circle'], 'error' => ['danger', 'alert-circle']] as $key => [$warna, $ikon])
        @if (session($key))
            <div class="alert alert-{{ $warna }}" role="alert">
                <i class="ti ti-{{ $ikon }} me-2"></i>
                {{ session($key) }}
            </div>
        @endif
    @endforeach

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
                        <th class="produk-col-no">No.</th>
                        <th class="produk-col-foto">Foto</th>
                        <th class="produk-name">Nama Produk</th>
                        <th class="produk-description">Deskripsi</th>
                        <th class="produk-col-status">Status</th>
                        <th class="produk-action-column">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($produk as $item)
                        @php $aktif = (bool) $item->status_aktif; @endphp

                        <tr>
                            <td class="text-secondary">{{ $loop->iteration }}</td>

                            <td>
                                @if ($item->foto_produk)
                                    <img src="{{ asset('storage/' . $item->foto_produk) }}" alt="{{ $item->nama_produk }}"
                                        class="produk-thumb">
                                @else
                                    <span class="produk-thumb produk-thumb-kosong" title="Belum ada foto">
                                        <i class="ti ti-photo-off"></i>
                                    </span>
                                @endif
                            </td>

                            <td class="produk-name">
                                <span class="produk-nama">{{ $item->nama_produk }}</span>
                            </td>

                            <td class="produk-description">
                                <span class="produk-deskripsi">
                                    {{ $item->deskripsi ? Str::limit($item->deskripsi, 140) : '-' }}
                                </span>
                            </td>

                            {{-- Status: switch (dipakai sebagai satu-satunya penanda status) --}}
                            <td class="produk-col-status">
                                @include('admin.partials.status-switch', [
                                    'action' => route('admin.produk.toggle-status', $item->id_produk),
                                    'aktif' => $aktif,
                                    'nama' => 'produk',
                                ])

                                <noscript>
                                    <button type="submit" class="btn btn-sm btn-outline-warning ms-2">
                                        Simpan
                                    </button>
                                </noscript>
                                </form>
                            </td>

                            <td class="produk-action-column">
                                <div class="produk-actions">
                                    <a href="{{ route('admin.produk.edit', $item->id_produk) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>
                                        Edit
                                    </a>

                                    <a href="{{ route('admin.varian.index', $item->id_produk) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="ti ti-layers-intersect me-1"></i>
                                        Varian
                                    </a>

                                    <form action="{{ route('admin.produk.destroy', $item->id_produk) }}" method="POST"
                                        class="m-0" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus produk" aria-label="Hapus produk">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                <i class="ti ti-package-off d-block mb-2 produk-empty-icon"></i>
                                Belum ada produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection