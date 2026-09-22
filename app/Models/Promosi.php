<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promosi extends Model
{
    protected $table = 'promosi';

    protected $primaryKey = 'id_promosi';

    protected $fillable = [
        'nama_promosi',
        'deskripsi',
        'jenis_promosi',
        'nilai_persen',
        'nilai_nominal',
        'minimal_pembelian',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_aktif',
    ];

    protected $casts = [
        'nilai_persen' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'status_aktif' => 'boolean',
    ];

    public function varian()
    {
        return $this->hasMany(PromosiVarian::class, 'id_promosi');
    }
}
