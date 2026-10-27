<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiPreOrder extends Model
{
    protected $table = 'produksi_pre_order';

    protected $primaryKey = 'id_produksi_pre_order';

    protected $fillable = [
        'id_detail_pesanan',
        'jumlah_produksi',
        'status_produksi',
    ];

    public function detailPesanan()
    {
        return $this->belongsTo(
            DetailPesanan::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
        );
    }
}
