@extends('admin.layouts.app')

@section('title', 'Riwayat Refund - TWFood')

@section('content')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><h1>Riwayat Refund</h1><p>Pantau pengembalian dana dari proses retur pelanggan.</p></div>
        <a href="{{ route('admin.retur.index') }}" class="btn btn-outline-secondary">Kembali ke Retur</a>
    </div>

    <div class="content-card">
        <form method="GET" action="{{ route('admin.refund.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <label for="q" class="form-label">Cari refund, pesanan, retur, atau pelanggan</label>
                <input id="q" name="q" class="form-control" value="{{ $filter['q'] ?? '' }}" placeholder="ID refund / nomor pesanan / nomor retur / nama">
            </div>
            <div class="col-md-4">
                <label for="status_refund" class="form-label">Status Refund</label>
                <select id="status_refund" name="status_refund" class="form-select">
                    <option value="">Semua status</option>
                    @foreach (['belum_diajukan','menunggu_diproses','diproses','berhasil','gagal','perlu_diproses_ulang'] as $status)
                        <option value="{{ $status }}" @selected(($filter['status_refund'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="ti ti-search me-1"></i> Cari</button>
                <a href="{{ route('admin.refund.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead><tr><th>ID Refund</th><th>Nomor Retur</th><th>Pesanan</th><th>Pelanggan</th><th>Nominal</th><th>Status</th><th>Percobaan</th><th></th></tr></thead>
                <tbody>
                    @forelse ($refunds as $refund)
                        <tr>
                            <td>#{{ $refund->id_refund }}</td>
                            <td>{{ $refund->retur?->nomor_retur ?? '-' }}</td>
                            <td>{{ $refund->pesanan?->nomor_pesanan ?? '-' }}</td>
                            <td>{{ $refund->pesanan?->pengguna?->nama ?? '-' }}</td>
                            <td>Rp{{ number_format((int) $refund->nominal_refund, 0, ',', '.') }}</td>
                            <td><span class="badge bg-azure-lt">{{ str_replace('_', ' ', $refund->status_refund) }}</span></td>
                            <td>{{ $refund->percobaanRefund->count() }}</td>
                            <td class="text-end"><a href="{{ route('admin.refund.show', $refund->id_refund) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada data refund yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $refunds->links() }}</div>
    </div>
@endsection
