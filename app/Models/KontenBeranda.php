<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KontenBeranda extends Model
{
    protected $table = 'konten_beranda';

    protected $primaryKey = 'id_konten';

    protected $fillable = [
        'id_kategori',
        'judul',
        'deskripsi',
        'gambar',
        'status_aktif',
        'urutan',
    ];

    protected $casts = [
        'id_kategori' => 'integer',
        'status_aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriKonten::class,
            'id_kategori',
            'id_kategori'
        );
    }
}
