@extends('admin.layouts.app')

@section('title', 'Verifikasi Pelanggan - TWFood')

@section('page-title', 'Verifikasi Pelanggan')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">Verifikasi Pelanggan</h2>
                <p class="text-secondary mb-0">
                    Periksa data pelanggan Toko dan Horeca yang menunggu verifikasi.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Menunggu Verifikasi</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Pelanggan</th>
                            <th>Email</th>
                            <th>Jenis Pelanggan</th>
                            <th>Nama Bisnis</th>
                            <th>Status Verifikasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($pelanggan as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $item->nama ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->email ?? '-' }}
                                </td>

                                <td>
                                    {{ strtoupper($item->jenis_pelanggan) }}
                                </td>

                                <td>
                                    {{ $item->profilBisnis?->nama_bisnis ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-yellow-lt">
                                        Menunggu Verifikasi
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a
                                        href="{{ route('admin.verifikasi-pelanggan.show', $item->id_pengguna) }}"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="ti ti-eye me-1"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
                                    Belum ada pelanggan yang menunggu verifikasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
