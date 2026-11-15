<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiRetur extends Model
{
    protected $table = 'bukti_retur';

    protected $primaryKey = 'id_bukti_retur';

    protected $hidden = ['file_bukti'];

    protected $appends = ['url_bukti'];

    protected $fillable = [
        'id_retur',
        'jenis_media',
        'file_bukti',
    ];

    public function getUrlBuktiAttribute(): string
    {
        return route('retur.bukti', ['id_bukti_retur' => $this->id_bukti_retur]);
    }

    public function retur(): BelongsTo
    {
        return $this->belongsTo(
            Retur::class,
            'id_retur',
            'id_retur'
        );
    }
}
