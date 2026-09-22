<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'nomor_telepon',
        'peran',
        'jenis_pelanggan',
        'foto_profil',
        'status_aktif',
    ];

    protected $hidden = [
        'kata_sandi',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function alamat()
    {
        return $this->hasMany(Alamat::class, 'id_pengguna');
    }

    public function profilBisnis()
    {
        return $this->hasOne(ProfilBisnis::class, 'id_pengguna');
    }

    public function keranjang()
    {
        return $this->hasMany(Keranjang::class, 'id_pengguna');
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_pengguna');
    }

    public function transaksiKeuangan()
    {
        return $this->hasMany(
            TransaksiKeuangan::class,
            'id_pengguna'
        );
    }

    public function notifikasi()
    {
        return $this->hasMany(
            Notifikasi::class,
            'id_pengguna'
        );
    }

    public function pengaturanMitra()
    {
        return $this->hasOne(
            PengaturanMitra::class,
            'id_pengguna'
        );
    }
}
