<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromosiVarian extends Model
{
    protected $table = 'promosi_varian';

    protected $primaryKey = 'id_promosi_varian';

    protected $fillable = [
        'id_promosi',
        'id_varian',
    ];

    public function promosi()
    {
        return $this->belongsTo(Promosi::class, 'id_promosi');
    }

    public function varian()
    {
        return $this->belongsTo(VarianProduk::class, 'id_varian');
    }
}
