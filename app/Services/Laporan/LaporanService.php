<?php

namespace App\Services\Laporan;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for reporting queries. Each report deliberately uses
 * its own business date: orders.created_at, payments.dibayar_pada,
 * returns.diajukan_pada, completed refunds.selesai_pada (pending refund records.created_at),
 * and production activity dates.
 */
class LaporanService
{
    public function orders(array $f): Builder
    {
        return DB::table('pesanan as p')
            ->whereBetween('p.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['status_pesanan'] ?? null, fn ($q, $v) => $q->where('p.status_pesanan', $v))
            ->when($f['jenis_pemenuhan'] ?? null, fn ($q, $v) => $q->where('p.jenis_pemenuhan', $v))
            ->when($f['saluran_pesanan'] ?? null, fn ($q, $v) => $q->where('p.saluran_pesanan', $v));
    }

    public function dashboard(array $f): array
    {
        $active = (clone $this->orders($f))->where('p.status_pesanan', '!=', 'dibatalkan');
        $gross = (int) (clone $active)->sum('p.subtotal_produk');
        $discount = (int) (clone $active)->sum('p.diskon');
        $net = max(0, $gross - $discount);
        $count = (int) (clone $active)->count('p.id_pesanan');
        $paid = (int) DB::table('pembayaran as pay')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'pay.id_pesanan')
            ->whereIn('pay.jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->whereIn('pay.status_pembayaran', ['paid', 'refunded'])
            ->whereBetween('pay.dibayar_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['jenis_pemenuhan'] ?? null, fn ($q, $v) => $q->where('p.jenis_pemenuhan', $v))
            ->when($f['saluran_pesanan'] ?? null, fn ($q, $v) => $q->where('p.saluran_pesanan', $v))
            ->sum('pay.jumlah_pembayaran');

        $successfulRefundBase = DB::table('refund as r')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'r.id_pesanan')
            ->where('r.status_refund', 'berhasil')
            ->whereNotNull('r.selesai_pada')
            ->whereBetween('r.selesai_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['jenis_pemenuhan'] ?? null, fn ($q, $v) => $q->where('p.jenis_pemenuhan', $v))
            ->when($f['saluran_pesanan'] ?? null, fn ($q, $v) => $q->where('p.saluran_pesanan', $v));

        $refund = (int) (clone $successfulRefundBase)->sum('r.nominal_refund');
        $returnRefund = (int) (clone $successfulRefundBase)
            ->where('r.jenis_refund', 'retur')
            ->sum('r.nominal_refund');
        $operationalRefund = (int) (clone $successfulRefundBase)
            ->where('r.jenis_refund', 'pembatalan_operasional')
            ->sum('r.nominal_refund');
        $cancelled = (int) (clone $this->orders($f))->where('p.status_pesanan', 'dibatalkan')->count('p.id_pesanan');

        return [
            'orders_count' => $count,
            'gross_sales' => $gross,
            'discount' => $discount,
            'net_sales_before_refund' => $net,
            'net_sales_after_successful_refund' => max(0, $net - $returnRefund),
            'successful_payment_receipts' => $paid,
            'successful_refunds' => $refund,
            'successful_return_refunds' => $returnRefund,
            'successful_operational_refunds' => $operationalRefund,
            'cancelled_orders' => $cancelled,
            'average_order_value' => $count > 0 ? (int) round($net / $count) : 0,
            'period' => [$f['tanggal_mulai'], $f['tanggal_selesai']],
        ];
    }

    public function sales(array $f)
    {
        $orders = $this->orders($f)->where('p.status_pesanan', '!=', 'dibatalkan');
        $products = DB::table('detail_pesanan as d')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'd.id_pesanan')
            ->whereBetween('p.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->where('p.status_pesanan', '!=', 'dibatalkan')
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['jenis_pemenuhan'] ?? null, fn ($q, $v) => $q->where('p.jenis_pemenuhan', $v))
            ->when($f['saluran_pesanan'] ?? null, fn ($q, $v) => $q->where('p.saluran_pesanan', $v))
            ->when($f['jenis_harga'] ?? null, fn ($q, $v) => $q->where('d.jenis_harga_saat_pesan', $v))
            ->select('d.nama_produk_saat_pesan', 'd.nama_varian_saat_pesan', 'd.jenis_harga_saat_pesan')
            ->selectRaw('SUM(d.jumlah) as unit_terjual, SUM(d.subtotal) as nilai_kotor_item, SUM(COALESCE(d.diskon_item, 0)) as diskon_item')
            ->groupBy('d.nama_produk_saat_pesan', 'd.nama_varian_saat_pesan', 'd.jenis_harga_saat_pesan')
            ->orderByDesc('unit_terjual');

        return [
            'orders' => $orders->select('p.*')->orderByDesc('p.created_at')->paginate(20)->withQueryString(),
            'products' => $products->paginate(20)->withQueryString(),
            'summary' => $this->dashboard($f),
        ];
    }

    public function payments(array $f)
    {
        return DB::table('pembayaran as pay')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'pay.id_pesanan')
            ->leftJoin('pengguna as u', 'u.id_pengguna', '=', 'p.id_pengguna')
            ->whereBetween('pay.dibayar_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->whereIn('pay.jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->whereIn('pay.status_pembayaran', ['paid', 'refunded'])
            ->when($f['jenis_pembayaran'] ?? null, fn ($q, $v) => $q->where('pay.jenis_pembayaran', $v))
            ->select('pay.*', 'p.nomor_pesanan', 'p.id_outlet', 'u.nama as nama_pelanggan')
            ->orderByDesc('pay.dibayar_pada')->paginate(20)->withQueryString();
    }

    public function transactions(array $f)
    {
        return $this->orders($f)->leftJoin('pengguna as u', 'u.id_pengguna', '=', 'p.id_pengguna')
            ->leftJoin('outlet as o', 'o.id_outlet', '=', 'p.id_outlet')
            ->select('p.*', 'u.nama as nama_pelanggan', 'o.nama_outlet')
            ->orderByDesc('p.created_at')->paginate(20)->withQueryString();
    }

    public function products(array $f)
    {
        return $this->productStockQuery($f)->paginate(20)->withQueryString();
    }

    private function productStockQuery(array $f): Builder
    {
        $soldByVariant = DB::table('detail_pesanan as d')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'd.id_pesanan')
            ->whereBetween('p.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->where('p.status_pesanan', '!=', 'dibatalkan')
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['jenis_pemenuhan'] ?? null, fn ($q, $v) => $q->where('p.jenis_pemenuhan', $v))
            ->when($f['saluran_pesanan'] ?? null, fn ($q, $v) => $q->where('p.saluran_pesanan', $v))
            ->when($f['jenis_harga'] ?? null, fn ($q, $v) => $q->where('d.jenis_harga_saat_pesan', $v))
            ->groupBy('d.id_varian')
            ->select('d.id_varian')
            ->selectRaw('SUM(d.jumlah) as unit_terjual, SUM(d.subtotal) as nilai_kotor_item');

        return DB::table('varian_produk as v')
            ->join('produk as pr', 'pr.id_produk', '=', 'v.id_produk')
            ->leftJoin('reservasi_stok as rs', function ($join) {
                $join->on('rs.id_varian', '=', 'v.id_varian')
                    ->where('rs.status_reservasi', '=', 'aktif')
                    ->where('rs.kadaluarsa_pada', '>', now());
            })
            ->leftJoinSub($soldByVariant, 'sold', fn ($join) => $join->on('sold.id_varian', '=', 'v.id_varian'))
            ->groupBy('v.id_varian', 'pr.nama_produk', 'v.nama_varian', 'v.stok', 'sold.unit_terjual', 'sold.nilai_kotor_item')
            ->select('v.id_varian', 'pr.nama_produk', 'v.nama_varian', 'v.stok')
            ->selectRaw('COALESCE(sold.unit_terjual, 0) as unit_terjual, COALESCE(sold.nilai_kotor_item, 0) as nilai_kotor_item')
            ->selectRaw('COALESCE(SUM(rs.jumlah), 0) as reservasi_aktif, (v.stok - COALESCE(SUM(rs.jumlah), 0)) as stok_tersedia')
            ->orderBy('pr.nama_produk');
    }

