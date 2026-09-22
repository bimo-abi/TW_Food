<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepProduk extends Model
{
    protected $table = 'resep_produk';

    protected $primaryKey = 'id_resep_produk';

    protected $fillable = [
        'id_resep',
        'id_produk',
        'jumlah',
    ];

    public function resep()
    {
        return $this->belongsTo(Resep::class, 'id_resep');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
