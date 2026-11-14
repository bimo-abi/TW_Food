<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Refund extends Model
{
    protected $table = 'refund';

    protected $primaryKey = 'id_refund';

    protected $fillable = [
        'id_pesanan',
        'id_pembayaran',
        'id_retur',
        'jenis_refund',
        'nominal_refund',
        'status_refund',
        'alasan',
        'id_pengguna_penyetuju',
        'disetujui_pada',
        'diproses_pada',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'nominal_refund' => 'integer',
            'disetujui_pada' => 'datetime',
            'diproses_pada' => 'datetime',
            'selesai_pada' => 'datetime',
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

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(
            Pembayaran::class,
            'id_pembayaran',
            'id_pembayaran'
        );
    }

    public function retur(): BelongsTo
    {
        return $this->belongsTo(
            Retur::class,
            'id_retur',
            'id_retur'
        );
    }

    public function penggunaPenyetuju(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna_penyetuju',
            'id_pengguna'
        );
    }

    public function percobaanRefund(): HasMany
    {
        return $this->hasMany(
            PercobaanRefund::class,
            'id_refund',
            'id_refund'
        );
    }
}
