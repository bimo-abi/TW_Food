<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilBisnis extends Model
{
    protected $table = 'profil_bisnis';

    protected $primaryKey = 'id_profil_bisnis';

    protected $fillable = [
        'id_pengguna',
        'jenis_bisnis',
        'nama_bisnis',
        'nama_pic',
        'nomor_telepon_bisnis',
        'alamat_bisnis',
        'kota',
        'provinsi',
        'kode_pos',
        'nib',
        'npwp',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
