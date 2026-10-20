@extends('admin.layouts.app')

@section('title', 'Outlet - Admin TWFood')
@section('page-title', 'Outlet')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Lokasi</div>
                <h2 class="page-title">Manajemen Outlet</h2>
                <div class="text-secondary mt-1">
                    Kelola informasi dan lokasi outlet TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.outlet.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Outlet
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
            <h3 class="card-title">Daftar Outlet</h3>
        </div>

        @if ($outlets->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-building-store d-block mb-3" style="font-size: 3rem;"></i>

                <h3 class="mb-1">Belum Ada Outlet</h3>

                <p class="text-secondary mb-3">
                    Tambahkan outlet untuk melengkapi informasi lokasi TWFood.
                </p>

                <a href="{{ route('admin.outlet.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Outlet
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Foto</th>
                            <th>Nama Outlet</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($outlets as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    @if ($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto {{ $item->nama_outlet }}" width="64"
                                            height="64" loading="lazy" style="object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div class="avatar avatar-md">
                                            <i class="ti ti-building-store"></i>
                                        </div>
                                        {{-- <small class="text-secondary">
                                            Belum ada foto
                                        </small> --}}
                                    @endif
                                </td>

                                <td>
                                    <span class="fw-bold">
                                        {{ $item->nama_outlet }}
                                    </span>
                                </td>

                                <td style="min-width: 200px; max-width: 300px;">
                                    {{ $item->alamat }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $item->nomor_telepon ?? '-' }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $item->latitude ?? '-' }}
                                </td>

                                <td class="text-nowrap">
                                    {{ $item->longitude ?? '-' }}
                                </td>

                                <td style="min-width: 180px; max-width: 280px;">
                                    {{ $item->deskripsi ?? '-' }}
                                </td>


                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-center gap-2">
                                        <a href="{{ route('admin.outlet.edit', $item->id_outlet) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap">
                                            <i class="ti ti-edit me-1"></i>
                                            Edit
                                        </a>

                                        <a href="{{ route('admin.jam-operasional.index', $item->id_outlet) }}"
                                            class="btn btn-sm btn-outline-info text-nowrap">
                                            <i class="ti ti-clock me-1"></i>
                                            Jam Operasional
                                        </a>

                                        <form action="{{ route('admin.outlet.destroy', $item->id_outlet) }}" method="POST"
                                            class="d-inline-flex shrink-0"
                                            onsubmit="return confirm('Yakin ingin menghapus outlet ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
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
