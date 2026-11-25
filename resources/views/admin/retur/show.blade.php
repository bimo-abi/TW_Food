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

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert" aria-label="Close"></a>
        </div>
    @endif

    {{-- Seluruh aksi menggunakan transisi status yang sama dengan Flutter Mitra. --}}
    @if ($retur->status_retur === 'menunggu_persetujuan')
        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title">Keputusan Pengajuan Retur</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.retur.keputusan', $retur->id_retur) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="keputusan" value="disetujui">
                    <div class="table-responsive mb-3"><table class="table table-sm">
                        <thead><tr><th>Produk</th><th>Diminta</th><th>Jumlah Disetujui</th></tr></thead><tbody>
                        @foreach ($retur->detailRetur as $detail)
                            <tr>
                                <td>{{ $detail->detailPesanan?->nama_produk_saat_pesan ?? 'Produk' }}</td>
                                <td>{{ $detail->jumlah_diminta ?? $detail->jumlah_diretur }}</td>
                                <td><input class="form-control" type="number" min="0" max="{{ $detail->jumlah_diminta ?? $detail->jumlah_diretur }}" required name="detail_retur[{{ $loop->index }}][jumlah_disetujui]" value="{{ $detail->jumlah_diminta ?? $detail->jumlah_diretur }}"><input type="hidden" name="detail_retur[{{ $loop->index }}][id_detail_retur]" value="{{ $detail->id_detail_retur }}"></td>
                            </tr>
                        @endforeach
                        </tbody></table></div>
                    <label class="form-label">Catatan Mitra (opsional)</label><textarea class="form-control mb-3" name="catatan_mitra" rows="2"></textarea>
                    <button class="btn btn-success" type="submit" onclick="return confirm('Setujui pengajuan retur ini?')"><i class="ti ti-check me-1"></i>Setujui Retur</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.retur.keputusan', $retur->id_retur) }}">
                    @csrf @method('PATCH')<input type="hidden" name="keputusan" value="ditolak">
                    <label class="form-label">Alasan Penolakan</label><textarea class="form-control mb-2" name="alasan_penolakan" minlength="5" maxlength="2000" required rows="2"></textarea>
                    <button class="btn btn-danger" type="submit" onclick="return confirm('Tolak pengajuan retur ini?')">Tolak Retur</button>
                </form>
            </div>
        </div>
    @elseif ($retur->status_retur === 'disetujui')
        <div class="card mb-4"><div class="card-header"><h3 class="card-title">Instruksi Pengembalian Barang</h3></div><div class="card-body">
            <form method="POST" action="{{ route('admin.retur.instruksi', $retur->id_retur) }}">@csrf @method('PATCH')
                <div class="row g-3"><div class="col-md-4"><label class="form-label">Metode Pengembalian</label><select class="form-select" name="metode_pengembalian" required><option value="outlet">Ke Outlet</option><option value="kurir">Melalui Kurir</option></select></div>
                <div class="col-md-8"><label class="form-label">Instruksi untuk Pelanggan</label><textarea class="form-control" name="instruksi_pengembalian" minlength="5" maxlength="3000" rows="2" required></textarea></div></div>
                <button class="btn btn-primary mt-3" type="submit">Kirim Instruksi</button>
            </form></div></div>
    @elseif ($retur->status_retur === 'menunggu_pengembalian_barang')
        <div class="card mb-4"><div class="card-body"><h3 class="card-title">Konfirmasi Barang Diterima</h3>
            <form method="POST" action="{{ route('admin.retur.barang-diterima', $retur->id_retur) }}">@csrf @method('PATCH')
                <label class="form-label">Catatan Penerimaan (opsional)</label><textarea class="form-control mb-3" name="catatan_penerimaan" maxlength="2000" rows="2"></textarea>
                <button class="btn btn-primary" type="submit" onclick="return confirm('Konfirmasi barang retur telah diterima secara fisik?')">Konfirmasi Barang Diterima</button>
            </form></div></div>
    @elseif ($retur->status_retur === 'barang_diterima')
        <div class="card mb-4"><div class="card-header"><h3 class="card-title">Pemeriksaan Barang Retur</h3></div><div class="card-body">
            <form method="POST" action="{{ route('admin.retur.pemeriksaan', $retur->id_retur) }}">@csrf @method('PATCH')
                <div class="table-responsive"><table class="table"><thead><tr><th>Produk</th><th>Jumlah Disetujui</th><th>Jumlah Layak Dijual</th></tr></thead><tbody>
                @foreach ($retur->detailRetur as $detail)
                    <tr><td>{{ $detail->detailPesanan?->nama_produk_saat_pesan ?? 'Produk' }}</td><td>{{ $detail->jumlah_disetujui }}</td><td><input class="form-control" type="number" min="0" max="{{ $detail->jumlah_disetujui }}" required name="detail_inspeksi[{{ $loop->index }}][jumlah_layak_dijual]" value="{{ $detail->jumlah_disetujui }}"><input type="hidden" name="detail_inspeksi[{{ $loop->index }}][id_detail_retur]" value="{{ $detail->id_detail_retur }}"></td></tr>
                @endforeach</tbody></table></div>
                <label class="form-label">Catatan Pemeriksaan (opsional)</label><textarea class="form-control mb-3" name="catatan_pemeriksaan" maxlength="2000" rows="2"></textarea>
                <button class="btn btn-primary" type="submit" onclick="return confirm('Simpan hasil pemeriksaan seluruh barang retur?')">Simpan Hasil Pemeriksaan</button>
            </form></div></div>
    @endif

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