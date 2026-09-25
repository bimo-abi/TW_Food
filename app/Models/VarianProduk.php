<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VarianProduk extends Model
{
    protected $table = 'varian_produk';

    protected $primaryKey = 'id_varian';

    protected $fillable = [
        'id_produk',
        'nama_varian',
        'berat_gram',
        'satuan_jual',
        'stok',
        'tersedia_pre_order',
        'tanggal_mulai_pre_order',
        'tanggal_selesai_pre_order',
        'estimasi_tersedia',
        'status_aktif',
    ];

    protected $casts = [
        'tersedia_pre_order' => 'boolean',
        'status_aktif' => 'boolean',
        'tanggal_mulai_pre_order' => 'date',
        'tanggal_selesai_pre_order' => 'date',
        'estimasi_tersedia' => 'date',
    ];

    public function produk()
    {
        return $this->belongsTo(
            Produk::class,
            'id_produk',
            'id_produk'
        );
    }
    public function daftarHarga()
    {
        return $this->hasMany(DaftarHarga::class, 'id_varian');
    }
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_varian');
    }

    public function promosi()
    {
        return $this->hasMany(
            PromosiVarian::class,
            'id_varian'
        );
    }
}
