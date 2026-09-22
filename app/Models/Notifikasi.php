<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'id_referensi',
        'sudah_dibaca',
        'created_at',
    ];

    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
