@extends('admin.layouts.app')

@section('title', 'Varian Produk - Admin TWFood')
@section('page-title', 'Varian Produk')

@section('content')
    @php
        // Format tanggal d-m-Y, '-' kalau kosong
        $tgl = fn ($tanggal) => $tanggal
            ? \Carbon\Carbon::parse($tanggal)->format('d-m-Y')
            : '-';
    @endphp

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
                <a href="{{ route('admin.varian.create', $produk->id_produk) }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Varian
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
                        @php
                            $aktif = (bool) $item->status_aktif;
                            $po    = (bool) $item->tersedia_pre_order;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="fw-bold">{{ $item->nama_varian }}</span>
                            </td>

                            <td>
                                {{ $item->berat_gram !== null
                                    ? number_format($item->berat_gram, 0, ',', '.') . ' gram'
                                    : '-' }}
                            </td>

                            <td>{{ $item->satuan_jual }}</td>

                            <td>{{ number_format($item->stok, 0, ',', '.') }}</td>

                            <td>
                                <span class="badge {{ $po ? 'bg-info-lt' : 'bg-secondary-lt' }}">
                                    {{ $po ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>

                            <td>
                                @if ($po)
                                    <div class="text-nowrap">
                                        <div>
                                            <span class="text-secondary">Mulai:</span>
                                            {{ $tgl($item->tanggal_mulai_pre_order) }}
                                        </div>
                                        <div>
                                            <span class="text-secondary">Selesai:</span>
                                            {{ $tgl($item->tanggal_selesai_pre_order) }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                @if ($po && $item->estimasi_tersedia)
                                    {{ $tgl($item->estimasi_tersedia) }}
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $aktif ? 'bg-success-lt' : 'bg-secondary-lt' }}">
                                    {{ $aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                    <a href="{{ route('admin.harga.index', $item->id_varian) }}"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="ti ti-tags me-1"></i>Harga
                                    </a>

                                    <a href="{{ route('admin.varian.edit', $item->id_varian) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit me-1"></i>Edit
                                    </a>

                                    {{-- Switch aktif / nonaktif --}}
                                    @include('admin.partials.status-switch', [
                                        'action' => route('admin.varian.toggle-status', $item->id_varian),
                                        'aktif'  => $aktif,
                                        'nama'   => 'varian',
                                    ])

                                    <form action="{{ route('admin.varian.destroy', $item->id_varian) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus varian ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">
                                <i class="ti ti-package-off d-block mb-2 fs-1"></i>
                                Belum ada varian untuk produk ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.produk.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Produk
        </a>
    </div>
@endsection
