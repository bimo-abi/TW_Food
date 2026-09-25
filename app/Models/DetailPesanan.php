<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $primaryKey = 'id_detail_pesanan';

    protected $fillable = [
        'id_pesanan',
        'id_varian',
        'nama_produk_saat_pesan',
        'nama_varian_saat_pesan',
        'harga_saat_pesan',
        'jumlah',
        'subtotal',
    ];

    public function pesanan()
    {
        return $this->belongsTo(
            Pesanan::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function varian()
    {
        return $this->belongsTo(
            VarianProduk::class,
            'id_varian',
            'id_varian'
        );
    }

    public function detailRetur()
    {
        return $this->hasMany(
            DetailRetur::class,
            'id_detail_pesanan'
        );
    }
}
