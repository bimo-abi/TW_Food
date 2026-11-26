@extends('admin.layouts.app')

@section('title', 'Pengaturan Mitra - Admin TWFood')
@section('page-title', 'Pengaturan Mitra')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Admin/pengaturan-mitra.css') }}">
@endpush

@section('content')
    <div class="pm">

        <div class="pm-head">
            <p class="pm-lead">Atur ketentuan pembayaran uang muka (DP) dan preferensi notifikasi.</p>
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

            {{-- ===== Pengaturan Pembayaran DP ===== --}}
            <section class="pm-card pm-dp">
                <header class="pm-card-head">
                    <span class="pm-icon pm-icon--dp"><i class="ti ti-wallet"></i></span>
                    <h3 class="pm-title">Pengaturan Pembayaran DP</h3>
                </header>

                <div class="pm-body">
                    <div class="pm-toggle">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="dp_aktif" id="dp_aktif" value="1" class="form-check-input"
                                @checked(old('dp_aktif', $pengaturan->dp_aktif))>
                        </label>

                        <div>
                            <label for="dp_aktif" class="pm-toggle-title">
                                Aktifkan pembayaran DP
                                <span class="pm-state"></span>
                            </label>
                            <div class="pm-toggle-hint">
                                Jika dinonaktifkan, pelanggan tidak dapat memilih
                                pembayaran DP berdasarkan pengaturan ini.
                            </div>
                        </div>
                    </div>

                    <div class="pm-fields">
                        <div class="pm-field">
                            <label for="minimal_nominal_dp" class="form-label">
                                Minimum Nominal Pesanan untuk DP
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="minimal_nominal_dp" id="minimal_nominal_dp"
                                    value="{{ old('minimal_nominal_dp', $pengaturan->minimal_nominal_dp) }}" min="1"
                                    max="4294967295" step="1" required placeholder="Contoh: 500000"
                                    class="form-control @error('minimal_nominal_dp') is-invalid @enderror">
                            </div>

                            @error('minimal_nominal_dp')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-hint">
                                DP dapat dipilih jika subtotal produk setelah diskon
                                mencapai batas ini. Ongkos kirim tidak dihitung.
                            </div>
                        </div>

                        <div class="pm-field">
                            <label class="form-label">Persentase DP</label>

                            <div class="pm-fixed" aria-readonly="true">
                                <i class="ti ti-lock"></i>
                                50%
                                <span class="pm-fixed-tag">Tetap</span>
                            </div>

                            <div class="form-hint">
                                Persentase DP ditetapkan sebesar 50% sesuai PRD TWFOOD
                                dan tidak dapat diubah melalui pengaturan.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== Pengaturan Notifikasi ===== --}}
            <section class="pm-card">
                <header class="pm-card-head">
                    <span class="pm-icon pm-icon--notif"><i class="ti ti-bell"></i></span>
                    <h3 class="pm-title">Pengaturan Notifikasi</h3>
                </header>

                <div class="pm-body">
                    <div class="pm-toggle">
                        <label class="form-check form-switch">
                            <input type="checkbox" name="notifikasi_aktif" id="notifikasi_aktif" value="1"
                                class="form-check-input"
                                @checked(old('notifikasi_aktif', $pengaturan->notifikasi_aktif))>
                        </label>

                        <div>
                            <label for="notifikasi_aktif" class="pm-toggle-title">
                                Aktifkan notifikasi
                                <span class="pm-state"></span>
                            </label>
                            <div class="pm-toggle-hint">
                                Pengaturan ini menyimpan preferensi notifikasi Mitra.
                                Pengiriman notifikasi belum otomatis aktif hanya
                                dengan mencentang pilihan ini.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="pm-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>

    </div>
@endsection