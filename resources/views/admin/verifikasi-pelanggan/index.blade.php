@extends('admin.layouts.app')

@section('title', 'Verifikasi Pelanggan - TWFood')

@section('page-title', 'Verifikasi Pelanggan')

@section('content')
    <div class="text-secondary mb-4">
        Periksa data pelanggan Toko dan Horeca yang menunggu verifikasi.
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
            <div class="card-actions text-secondary">
                {{ $pelanggan->count() }} pelanggan
            </div>
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
                            <td class="text-secondary">{{ $loop->iteration }}</td>

                            <td class="fw-bold">
                                {{ $item->nama ?? '-' }}
                            </td>

                            <td class="text-secondary">
                                {{ $item->email ?? '-' }}
                            </td>

                            <td>
                                <span class="badge bg-azure-lt">
                                    {{ strtoupper($item->jenis_pelanggan) }}
                                </span>
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
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    <i class="ti ti-eye me-1"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                <i class="ti ti-user-check d-block mb-2" style="font-size: 2.4rem; color: #F7941D;"></i>
                                <div class="fw-bold text-dark">Tidak ada yang menunggu verifikasi</div>
                                <div class="small">Pelanggan baru yang mendaftar akan muncul di sini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection