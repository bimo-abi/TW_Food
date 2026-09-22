<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    protected $fillable = [
        'nomor_pesanan',
        'id_pengguna',
        'id_alamat',
        'id_outlet',
        'saluran_pesanan',
        'jenis_pesanan',
        'subtotal_produk',
        'diskon',
        'total_pesanan',
        'status_pembayaran',
        'status_pesanan',
        'kurir',
        'nomor_resi',
        'tautan_pelacakan',
        'catatan',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'id_outlet');
    }

    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan');
    }
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan');
    }

    public function retur()
    {
        return $this->hasMany(Retur::class, 'id_pesanan');
    }
}
