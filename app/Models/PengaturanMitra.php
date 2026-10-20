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
        'minimal_nominal_dp',
        'persentase_dp',
        'dp_aktif',
        'notifikasi_aktif',
    ];

    protected $casts = [
        'minimal_quantity_dp' => 'integer',
        'minimal_nominal_dp' => 'integer',
        'persentase_dp' => 'decimal:2',
        'dp_aktif' => 'boolean',
        'notifikasi_aktif' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna'
        );
    }
}
