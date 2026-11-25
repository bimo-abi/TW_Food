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

    <div class="refund-detail-page">
        <style>
            .refund-detail-page { width: 100%; min-width: 0; }
            .refund-detail-page .refund-heading {
                display: flex; align-items: center; justify-content: space-between;
                flex-wrap: wrap; gap: 1rem; margin: 0 0 1.25rem;
                text-align: left;
            }
            .refund-detail-page .refund-heading h1 {
                margin: 0 0 .3rem; font-size: 1.5rem; font-weight: 700;
                line-height: 1.3; text-align: left;
            }
            .refund-detail-page .refund-heading p { margin: 0; }
            .refund-detail-page .refund-summary .card { height: 100%; }
            .refund-detail-page .refund-summary .card-body { padding: 1.1rem 1.2rem; }
            .refund-detail-page .summary-label {
                color: var(--tblr-secondary, #667382); font-size: .82rem; margin-bottom: .45rem;
            }
            .refund-detail-page .summary-value {
                font-size: 1.35rem; line-height: 1.35; font-weight: 700;
                overflow-wrap: anywhere;
            }
            .refund-detail-page .refund-info-card { height: 100%; }
            .refund-detail-page .refund-info-card .card-body { padding: 1.1rem 1.25rem; }
            .refund-detail-page .refund-info-list {
                display: grid; grid-template-columns: minmax(125px,  .8fr) minmax(0, 1.4fr);
                gap: .8rem 1rem; margin: 0;
            }
            .refund-detail-page .refund-info-list dt {
                color: var(--tblr-secondary, #667382); font-weight: 500;
                overflow-wrap: anywhere;
            }
            .refund-detail-page .refund-info-list dd {
                margin: 0; font-weight: 500; min-width: 0; overflow-wrap: anywhere;
            }
            .refund-detail-page .card-header { min-height: 3.4rem; padding: .9rem 1.2rem; }
            .refund-detail-page .card-title { font-size: 1rem; font-weight: 650; }
            .refund-detail-page .refund-table { min-width: 900px; margin: 0; }
            .refund-detail-page .refund-table th {
                white-space: nowrap; font-size: .72rem; letter-spacing: .035em;
                color: var(--tblr-secondary, #667382); text-transform: uppercase;
            }
            .refund-detail-page .refund-table td { vertical-align: middle; }
            .refund-detail-page .refund-note { min-width: 170px; max-width: 300px; white-space: normal; overflow-wrap: anywhere; }
            .refund-detail-page .refund-form-intro { max-width: 850px; }
            .refund-detail-page .refund-form-grid { align-items: start; }
            .refund-detail-page .refund-form-grid .form-label { font-weight: 600; }
            .refund-detail-page .refund-form-actions {
                display: flex; justify-content: flex-end; border-top: 1px solid var(--tblr-border-color, #e6e8eb);
                padding-top: 1rem; margin-top: 1.25rem;
            }
            .refund-detail-page [hidden] { display: none !important; }
            @media (max-width: 767.98px) {
                .refund-detail-page .refund-heading { align-items: flex-start; }
                .refund-detail-page .refund-heading .btn { width: 100%; }
                .refund-detail-page .refund-info-list {
                    grid-template-columns: minmax(0, 1fr); gap: .25rem;
                }
                .refund-detail-page .refund-info-list dd { margin-bottom: .65rem; }
                .refund-detail-page .refund-info-list dt { margin-top: .25rem; }
                .refund-detail-page .refund-form-actions .btn { width: 100%; }
            }
        </style>

        <div class="refund-heading">
            <div>
                <h1>Detail Refund #{{ $refund->id_refund }}</h1>
                <p class="text-secondary">Ringkasan pengembalian dana, pembayaran sumber, dan riwayat percobaan.</p>
            </div>
            <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali ke Refund
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Periksa kembali data yang dimasukkan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-3 refund-summary">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="summary-label">Nominal Refund</div>
                        <div class="summary-value">Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="summary-label">Status Refund</div>
                        <div class="pt-1"><span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $statusRefund)) }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="summary-label">Jumlah Percobaan</div>
                        <div class="summary-value">{{ $percobaanRefund->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="summary-label">Jenis Refund</div>
                        <div class="summary-value fs-3">{{ ucfirst(str_replace('_', ' ', $refund->jenis_refund ?? '-')) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-6">
                <div class="card refund-info-card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Pesanan dan Retur</h3>
                    </div>
                    <div class="card-body">
                        <dl class="refund-info-list">
                            <dt>Nomor Pesanan</dt>
                            <dd>{{ $refund->pesanan?->nomor_pesanan ?? '-' }}</dd>
                            <dt>Nomor Retur</dt>
                            <dd>{{ $refund->retur?->nomor_retur ?? '-' }}</dd>
                            <dt>Pelanggan</dt>
                            <dd>{{ $refund->pesanan?->pengguna?->nama ?? '-' }}</dd>
                            <dt>Alasan Refund</dt>
                            <dd>{{ $refund->alasan ?: '-' }}</dd>
                            <dt>Disetujui Pada</dt>
                            <dd>{{ $refund->disetujui_pada?->format('d/m/Y H:i') ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="card refund-info-card">
                    <div class="card-header">
                        <h3 class="card-title">Pembayaran Sumber</h3>
                    </div>
                    <div class="card-body">
                        @if ($refund->pembayaran)
                            <dl class="refund-info-list">
                                <dt>ID Transaksi</dt>
                                <dd>{{ $refund->pembayaran->id_transaksi ?: '-' }}</dd>
                                <dt>Metode</dt>
                                <dd>{{ $refund->pembayaran->metode_pembayaran ?: '-' }}</dd>
                                <dt>Nominal Dibayar</dt>
                                <dd>Rp{{ number_format((int) $refund->pembayaran->jumlah_pembayaran, 0, ',', '.') }}</dd>
                                <dt>Status Pembayaran</dt>
                                <dd>{{ ucfirst(str_replace('_', ' ', $refund->pembayaran->status_pembayaran ?? '-')) }}</dd>
                            </dl>
                        @else
                            <p class="text-secondary mb-0">Data pembayaran sumber tidak tersedia.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Riwayat Percobaan Refund</h3>
                <div class="ms-auto text-secondary small">{{ $percobaanRefund->count() }} percobaan</div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table refund-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Penyedia</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Referensi</th>
                            <th>Catatan</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
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
                                <td class="refund-note">{{ $respons['catatan'] ?? $percobaan->pesan_error ?? '-' }}</td>
                                <td>
                                    @if ($buktiAda)
                                        <a href="{{ route('admin.refund.bukti', $percobaan->id_percobaan_refund) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Lihat / Unduh</a>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">Belum ada percobaan refund.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($refund->retur && $refund->retur->status_retur === 'sedang_diperiksa' && in_array($refund->status_refund, ['menunggu_diproses', 'gagal', 'perlu_diproses_ulang'], true))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Catat Hasil Refund Manual</h3>
                </div>
                <div class="card-body">
                    <p class="text-secondary refund-form-intro">
                        Catat hasil setelah pengembalian dana benar-benar dilakukan. Jangan tandai berhasil sebelum dana dikirim.
                        Untuk hasil berhasil, bukti refund wajib dilampirkan.
                    </p>
                    <form method="POST" action="{{ route('admin.refund.manual', $refund->id_refund) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 refund-form-grid">
                            <div class="col-12 col-md-6 col-xl-4">
                                <label for="hasil" class="form-label">Hasil</label>
                                <select id="hasil" name="hasil" class="form-select" required>
                                    <option value="berhasil" @selected(old('hasil', 'berhasil') === 'berhasil')>Berhasil</option>
                                    <option value="gagal" @selected(old('hasil') === 'gagal')>Gagal</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label for="metode_refund" class="form-label">Metode Refund</label>
                                <select id="metode_refund" name="metode_refund" class="form-select" required>
                                    <option value="midtrans_dashboard" @selected(old('metode_refund', 'midtrans_dashboard') === 'midtrans_dashboard')>Midtrans Dashboard</option>
                                    <option value="transfer_bank" @selected(old('metode_refund') === 'transfer_bank')>Transfer Bank</option>
                                    <option value="lainnya" @selected(old('metode_refund') === 'lainnya')>Metode Lainnya</option>
                                </select>
                            </div>
                            <div class="col-12 col-xl-4" id="referensi-wrapper">
                                <label for="referensi_transaksi" class="form-label">Referensi Transaksi</label>
                                <input id="referensi_transaksi" name="referensi_transaksi" class="form-control" maxlength="100" value="{{ old('referensi_transaksi') }}">
                            </div>
                            <div class="col-12 col-md-6" id="bukti-wrapper">
                                <label for="bukti_refund" class="form-label">Bukti Refund (JPG/PNG/PDF, maks. 10 MB)</label>
                                <input id="bukti_refund" type="file" name="bukti_refund" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="col-12 col-md-6" id="catatan-wrapper">
                                <label for="catatan" class="form-label">Catatan Kegagalan</label>
                                <textarea id="catatan" name="catatan" class="form-control" rows="3" maxlength="2000">{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                        <div class="refund-form-actions">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Pastikan hasil refund sesuai kondisi transaksi sebenarnya. Lanjutkan pencatatan?')">
                                Simpan Hasil Refund
                            </button>
                        </div>
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
