<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Retur extends Model
{
    protected $table = 'retur';

    protected $primaryKey = 'id_retur';

    protected $fillable = [
        'id_pesanan',
        'nomor_retur',
        'alasan_retur',
        'status_retur',
        'catatan_mitra',
        'status_pengembalian_dana',
        'nominal_pengembalian',
        'diajukan_pada',
        'disetujui_pada',
        'barang_diterima_pada',
        'diperiksa_pada',
        'selesai_pada',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'nominal_pengembalian' => 'integer',
            'diajukan_pada' => 'datetime',
            'disetujui_pada' => 'datetime',
            'barang_diterima_pada' => 'datetime',
            'diperiksa_pada' => 'datetime',
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

    public function detailRetur(): HasMany
    {
        return $this->hasMany(
            DetailRetur::class,
            'id_retur',
            'id_retur'
        );
    }

    public function buktiRetur(): HasMany
    {
        return $this->hasMany(
            BuktiRetur::class,
            'id_retur',
            'id_retur'
        );
    }

    public function refund(): HasMany
    {
        return $this->hasMany(
            Refund::class,
            'id_retur',
            'id_retur'
        );
    }
}
