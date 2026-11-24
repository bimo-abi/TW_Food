@extends('admin.layouts.app')

@section('title', 'Detail Retur - TWFood')
@section('page-title', 'Detail Retur')

@section('content')
    @php
        $kelasRetur = fn ($st) => match ($st) {
            'menunggu_persetujuan' => 'bg-yellow-lt',
            'disetujui', 'selesai' => 'bg-success-lt',
            'menunggu_pengembalian_barang' => 'bg-orange-lt',
            'barang_diterima' => 'bg-azure-lt',
            'sedang_diperiksa' => 'bg-purple-lt',
            'ditolak' => 'bg-danger-lt',
            default => 'bg-secondary-lt',
        };
        $kelasRefund = fn ($st) => match ($st) {
            'berhasil', 'selesai', 'dicatat_manual', 'dikembalikan' => 'bg-success-lt',
            'gagal', 'ditolak' => 'bg-danger-lt',
            'menunggu', 'diproses', 'menunggu_refund' => 'bg-yellow-lt',
            default => 'bg-secondary-lt',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">
            Nomor retur: <span class="fw-semibold text-dark">{{ $retur->nomor_retur }}</span>
        </div>

        <a href="{{ route('admin.retur.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar Retur
        </a>
    </div>

    <div class="row row-cards">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-clipboard-list me-2"></i>Informasi Pengajuan</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Nomor Retur</dt>
                        <dd class="col-sm-7 mb-3 fw-semibold">{{ $retur->nomor_retur }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Nomor Pesanan</dt>
                        <dd class="col-sm-7 mb-3">{{ $retur->pesanan?->nomor_pesanan ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Pelanggan</dt>
                        <dd class="col-sm-7 mb-3">{{ $retur->pesanan?->pengguna?->nama ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Tanggal Pengajuan</dt>
                        <dd class="col-sm-7 mb-3">{{ $retur->diajukan_pada?->locale('id')->translatedFormat('j M Y, H:i') ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Status Retur</dt>
                        <dd class="col-sm-7 mb-3">
                            <span class="badge {{ $kelasRetur($retur->status_retur) }}">{{ ucwords(str_replace('_', ' ', $retur->status_retur)) }}</span>
                        </dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Alasan</dt>
                        <dd class="col-sm-7 mb-3 text-break">{{ $retur->alasan_retur }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Catatan Mitra</dt>
                        <dd class="col-sm-7 mb-3 text-break">{{ $retur->catatan_mitra ?: '-' }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Alasan Penolakan</dt>
                        <dd class="col-sm-7 mb-3 text-break">{{ $retur->alasan_penolakan ?: '-' }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal mb-3">Instruksi Pengembalian</dt>
                        <dd class="col-sm-7 mb-3 text-break">{{ $retur->instruksi_pengembalian ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-paperclip me-2"></i>Bukti Retur</h3>
                    <div class="card-actions">
                        <span class="badge bg-secondary-lt">{{ $retur->buktiRetur->count() }} berkas</span>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($retur->buktiRetur as $bukti)
                        @php
                            $previewUrl = route('admin.retur.bukti.preview', $bukti->id_bukti_retur);
                            $downloadUrl = route('admin.retur.bukti', $bukti->id_bukti_retur);
                            $isVideo = strtolower((string) $bukti->jenis_media) === 'video';
                        @endphp
                        <div class="border-bottom py-3 {{ $loop->first ? 'pt-0' : '' }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div class="min-w-0">
                                    <div class="fw-semibold">Bukti {{ $loop->iteration }} · {{ ucfirst($bukti->jenis_media) }}</div>
                                    <div class="text-secondary small">{{ $bukti->created_at?->locale('id')->translatedFormat('j M Y, H:i') ?? '-' }}</div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ $downloadUrl }}">
                                    <i class="ti ti-download me-1"></i>Unduh
                                </a>
                            </div>
                            @if ($isVideo)
                                <video
                                    class="w-100 rounded border bg-dark"
                                    style="max-height: 360px;"
                                    controls
                                    preload="metadata"
                                    playsinline
                                >
                                    <source src="{{ $previewUrl }}">
                                    Browser tidak mendukung pemutaran video.
                                </video>
                            @else
                                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="d-inline-block">
                                    <img
                                        src="{{ $previewUrl }}"
                                        alt="Bukti foto retur {{ $loop->iteration }}"
                                        class="img-fluid rounded border"
                                        style="max-height: 300px; object-fit: contain;"
                                        loading="lazy"
                                    >
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="empty py-4">
                            <div class="empty-icon"><i class="ti ti-file-off"></i></div>
                            <p class="empty-title">Tidak ada bukti retur</p>
                            <p class="empty-subtitle text-secondary">Pelanggan tidak melampirkan bukti pada pengajuan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-package me-2"></i>Rincian Barang Retur</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Jumlah Diminta</th>
                                <th class="text-center">Disetujui</th>
                                <th class="text-center">Layak Dijual</th>
                                <th class="text-end">Nominal Refund</th>
                                <th>Alasan Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($retur->detailRetur as $detail)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $detail->detailPesanan?->nama_produk_saat_pesan ?? 'Produk tidak tersedia' }}</div>
                                        @if ($detail->detailPesanan?->nama_varian_saat_pesan)
                                            <div class="text-secondary small">{{ $detail->detailPesanan->nama_varian_saat_pesan }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $detail->jumlah_diminta ?? $detail->jumlah_diretur }}</td>
                                    <td class="text-center">{{ $detail->jumlah_disetujui ?? '-' }}</td>
                                    <td class="text-center">{{ $detail->jumlah_layak_dijual ?? '-' }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format((int) $detail->nominal_refund, 0, ',', '.') }}</td>
                                    <td>{{ $detail->alasan_item ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">Rincian barang tidak tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title"><i class="ti ti-wallet me-2"></i>Pengembalian Dana</h3>
                    <a href="{{ route('admin.refund.index', ['q' => $retur->nomor_retur]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ti ti-history me-1"></i>Buka Riwayat Refund
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>ID Refund</th>
                                <th class="text-end">Nominal</th>
                                <th>Status</th>
                                <th class="text-center">Percobaan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($retur->refund as $refund)
                                <tr>
                                    <td class="fw-semibold">#{{ $refund->id_refund }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</td>
                                    <td><span class="badge {{ $kelasRefund($refund->status_refund) }}">{{ ucwords(str_replace('_', ' ', $refund->status_refund)) }}</span></td>
                                    <td class="text-center">{{ $refund->percobaanRefund->count() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.refund.show', $refund->id_refund) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye me-1"></i>Detail Refund
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Refund belum disiapkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection