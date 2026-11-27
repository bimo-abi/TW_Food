<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Laporan TWFOOD - {{ $title }}</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#202733}h1{font-size:18px;margin:0 0 6px}p{margin:3px 0 12px;color:#596579}.summary{width:100%;margin:12px 0;border-collapse:collapse}.summary td{border:1px solid #d9dee7;padding:6px}.summary strong{display:block;font-size:12px;margin-top:3px}table.data{width:100%;border-collapse:collapse}table.data th,table.data td{border:1px solid #d9dee7;padding:5px;text-align:left;vertical-align:top}table.data th{background:#f1f4f8}
</style></head><body>
<h1>Laporan {{ $title }} – TWFOOD</h1>
<p>Periode: {{ $filters['tanggal_mulai'] }} s.d. {{ $filters['tanggal_selesai'] }} | Outlet: {{ $filters['id_outlet'] ?? 'Semua outlet' }} | Dibuat: {{ now()->format('d-m-Y H:i') }}</p>
<table class="summary"><tr><td>Penjualan bersih sebelum refund<strong>Rp {{ number_format($summary['net_sales_before_refund'],0,',','.') }}</strong></td><td>Penjualan aktif setelah refund retur berhasil<strong>Rp {{ number_format($summary['net_sales_after_successful_refund'],0,',','.') }}</strong></td><td>Pembayaran diterima<strong>Rp {{ number_format($summary['successful_payment_receipts'],0,',','.') }}</strong></td></tr><tr><td>Total refund berhasil<strong>Rp {{ number_format($summary['successful_refunds'],0,',','.') }}</strong></td><td>Refund retur berhasil<strong>Rp {{ number_format($summary['successful_return_refunds'],0,',','.') }}</strong></td><td>Refund pembatalan operasional berhasil<strong>Rp {{ number_format($summary['successful_operational_refunds'],0,',','.') }}</strong></td></tr></table>
<table class="data"><thead><tr>@foreach(array_keys($rows[0] ?? []) as $key)<th>{{ str($key)->replace('_',' ')->title() }}</th>@endforeach</tr></thead><tbody>
@forelse($rows as $row)<tr>@foreach($row as $value)<td>{{ is_scalar($value) ? $value : json_encode($value) }}</td>@endforeach</tr>@empty<tr><td colspan="20">Tidak ada data pada periode ini.</td></tr>@endforelse
</tbody></table>
<p>Catatan: refund pembatalan operasional tidak dikurangkan lagi dari penjualan aktif karena pesanan batal sudah dikecualikan; hanya refund retur berhasil yang mengurangi indikator penjualan aktif setelah refund. Angka laporan merupakan indikator operasional, bukan laporan laba rugi. Laba belum disajikan sampai data HPP dan biaya operasional siap.</p>
</body></html>
