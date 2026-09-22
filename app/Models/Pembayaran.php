<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'dibayar_pada' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}
