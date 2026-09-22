<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKeuangan extends Model
{
    protected $table = 'transaksi_keuangan';

    protected $primaryKey = 'id_transaksi_keuangan';

    protected $fillable = [
        'id_pengguna',
        'jenis_transaksi',
        'kategori',
        'keterangan',
        'nominal',
        'tanggal_transaksi',
        'bukti_transaksi',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
