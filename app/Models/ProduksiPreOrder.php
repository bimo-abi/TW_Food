<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduksiPreOrder extends Model
{
    protected $table = 'produksi_pre_order';

    protected $primaryKey = 'id_produksi_pre_order';

    protected $fillable = [
        'id_detail_pesanan',
        'jumlah_produksi',
        'status_produksi',
        'estimasi_selesai',
        'mulai_produksi_pada',
        'selesai_produksi_pada',
        'catatan_produksi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_produksi' => 'integer',
            'estimasi_selesai' => 'datetime',
            'mulai_produksi_pada' => 'datetime',
            'selesai_produksi_pada' => 'datetime',
        ];
    }

    public function detailPesanan(): BelongsTo
    {
        return $this->belongsTo(
            DetailPesanan::class,
            'id_detail_pesanan',
            'id_detail_pesanan'
        );
    }
}
