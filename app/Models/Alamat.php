<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alamat extends Model
{
    protected $table = 'alamat';

    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_pengguna',
        'label',
        'nama_penerima',
        'nomor_telepon',
        'alamat_lengkap',
        'kota',
        'provinsi',
        'kode_pos',
        'latitude',
        'longitude',
        'alamat_utama',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'alamat_utama' => 'boolean',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_alamat');
    }
}
