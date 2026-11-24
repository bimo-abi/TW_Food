@extends('admin.layouts.app')

@section('title', 'Detail Verifikasi Pelanggan - TWFood')

@section('page-title', 'Detail Verifikasi Pelanggan')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">
            Periksa data akun dan profil bisnis sebelum melakukan verifikasi.
        </div>

        <a href="{{ route('admin.verifikasi-pelanggan.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div>
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

        <div class="row row-cards">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Data Akun Pelanggan</h3>
                    </div>

                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 mb-3 text-secondary fw-normal">Nama</dt>
                            <dd class="col-sm-7 mb-3 fw-semibold">
                                {{ $pelanggan->nama ?? '-' }}
                            </dd>

                            <dt class="col-sm-5 mb-3 text-secondary fw-normal">Email</dt>
                            <dd class="col-sm-7 mb-3 fw-semibold">
                                {{ $pelanggan->email ?? '-' }}
                            </dd>

                            <dt class="col-sm-5 mb-3 text-secondary fw-normal">Jenis Pelanggan</dt>
                            <dd class="col-sm-7 mb-3 fw-semibold">
                                {{ strtoupper($pelanggan->jenis_pelanggan) }}
                            </dd>

                            <dt class="col-sm-5 mb-3 text-secondary fw-normal">Status Akun</dt>
                            <dd class="col-sm-7 mb-3 fw-semibold">
                                {{ $pelanggan->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </dd>

                            <dt class="col-sm-5 mb-3 text-secondary fw-normal">Status Verifikasi</dt>
                            <dd class="col-sm-7 mb-3 fw-semibold">
                                @switch($pelanggan->status_verifikasi)
                                    @case('menunggu_verifikasi')
                                        <span class="badge bg-yellow-lt">
                                            Menunggu Verifikasi
                                        </span>
                                        @break

                                    @case('disetujui')
                                        <span class="badge bg-green-lt">
                                            Disetujui
                                        </span>
                                        @break

                                    @case('ditolak')
                                        <span class="badge bg-red-lt">
                                            Ditolak
                                        </span>
                                        @break

                                    @default
                                        <span class="badge bg-secondary-lt">
                                            Belum Tersedia
                                        </span>
                                @endswitch
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Profil Bisnis</h3>
                    </div>

                    <div class="card-body">
                        @if ($pelanggan->profilBisnis)
                            <dl class="row mb-0">
                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Nama Bisnis</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->nama_bisnis ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Jenis Bisnis</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->jenis_bisnis ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Nama PIC</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->nama_pic ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Telepon Bisnis</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->nomor_telepon_bisnis ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Alamat Bisnis</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->alamat_bisnis ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Kota</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->kota ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Provinsi</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->provinsi ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">Kode Pos</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->kode_pos ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">NIB</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->nib ?? '-' }}
                                </dd>

                                <dt class="col-sm-5 mb-3 text-secondary fw-normal">NPWP</dt>
                                <dd class="col-sm-7 mb-3 fw-semibold">
                                    {{ $pelanggan->profilBisnis->npwp ?? '-' }}
                                </dd>
                            </dl>
                        @else
                            <div class="alert alert-warning mb-0">
                                Profil bisnis belum tersedia untuk pelanggan ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($pelanggan->status_verifikasi === 'menunggu_verifikasi')
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Keputusan Verifikasi</h3>
                </div>

                <div class="card-body">
                    <p class="text-secondary">
                        Pastikan data akun dan profil bisnis sudah diperiksa sebelum
                        menentukan keputusan.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <form
                            action="{{ route('admin.verifikasi-pelanggan.setujui', $pelanggan->id_pengguna) }}"
                            method="POST"
                            onsubmit="return confirm('Apakah kamu yakin ingin menyetujui pelanggan ini?')"
                        >
                            @csrf
                            @method('PUT')

                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i>
                                Setujui Pelanggan
                            </button>
                        </form>

                        <form
                            action="{{ route('admin.verifikasi-pelanggan.tolak', $pelanggan->id_pengguna) }}"
                            method="POST"
                            onsubmit="return confirm('Apakah kamu yakin ingin menolak pelanggan ini?')"
                        >
                            @csrf
                            @method('PUT')

                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-x me-1"></i>
                                Tolak Pelanggan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
