@extends('admin.layouts.app')

@section('title', 'Riwayat Refund - TWFood')
@section('page-title', 'Riwayat Refund')

@push('styles')
    <style>
        .refund-index-page { min-width: 0; }
        .refund-index-page .refund-page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .refund-index-page .refund-page-heading h1 {
            margin: 0 0 .25rem;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .refund-index-page .refund-page-heading p {
            margin: 0;
            color: var(--admin-muted);
        }
        .refund-index-page .refund-filter-card .card-body { padding: 1.1rem 1.25rem; }
        .refund-index-page .refund-table { min-width: 850px; margin: 0; }
        .refund-index-page .refund-table th {
            white-space: nowrap;
            font-size: .72rem;
            letter-spacing: .035em;
            text-transform: uppercase;
            color: var(--admin-muted);
        }
        .refund-index-page .refund-table td { vertical-align: middle; }
        .refund-index-page .refund-id { font-weight: 700; white-space: nowrap; }
        .refund-index-page .refund-nominal { font-weight: 700; white-space: nowrap; }
        .refund-index-page .refund-customer { min-width: 150px; }
        .refund-index-page .refund-actions { text-align: right; white-space: nowrap; }
        .refund-index-page .refund-empty {
            padding: 2.5rem 1rem !important;
            text-align: center;
            color: var(--admin-muted);
        }
        .refund-index-page .refund-empty i {
            display: block;
            font-size: 2.2rem;
            margin-bottom: .5rem;
            color: var(--admin-accent);
        }
        .refund-index-page .refund-pagination {
            display: flex;
            justify-content: flex-end;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--admin-border);
        }
        @media (max-width: 575.98px) {
            .refund-index-page .refund-page-heading { align-items: stretch; }
            .refund-index-page .refund-page-heading .btn { width: 100%; }
            .refund-index-page .refund-filter-actions { display: flex; }
            .refund-index-page .refund-filter-actions .btn { flex: 1; }
            .refund-index-page .refund-pagination { justify-content: center; overflow-x: auto; }
        }
    </style>
@endpush

@section('content')
    <div class="refund-index-page">
        <div class="refund-page-heading">
            <div>
                <h1>Riwayat Refund</h1>
                <p>Pantau pengembalian dana dari proses retur pelanggan.</p>
            </div>
            <a href="{{ route('admin.retur.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Kembali ke Retur
            </a>
        </div>

        <div class="card refund-filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.refund.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <label for="q" class="form-label">Cari refund, pesanan, retur, atau pelanggan</label>
                        <input
                            id="q"
                            name="q"
                            class="form-control"
                            value="{{ $filter['q'] ?? '' }}"
                            placeholder="ID refund / nomor pesanan / nomor retur / nama"
                        >
                    </div>

                    <div class="col-12 col-md-7 col-lg-4">
                        <label for="status_refund" class="form-label">Status Refund</label>
                        <select id="status_refund" name="status_refund" class="form-select">
                            <option value="">Semua status</option>
                            @foreach (['belum_diajukan','menunggu_diproses','diproses','berhasil','gagal','perlu_diproses_ulang'] as $status)
                                <option value="{{ $status }}" @selected(($filter['status_refund'] ?? '') === $status)>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-5 col-lg-3 d-flex gap-2 refund-filter-actions">
                        <button class="btn btn-primary" type="submit">
                            <i class="ti ti-search me-1"></i>Cari
                        </button>
                        <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h3 class="card-title mb-0">Daftar Pengembalian Dana</h3>
                <span class="text-secondary small">{{ $refunds->total() }} data refund</span>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table refund-table">
                    <thead>
                        <tr>
                            <th>ID Refund</th>
                            <th>Nomor Retur</th>
                            <th>Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Percobaan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($refunds as $refund)
                            @php
                                $statusClass = match ($refund->status_refund) {
                                    'berhasil' => 'bg-success-lt',
                                    'gagal', 'perlu_diproses_ulang' => 'bg-danger-lt',
                                    'diproses', 'menunggu_diproses' => 'bg-warning-lt',
                                    default => 'bg-azure-lt',
                                };
                            @endphp
                            <tr>
                                <td class="refund-id">#{{ $refund->id_refund }}</td>
                                <td>{{ $refund->retur?->nomor_retur ?? '-' }}</td>
                                <td>{{ $refund->pesanan?->nomor_pesanan ?? '-' }}</td>
                                <td class="refund-customer">{{ $refund->pesanan?->pengguna?->nama ?? '-' }}</td>
                                <td class="refund-nominal">Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $refund->status_refund)) }}
                                    </span>
                                </td>
                                <td>{{ $refund->percobaanRefund->count() }}</td>
                                <td class="refund-actions">
                                    <a href="{{ route('admin.refund.show', $refund->id_refund) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="refund-empty">
                                    <i class="ti ti-receipt-off"></i>
                                    Belum ada data refund yang sesuai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($refunds->hasPages())
                <div class="refund-pagination">
                    {{ $refunds->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
