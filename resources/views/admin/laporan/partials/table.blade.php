@php
    /**
     * Kolom laporan sengaja menggunakan whitelist. Jangan render seluruh field
     * hasil query (mis. respons_gateway, payload/token, metadata internal).
     */
    $columnLabels = [
        'id_transaksi' => 'ID Transaksi',
        'nomor_pesanan' => 'Nomor Pesanan',
        'nama_pelanggan' => 'Pelanggan',
        'nama_pihak' => 'Pihak Terkait',
        'nama_outlet' => 'Outlet',
        'id_outlet' => 'ID Outlet',
        'jenis_pembayaran' => 'Jenis Pembayaran',
        'metode_pembayaran' => 'Metode Pembayaran',
        'jumlah_pembayaran' => 'Jumlah Pembayaran',
        'status_pembayaran' => 'Status Pembayaran',
        'dibayar_pada' => 'Tanggal Dibayar',
        'sumber' => 'Sumber Catatan',
        'nomor_referensi' => 'Nomor Referensi',
        'jenis' => 'Jenis',
        'metode' => 'Kategori / Metode',
        'nominal' => 'Nominal',
        'tanggal' => 'Tanggal',
        'total_pesanan' => 'Total Pesanan',
        'total_dibayar' => 'Total Dibayar',
        'saldo_belum_dibayar' => 'Saldo Belum Dibayar',
        'subtotal_produk' => 'Subtotal Produk',
        'diskon' => 'Diskon',
        'status_pesanan' => 'Status Pesanan',
        'jenis_pesanan' => 'Jenis Pesanan',
        'jenis_pemenuhan' => 'Pemenuhan',
        'saluran_pesanan' => 'Kanal Pesanan',
        'created_at' => 'Tanggal Pesanan',
        'nama_produk_saat_pesan' => 'Produk',
        'nama_varian_saat_pesan' => 'Varian',
        'jenis_harga_saat_pesan' => 'Jenis Harga',
        'unit_terjual' => 'Unit Terjual',
        'nilai_kotor_item' => 'Nilai Kotor Item',
        'diskon_item' => 'Diskon Item',
        'nama_produk' => 'Produk',
        'nama_varian' => 'Varian',
        'stok' => 'Stok Fisik',
        'reservasi_aktif' => 'Stok Direservasi',
        'stok_tersedia' => 'Stok Tersedia',
        'jenis_pelanggan' => 'Segmen Pelanggan',
        'jumlah_pesanan' => 'Jumlah Pesanan',
        'nilai_pesanan' => 'Nilai Pesanan',
        'transaksi_pertama' => 'Transaksi Pertama',
        'nomor_retur' => 'Nomor Retur',
        'nomor_retur_refund' => 'Nomor Retur / ID Refund',
        'jenis_catatan' => 'Jenis Catatan',
        'status' => 'Status',
        'status_dana' => 'Status / Jenis Dana',
        'status_retur' => 'Status Retur',
        'status_pengembalian_dana' => 'Status Pengembalian Dana',
        'nominal_pengembalian' => 'Nominal Pengembalian',
        'jenis_refund' => 'Jenis Refund',
        'status_refund' => 'Status Refund',
        'nominal_refund' => 'Nominal Refund',
        'jumlah_produksi' => 'Jumlah Produksi',
        'status_produksi' => 'Status Produksi',
        'estimasi_selesai' => 'Estimasi Selesai',
        'mulai_produksi_pada' => 'Mulai Produksi',
        'selesai_produksi_pada' => 'Selesai Produksi',
        'periode_mulai' => 'Periode Mulai',
        'periode_selesai' => 'Periode Selesai',
        'jumlah_pesanan_aktif' => 'Jumlah Pesanan Aktif',
        'penjualan_kotor' => 'Penjualan Kotor',
        'penjualan_bersih_sebelum_refund' => 'Penjualan Bersih Sebelum Refund',
        'refund_berhasil_total' => 'Total Refund Berhasil',
        'refund_retur_berhasil' => 'Refund Retur Berhasil',
        'refund_pembatalan_operasional_berhasil' => 'Refund Pembatalan Operasional',
        'indikator_penjualan_setelah_refund' => 'Penjualan Setelah Refund Retur',
        'pembayaran_diterima' => 'Pembayaran Diterima',
        'pesanan_dibatalkan' => 'Pesanan Dibatalkan',
        'rata_rata_nilai_pesanan' => 'Rata-rata Nilai Pesanan',
    ];
    $collection = $items instanceof \Illuminate\Pagination\AbstractPaginator ? $items->getCollection() : collect($items);
    $first = $collection->first();
    $available = $first ? array_keys((array) $first) : [];
    $columns = array_values(array_filter($available, fn ($key) => array_key_exists($key, $columnLabels)));
    $moneyColumns = ['jumlah_pembayaran','nominal','total_pesanan','total_dibayar','saldo_belum_dibayar','subtotal_produk','diskon','nilai_kotor_item','diskon_item','nilai_pesanan','nominal_pengembalian','nominal_refund','penjualan_kotor','penjualan_bersih_sebelum_refund','refund_berhasil_total','refund_retur_berhasil','refund_pembatalan_operasional_berhasil','indikator_penjualan_setelah_refund','pembayaran_diterima','rata_rata_nilai_pesanan'];
    $dateColumns = ['dibayar_pada','tanggal','created_at','transaksi_pertama','estimasi_selesai','mulai_produksi_pada','selesai_produksi_pada'];
@endphp
<div class="table-responsive">
    <table class="table table-vcenter card-table">
        @if ($columns)
            <thead><tr>@foreach ($columns as $column)<th>{{ $columnLabels[$column] }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach ($collection as $item)
                <tr>@foreach ($columns as $column)
                    @php($value = data_get($item, $column))
                    <td>
                        @if (in_array($column, $moneyColumns, true) && is_numeric($value))
                            Rp {{ number_format((int) $value, 0, ',', '.') }}
                        @elseif (in_array($column, $dateColumns, true) && $value)
                            {{ \Illuminate\Support\Carbon::parse($value)->format('d-m-Y H:i') }}
                        @elseif (is_bool($value))
                            {{ $value ? 'Ya' : 'Tidak' }}
                        @else
                            {{ is_scalar($value) ? ($value === '' ? '-' : $value) : '-' }}
                        @endif
                    </td>
                @endforeach</tr>
            @endforeach
            </tbody>
        @else
            <tbody><tr><td class="text-center text-secondary py-5">Belum ada data pada periode ini.</td></tr></tbody>
        @endif
    </table>
</div>
@if ($items instanceof \Illuminate\Pagination\AbstractPaginator && $items->hasPages())
    <div class="card-footer">{{ $items->links() }}</div>
@endif
