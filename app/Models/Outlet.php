<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $table = 'outlet';

    protected $primaryKey = 'id_outlet';

    protected $fillable = [
        'nama_outlet',
        'alamat',
        'nomor_telepon',
        'latitude',
        'longitude',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_outlet');
    }

    public function jamOperasional()
    {
        return $this->hasMany(
            JamOperasionalOutlet::class,
            'id_outlet',
            'id_outlet'
        );
    }
}
