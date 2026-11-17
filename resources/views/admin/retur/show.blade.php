@extends('admin.layouts.app')

@section('title', 'Detail Retur - TWFood')
@section('page-title', 'Detail Retur')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center g-3">
            <div class="col">
                <div class="page-pretitle">Manajemen Transaksi</div>
                <h2 class="page-title">Detail Retur</h2>
                <div class="text-secondary mt-1">Nomor retur: <span class="fw-semibold">{{ $retur->nomor_retur }}</span></div>
            </div>
            <div class="col-12 col-sm-auto">
                <a href="{{ route('admin.retur.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar Retur
                </a>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-clipboard-list me-2"></i>Informasi Pengajuan</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-secondary">Nomor Retur</dt>
                        <dd class="col-sm-7 fw-semibold">{{ $retur->nomor_retur }}</dd>
                        <dt class="col-sm-5 text-secondary">Nomor Pesanan</dt>
                        <dd class="col-sm-7">{{ $retur->pesanan?->nomor_pesanan ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Pelanggan</dt>
                        <dd class="col-sm-7">{{ $retur->pesanan?->pengguna?->nama ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Tanggal Pengajuan</dt>
                        <dd class="col-sm-7">{{ $retur->diajukan_pada?->format('d/m/Y H:i') ?? '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Status Retur</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-azure-lt text-azure">{{ ucfirst(str_replace('_', ' ', $retur->status_retur)) }}</span>
                        </dd>
                        <dt class="col-sm-5 text-secondary">Alasan</dt>
                        <dd class="col-sm-7 text-break">{{ $retur->alasan_retur }}</dd>
                        <dt class="col-sm-5 text-secondary">Catatan Mitra</dt>
                        <dd class="col-sm-7 text-break">{{ $retur->catatan_mitra ?: '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Alasan Penolakan</dt>
                        <dd class="col-sm-7 text-break">{{ $retur->alasan_penolakan ?: '-' }}</dd>
                        <dt class="col-sm-5 text-secondary">Instruksi Pengembalian</dt>
                        <dd class="col-sm-7 text-break">{{ $retur->instruksi_pengembalian ?: '-' }}</dd>
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
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom py-3 {{ $loop->first ? 'pt-0' : '' }}">
                            <div class="min-w-0">
                                <div class="fw-semibold">Bukti {{ $loop->iteration }} · {{ ucfirst($bukti->jenis_media) }}</div>
                                <div class="text-secondary small">{{ $bukti->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.retur.bukti', $bukti->id_bukti_retur) }}">
                                <i class="ti ti-download me-1"></i>Unduh
                            </a>
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
                                    <td class="text-end text-nowrap">Rp{{ number_format((int) $detail->nominal_refund, 0, ',', '.') }}</td>
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
                                    <td class="text-end text-nowrap">Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $refund->status_refund)) }}</td>
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
