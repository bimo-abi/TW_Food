@extends('admin.layouts.app')

@section('title', 'Detail Refund - TWFood')

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><h1>Detail Refund #{{ $refund->id_refund }}</h1><p>Periksa transaksi sumber dan riwayat percobaan pengembalian dana.</p></div>
        <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><strong>Periksa kembali data:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="content-card h-100">
                <h3 class="h3">Informasi Refund</h3>
                <dl class="row mb-0">
                    <dt class="col-sm-5">ID Refund</dt><dd class="col-sm-7">#{{ $refund->id_refund }}</dd>
                    <dt class="col-sm-5">Jenis</dt><dd class="col-sm-7">{{ $refund->jenis_refund }}</dd>
                    <dt class="col-sm-5">Nomor Retur</dt><dd class="col-sm-7">{{ $refund->retur?->nomor_retur ?? '-' }}</dd>
                    <dt class="col-sm-5">Nomor Pesanan</dt><dd class="col-sm-7">{{ $refund->pesanan?->nomor_pesanan ?? '-' }}</dd>
                    <dt class="col-sm-5">Pelanggan</dt><dd class="col-sm-7">{{ $refund->pesanan?->pengguna?->nama ?? '-' }}</dd>
                    <dt class="col-sm-5">Nominal Refund</dt><dd class="col-sm-7 fw-bold">Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Status Refund</dt><dd class="col-sm-7">{{ str_replace('_', ' ', $refund->status_refund) }}</dd>
                    <dt class="col-sm-5">Alasan</dt><dd class="col-sm-7">{{ $refund->alasan ?: '-' }}</dd>
                    <dt class="col-sm-5">Disetujui</dt><dd class="col-sm-7">{{ $refund->disetujui_pada?->format('d/m/Y H:i') ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="content-card h-100">
                <h3 class="h3">Pembayaran Sumber</h3>
                @if ($refund->pembayaran)
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID Transaksi</dt><dd class="col-sm-7">{{ $refund->pembayaran->id_transaksi ?: '-' }}</dd>
                        <dt class="col-sm-5">Metode</dt><dd class="col-sm-7">{{ $refund->pembayaran->metode_pembayaran }}</dd>
                        <dt class="col-sm-5">Nominal Dibayar</dt><dd class="col-sm-7">Rp{{ number_format((int) $refund->pembayaran->jumlah_pembayaran, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Status Pembayaran</dt><dd class="col-sm-7">{{ $refund->pembayaran->status_pembayaran }}</dd>
                    </dl>
                @else
                    <p class="text-secondary mb-0">Data pembayaran sumber tidak tersedia.</p>
                @endif
            </div>
        </div>

        <div class="col-12">
            <div class="content-card">
                <h3 class="h3">Riwayat Percobaan Refund</h3>
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead><tr><th>Waktu</th><th>Penyedia</th><th>Nominal</th><th>Status</th><th>Referensi</th><th>Catatan</th><th>Bukti</th></tr></thead>
                        <tbody>
                            @forelse ($refund->percobaanRefund->sortBy('dimulai_pada') as $percobaan)
                                @php
                                    $respons = is_array($percobaan->respons_gateway) ? $percobaan->respons_gateway : [];
                                    $pathBukti = $respons['bukti_path'] ?? null;
                                    $buktiAda = is_string($pathBukti) && str_starts_with($pathBukti, 'refund-manual/') && \Illuminate\Support\Facades\Storage::disk('local')->exists($pathBukti);
                                @endphp
                                <tr>
                                    <td>{{ $percobaan->dimulai_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $percobaan->penyedia_pembayaran }}</td>
                                    <td>Rp{{ number_format((int) $percobaan->nominal_refund, 0, ',', '.') }}</td>
                                    <td>{{ $percobaan->status_percobaan }}</td>
                                    <td>{{ $percobaan->referensi_gateway ?: '-' }}</td>
                                    <td>{{ $respons['catatan'] ?? $percobaan->pesan_error ?? '-' }}</td>
                                    <td>
                                        @if ($buktiAda)
                                            <a href="{{ route('admin.refund.bukti', $percobaan->id_percobaan_refund) }}">Unduh</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary">Belum ada percobaan refund.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($refund->retur && $refund->retur->status_retur === 'sedang_diperiksa' && in_array($refund->status_refund, ['menunggu_diproses', 'gagal', 'perlu_diproses_ulang'], true))
            <div class="col-12">
                <div class="content-card">
                    <h3 class="h3">Catat Hasil Refund Manual</h3>
                    <p class="text-secondary">Gunakan formulir ini setelah pengembalian dana benar-benar dilakukan melalui metode yang dipilih. Jangan tandai berhasil sebelum dana dikirim.</p>
                    <form method="POST" action="{{ route('admin.refund.manual', $refund->id_refund) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="hasil" class="form-label">Hasil</label>
                                <select id="hasil" name="hasil" class="form-select" required>
                                    <option value="berhasil" @selected(old('hasil') === 'berhasil')>Berhasil</option>
                                    <option value="gagal" @selected(old('hasil') === 'gagal')>Gagal</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="metode_refund" class="form-label">Metode Refund</label>
                                <select id="metode_refund" name="metode_refund" class="form-select" required>
                                    <option value="midtrans_dashboard">Midtrans Dashboard</option>
                                    <option value="transfer_bank">Transfer Bank</option>
                                    <option value="lainnya">Metode Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="referensi-wrapper">
                                <label for="referensi_transaksi" class="form-label">Referensi Transaksi</label>
                                <input id="referensi_transaksi" name="referensi_transaksi" class="form-control" maxlength="100" value="{{ old('referensi_transaksi') }}">
                            </div>
                            <div class="col-md-6" id="bukti-wrapper">
                                <label for="bukti_refund" class="form-label">Bukti Refund (JPG/PNG/PDF, maks. 10 MB)</label>
                                <input id="bukti_refund" type="file" name="bukti_refund" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-md-6" id="catatan-wrapper">
                                <label for="catatan" class="form-label">Catatan Kegagalan</label>
                                <textarea id="catatan" name="catatan" class="form-control" rows="2" maxlength="2000">{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" onclick="return confirm('Pastikan hasil refund sesuai kondisi transaksi sebenarnya. Lanjutkan pencatatan?')">Simpan Hasil Refund</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        (() => {
            const hasil = document.getElementById('hasil');
            const buktiWrapper = document.getElementById('bukti-wrapper');
            const referensiWrapper = document.getElementById('referensi-wrapper');
            const catatanWrapper = document.getElementById('catatan-wrapper');
            const bukti = document.getElementById('bukti_refund');
            const referensi = document.getElementById('referensi_transaksi');
            const catatan = document.getElementById('catatan');

            if (!hasil) return;

            const sinkronkan = () => {
                const berhasil = hasil.value === 'berhasil';
                buktiWrapper.hidden = !berhasil;
                referensiWrapper.hidden = !berhasil;
                catatanWrapper.hidden = berhasil;
                bukti.required = berhasil;
                referensi.required = berhasil;
                catatan.required = !berhasil;
            };

            hasil.addEventListener('change', sinkronkan);
            sinkronkan();
        })();
    </script>
@endsection
