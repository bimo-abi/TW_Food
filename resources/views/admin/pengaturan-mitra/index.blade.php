@extends('admin.layouts.app')

@section('title', 'Pengaturan Mitra - Admin TWFood')
@section('page-title', 'Pengaturan Mitra')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Konfigurasi Sistem</div>
                <h2 class="page-title">Pengaturan Mitra</h2>
                <div class="text-secondary mt-1">
                    Atur ketentuan pembayaran uang muka (DP) dan preferensi notifikasi.
                </div>
            </div>
        </div>
    </div>

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

    <form action="{{ route('admin.pengaturan-mitra.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-blue-lt">
                                <i class="ti ti-wallet"></i>
                            </span>
                            <h3 class="card-title">
                                Pengaturan Pembayaran DP
                            </h3>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-check form-switch">
                                <input type="checkbox" name="dp_aktif" value="1" class="form-check-input"
                                    @checked(old('dp_aktif', $pengaturan->dp_aktif))>

                                <span class="form-check-label fw-semibold">
                                    Aktifkan pembayaran DP
                                </span>
                            </label>

                            <div class="form-hint mt-1">
                                Jika dinonaktifkan, pelanggan tidak dapat memilih
                                pembayaran DP berdasarkan pengaturan ini.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="minimal_nominal_dp">
                                    Minimum Nominal Pesanan untuk DP (Rp)
                                </label>

                                <input type="number" name="minimal_nominal_dp" id="minimal_nominal_dp"
                                    value="{{ old('minimal_nominal_dp', $pengaturan->minimal_nominal_dp) }}" min="1"
                                    max="4294967295" step="1" required
                                    class="form-control @error('minimal_nominal_dp') is-invalid @enderror">

                                @error('minimal_nominal_dp')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror   

                                <div class="form-hint">
                                    DP dapat dipilih jika subtotal produk setelah diskon
                                    mencapai batas ini. Ongkos kirim tidak dihitung.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Persentase DP
                                </label>

                                <div class="form-control bg-light" aria-readonly="true">
                                    50%
                                </div>

                                <div class="form-hint">
                                    Persentase DP ditetapkan sebesar 50% sesuai PRD TWFOOD
                                    dan tidak dapat diubah melalui pengaturan.
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-azure-lt">
                                <i class="ti ti-bell"></i>
                            </span>
                            <h3 class="card-title">
                                Pengaturan Notifikasi
                            </h3>
                        </div>
                    </div>

                    <div class="card-body">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="notifikasi_aktif" value="1" class="form-check-input"
                                @checked(old('notifikasi_aktif', $pengaturan->notifikasi_aktif))>

                            <span class="form-check-label fw-semibold">
                                Aktifkan notifikasi
                            </span>
                        </label>

                        <div class="form-hint mt-1">
                            Pengaturan ini menyimpan preferensi notifikasi Mitra.
                            Pengiriman notifikasi belum otomatis aktif hanya
                            dengan mencentang pilihan ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
@endsection
