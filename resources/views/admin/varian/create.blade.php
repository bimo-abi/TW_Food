@extends('admin.layouts.app')

@section('title', 'Tambah Varian - Admin TWFood')
@section('page-title', 'Tambah Varian')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Manajemen Produk</div>
                <h2 class="page-title">Tambah Varian</h2>
                <div class="text-secondary mt-1">
                    Produk: <strong>{{ $produk->nama_produk }}</strong>
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

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.varian.store', $produk->id_produk) }}"
          method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Varian</h3>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_varian" class="form-label">
                            Nama Varian <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="nama_varian"
                               name="nama_varian"
                               value="{{ old('nama_varian') }}"
                               class="form-control @error('nama_varian') is-invalid @enderror"
                               placeholder="Contoh: 185 gram, Pedas, Original"
                               maxlength="100"
                               required>
                        @error('nama_varian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="berat_gram" class="form-label">
                            Berat (gram)
                        </label>
                        <input type="number"
                               id="berat_gram"
                               name="berat_gram"
                               value="{{ old('berat_gram') }}"
                               class="form-control @error('berat_gram') is-invalid @enderror"
                               min="0"
                               max="65535"
                               placeholder="Contoh: 185">
                        @error('berat_gram')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Opsional, maksimal 65.535 gram.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="satuan_jual" class="form-label">
                            Satuan Jual <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="satuan_jual"
                               name="satuan_jual"
                               value="{{ old('satuan_jual') }}"
                               class="form-control @error('satuan_jual') is-invalid @enderror"
                               maxlength="20"
                               placeholder="Contoh: pc, pack, kg"
                               required>
                        @error('satuan_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="stok" class="form-label">
                            Stok <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               id="stok"
                               name="stok"
                               value="{{ old('stok', 0) }}"
                               class="form-control @error('stok') is-invalid @enderror"
                               min="0"
                               max="16777215"
                               required>
                        @error('stok')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Masukkan jumlah stok awal.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Pengaturan Pre-Order</h3>
            </div>

            <div class="card-body">
                <label class="form-check">
                    <input type="checkbox"
                           id="tersedia_pre_order"
                           name="tersedia_pre_order"
                           value="1"
                           class="form-check-input"
                           {{ old('tersedia_pre_order') ? 'checked' : '' }}>
                    <span class="form-check-label">
                        Varian tersedia untuk Pre-Order
                    </span>
                </label>

                <div id="pre-order-fields" class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label for="tanggal_mulai_pre_order" class="form-label">
                            Tanggal Mulai <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_mulai_pre_order"
                               name="tanggal_mulai_pre_order"
                               value="{{ old('tanggal_mulai_pre_order') }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="form-control @error('tanggal_mulai_pre_order') is-invalid @enderror">
                        @error('tanggal_mulai_pre_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="tanggal_selesai_pre_order" class="form-label">
                            Tanggal Selesai <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               id="tanggal_selesai_pre_order"
                               name="tanggal_selesai_pre_order"
                               value="{{ old('tanggal_selesai_pre_order') }}"
                               class="form-control @error('tanggal_selesai_pre_order') is-invalid @enderror">
                        @error('tanggal_selesai_pre_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="estimasi_tersedia" class="form-label">
                            Estimasi Tersedia <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               id="estimasi_tersedia"
                               name="estimasi_tersedia"
                               value="{{ old('estimasi_tersedia') }}"
                               class="form-control @error('estimasi_tersedia') is-invalid @enderror">
                        @error('estimasi_tersedia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <small class="form-hint">
                            Tanggal wajib diisi jika Pre-Order diaktifkan.
                            Estimasi tersedia harus setelah tanggal selesai Pre-Order.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                Simpan Varian
            </button>

            <a href="{{ route('admin.varian.index', $produk->id_produk) }}"
               class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('tersedia_pre_order');
            const fields = document.getElementById('pre-order-fields');

            const tanggalMulai =
                document.getElementById('tanggal_mulai_pre_order');
            const tanggalSelesai =
                document.getElementById('tanggal_selesai_pre_order');
            const estimasi =
                document.getElementById('estimasi_tersedia');

            function updatePreOrderFields() {
                const aktif = checkbox.checked;

                fields.style.display = aktif ? '' : 'none';

                tanggalMulai.required = aktif;
                tanggalSelesai.required = aktif;
                estimasi.required = aktif;
            }

            checkbox.addEventListener('change', updatePreOrderFields);
            updatePreOrderFields();
        });
    </script>
@endsection
