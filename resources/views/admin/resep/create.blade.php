@extends('admin.layouts.app')

@section('title', 'Tambah Resep - Admin TWFood')
@section('page-title', 'Tambah Resep')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Resep</div>
                <h2 class="page-title">Tambah Resep</h2>
                <div class="text-secondary mt-1">
                    Masukkan informasi resep yang akan ditampilkan di TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

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

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <form
        action="{{ route('admin.resep.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Resep</h3>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="judul" class="form-label required">Judul Resep</label>
                    <input
                        type="text"
                        name="judul"
                        id="judul"
                        class="form-control @error('judul') is-invalid @enderror"
                        value="{{ old('judul') }}"
                        placeholder="Contoh: Tumis Jamur Tiram"
                        maxlength="100"
                        required
                    >
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        rows="4"
                        maxlength="5000"
                        placeholder="Masukkan deskripsi resep"
                    >{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto Resep</label>
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        class="form-control @error('foto') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.webp"
                    >
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">
                        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="bahan" class="form-label">Bahan</label>
                    <textarea
                        name="bahan"
                        id="bahan"
                        class="form-control @error('bahan') is-invalid @enderror"
                        rows="6"
                        maxlength="10000"
                        placeholder="Tuliskan bahan-bahan yang diperlukan"
                    >{{ old('bahan') }}</textarea>
                    @error('bahan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="langkah_pembuatan" class="form-label">Langkah Pembuatan</label>
                    <textarea
                        name="langkah_pembuatan"
                        id="langkah_pembuatan"
                        class="form-control @error('langkah_pembuatan') is-invalid @enderror"
                        rows="8"
                        maxlength="10000"
                        placeholder="Tuliskan langkah-langkah pembuatan resep"
                    >{{ old('langkah_pembuatan') }}</textarea>
                    @error('langkah_pembuatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label for="waktu_memasak" class="form-label">Waktu Memasak (menit)</label>
                    <input
                        type="number"
                        name="waktu_memasak"
                        id="waktu_memasak"
                        class="form-control @error('waktu_memasak') is-invalid @enderror"
                        value="{{ old('waktu_memasak') }}"
                        min="1"
                        max="1440"
                        placeholder="Contoh: 30"
                    >
                    @error('waktu_memasak')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">
                        Isi dalam menit, antara 1 hingga 1.440 menit. Boleh dikosongkan.
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Resep
                </button>
            </div>
        </div>
    </form>
@endsection
