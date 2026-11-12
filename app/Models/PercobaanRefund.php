<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PercobaanRefund extends Model
{
    protected $table = 'percobaan_refund';

    protected $primaryKey = 'id_percobaan_refund';

    protected $fillable = [
        'id_refund',
        'kunci_percobaan',
        'penyedia_pembayaran',
        'nominal_refund',
        'status_percobaan',
        'referensi_gateway',
        'respons_gateway',
        'pesan_error',
        'dimulai_pada',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'nominal_refund' => 'integer',
            'respons_gateway' => 'array',
            'dimulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(
            Refund::class,
            'id_refund',
            'id_refund'
        );
    }
}
