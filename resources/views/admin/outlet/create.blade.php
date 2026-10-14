@extends('admin.layouts.app')

@section('title', 'Tambah Outlet - Admin TWFood')
@section('page-title', 'Tambah Outlet')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Lokasi</div>
                <h2 class="page-title">Tambah Outlet</h2>
                <div class="text-secondary mt-1">
                    Masukkan informasi outlet TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.outlet.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">
                Periksa kembali data yang dimasukkan:
            </div>

            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.outlet.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Outlet</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="nama_outlet" class="form-label required">
                            Nama Outlet
                        </label>

                        <input
                            type="text"
                            name="nama_outlet"
                            id="nama_outlet"
                            class="form-control @error('nama_outlet') is-invalid @enderror"
                            value="{{ old('nama_outlet') }}"
                            maxlength="150"
                            placeholder="Masukkan nama outlet"
                            required
                        >

                        @error('nama_outlet')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label required">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            id="alamat"
                            rows="3"
                            class="form-control @error('alamat') is-invalid @enderror"
                            placeholder="Masukkan alamat lengkap outlet"
                            required
                        >{{ old('alamat') }}</textarea>

                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nomor_telepon" class="form-label">
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            name="nomor_telepon"
                            id="nomor_telepon"
                            class="form-control @error('nomor_telepon') is-invalid @enderror"
                            value="{{ old('nomor_telepon') }}"
                            maxlength="20"
                            placeholder="Contoh: 081234567890"
                        >

                        @error('nomor_telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="foto" class="form-label">
                            Foto Outlet
                        </label>

                        <input
                            type="file"
                            name="foto"
                            id="foto"
                            class="form-control @error('foto') is-invalid @enderror"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="form-hint">
                            Format JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2 MB.
                        </small>

                        @error('foto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="latitude" class="form-label">
                            Latitude
                        </label>

                        <input
                            type="number"
                            name="latitude"
                            id="latitude"
                            class="form-control @error('latitude') is-invalid @enderror"
                            value="{{ old('latitude') }}"
                            min="-90"
                            max="90"
                            step="0.00000001"
                            placeholder="Contoh: -8.1723"
                        >

                        <small class="form-hint">
                            Rentang koordinat: -90 sampai 90.
                        </small>

                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="longitude" class="form-label">
                            Longitude
                        </label>

                        <input
                            type="number"
                            name="longitude"
                            id="longitude"
                            class="form-control @error('longitude') is-invalid @enderror"
                            value="{{ old('longitude') }}"
                            min="-180"
                            max="180"
                            step="0.00000001"
                            placeholder="Contoh: 113.7032"
                        >

                        <small class="form-hint">
                            Rentang koordinat: -180 sampai 180.
                        </small>

                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label">
                            Deskripsi Outlet
                        </label>

                        <textarea
                            name="deskripsi"
                            id="deskripsi"
                            rows="4"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan deskripsi outlet jika diperlukan"
                        >{{ old('deskripsi') }}</textarea>

                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.outlet.index') }}"
                   class="btn btn-outline-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Outlet
                </button>
            </div>
        </div>
    </form>
@endsection
