<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailKeranjang extends Model
{
    protected $table = 'detail_keranjang';

    protected $primaryKey = 'id_detail_keranjang';

    protected $fillable = [
        'id_keranjang',
        'id_varian',
        'jumlah',
    ];

    public function keranjang()
    {
        return $this->belongsTo(Keranjang::class, 'id_keranjang');
    }

    public function varian()
    {
        return $this->belongsTo(VarianProduk::class, 'id_varian');
    }
}
