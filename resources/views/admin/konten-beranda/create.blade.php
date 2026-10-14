@extends('admin.layouts.app')

@section('title', 'Tambah Konten Beranda - Admin TWFood')
@section('page-title', 'Tambah Konten Beranda')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Konten</div>
                <h2 class="page-title">Tambah Konten Beranda</h2>
                <div class="text-secondary mt-1">
                    Tambahkan informasi yang akan ditampilkan pada halaman Home TWFood.
                </div>
            </div>

            <div class="col-auto">
                <a
                    href="{{ route('admin.konten-beranda.index') }}"
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
                Periksa kembali data berikut:
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

    <form
        action="{{ route('admin.konten-beranda.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Konten</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="judul" class="form-label required">
                            Judul Konten
                        </label>

                        <input
                            type="text"
                            id="judul"
                            name="judul"
                            value="{{ old('judul') }}"
                            maxlength="150"
                            placeholder="Contoh: Mengenal Bahan Kecap TWFOOD"
                            required
                            class="form-control @error('judul') is-invalid @enderror"
                        >

                        @error('judul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="deskripsi" class="form-label">
                            Deskripsi
                        </label>

                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            rows="5"
                            placeholder="Tuliskan informasi yang ingin disampaikan."
                            class="form-control @error('deskripsi') is-invalid @enderror"
                        >{{ old('deskripsi') }}</textarea>

                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="gambar" class="form-label">
                            Gambar Konten
                        </label>

                        <input
                            type="file"
                            id="gambar"
                            name="gambar"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="form-control @error('gambar') is-invalid @enderror"
                        >

                        <div class="form-hint">
                            Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                        </div>

                        @error('gambar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="id_kategori" class="form-label required">
                            Kategori Konten
                        </label>

                        <select
                            name="id_kategori"
                            id="id_kategori"
                            required
                            class="form-select @error('id_kategori') is-invalid @enderror"
                        >
                            <option value="">-- Pilih Kategori --</option>

                            @foreach ($kategori as $item)
                                <option
                                    value="{{ $item->id_kategori }}"
                                    @selected(old('id_kategori') == $item->id_kategori)
                                >
                                    {{ $item->nama_kategori }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="urutan" class="form-label required">
                            Urutan Tampilan
                        </label>

                        <input
                            type="number"
                            id="urutan"
                            name="urutan"
                            value="{{ old('urutan', 0) }}"
                            min="0"
                            max="65535"
                            step="1"
                            required
                            class="form-control @error('urutan') is-invalid @enderror"
                        >

                        <div class="form-hint">
                            Angka lebih kecil akan ditampilkan lebih dahulu.
                        </div>

                        @error('urutan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-check form-switch">
                            <input
                                type="checkbox"
                                name="status_aktif"
                                value="1"
                                class="form-check-input"
                                @checked(old('status_aktif', true))
                            >

                            <span class="form-check-label fw-semibold">
                                Aktifkan konten
                            </span>
                        </label>

                        <div class="form-hint mt-1">
                            Jika diaktifkan, konten berstatus aktif. Penayangan
                            di Public Website tetap mengikuti periode dan filter
                            yang diterapkan pada halaman publik.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a
                    href="{{ route('admin.konten-beranda.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Konten
                </button>
            </div>
        </div>
    </form>
@endsection
