<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatAktivitasTransaksi extends Model
{
    protected $table = 'riwayat_aktivitas_transaksi';

    protected $primaryKey = 'id_riwayat_aktivitas';

    // Tabel hanya memiliki created_at, tanpa updated_at.
    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'jenis_aktor',
        'id_pesanan',
        'id_pembayaran',
        'jenis_aktivitas',
        'status_sebelum',
        'status_sesudah',
        'deskripsi',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna',
            'id_pengguna'
        );
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
}
