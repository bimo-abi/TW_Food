@extends('admin.layouts.app')

@section('title', 'Tambah Produk - Admin TWFood')
@section('page-title', 'Tambah Produk')

@section('content')
    <div class="page-header mb-3">
        <div class="page-pretitle">Manajemen</div>
        <h2 class="page-title">Tambah Produk</h2>
        <div class="text-secondary mt-1">
            Tambahkan produk baru ke katalog TWFood.
        </div>
    </div>

    {{-- Error validasi --}}
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
            <h3 class="card-title">Informasi Produk</h3>
        </div>

        <form
            action="{{ route('admin.produk.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">
                        Nama Produk <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama_produk"
                        name="nama_produk"
                        value="{{ old('nama_produk') }}"
                        class="form-control @error('nama_produk') is-invalid @enderror"
                        placeholder="Masukkan nama produk"
                        required
                    >

                    @error('nama_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">
                        Deskripsi
                    </label>

                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="5"
                        class="form-control @error('deskripsi') is-invalid @enderror"
                        placeholder="Masukkan deskripsi produk"
                    >{{ old('deskripsi') }}</textarea>

                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="foto_produk" class="form-label">
                        Foto Produk
                    </label>

                    <input
                        type="file"
                        id="foto_produk"
                        name="foto_produk"
                        accept="image/*"
                        class="form-control @error('foto_produk') is-invalid @enderror"
                    >

                    <div class="form-hint">
                        Pilih gambar produk jika tersedia.
                    </div>

                    @error('foto_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a
                    href="{{ route('admin.produk.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
@endsection
