<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKonten extends Model
{
    protected $table = 'kategori_konten';

    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];


    public function kontenBeranda()
    {
        return $this->hasMany(
            KontenBeranda::class,
            'id_kategori',
            'id_kategori'
        );
    }
}
