<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamOperasionalOutlet extends Model
{
    protected $table = 'jam_operasional_outlet';

    protected $primaryKey = 'id_jam_operasional';

    protected $fillable = [
        'id_outlet',
        'hari',
        'jam_buka',
        'jam_tutup',
        'tutup',
    ];

    protected $casts = [
        'tutup' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(
            Outlet::class,
            'id_outlet',
            'id_outlet'
        );
    }
    public function jamOperasional()
{
    return $this->hasMany(
        JamOperasionalOutlet::class,
        'id_outlet',
        'id_outlet'
    );
}
}
