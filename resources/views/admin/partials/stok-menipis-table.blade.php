<div class="table-responsive">
    <table class="table table-vcenter card-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Varian</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokList as $item)
                <tr>
                    <td>{{ $stokList->firstItem() + $loop->index }}</td>
                    <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                    <td>{{ $item->nama_varian ?? '-' }}</td>
                    <td><span class="badge bg-red-lt">{{ $item->stok }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-secondary py-4">Semua stok aman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer d-flex align-items-center">
    {{ $stokList->links('pagination::bootstrap-5') }}
</div>
