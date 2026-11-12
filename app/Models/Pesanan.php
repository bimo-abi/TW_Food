<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    protected $fillable = [
        'nomor_pesanan',
        'id_pengguna',
        'id_alamat',
        'id_outlet',

        'nama_penerima_saat_pesan',
        'nomor_telepon_saat_pesan',
        'alamat_lengkap_saat_pesan',
        'kota_saat_pesan',
        'provinsi_saat_pesan',
        'kode_pos_saat_pesan',

        'saluran_pesanan',
        'jenis_pesanan',
        'jenis_pemenuhan',

        'subtotal_produk',
        'diskon',
        'total_pesanan',

        'menggunakan_dp',
        'dasar_perhitungan_dp',
        'persentase_dp',
        'nominal_dp',

        'status_pembayaran',
        'status_pesanan',
        'diterima_pada',
        'estimasi_selesai',

        'kurir',
        'nomor_resi',
        'tautan_pelacakan',
        'catatan',
    ];

    protected $casts = [
        'menggunakan_dp' => 'boolean',
        'dasar_perhitungan_dp' => 'integer',
        'persentase_dp' => 'decimal:2',
        'nominal_dp' => 'integer',

        'subtotal_produk' => 'integer',
        'diskon' => 'integer',
        'total_pesanan' => 'integer',

        'diterima_pada' => 'datetime',
        'estimasi_selesai' => 'date',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            Pengguna::class,
            'id_pengguna',
            'id_pengguna'
        );
    }

    public function alamat(): BelongsTo
    {
        return $this->belongsTo(
            Alamat::class,
            'id_alamat',
            'id_alamat'
        );
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(
            Outlet::class,
            'id_outlet',
            'id_outlet'
        );
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(
            DetailPesanan::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(
            Pembayaran::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function retur(): HasMany
    {
        return $this->hasMany(
            Retur::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function reservasiStok(): HasMany
    {
        return $this->hasMany(
            ReservasiStok::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function refund(): HasMany
    {
        return $this->hasMany(
            Refund::class,
            'id_pesanan',
            'id_pesanan'
        );
    }

    public function riwayatAktivitas(): HasMany
    {
        return $this->hasMany(
            RiwayatAktivitasTransaksi::class,
            'id_pesanan',
            'id_pesanan'
        );
    }
}
