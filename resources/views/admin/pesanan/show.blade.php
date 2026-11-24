@extends('admin.layouts.app')

@section('title', 'Detail Pesanan - Admin TWFood')
@section('page-title', 'Detail Pesanan')

@section('content')
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">
            Nomor pesanan:
            <strong class="text-dark">{{ $pesanan->nomor_pesanan }}</strong>
        </div>

        <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => $pesanan->jenis_pemenuhan ?? 'reguler']) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>
            Kembali
        </a>
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

                    <div class="mb-3">
                        <div class="text-secondary small">Tipe Pesanan</div>
                        <div>
                            @if ($pesanan->jenis_pemenuhan === 'pre_order')
                                <span class="badge bg-purple-lt">Pre-order</span>
                            @else
                                <span class="badge bg-blue-lt">Reguler</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-secondary small">Metode Pemenuhan</div>
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
                            'diterima' => 'bg-success-lt',
                            'dibatalkan' => 'bg-danger-lt',
                            'sedang_diproses',
                            'siap_diambil',
                            'siap_dikirim',
                            'dalam_pengiriman' => 'bg-info-lt',
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
                        @php
                            $kelasBayar = match ($pesanan->status_pembayaran) {
                                'lunas' => 'bg-success-lt',
                                'dp_dibayar', 'sebagian_dibayar' => 'bg-orange-lt',
                                'gagal', 'kadaluarsa', 'dibatalkan' => 'bg-danger-lt',
                                'dikembalikan' => 'bg-secondary-lt',
                                default => 'bg-yellow-lt',
                            };
                        @endphp
                        <span class="badge {{ $kelasBayar }}">
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

            @error('status_pesanan')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror

            @php
                $status = $pesanan->status_pesanan;

                $statusBisaDibatalkan = in_array($status, [
                    'menunggu_pembayaran',
                    'sedang_diproses',
                    'siap_diambil',
                    'siap_dikirim',
                    'dalam_pengiriman',
                ], true);
            @endphp

            <div class="d-flex flex-wrap gap-2">

                {{-- MENUNGGU PEMBAYARAN --}}
                @if ($status === 'menunggu_pembayaran')
                    <div class="text-secondary">
                        Pesanan menunggu pembayaran pelanggan.
                    </div>

                    {{-- SEDANG DIPROSES --}}
                @elseif ($status === 'sedang_diproses')

                    @if ($pesanan->jenis_pesanan === 'pickup')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="siap_diambil" class="btn btn-primary">
                                <i class="ti ti-package me-1"></i>
                                Siap Diambil
                            </button>
                        </form>

                    @elseif ($pesanan->jenis_pesanan === 'delivery')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="siap_dikirim" class="btn btn-primary">
                                <i class="ti ti-truck me-1"></i>
                                Siap Dikirim
                            </button>
                        </form>
                    @endif

                    {{-- SIAP DIAMBIL --}}
                @elseif ($status === 'siap_diambil')
                    @if ($pesanan->status_pembayaran === 'lunas')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="diterima" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Tandai Diterima
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning w-100 mb-0">
                            Pesanan belum dapat ditandai diterima karena pembayaran belum lunas dan terverifikasi.
                        </div>
                    @endif

                    {{-- SIAP DIKIRIM --}}
                @elseif ($status === 'siap_dikirim')
                    <div class="text-secondary w-100 mb-2">
                        Pastikan informasi tracking sudah disimpan sebelum memulai pengiriman.
                    </div>

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dalam_pengiriman" class="btn btn-primary">
                            <i class="ti ti-truck-delivery me-1"></i>
                            Mulai Pengiriman
                        </button>
                    </form>

                    {{-- DALAM PENGIRIMAN --}}
                @elseif ($status === 'dalam_pengiriman')
                    @if ($pesanan->status_pembayaran === 'lunas')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="diterima" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>
                                Tandai Diterima
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning w-100 mb-0">
                            Pesanan belum dapat ditandai diterima karena pembayaran belum lunas dan terverifikasi.
                        </div>
                    @endif

                    {{-- DITERIMA --}}
                @elseif ($status === 'diterima')
                    <div class="text-success">
                        <i class="ti ti-circle-check me-1"></i>
                        Pesanan telah diterima pelanggan.
                    </div>

                    {{-- DIBATALKAN --}}
                @elseif ($status === 'dibatalkan')
                    <div class="text-danger">
                        <i class="ti ti-circle-x me-1"></i>
                        Pesanan telah dibatalkan.
                    </div>
                @endif

                {{-- PEMBATALAN OPERASIONAL --}}
                @if ($statusBisaDibatalkan)
                    <div class="w-100">
                        <hr>

                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Pastikan alasan pembatalan sudah benar.')">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="alasan_pembatalan" class="form-label">
                                    Alasan Pembatalan
                                </label>

                                <textarea name="alasan_pembatalan" id="alasan_pembatalan" class="form-control" rows="3"
                                    maxlength="1000" placeholder="Masukkan alasan pembatalan pesanan..."
                                    required>{{ old('alasan_pembatalan') }}</textarea>

                                <small class="form-hint">
                                    Alasan pembatalan wajib diisi.
                                </small>
                            </div>

                            <button type="submit" name="status_pesanan" value="dibatalkan" class="btn btn-outline-danger">
                                <i class="ti ti-x me-1"></i>
                                Batalkan Pesanan
                            </button>
                        </form>
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
                            <a href="{{ $pesanan->tautan_pelacakan }}" target="_blank" rel="noopener noreferrer">
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

                @if ($pesanan->status_pesanan === 'siap_dikirim')
                    <hr>

                    <h4 class="mb-3">Perbarui Informasi Pengiriman</h4>

                    <form action="{{ route('admin.pesanan.update-pengiriman', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="kurir" class="form-label">Pilih Kurir</label>
                            <select name="kurir" id="kurir" class="form-select @error('kurir') is-invalid @enderror" required>
                                <option value="">Pilih kurir</option>
                                <option value="gosend" @selected(old('kurir', $pesanan->kurir) === 'gosend')>GoSend</option>
                                <option value="jnt" @selected(old('kurir', $pesanan->kurir) === 'jnt')>J&amp;T</option>
                            </select>
                            @error('kurir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="field-tautan-gosend">
                            <label for="tautan_pelacakan" class="form-label">Tautan Tracking GoSend</label>
                            <input type="url" name="tautan_pelacakan" id="tautan_pelacakan"
                                value="{{ old('tautan_pelacakan', $pesanan->tautan_pelacakan) }}"
                                class="form-control @error('tautan_pelacakan') is-invalid @enderror"
                                placeholder="https://..." maxlength="255">
                            @error('tautan_pelacakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Masukkan tautan tracking dari GoSend.</small>
                        </div>

                        <div class="mb-3" id="field-resi-jnt">
                            <label for="nomor_resi" class="form-label">Nomor Resi J&amp;T</label>
                            <input type="text" name="nomor_resi" id="nomor_resi"
                                value="{{ old('nomor_resi', $pesanan->nomor_resi) }}"
                                class="form-control @error('nomor_resi') is-invalid @enderror"
                                placeholder="Masukkan nomor resi J&amp;T" maxlength="100">
                            @error('nomor_resi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-hint">Masukkan nomor resi/AWB dari J&amp;T.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Informasi Pengiriman
                        </button>
                    </form>
                @else
                    <div class="text-secondary small">
                        Informasi tracking hanya dapat diperbarui saat pesanan berstatus Siap Dikirim.
                    </div>
                @endif
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
                <i class="ti ti-package-off d-block mb-2" style="font-size: 2rem;"></i>
                Belum ada detail produk pada pesanan ini.
            </div>
        @endforelse
    </div>

    {{-- ALAMAT PENGIRIMAN: gunakan snapshot saat pesanan dibuat --}}
    @if ($pesanan->jenis_pesanan === 'delivery')
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-map-pin me-2"></i>
                    Alamat Pengiriman Saat Pemesanan
                </h3>
            </div>

            <div class="card-body">
                <div class="fw-bold">
                    {{ $pesanan->nama_penerima_saat_pesan ?? $pesanan->alamat?->nama_penerima ?? '-' }}
                </div>
                <div class="text-secondary mt-1">
                    {{ $pesanan->nomor_telepon_saat_pesan ?? $pesanan->alamat?->nomor_telepon ?? '-' }}
                </div>
                <div class="mt-2">
                    {{ $pesanan->alamat_lengkap_saat_pesan ?? $pesanan->alamat?->alamat_lengkap ?? '-' }}
                    @if ($pesanan->desa_kelurahan_saat_pesan ?? false)
                        , {{ $pesanan->desa_kelurahan_saat_pesan }}
                    @endif
                    @if ($pesanan->kecamatan_saat_pesan ?? false)
                        , {{ $pesanan->kecamatan_saat_pesan }}
                    @endif
                    @if ($pesanan->kabupaten_kota_saat_pesan ?? $pesanan->kota_saat_pesan ?? false)
                        , {{ $pesanan->kabupaten_kota_saat_pesan ?? $pesanan->kota_saat_pesan }}
                    @endif
                    @if ($pesanan->provinsi_saat_pesan ?? false)
                        , {{ $pesanan->provinsi_saat_pesan }}
                    @endif
                    @if ($pesanan->kode_pos_saat_pesan ?? false)
                        {{ $pesanan->kode_pos_saat_pesan }}
                    @endif
                </div>
                <small class="text-secondary d-block mt-2">
                    Alamat ditampilkan berdasarkan data saat pesanan dibuat agar perubahan alamat pelanggan tidak mengubah riwayat transaksi.
                </small>
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

    {{-- INFORMASI DP BERDASARKAN SNAPSHOT PESANAN --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-wallet me-2"></i>
                Ketentuan Pembayaran Pesanan
            </h3>
        </div>
        <div class="card-body">
            @if ($pesanan->menggunakan_dp)
                <div class="row mb-2">
                    <div class="col text-secondary">Skema Pembayaran</div>
                    <div class="col-auto fw-semibold">DP {{ number_format((float) ($pesanan->persentase_dp ?? 50), 0) }}%</div>
                </div>
                <div class="row mb-2">
                    <div class="col text-secondary">Dasar Perhitungan DP</div>
                    <div class="col-auto">Rp {{ number_format($pesanan->dasar_perhitungan_dp ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col text-secondary">Nominal DP Saat Pemesanan</div>
                    <div class="col-auto fw-bold">Rp {{ number_format($pesanan->nominal_dp ?? 0, 0, ',', '.') }}</div>
                </div>
            @else
                <div class="text-secondary">
                    Pesanan ini menggunakan pembayaran penuh dan tidak menggunakan skema DP.
                </div>
            @endif
            <small class="text-secondary d-block mt-2">
                Informasi ini merupakan snapshot transaksi saat pesanan dibuat, bukan nilai pengaturan DP saat ini.
            </small>
        </div>
    </div>

    {{-- RIWAYAT PEMBAYARAN --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-credit-card me-2"></i>
                Riwayat Pembayaran
            </h3>
        </div>
        @forelse ($pesanan->pembayaran->sortBy('created_at') as $pembayaran)
            @php
                $kelasPembayaran = match ($pembayaran->status_pembayaran) {
                    'paid' => 'bg-success-lt',
                    'failed', 'expired' => 'bg-danger-lt',
                    'refunded' => 'bg-warning-lt',
                    default => 'bg-secondary-lt',
                };
                $labelJenisPembayaran = match ($pembayaran->jenis_pembayaran) {
                    'penuh' => 'Pembayaran Penuh',
                    'dp' => 'Pembayaran DP',
                    'pelunasan' => 'Pelunasan',
                    'refund' => 'Refund',
                    default => ucwords(str_replace('_', ' ', $pembayaran->jenis_pembayaran)),
                };
            @endphp
            <div class="card-body border-bottom">
                <div class="row g-2 align-items-start">
                    <div class="col">
                        <div class="fw-semibold">{{ $labelJenisPembayaran }}</div>
                        <div class="text-secondary small">
                            {{ $pembayaran->penyedia_pembayaran ?? '-' }}
                            @if ($pembayaran->metode_pembayaran)
                                · {{ strtoupper($pembayaran->metode_pembayaran) }}
                            @endif
                        </div>
                        <div class="text-secondary small">
                            ID Transaksi: {{ $pembayaran->id_transaksi ?: '-' }}
                        </div>
                        <div class="text-secondary small">
                            {{ $pembayaran->dibayar_pada?->format('d/m/Y H:i') ?? 'Belum ada waktu pembayaran' }}
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <div class="fw-bold">Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}</div>
                        <span class="badge {{ $kelasPembayaran }}">
                            {{ strtoupper($pembayaran->status_pembayaran) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="card-body text-center text-secondary">
                Belum ada catatan pembayaran untuk pesanan ini.
            </div>
        @endforelse
    </div>

    {{-- RIWAYAT AKTIVITAS TRANSAKSI --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-history me-2"></i>
                Riwayat Aktivitas Pesanan
            </h3>
        </div>
        @forelse ($pesanan->riwayatAktivitas->sortByDesc('created_at') as $aktivitas)
            <div class="card-body border-bottom">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold">
                            {{ ucwords(str_replace('_', ' ', $aktivitas->jenis_aktivitas)) }}
                        </div>
                        <div class="text-secondary small">
                            {{ $aktivitas->deskripsi }}
                        </div>
                        @if ($aktivitas->status_sebelum || $aktivitas->status_sesudah)
                            <div class="small mt-1">
                                {{ $aktivitas->status_sebelum ? ucwords(str_replace('_', ' ', $aktivitas->status_sebelum)) : '—' }}
                                →
                                {{ $aktivitas->status_sesudah ? ucwords(str_replace('_', ' ', $aktivitas->status_sesudah)) : '—' }}
                            </div>
                        @endif
                        <div class="text-secondary small">
                            Aktor: {{ $aktivitas->jenis_aktor }}
                            @if ($aktivitas->pengguna)
                                · {{ $aktivitas->pengguna->nama }}
                            @endif
                        </div>
                    </div>
                    <div class="text-secondary small text-nowrap">
                        {{ $aktivitas->created_at?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                </div>
            </div>
        @empty
            <div class="card-body text-center text-secondary">
                Belum ada riwayat aktivitas yang tercatat.
            </div>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const courier = document.getElementById('kurir');
            const gosend = document.getElementById('field-tautan-gosend');
            const jnt = document.getElementById('field-resi-jnt');
            const tracking = document.getElementById('tautan_pelacakan');
            const resi = document.getElementById('nomor_resi');
            if (!courier || !gosend || !jnt) return;

            function syncCourierFields() {
                const isGosend = courier.value === 'gosend';
                const isJnt = courier.value === 'jnt';
                gosend.hidden = !isGosend;
                jnt.hidden = !isJnt;
                tracking.required = isGosend;
                resi.required = isJnt;
            }
            courier.addEventListener('change', syncCourierFields);
            syncCourierFields();
        });
    </script>

@endsection