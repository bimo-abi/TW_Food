<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    public $timestamps = false;

    protected $fillable = [
        'id_pengguna',
        'judul',
        'pesan',
        'jenis',
        'jenis_referensi',
        'id_referensi',
        'sudah_dibaca',
        'diarsipkan_pada',
        'created_at',
    ];

    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'created_at' => 'datetime',
        'diarsipkan_pada' => 'datetime',
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
