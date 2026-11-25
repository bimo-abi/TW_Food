@extends('admin.layouts.app')

@section('title', 'Detail Refund - TWFood')

@section('content')
    @php
        $statusRefund = $refund->status_refund;
        $statusClass = match ($statusRefund) {
            'berhasil' => 'bg-success-lt',
            'gagal', 'perlu_diproses_ulang' => 'bg-danger-lt',
            'diproses', 'menunggu_diproses' => 'bg-warning-lt',
            default => 'bg-azure-lt',
        };
        $percobaanRefund = $refund->percobaanRefund->sortByDesc('dimulai_pada');
    @endphp

    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="mb-1">Detail Refund #{{ $refund->id_refund }}</h1>
            <p class="text-secondary mb-0">Ringkasan pengembalian dana, pembayaran sumber, dan riwayat percobaan.</p>
        </div>
        <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-secondary">Kembali ke Refund</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Periksa kembali data yang dimasukkan:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-secondary small mb-1">Nominal Refund</div>
                <div class="h2 mb-0">Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-secondary small mb-1">Status Refund</div>
                <div><span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $statusRefund)) }}</span></div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-secondary small mb-1">Jumlah Percobaan</div>
                <div class="h2 mb-0">{{ $percobaanRefund->count() }}</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-secondary small mb-1">Jenis Refund</div>
                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $refund->jenis_refund ?? '-')) }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Informasi Pesanan dan Retur</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-secondary">Nomor Pesanan</dt><dd class="col-sm-7">{{ $refund->pesanan?->nomor_pesanan ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Nomor Retur</dt><dd class="col-sm-7">{{ $refund->retur?->nomor_retur ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Pelanggan</dt><dd class="col-sm-7">{{ $refund->pesanan?->pengguna?->nama ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Alasan Refund</dt><dd class="col-sm-7">{{ $refund->alasan ?: '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Disetujui Pada</dt><dd class="col-sm-7">{{ $refund->disetujui_pada?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Pembayaran Sumber</h3></div>
                <div class="card-body">
                    @if ($refund->pembayaran)
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary">ID Transaksi</dt><dd class="col-sm-7 text-break">{{ $refund->pembayaran->id_transaksi ?: '-' }}</dd>
                            <dt class="col-sm-5 text-secondary">Metode</dt><dd class="col-sm-7">{{ $refund->pembayaran->metode_pembayaran ?: '-' }}</dd>
                            <dt class="col-sm-5 text-secondary">Nominal Dibayar</dt><dd class="col-sm-7">Rp{{ number_format((int) $refund->pembayaran->jumlah_pembayaran, 0, ',', '.') }}</dd>
                            <dt class="col-sm-5 text-secondary">Status Pembayaran</dt><dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $refund->pembayaran->status_pembayaran ?? '-')) }}</dd>
                        </dl>
                    @else
                        <p class="text-secondary mb-0">Data pembayaran sumber tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Riwayat Percobaan Refund</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Waktu</th><th>Penyedia</th><th>Nominal</th><th>Status</th><th>Referensi</th><th>Catatan</th><th>Bukti</th></tr></thead>
                <tbody>
                    @forelse ($percobaanRefund as $percobaan)
                        @php
                            $respons = is_array($percobaan->respons_gateway) ? $percobaan->respons_gateway : [];
                            $pathBukti = $respons['bukti_path'] ?? null;
                            $buktiAda = is_string($pathBukti)
                                && str_starts_with($pathBukti, 'refund-manual/')
                                && \Illuminate\Support\Facades\Storage::disk('local')->exists($pathBukti);
                            $statusPercobaanClass = match ($percobaan->status_percobaan) {
                                'berhasil' => 'bg-success-lt',
                                'gagal' => 'bg-danger-lt',
                                'pending', 'diproses' => 'bg-warning-lt',
                                default => 'bg-azure-lt',
                            };
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $percobaan->dimulai_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $percobaan->penyedia_pembayaran ?: '-' }}</td>
                            <td class="text-nowrap">Rp{{ number_format((int) $percobaan->nominal_refund, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $statusPercobaanClass }}">{{ ucfirst(str_replace('_', ' ', $percobaan->status_percobaan)) }}</span></td>
                            <td class="text-break">{{ $percobaan->referensi_gateway ?: '-' }}</td>
                            <td>{{ $respons['catatan'] ?? $percobaan->pesan_error ?? '-' }}</td>
                            <td>
                                @if ($buktiAda)
                                    <a href="{{ route('admin.refund.bukti', $percobaan->id_percobaan_refund) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Lihat / Unduh</a>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada percobaan refund.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($refund->retur && $refund->retur->status_retur === 'sedang_diperiksa' && in_array($refund->status_refund, ['menunggu_diproses', 'gagal', 'perlu_diproses_ulang'], true))
        <div class="card">
            <div class="card-header"><h3 class="card-title">Catat Hasil Refund Manual</h3></div>
            <div class="card-body">
                <p class="text-secondary">Catat hasil setelah pengembalian dana benar-benar dilakukan. Jangan tandai berhasil sebelum dana dikirim. Untuk hasil berhasil, bukti refund wajib dilampirkan.</p>
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
                                <option value="midtrans_dashboard" @selected(old('metode_refund') === 'midtrans_dashboard')>Midtrans Dashboard</option>
                                <option value="transfer_bank" @selected(old('metode_refund') === 'transfer_bank')>Transfer Bank</option>
                                <option value="lainnya" @selected(old('metode_refund') === 'lainnya')>Metode Lainnya</option>
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
                    <div class="mt-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Pastikan hasil refund sesuai kondisi transaksi sebenarnya. Lanjutkan pencatatan?')">Simpan Hasil Refund</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
