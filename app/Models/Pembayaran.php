<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pesanan',
        'jenis_pembayaran',
        'penyedia_pembayaran',
        'id_transaksi',
        'metode_pembayaran',
        'jumlah_pembayaran',
        'status_pembayaran',
        'dibayar_pada',
        'respons_gateway',
        'kunci_idempotensi',
        'waktu_kadaluarsa',
        'dikonfirmasi_oleh',
        'dikonfirmasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_pembayaran' => 'integer',
            'respons_gateway' => 'array',
            'dibayar_pada' => 'datetime',
            'waktu_kadaluarsa' => 'datetime',
            'dikonfirmasi_pada' => 'datetime',
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

    public function pengonfirmasi(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'dikonfirmasi_oleh',
            'id_pengguna'
        );
    }

    public function refund(): HasMany
    {
        return $this->hasMany(
            Refund::class,
            'id_pembayaran',
            'id_pembayaran'
        );
    }

    public function riwayatAktivitas(): HasMany
    {
        return $this->hasMany(
            RiwayatAktivitasTransaksi::class,
            'id_pembayaran',
            'id_pembayaran'
        );
    }
}
