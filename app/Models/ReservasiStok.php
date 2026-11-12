<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservasiStok extends Model
{
    protected $table = 'reservasi_stok';

    protected $primaryKey = 'id_reservasi_stok';

    protected $fillable = [
        'id_pesanan',
        'id_detail_pesanan',
        'id_varian',
        'jumlah',
        'status_reservasi',
        'dipesan_pada',
        'kadaluarsa_pada',
        'dikonversi_pada',
        'dilepas_pada',
        'alasan_pelepasan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'dipesan_pada' => 'datetime',
            'kadaluarsa_pada' => 'datetime',
            'dikonversi_pada' => 'datetime',
            'dilepas_pada' => 'datetime',
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

    public function detailPesanan(): BelongsTo
    {
        return $this->belongsTo(
            DetailPesanan::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
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
}
