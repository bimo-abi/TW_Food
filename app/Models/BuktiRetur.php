<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiRetur extends Model
{
    protected $table = 'bukti_retur';

    protected $primaryKey = 'id_bukti_retur';

    protected $fillable = [
        'id_retur',
        'jenis_media',
        'file_bukti',
    ];

    public function retur()
    {
        return $this->belongsTo(
            Retur::class,
            'id_retur',
            'id_retur'
        );
    }
}
