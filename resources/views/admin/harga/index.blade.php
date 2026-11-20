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
                    Produk: <strong>{{ $varian->produk->nama_produk }}</strong>
                </div>

                <div class="text-secondary">
                    Varian: <strong>{{ $varian->nama_varian }}</strong>
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.harga.create', $varian->id_varian) }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Harga
                </a>
            </div>
        </div>
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
                        @php
                            $aktif = (bool) $item->status_aktif;
                            $ecer  = $item->jenis_harga === 'ecer';
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="badge {{ $ecer ? 'bg-info-lt' : 'bg-purple-lt' }}">
                                    {{ $ecer ? 'Ecer' : 'Grosir' }}
                                </span>
                            </td>

                            <td class="fw-bold">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ number_format($item->minimal_pembelian, 0, ',', '.') }}
                                {{ $item->satuan_minimal ?? '' }}
                            </td>

                            <td>
                                <span class="badge {{ $aktif ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                                    {{ $aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                    <a href="{{ route('admin.harga.edit', $item->id_daftar_harga) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>
                                        Edit
                                    </a>

                                    {{-- Switch aktif / nonaktif --}}
                                    @include('admin.partials.status-switch', [
                                        'action' => route('admin.harga.toggle-status', $item->id_daftar_harga),
                                        'aktif'  => $aktif,
                                        'nama'   => 'harga',
                                    ])

                                    <form action="{{ route('admin.harga.destroy', $item->id_daftar_harga) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus harga ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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
                                <i class="ti ti-tags d-block mb-2 fs-1"></i>
                                Belum ada daftar harga untuk varian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.varian.index', $varian->id_produk) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Varian
        </a>
    </div>
@endsection
