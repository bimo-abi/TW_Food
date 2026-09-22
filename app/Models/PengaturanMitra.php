<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanMitra extends Model
{
    protected $table = 'pengaturan_mitra';

    protected $primaryKey = 'id_pengaturan';

    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'minimal_quantity_dp',
        'persentase_dp',
        'notifikasi_aktif',
        'updated_at',
    ];

    protected $casts = [
        'persentase_dp' => 'decimal:2',
        'notifikasi_aktif' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
