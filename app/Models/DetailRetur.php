<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailRetur extends Model
{
    protected $table = 'detail_retur';

    protected $primaryKey = 'id_detail_retur';

    protected $fillable = [
        'id_retur',
        'id_detail_pesanan',
        'jumlah_diretur',
        'alasan_item',
        'nominal_refund',
    ];

    public function retur()
    {
        return $this->belongsTo(Retur::class, 'id_retur');
    }

    public function detailPesanan()
    {
        return $this->belongsTo(
            DetailPesanan::class,
            'id_detail_pesanan'
        );
    }
}
