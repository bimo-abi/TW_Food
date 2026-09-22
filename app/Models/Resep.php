<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 'resep';

    protected $primaryKey = 'id_resep';

    protected $fillable = [
        'judul',
        'deskripsi',
        'foto',
        'bahan',
        'langkah_pembuatan',
        'waktu_memasak',
    ];

    public function produk()
    {
        return $this->hasMany(ResepProduk::class, 'id_resep');
    }
}
