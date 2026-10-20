@extends('admin.layouts.app')

@section('title', 'Edit Kategori Konten - Admin TWFood')
@section('page-title', 'Edit Kategori Konten')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Konten</div>
                <h2 class="page-title">Edit Kategori Konten</h2>
                <div class="text-secondary mt-1">
                    Perbarui informasi kategori konten beranda.
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.kategori-konten.index') }}"
                    class="btn btn-outline-secondary"
                >
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

    <form
        action="{{ route('admin.kategori-konten.update', $kategori->id_kategori) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Kategori</h3>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label required">
                        Nama Kategori
                    </label>

                    <input
                        type="text"
                        name="nama_kategori"
                        id="nama_kategori"
                        class="form-control @error('nama_kategori') is-invalid @enderror"
                        value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                        maxlength="100"
                        required
                    >

                    @error('nama_kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        rows="4"
                        placeholder="Masukkan deskripsi kategori jika diperlukan"
                    >{{ old('deskripsi', $kategori->deskripsi) }}</textarea>

                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label class="form-check form-switch">
                        <input
                            type="checkbox"
                            name="status_aktif"
                            value="1"
                            class="form-check-input"
                            {{ old('status_aktif', $kategori->status_aktif) ? 'checked' : '' }}
                        >

                        <span class="form-check-label">
                            Kategori Aktif
                        </span>
                    </label>

                    <div class="form-hint">
                        Aktifkan kategori jika masih ingin digunakan.
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a
                    href="{{ route('admin.kategori-konten.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
@endsection
