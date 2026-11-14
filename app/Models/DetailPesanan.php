<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'jenis_harga_saat_pesan',
        'jumlah',
        'subtotal',
        'diskon_item',
        'subtotal_setelah_diskon',
    ];

    protected function casts(): array
    {
        return [
            'harga_saat_pesan' => 'integer',
            'jumlah' => 'integer',
            'subtotal' => 'integer',
            'diskon_item' => 'integer',
            'subtotal_setelah_diskon' => 'integer',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(
            Pesanan::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function varian(): BelongsTo
    {
        return $this->belongsTo(
            VarianProduk::class,
            'id_varian',
            'id_varian'
        );
    }

    public function detailRetur(): HasMany
    {
        return $this->hasMany(
            DetailRetur::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
        );
    }

    public function produksiPreOrder(): HasOne
    {
        return $this->hasOne(
            ProduksiPreOrder::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
        );
    }

    public function reservasiStok(): HasMany
    {
        return $this->hasMany(
            ReservasiStok::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
        );
    }
}
