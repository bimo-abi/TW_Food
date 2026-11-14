<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'jumlah_diminta',
        'jumlah_disetujui',
        'diskon_dialokasikan',
        'status_kelayakan_stok',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_diretur' => 'integer',
            'jumlah_diminta' => 'integer',
            'jumlah_disetujui' => 'integer',
            'nominal_refund' => 'integer',
            'diskon_dialokasikan' => 'integer',
        ];
    }

    public function retur(): BelongsTo
    {
        return $this->belongsTo(
            Retur::class,
            'id_retur',
            'id_retur'
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
}
