<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanMitra extends Model
{
    protected $table = 'pengaturan_mitra';

    protected $primaryKey = 'id_pengaturan';

    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'minimal_nominal_dp',
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

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna',
            'id_pengguna'
        );
    }
}
