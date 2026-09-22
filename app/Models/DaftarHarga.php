<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarHarga extends Model
{
    protected $table = 'daftar_harga';

    protected $primaryKey = 'id_daftar_harga';

    protected $fillable = [
        'id_varian',
        'jenis_harga',
        'harga',
        'minimal_pembelian',
        'satuan_minimal',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function varian()
    {
        return $this->belongsTo(VarianProduk::class, 'id_varian');
    }
}
