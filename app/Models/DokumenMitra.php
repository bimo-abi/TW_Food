<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenMitra extends Model
{
    protected $table = 'dokumen_mitra';

    protected $primaryKey = 'id_dokumen_mitra';

    protected $fillable = [
        'id_pengguna',
        'jenis_dokumen',
        'nomor_dokumen',
        'file_dokumen',
        'status_verifikasi',
        'catatan',
    ];

    public function pengguna()
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna',
            'id_pengguna'
        );
    }
}
