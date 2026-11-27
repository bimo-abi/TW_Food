@extends('admin.layouts.app')

@section('title', 'Laporan Bisnis - TWFOOD')
@section('page-title', 'Laporan Bisnis')

@section('content')
@php
    $formatRp = fn ($value) => 'Rp ' . number_format((int) $value, 0, ',', '.');
    $exportUrl = fn ($format) => route('admin.laporan.export', array_merge(['jenis' => $jenis, 'format' => $format], request()->query()));
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
        <div class="text-secondary">Laporan bisnis TWFOOD</div>
        <h2 class="mb-0">{{ $judul }}</h2>
    </div>
    <div class="btn-list">
            <a class="btn btn-outline-success" href="{{ $exportUrl('xlsx') }}"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
            <a class="btn btn-outline-danger" href="{{ $exportUrl('pdf') }}"><i class="ti ti-file-type-pdf me-1"></i>PDF</a>
            <a class="btn btn-outline-secondary" href="{{ $exportUrl('csv') }}"><i class="ti ti-file-text me-1"></i>CSV</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.laporan.index', request()->query()) }}" class="btn {{ $jenis === 'ringkasan' ? 'btn-primary' : 'btn-outline-secondary' }}">Ringkasan</a>
            @foreach ($reports as $key => $label)
                <a href="{{ route('admin.laporan.show', array_merge(['jenis' => $key], request()->query())) }}" class="btn {{ $jenis === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ $jenis === 'ringkasan' ? route('admin.laporan.index') : route('admin.laporan.show', ['jenis' => $jenis]) }}" class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="tanggal_mulai">Tanggal mulai</label>
                <input class="form-control" type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ $filters['tanggal_mulai'] }}" required>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="tanggal_selesai">Tanggal selesai</label>
                <input class="form-control" type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ $filters['tanggal_selesai'] }}" min="{{ $filters['tanggal_mulai'] }}" required>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="id_outlet">Outlet</label>
                <select class="form-select" id="id_outlet" name="id_outlet">
                    <option value="">Semua outlet</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id_outlet }}" @selected(($filters['id_outlet'] ?? '') == $outlet->id_outlet)>{{ $outlet->nama_outlet }}</option>
                    @endforeach
                </select>
            </div>
            @if ($jenis === 'penjualan')
                <div class="col-sm-6 col-lg-3"><label class="form-label">Pemenuhan</label><select class="form-select" name="jenis_pemenuhan"><option value="">Semua</option><option value="reguler" @selected(($filters['jenis_pemenuhan'] ?? '') === 'reguler')>Reguler</option><option value="pre_order" @selected(($filters['jenis_pemenuhan'] ?? '') === 'pre_order')>Pre-order</option></select></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Kanal</label><select class="form-select" name="saluran_pesanan"><option value="">Semua</option>@foreach(['online'=>'Online','toko'=>'Toko','horeca'=>'Horeca'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['saluran_pesanan'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Jenis harga</label><select class="form-select" name="jenis_harga"><option value="">Semua</option><option value="ecer" @selected(($filters['jenis_harga'] ?? '') === 'ecer')>Ecer</option><option value="grosir" @selected(($filters['jenis_harga'] ?? '') === 'grosir')>Grosir</option></select></div>
            @elseif ($jenis === 'keuangan')
                <div class="col-sm-6 col-lg-3"><label class="form-label">Jenis pembayaran</label><select class="form-select" name="jenis_pembayaran"><option value="">Semua</option>@foreach(['penuh'=>'Penuh','dp'=>'DP','pelunasan'=>'Pelunasan'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['jenis_pembayaran'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Pencatatan manual</label><select class="form-select" name="jenis_transaksi_keuangan"><option value="">Semua</option>@foreach(['pemasukan'=>'Pemasukan','pengeluaran'=>'Pengeluaran','modal'=>'Modal'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['jenis_transaksi_keuangan'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
            @elseif ($jenis === 'transaksi')
                <div class="col-sm-6 col-lg-3"><label class="form-label">Status pesanan</label><select class="form-select" name="status_pesanan"><option value="">Semua</option>@foreach(['menunggu_pembayaran','sedang_diproses','siap_diambil','siap_dikirim','dalam_pengiriman','diterima','dibatalkan'] as $v)<option value="{{ $v }}" @selected(($filters['status_pesanan'] ?? '') === $v)>{{ str($v)->replace('_',' ')->title() }}</option>@endforeach</select></div>
            @elseif ($jenis === 'retur-refund')
                <div class="col-sm-6 col-lg-3"><label class="form-label">Jenis refund</label><select class="form-select" name="jenis_refund"><option value="">Semua</option><option value="pembatalan_operasional" @selected(($filters['jenis_refund'] ?? '') === 'pembatalan_operasional')>Pembatalan operasional</option><option value="retur" @selected(($filters['jenis_refund'] ?? '') === 'retur')>Retur</option></select></div>
                <div class="col-sm-6 col-lg-3"><label class="form-label">Status refund</label><select class="form-select" name="status_refund"><option value="">Semua</option>@foreach(['belum_diajukan','menunggu_diproses','diproses','berhasil','gagal','perlu_diproses_ulang'] as $v)<option value="{{ $v }}" @selected(($filters['status_refund'] ?? '') === $v)>{{ str($v)->replace('_',' ')->title() }}</option>@endforeach</select></div>
            @elseif ($jenis === 'produksi')
                <div class="col-sm-6 col-lg-3"><label class="form-label">Status produksi</label><select class="form-select" name="status_produksi"><option value="">Semua</option>@foreach(['menunggu','diproses','selesai'] as $v)<option value="{{ $v }}" @selected(($filters['status_produksi'] ?? '') === $v)>{{ str($v)->title() }}</option>@endforeach</select></div>
            @endif
            <div class="col-sm-6 col-lg-3 d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ $jenis === 'ringkasan' ? route('admin.laporan.index') : route('admin.laporan.show', ['jenis' => $jenis]) }}">Reset</a>
                <button class="btn btn-primary flex-fill" type="submit"><i class="ti ti-filter me-1"></i>Terapkan</button>
            </div>
        </form>
        <div class="text-secondary small mt-2">Periode: {{ \Illuminate\Support\Carbon::parse($filters['tanggal_mulai'])->locale('id')->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($filters['tanggal_selesai'])->locale('id')->translatedFormat('d M Y') }}. Tanggal acuan mengikuti jenis laporan.</div>
    </div>
</div>

@if ($jenis === 'ringkasan' || isset($summary))
    <div class="row row-cards mb-3">
        @foreach ([
            ['Pesanan aktif', number_format($summary['orders_count'])],
            ['Penjualan bersih sebelum refund', $formatRp($summary['net_sales_before_refund'])],
            ['Penjualan aktif setelah refund retur berhasil', $formatRp($summary['net_sales_after_successful_refund'])],
            ['Pembayaran diterima', $formatRp($summary['successful_payment_receipts'])],
            ['Refund retur berhasil', $formatRp($summary['successful_return_refunds'])],
            ['Refund pembatalan operasional berhasil', $formatRp($summary['successful_operational_refunds'])],
            ['Pesanan dibatalkan', number_format($summary['cancelled_orders'])],
        ] as [$label, $value])
            <div class="col-sm-6 col-xl-4">
                <div class="card card-sm"><div class="card-body">
                    <div class="text-secondary">{{ $label }}</div>
                    <div class="h2 mb-0">{{ $value }}</div>
                </div></div>
            </div>
        @endforeach
    </div>
    <div class="alert alert-info" role="note">
        Penjualan menggunakan tanggal pembuatan pesanan. Penerimaan pembayaran memakai tanggal pembayaran berhasil. Refund berhasil memakai tanggal penyelesaian refund; refund yang belum berhasil memakai tanggal pencatatan untuk pemantauan. Indikator penjualan aktif setelah refund hanya mengurangi refund retur berhasil karena pesanan batal sudah dikeluarkan dari penjualan aktif. Refund pembatalan operasional ditampilkan terpisah agar tidak dikurangkan dua kali. Indikator ini bukan pengakuan pendapatan akuntansi lintas periode. Laba belum ditampilkan karena data HPP dan biaya operasional belum dinyatakan lengkap.
    </div>
@endif

@if ($jenis === 'keuangan' && isset($rows))
    <div class="alert alert-warning" role="note">
        Catatan keuangan manual tidak memiliki relasi outlet pada skema saat ini. Karena itu, bagian pencatatan manual tetap menampilkan catatan global meskipun filter outlet dipilih; data pembayaran dan saldo pesanan tetap mengikuti filter outlet.
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Pembayaran pesanan berhasil</h3></div>
        @include('admin.laporan.partials.table', ['items' => $rows])
    </div>
    @if (isset($secondaryRows) && $secondaryRows instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="card mb-3"><div class="card-header"><h3 class="card-title">Pencatatan keuangan manual (pemasukan, pengeluaran, modal)</h3></div>
            @include('admin.laporan.partials.table', ['items' => $secondaryRows])
        </div>
    @endif
    @if (isset($outstandingRows) && $outstandingRows instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="card mb-3"><div class="card-header"><h3 class="card-title">Saldo belum dibayar pada pesanan aktif</h3></div>
            @include('admin.laporan.partials.table', ['items' => $outstandingRows])
        </div>
    @endif
@elseif ($jenis === 'retur-refund' && isset($rows))
    <div class="card mb-3"><div class="card-header"><h3 class="card-title">Pengajuan retur</h3></div>
        @include('admin.laporan.partials.table', ['items' => $rows])
    </div>
    @if (isset($secondaryRows) && $secondaryRows instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="card mb-3"><div class="card-header"><h3 class="card-title">Refund (berdasarkan tanggal pencatatan; metrik refund berhasil berdasarkan selesai_pada)</h3></div>
            @include('admin.laporan.partials.table', ['items' => $secondaryRows])
        </div>
    @endif
@elseif ($jenis !== 'ringkasan' && isset($rows))
    @if ($jenis === 'produk-stok')
        <div class="alert alert-info" role="note">
            Unit dan nilai produk terjual mengikuti periode pesanan serta filter outlet. Stok fisik, reservasi, dan stok tersedia merupakan snapshot saat laporan dibuat; skema saat ini belum menyediakan ledger mutasi untuk rekonstruksi stok historis atau stok terpisah per outlet.
        </div>
    @endif
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">{{ $judul }} – rincian</h3></div>
        @include('admin.laporan.partials.table', ['items' => $rows])
    </div>
@endif

@if ($jenis === 'penjualan' && isset($orders) && $orders instanceof \Illuminate\Pagination\AbstractPaginator)
    <div class="card mb-3"><div class="card-header"><h3 class="card-title">Pesanan dalam periode penjualan</h3></div>
        @include('admin.laporan.partials.table', ['items' => $orders])
    </div>
@endif
@endsection
