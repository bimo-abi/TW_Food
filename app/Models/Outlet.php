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
        'jam_buka',
        'jam_tutup',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'jam_buka' => 'datetime:H:i',
        'jam_tutup' => 'datetime:H:i',
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_outlet');
    }
}
