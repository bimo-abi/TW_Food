<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontenBeranda extends Model
{
    protected $table = 'konten_beranda';

    protected $primaryKey = 'id_konten';

    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'jenis_konten',
        'status_aktif',
        'urutan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
