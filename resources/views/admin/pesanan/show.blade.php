@extends('admin.layouts.app')

@section('title', 'Detail Pesanan - Admin TWFood')
@section('page-title', 'Detail Pesanan')

@section('content')
    {{-- HEADER --}}
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Transaksi</div>
                <h2 class="page-title">Detail Pesanan</h2>
                <div class="text-secondary mt-1">
                    Nomor pesanan:
                    <strong>{{ $pesanan->nomor_pesanan }}</strong>
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.pesanan.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI --}}
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

    {{-- INFORMASI PESANAN --}}
    <div class="row row-cards mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-user me-2"></i>
                        Informasi Pelanggan
                    </h3>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-secondary small">Nama Pelanggan</div>
                        <div class="fw-bold">
                            {{ $pesanan->pengguna->nama ?? '-' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">Email</div>
                        <div>
                            {{ $pesanan->pengguna->email ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-secondary small">Jenis Pesanan</div>
                        <div>
                            @if ($pesanan->jenis_pesanan === 'pickup')
                                <span class="badge bg-secondary-lt">Pickup</span>
                            @elseif ($pesanan->jenis_pesanan === 'delivery')
                                <span class="badge bg-info-lt">Delivery</span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-clipboard-check me-2"></i>
                        Status Pesanan
                    </h3>
                </div>

                <div class="card-body">
                    @php
                        $statusPesanan = $pesanan->status_pesanan;

                        $kelasStatus = match ($statusPesanan) {
                            'selesai',
                            'diterima',
                            'pesanan_diambil' => 'bg-success-lt',

                            'dibatalkan' => 'bg-danger-lt',

                            'sedang_diproses',
                            'pesanan_siap',
                            'menunggu_pengiriman',
                            'dalam_pengiriman',
                            'siap_diambil' => 'bg-info-lt',

                            default => 'bg-secondary-lt',
                        };
                    @endphp

                    <div class="mb-3">
                        <div class="text-secondary small mb-1">
                            Status Pesanan
                        </div>
                        <span class="badge {{ $kelasStatus }}">
                            {{ ucwords(str_replace('_', ' ', $statusPesanan)) }}
                        </span>
                    </div>

                    <div>
                        <div class="text-secondary small mb-1">
                            Status Pembayaran
                        </div>
                        <span class="badge bg-secondary-lt">
                            {{ ucwords(str_replace('_', ' ', $pesanan->status_pembayaran)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TINDAKAN PESANAN --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-refresh me-2"></i>
                Tindakan Pesanan
            </h3>
        </div>

        <div class="card-body">
            <p class="text-secondary">
                Pilih tindakan sesuai dengan kondisi pesanan saat ini.
            </p>

            <div class="d-flex flex-wrap gap-2">
                @if ($pesanan->status_pesanan === 'pesanan_diterima')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="sedang_diproses"
                                class="btn btn-primary">
                            <i class="ti ti-player-play me-1"></i>
                            Proses Pesanan
                        </button>
                    </form>

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="dibatalkan"
                                class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i>
                            Batalkan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'sedang_diproses')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="pesanan_siap"
                                class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>
                            Pesanan Siap
                        </button>
                    </form>

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="dibatalkan"
                                class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i>
                            Batalkan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'pesanan_siap')
                    @if ($pesanan->jenis_pesanan === 'delivery')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                    name="status_pesanan"
                                    value="menunggu_pengiriman"
                                    class="btn btn-primary">
                                <i class="ti ti-truck me-1"></i>
                                Menunggu Pengiriman
                            </button>
                        </form>
                    @elseif ($pesanan->jenis_pesanan === 'pickup')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                              method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                    name="status_pesanan"
                                    value="siap_diambil"
                                    class="btn btn-primary">
                                <i class="ti ti-package me-1"></i>
                                Siap Diambil
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="dibatalkan"
                                class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i>
                            Batalkan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'menunggu_pengiriman')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="dalam_pengiriman"
                                class="btn btn-primary">
                            <i class="ti ti-truck-delivery me-1"></i>
                            Mulai Pengiriman
                        </button>
                    </form>

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="dibatalkan"
                                class="btn btn-outline-danger">
                            <i class="ti ti-x me-1"></i>
                            Batalkan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'dalam_pengiriman')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="diterima"
                                class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>
                            Tandai Diterima
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'diterima')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="selesai"
                                class="btn btn-success">
                            <i class="ti ti-checks me-1"></i>
                            Selesaikan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'siap_diambil')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="pesanan_diambil"
                                class="btn btn-primary">
                            <i class="ti ti-package-export me-1"></i>
                            Tandai Pesanan Diambil
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'pesanan_diambil')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                name="status_pesanan"
                                value="selesai"
                                class="btn btn-success">
                            <i class="ti ti-checks me-1"></i>
                            Selesaikan Pesanan
                        </button>
                    </form>

                @elseif ($pesanan->status_pesanan === 'selesai')
                    <div class="text-success">
                        <i class="ti ti-circle-check me-1"></i>
                        Pesanan telah selesai.
                    </div>

                @elseif ($pesanan->status_pesanan === 'dibatalkan')
                    <div class="text-danger">
                        <i class="ti ti-circle-x me-1"></i>
                        Pesanan telah dibatalkan.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- INFORMASI PENGIRIMAN --}}
    @if ($pesanan->jenis_pesanan === 'delivery')
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-truck me-2"></i>
                    Informasi Pengiriman
                </h3>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <div class="text-secondary small">Kurir</div>
                    <div class="fw-bold">
                        @if ($pesanan->kurir === 'gosend')
                            GoSend
                        @elseif ($pesanan->kurir === 'jnt')
                            J&T
                        @else
                            -
                        @endif
                    </div>
                </div>

                @if ($pesanan->kurir === 'gosend')
                    <div class="mb-3">
                        <div class="text-secondary small mb-1">
                            Tautan Pelacakan GoSend
                        </div>

                        @if ($pesanan->tautan_pelacakan)
                            <a href="{{ $pesanan->tautan_pelacakan }}"
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="ti ti-external-link me-1"></i>
                                Lihat Tracking GoSend
                            </a>
                        @else
                            <span class="text-secondary">
                                Tautan pelacakan belum diisi.
                            </span>
                        @endif
                    </div>

                @elseif ($pesanan->kurir === 'jnt')
                    <div class="mb-3">
                        <div class="text-secondary small mb-1">
                            Nomor Resi J&T
                        </div>

                        @if ($pesanan->nomor_resi)
                            <span class="fw-bold">
                                {{ $pesanan->nomor_resi }}
                            </span>
                        @else
                            <span class="text-secondary">
                                Nomor resi belum diisi.
                            </span>
                        @endif
                    </div>
                @endif

                <hr>

                <h4 class="mb-3">Perbarui Informasi Pengiriman</h4>

                <form action="{{ route('admin.pesanan.update-pengiriman', $pesanan->id_pesanan) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    @if ($pesanan->kurir === 'gosend')
                        <div class="mb-3">
                            <label for="tautan_pelacakan" class="form-label">
                                Tautan Pelacakan GoSend
                            </label>

                            <input type="url"
                                   name="tautan_pelacakan"
                                   id="tautan_pelacakan"
                                   value="{{ old('tautan_pelacakan', $pesanan->tautan_pelacakan) }}"
                                   class="form-control @error('tautan_pelacakan') is-invalid @enderror"
                                   placeholder="https://..."
                                   maxlength="2048">

                            @error('tautan_pelacakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="form-hint">
                                Masukkan tautan tracking yang diberikan oleh GoSend.
                            </small>
                        </div>

                    @elseif ($pesanan->kurir === 'jnt')
                        <div class="mb-3">
                            <label for="nomor_resi" class="form-label">
                                Nomor Resi J&T
                            </label>

                            <input type="text"
                                   name="nomor_resi"
                                   id="nomor_resi"
                                   value="{{ old('nomor_resi', $pesanan->nomor_resi) }}"
                                   class="form-control @error('nomor_resi') is-invalid @enderror"
                                   placeholder="Masukkan nomor resi J&T"
                                   maxlength="100">

                            @error('nomor_resi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="form-hint">
                                Masukkan nomor resi/AWB yang diberikan oleh J&T.
                            </small>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Simpan Informasi Pengiriman
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- DETAIL PRODUK --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-shopping-bag me-2"></i>
                Produk dalam Pesanan
            </h3>
        </div>

        @forelse ($pesanan->detailPesanan as $detail)
            <div class="card-body border-bottom">
                <div class="row align-items-start g-3">
                    <div class="col">
                        <h4 class="mb-1">
                            {{ $detail->nama_produk_saat_pesan }}
                        </h4>

                        <div class="text-secondary">
                            Varian: {{ $detail->nama_varian_saat_pesan }}
                        </div>

                        <div class="text-secondary mt-1">
                            Harga satuan:
                            Rp {{ number_format($detail->harga_saat_pesan, 0, ',', '.') }}
                        </div>

                        <div class="text-secondary">
                            Jumlah: {{ $detail->jumlah }}
                        </div>
                    </div>

                    <div class="col-auto text-end">
                        <div class="text-secondary small">Subtotal</div>
                        <div class="fw-bold">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card-body text-center text-secondary py-4">
                <i class="ti ti-package-off d-block mb-2"
                   style="font-size: 2rem;"></i>
                Belum ada detail produk pada pesanan ini.
            </div>
        @endforelse
    </div>

    {{-- ALAMAT PENGIRIMAN --}}
    @if ($pesanan->alamat)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-map-pin me-2"></i>
                    Alamat Pengiriman
                </h3>
            </div>

            <div class="card-body">
                <div class="fw-bold">
                    {{ $pesanan->alamat->nama_penerima }}
                </div>

                <div class="text-secondary mt-1">
                    {{ $pesanan->alamat->nomor_telepon }}
                </div>

                <div class="mt-2">
                    {{ $pesanan->alamat->alamat_lengkap }},
                    {{ $pesanan->alamat->kota }},
                    {{ $pesanan->alamat->provinsi }}
                    {{ $pesanan->alamat->kode_pos }}
                </div>
            </div>
        </div>
    @endif

    {{-- TOTAL PESANAN --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-receipt me-2"></i>
                Ringkasan Pembayaran
            </h3>
        </div>

        <div class="card-body">
            <div class="row mb-2">
                <div class="col text-secondary">Subtotal Produk</div>
                <div class="col-auto text-end">
                    Rp {{ number_format($pesanan->subtotal_produk, 0, ',', '.') }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col text-secondary">Diskon</div>
                <div class="col-auto text-end">
                    Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}
                </div>
            </div>

            <hr>

            <div class="row align-items-center">
                <div class="col fw-bold">Total Pesanan</div>
                <div class="col-auto text-end">
                    <h2 class="text-primary mb-0">
                        Rp {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- KEMBALI --}}
    <div class="mt-3">
        <a href="{{ route('admin.pesanan.index') }}"
           class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali ke Daftar Pesanan
        </a>
    </div>
@endsection
