<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $table = 'retur';

    protected $primaryKey = 'id_retur';

    protected $fillable = [
        'id_pesanan',
        'nomor_retur',
        'alasan_retur',
        'bukti_retur',
        'status_retur',
        'catatan_mitra',
        'status_pengembalian_dana',
        'nominal_pengembalian',
        'diajukan_pada',
    ];

    protected $casts = [
        'diajukan_pada' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    public function detail()
    {
        return $this->hasMany(DetailRetur::class, 'id_retur');
    }
}