    public function customers(array $f)
    {
        return DB::table('pesanan as p')->join('pengguna as u', 'u.id_pengguna', '=', 'p.id_pengguna')
            ->whereBetween('p.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->where('p.status_pesanan', '!=', 'dibatalkan')
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->groupBy('u.id_pengguna', 'u.nama', 'u.jenis_pelanggan')
            ->select('u.id_pengguna', 'u.nama', 'u.jenis_pelanggan')
            ->selectRaw('COUNT(DISTINCT p.id_pesanan) as jumlah_pesanan, SUM(p.total_pesanan) as nilai_pesanan, MIN(p.created_at) as transaksi_pertama')
            ->orderByDesc('nilai_pesanan')->paginate(20)->withQueryString();
    }

    public function returns(array $f)
    {
        return DB::table('retur as r')->join('pesanan as p', 'p.id_pesanan', '=', 'r.id_pesanan')
            ->leftJoin('outlet as o', 'o.id_outlet', '=', 'p.id_outlet')
            ->whereBetween('r.diajukan_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->select('r.*', 'p.nomor_pesanan', 'o.nama_outlet')->orderByDesc('r.diajukan_pada')->paginate(20)->withQueryString();
    }

    public function refunds(array $f)
    {
        return DB::table('refund as r')->join('pesanan as p', 'p.id_pesanan', '=', 'r.id_pesanan')
            ->leftJoin('outlet as o', 'o.id_outlet', '=', 'p.id_outlet')
            ->where(function ($query) use ($f) {
                $query->where(function ($success) use ($f) {
                    $success->where('r.status_refund', 'berhasil')
                        ->whereBetween('r.selesai_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59']);
                })->orWhere(function ($notCompleted) use ($f) {
                    $notCompleted->where('r.status_refund', '!=', 'berhasil')
                        ->whereBetween('r.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59']);
                });
            })
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['status_refund'] ?? null, fn ($q, $v) => $q->where('r.status_refund', $v))
            ->when($f['jenis_refund'] ?? null, fn ($q, $v) => $q->where('r.jenis_refund', $v))
            ->select('r.*', 'p.nomor_pesanan', 'o.nama_outlet')
            ->selectRaw('COALESCE(r.selesai_pada, r.created_at) as tanggal_acuan')
            ->orderByRaw('COALESCE(r.selesai_pada, r.created_at) DESC')->paginate(20)->withQueryString();
    }

    public function production(array $f)
    {
        return DB::table('produksi_pre_order as pp')->join('detail_pesanan as d', 'd.id_detail_pesanan', '=', 'pp.id_detail_pesanan')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'd.id_pesanan')
            ->where(function ($q) use ($f) {
                $q->whereBetween('pp.mulai_produksi_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
                    ->orWhereBetween('pp.selesai_produksi_pada', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59'])
                    ->orWhereBetween('pp.created_at', [$f['tanggal_mulai'].' 00:00:00', $f['tanggal_selesai'].' 23:59:59']);
            })
            ->when($f['id_outlet'] ?? null, fn ($q, $id) => $q->where('p.id_outlet', $id))
            ->when($f['status_produksi'] ?? null, fn ($q, $v) => $q->where('pp.status_produksi', $v))
            ->select('pp.*', 'p.nomor_pesanan', 'd.nama_produk_saat_pesan', 'd.nama_varian_saat_pesan')->orderByDesc('pp.created_at')->paginate(20)->withQueryString();
    }

    public function exportRows(string $jenis, array $f): array
    {
        $start = $f['tanggal_mulai'].' 00:00:00';
        $end = $f['tanggal_selesai'].' 23:59:59';

        if ($jenis === 'ringkasan') {
            $summary = $this->dashboard($f);
            return [[
                'periode_mulai' => $f['tanggal_mulai'],
                'periode_selesai' => $f['tanggal_selesai'],
                'jumlah_pesanan_aktif' => $summary['orders_count'],
                'penjualan_kotor' => $summary['gross_sales'],
                'diskon' => $summary['discount'],
                'penjualan_bersih_sebelum_refund' => $summary['net_sales_before_refund'],
                'refund_berhasil_total' => $summary['successful_refunds'],
                'refund_retur_berhasil' => $summary['successful_return_refunds'],
                'refund_pembatalan_operasional_berhasil' => $summary['successful_operational_refunds'],
                'indikator_penjualan_setelah_refund'  => $summary['net_sales_after_successful_refund'],
                'pembayaran_diterima' => $summary['successful_payment_receipts'],
                'pesanan_dibatalkan' => $summary['cancelled_orders'],
                'rata_rata_nilai_pesanan' => $summary['average_order_value'],
            ]];
        }

        if ($jenis === 'penjualan') {
            return DB::table('detail_pesanan as d')->join('pesanan as p','p.id_pesanan','=','d.id_pesanan')
                ->whereBetween('p.created_at',[$start,$end])->where('p.status_pesanan','!=','dibatalkan')
                ->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))
                ->when($f['jenis_pemenuhan'] ?? null,fn($q,$v)=>$q->where('p.jenis_pemenuhan',$v))
                ->when($f['saluran_pesanan'] ?? null,fn($q,$v)=>$q->where('p.saluran_pesanan',$v))
                ->when($f['jenis_harga'] ?? null,fn($q,$v)=>$q->where('d.jenis_harga_saat_pesan',$v))
                ->select('d.nama_produk_saat_pesan','d.nama_varian_saat_pesan','d.jenis_harga_saat_pesan')
                ->selectRaw('SUM(d.jumlah) as unit_terjual, SUM(d.subtotal) as nilai_kotor_item, SUM(COALESCE(d.diskon_item,0)) as diskon_item')
                ->groupBy('d.nama_produk_saat_pesan','d.nama_varian_saat_pesan','d.jenis_harga_saat_pesan')->orderByDesc('unit_terjual')->get()->map(fn($r)=>(array)$r)->all();
        }

        if ($jenis === 'keuangan') {
            $payments = DB::table('pembayaran as pay')->join('pesanan as p','p.id_pesanan','=','pay.id_pesanan')->leftJoin('pengguna as u','u.id_pengguna','=','p.id_pengguna')
                ->whereBetween('pay.dibayar_pada',[$start,$end])->whereIn('pay.jenis_pembayaran',['penuh','dp','pelunasan'])->whereIn('pay.status_pembayaran',['paid','refunded'])
                ->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))
                ->when($f['jenis_pembayaran'] ?? null,fn($q,$v)=>$q->where('pay.jenis_pembayaran',$v))
                ->select('pay.id_transaksi','p.nomor_pesanan','u.nama as nama_pelanggan','pay.jenis_pembayaran','pay.metode_pembayaran','pay.jumlah_pembayaran','pay.dibayar_pada')->get()
                ->map(fn($r)=>['sumber'=>'pembayaran_pesanan','nomor_referensi'=>$r->id_transaksi,'nomor_pesanan'=>$r->nomor_pesanan,'nama_pihak'=>$r->nama_pelanggan,'jenis'=>$r->jenis_pembayaran,'metode'=>$r->metode_pembayaran,'nominal'=>$r->jumlah_pembayaran,'tanggal'=>$r->dibayar_pada]);
            $manual = DB::table('transaksi_keuangan as tk')->leftJoin('pengguna as u','u.id_pengguna','=','tk.id_pengguna')
                ->whereBetween('tk.tanggal_transaksi',[$f['tanggal_mulai'],$f['tanggal_selesai']])
                ->when($f['jenis_transaksi_keuangan'] ?? null,fn($q,$v)=>$q->where('tk.jenis_transaksi',$v))
                ->select('tk.id_transaksi_keuangan','tk.kategori','tk.jenis_transaksi','tk.nominal','tk.tanggal_transaksi','u.nama as nama_pihak')->get()
                ->map(fn($r)=>['sumber'=>'pencatatan_manual','nomor_referensi'=>$r->id_transaksi_keuangan,'nomor_pesanan'=>null,'nama_pihak'=>$r->nama_pihak,'jenis'=>$r->jenis_transaksi,'metode'=>$r->kategori,'nominal'=>$r->nominal,'tanggal'=>$r->tanggal_transaksi]);
            $outstanding = $this->outstandingOrderQuery($f)->get()->map(fn($r)=>['sumber'=>'saldo_order','nomor_referensi'=>null,'nomor_pesanan'=>$r->nomor_pesanan,'nama_pihak'=>null,'jenis'=>'saldo_belum_dibayar','metode'=>$r->status_pembayaran,'nominal'=>$r->saldo_belum_dibayar,'tanggal'=>$r->created_at]);
            return $payments->concat($manual)->concat($outstanding)->values()->all();
        }

        if ($jenis === 'transaksi') {
            return (clone $this->orders($f))->leftJoin('pengguna as u','u.id_pengguna','=','p.id_pengguna')->leftJoin('outlet as o','o.id_outlet','=','p.id_outlet')
                ->select('p.nomor_pesanan','u.nama as nama_pelanggan','o.nama_outlet','p.saluran_pesanan','p.jenis_pesanan','p.jenis_pemenuhan','p.subtotal_produk','p.diskon','p.total_pesanan','p.status_pembayaran','p.status_pesanan','p.created_at')->orderBy('p.created_at')->get()->map(fn($r)=>(array)$r)->all();
        }

        if ($jenis === 'produk-stok') {
            return $this->productStockQuery($f)->get()->map(fn ($row) => (array) $row)->all();
        }

        if ($jenis === 'pelanggan') {
            return DB::table('pesanan as p')->join('pengguna as u','u.id_pengguna','=','p.id_pengguna')->whereBetween('p.created_at',[$start,$end])->where('p.status_pesanan','!=','dibatalkan')
                ->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))->groupBy('u.id_pengguna','u.nama','u.jenis_pelanggan')->select('u.id_pengguna','u.nama','u.jenis_pelanggan')->selectRaw('COUNT(DISTINCT p.id_pesanan) as jumlah_pesanan,SUM(p.total_pesanan) as nilai_pesanan,MIN(p.created_at) as transaksi_pertama')->orderByDesc('nilai_pesanan')->get()->map(fn($r)=>(array)$r)->all();
        }

        if ($jenis === 'retur-refund') {
            $returns = DB::table('retur as r')->join('pesanan as p','p.id_pesanan','=','r.id_pesanan')->whereBetween('r.diajukan_pada',[$start,$end])->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))->select('p.nomor_pesanan','r.nomor_retur','r.status_retur','r.status_pengembalian_dana','r.nominal_pengembalian as nominal','r.diajukan_pada as tanggal')->get()->map(fn($r)=>['jenis_catatan'=>'retur','nomor_pesanan'=>$r->nomor_pesanan,'nomor_retur_refund'=>$r->nomor_retur,'status'=>$r->status_retur,'status_dana'=>$r->status_pengembalian_dana,'nominal'=>$r->nominal,'tanggal'=>$r->tanggal]);
            $refunds = DB::table('refund as r')->join('pesanan as p','p.id_pesanan','=','r.id_pesanan')
                ->where(function($q)use($start,$end){$q->where(function($success)use($start,$end){$success->where('r.status_refund','berhasil')->whereBetween('r.selesai_pada',[$start,$end]);})->orWhere(function($pending)use($start,$end){$pending->where('r.status_refund','!=','berhasil')->whereBetween('r.created_at',[$start,$end]);});})
                ->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))
                ->when($f['status_refund'] ?? null,fn($q,$v)=>$q->where('r.status_refund',$v))
                ->when($f['jenis_refund'] ?? null,fn($q,$v)=>$q->where('r.jenis_refund',$v))
                ->select('p.nomor_pesanan','r.id_refund','r.jenis_refund','r.status_refund','r.nominal_refund')->selectRaw('COALESCE(r.selesai_pada,r.created_at) as tanggal_acuan')->get()->map(fn($r)=>['jenis_catatan'=>'refund','nomor_pesanan'=>$r->nomor_pesanan,'nomor_retur_refund'=>$r->id_refund,'status'=>$r->status_refund,'status_dana'=>$r->jenis_refund,'nominal'=>$r->nominal_refund,'tanggal'=>$r->tanggal_acuan]);
            return $returns->concat($refunds)->values()->all();
        }

        if ($jenis === 'produksi') {
            return DB::table('produksi_pre_order as pp')->join('detail_pesanan as d','d.id_detail_pesanan','=','pp.id_detail_pesanan')->join('pesanan as p','p.id_pesanan','=','d.id_pesanan')
                ->where(function($q)use($start,$end){$q->whereBetween('pp.mulai_produksi_pada',[$start,$end])->orWhereBetween('pp.selesai_produksi_pada',[$start,$end])->orWhereBetween('pp.created_at',[$start,$end]);})
                ->when($f['id_outlet'] ?? null,fn($q,$id)=>$q->where('p.id_outlet',$id))->when($f['status_produksi'] ?? null,fn($q,$v)=>$q->where('pp.status_produksi',$v))
                ->select('p.nomor_pesanan','d.nama_produk_saat_pesan','d.nama_varian_saat_pesan','pp.jumlah_produksi','pp.status_produksi','pp.estimasi_selesai','pp.mulai_produksi_pada','pp.selesai_produksi_pada')->orderBy('pp.created_at')->get()->map(fn($r)=>(array)$r)->all();
        }

        return [];
    }

    public function outstandingOrders(array $f)
    {
        return $this->outstandingOrderQuery($f)->paginate(20)->withQueryString();
    }

    private function outstandingOrderQuery(array $f): Builder
    {
        $paidByOrder = DB::table('pembayaran')
            ->select('id_pesanan')
            ->selectRaw("SUM(CASE WHEN status_pembayaran IN ('paid','refunded') AND jenis_pembayaran IN ('penuh','dp','pelunasan') THEN jumlah_pembayaran ELSE 0 END) as total_dibayar")
            ->groupBy('id_pesanan');

        return $this->orders($f)
            ->leftJoinSub($paidByOrder, 'paid_sum', fn ($join) => $join->on('paid_sum.id_pesanan', '=', 'p.id_pesanan'))
            ->where('p.status_pesanan', '!=', 'dibatalkan')
            ->whereRaw('GREATEST(p.total_pesanan - COALESCE(paid_sum.total_dibayar, 0), 0) > 0')
            ->select('p.nomor_pesanan', 'p.id_outlet', 'p.total_pesanan', 'p.status_pembayaran', 'p.created_at')
            ->selectRaw('COALESCE(paid_sum.total_dibayar, 0) as total_dibayar')
            ->selectRaw('GREATEST(p.total_pesanan - COALESCE(paid_sum.total_dibayar, 0), 0) as saldo_belum_dibayar')
            ->orderByDesc('p.created_at');
    }

    public function manualFinancialEntries(array $f)
    {
        return DB::table('transaksi_keuangan as tk')->leftJoin('pengguna as u', 'u.id_pengguna', '=', 'tk.id_pengguna')
            ->whereBetween('tk.tanggal_transaksi', [$f['tanggal_mulai'], $f['tanggal_selesai']])
            ->when($f['jenis_transaksi_keuangan'] ?? null, fn ($q, $v) => $q->where('tk.jenis_transaksi', $v))
            ->select('tk.*', 'u.nama as dicatat_oleh')->orderByDesc('tk.tanggal_transaksi')->paginate(20)->withQueryString();
    }
}
