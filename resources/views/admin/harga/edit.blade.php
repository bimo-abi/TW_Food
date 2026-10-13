@extends('admin.layouts.app')

@section('title', 'Edit Harga - Admin TWFood')
@section('page-title', 'Edit Harga')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Manajemen Produk</div>
                <h2 class="page-title">Edit Daftar Harga</h2>

                <div class="text-secondary mt-1">
                    Produk:
                    <strong>{{ $harga->varian->produk->nama_produk }}</strong>
                </div>

                <div class="text-secondary">
                    Varian:
                    <strong>{{ $harga->varian->nama_varian }}</strong>
                </div>
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

    <form action="{{ route('admin.harga.update', $harga->id_daftar_harga) }}"
          method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Harga</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="jenis_harga" class="form-label">
                            Jenis Harga <span class="text-danger">*</span>
                        </label>

                        <select id="jenis_harga"
                                name="jenis_harga"
                                class="form-select @error('jenis_harga') is-invalid @enderror"
                                required>
                            <option value="ecer"
                                {{ old('jenis_harga', $harga->jenis_harga) === 'ecer' ? 'selected' : '' }}>
                                Ecer
                            </option>
                            <option value="grosir"
                                {{ old('jenis_harga', $harga->jenis_harga) === 'grosir' ? 'selected' : '' }}>
                                Grosir
                            </option>
                        </select>

                        @error('jenis_harga')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="harga" class="form-label">
                            Harga (Rp) <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               id="harga"
                               name="harga"
                               value="{{ old('harga', $harga->harga) }}"
                               class="form-control @error('harga') is-invalid @enderror"
                               min="100"
                               max="16777215"
                               step="1"
                               required>

                        @error('harga')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="form-hint">
                            Harga minimal Rp100 dan maksimal Rp16.777.215.
                        </small>
                    </div>

                    <div class="col-md-6">
                        <label for="minimal_pembelian" class="form-label">
                            Minimal Pembelian <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               id="minimal_pembelian"
                               name="minimal_pembelian"
                               value="{{ old('minimal_pembelian', $harga->minimal_pembelian) }}"
                               class="form-control @error('minimal_pembelian') is-invalid @enderror"
                               min="1"
                               max="65535"
                               step="1"
                               required>

                        @error('minimal_pembelian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="satuan_minimal" class="form-label">
                            Satuan Minimal
                        </label>

                        <input type="text"
                               id="satuan_minimal"
                               name="satuan_minimal"
                               value="{{ old('satuan_minimal', $harga->satuan_minimal) }}"
                               class="form-control @error('satuan_minimal') is-invalid @enderror"
                               maxlength="20"
                               pattern="[A-Za-zÀ-ÖØ-öø-ÿ]+"
                               title="Satuan minimal hanya boleh berisi huruf."
                               placeholder="Contoh: pcs, doz, kg">

                        @error('satuan_minimal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="form-hint">
                            Opsional. Isi dengan satuan seperti pcs, doz, atau kg.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Update Harga
                </button>

                <a href="{{ route('admin.harga.index', $harga->id_varian) }}"
                   class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </form>
@endsection
