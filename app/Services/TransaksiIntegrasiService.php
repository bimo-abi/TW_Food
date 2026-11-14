<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PercobaanRefund;
use App\Models\Pesanan;
use App\Models\ProduksiPreOrder;
use App\Models\Refund;
use App\Models\ReservasiStok;
use App\Models\RiwayatAktivitasTransaksi;
use App\Models\VarianProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Transaction as MidtransTransaction;
use Throwable;

/**
 * Integrasi stok reguler, produksi pre-order, dan refund Midtrans.
 * Dipanggil dari transaksi database webhook/admin agar perubahan lokal atomik.
 */
class TransaksiIntegrasiService
{
    /** Terapkan dampak operasional setelah satu transaksi pembayaran terverifikasi paid. */
    public function terapkanPembayaranBerhasil(Pesanan $pesanan, Pembayaran $pembayaran): void
    {
        if ($pesanan->jenis_pemenuhan === 'pre_order') {
            $this->mulaiProduksiPreOrder($pesanan, $pembayaran);
            return;
        }

        $this->konversiReservasiStok($pesanan, $pembayaran);
    }

    /**
     * Konversi reservasi reguler menjadi pengurangan stok fisik.
     * Status reservasi mencegah pengurangan stok ganda ketika webhook diulang.
     */
    private function konversiReservasiStok(Pesanan $pesanan, Pembayaran $pembayaran): void
    {
        $reservasi = ReservasiStok::where('id_pesanan', $pesanan->id_pesanan)
            ->lockForUpdate()
            ->get();

        if ($reservasi->contains('status_reservasi', 'dikonversi')) {
            return;
        }

        if ($reservasi->isEmpty()) {
            Log::critical('Pembayaran reguler terverifikasi tetapi data reservasi stok tidak ditemukan.', [
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => $pembayaran->id_pembayaran,
            ]);
            $this->catatAktivitas(
                $pesanan,
                $pembayaran,
                'reservasi_stok_tidak_ditemukan',
                null,
                null,
                'Pembayaran berhasil, tetapi reservasi stok tidak ditemukan. Perlu pemeriksaan Admin/Mitra.'
            );
            $this->notifikasiOperasional(
                $pesanan,
                'Pemeriksaan stok diperlukan',
                'Pembayaran pesanan ' . $pesanan->nomor_pesanan . ' berhasil, tetapi data reservasi stok tidak ditemukan.'
            );
            return;
        }

        foreach ($reservasi as $itemReservasi) {
            if ($itemReservasi->status_reservasi === 'dikonversi') {
                continue;
            }

            $reservasiTerlambat = $itemReservasi->status_reservasi === 'dilepas'
                || ($itemReservasi->kadaluarsa_pada && $itemReservasi->kadaluarsa_pada->isPast());

            $varian = VarianProduk::where('id_varian', $itemReservasi->id_varian)
                ->lockForUpdate()
                ->first();

            if (!$varian) {
                throw new \RuntimeException('Varian untuk reservasi stok tidak ditemukan.');
            }

            $jumlah = (int) $itemReservasi->jumlah;
            if ($jumlah < 1) {
                throw new \RuntimeException('Jumlah reservasi stok tidak valid.');
            }

            $stokDapatDipakai = (int) $varian->stok;
            if ($reservasiTerlambat) {
                $reservasiLain = (int) ReservasiStok::where('id_varian', $varian->id_varian)
                    ->where('id_pesanan', '!=', $pesanan->id_pesanan)
                    ->where('status_reservasi', 'aktif')
                    ->where('kadaluarsa_pada', '>', now())
                    ->sum('jumlah');
                $stokDapatDipakai -= $reservasiLain;
            }

            if ($stokDapatDipakai < $jumlah) {
                // Jangan pernah membuat stok negatif. Pembayaran tetap tercatat
                // melalui webhook terpisah; kasus ini harus diselesaikan operasional.
                Log::critical('Stok fisik kurang saat reservasi harus dikonversi setelah pembayaran berhasil.', [
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_varian' => $varian->id_varian,
                    'stok' => (int) $varian->stok,
                    'jumlah_reservasi' => $jumlah,
                ]);
                $this->catatAktivitas(
                    $pesanan,
                    $pembayaran,
                    'konversi_reservasi_stok_terkendala',
                    $itemReservasi->status_reservasi,
                    $itemReservasi->status_reservasi,
                    'Pembayaran berhasil tetapi stok fisik tidak mencukupi untuk konversi reservasi. Stok tidak dibuat negatif; perlu tindakan Admin/Mitra.'
                );
                $this->notifikasiOperasional(
                    $pesanan,
                    'Stok pesanan perlu diperiksa',
                    'Pesanan ' . $pesanan->nomor_pesanan . ' sudah dibayar, tetapi stok varian tidak mencukupi untuk konversi reservasi.'
                );
                continue;
            }

            $varian->decrement('stok', $jumlah);

            $statusReservasiSebelumnya = $itemReservasi->status_reservasi;
            $itemReservasi->status_reservasi = 'dikonversi';
            $itemReservasi->dikonversi_pada = now();
            $itemReservasi->dilepas_pada = null;
            $itemReservasi->alasan_pelepasan = null;
            $itemReservasi->save();

            $this->catatAktivitas(
                $pesanan,
                $pembayaran,
                'reservasi_stok_dikonversi',
                $statusReservasiSebelumnya,
                'dikonversi',
                'Reservasi stok dikonversi menjadi pengurangan stok fisik sebanyak ' . $jumlah . ' unit untuk varian ' . $varian->id_varian . '.'
            );
        }
    }

    /** Mulai kebutuhan produksi PO satu kali setelah DP atau pembayaran penuh diverifikasi. */
    private function mulaiProduksiPreOrder(Pesanan $pesanan, Pembayaran $pembayaran): void
    {
        $detailIds = $pesanan->detailPesanan()
            ->lockForUpdate()
            ->pluck('id_detail_pesanan');

        if ($detailIds->isEmpty()) {
            Log::critical('Pesanan pre-order tidak memiliki detail pesanan saat pembayaran terverifikasi.', [
                'id_pesanan' => $pesanan->id_pesanan,
            ]);
            return;
        }

        $produksiMenunggu = ProduksiPreOrder::whereIn('id_detail_pesanan', $detailIds)
            ->where('status_produksi', 'menunggu')
            ->lockForUpdate()
            ->get();

        foreach ($produksiMenunggu as $produksi) {
            $produksi->status_produksi = 'diproses';
            $produksi->mulai_produksi_pada = now();
            $produksi->save();
        }

        if ($produksiMenunggu->isNotEmpty()) {
            $this->catatAktivitas(
                $pesanan,
                $pembayaran,
                'produksi_pre_order_dimulai',
                'menunggu',
                'diproses',
                'Produksi pre-order dimulai setelah pembayaran ' . $pembayaran->jenis_pembayaran . ' diverifikasi.'
            );

            $this->notifikasiOperasional(
                $pesanan,
                'Produksi pre-order dimulai',
                'Produksi untuk pesanan ' . $pesanan->nomor_pesanan . ' dimulai setelah pembayaran diverifikasi.'
            );
        }
    }

    /** Lepaskan reservasi hanya jika seluruh transaksi pembayaran masih tidak menghasilkan dana masuk. */
    public function lepaskanReservasiJikaTidakAdaPembayaranBerhasil(Pesanan $pesanan, string $alasan): void
    {
        $adaDanaMasuk = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
            ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->where('status_pembayaran', 'paid')
            ->exists();

        $adaTransaksiPending = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
            ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->where('status_pembayaran', 'pending')
            ->exists();

        if ($adaDanaMasuk || $adaTransaksiPending) {
            return;
        }

        $reservasiAktif = ReservasiStok::where('id_pesanan', $pesanan->id_pesanan)
            ->where('status_reservasi', 'aktif')
            ->lockForUpdate()
            ->get();

        foreach ($reservasiAktif as $reservasi) {
            $reservasi->status_reservasi = 'dilepas';
            $reservasi->dilepas_pada = now();
            $reservasi->alasan_pelepasan = Str::limit($alasan, 255, '');
            $reservasi->save();

            $this->catatAktivitas(
                $pesanan,
                null,
                'reservasi_stok_dilepas',
                'aktif',
                'dilepas',
                'Reservasi stok dilepas: ' . Str::limit($alasan, 180, '')
            );
        }
    }

    /** Kirim refund ke Midtrans setelah transaksi pembatalan admin berhasil commit. */
    public function prosesRefund(int $idRefund): void
    {
        $data = DB::transaction(function () use ($idRefund) {
            $refund = Refund::where('id_refund', $idRefund)
                ->lockForUpdate()
                ->first();

            if (!$refund || !in_array($refund->status_refund, ['menunggu_diproses', 'perlu_diproses_ulang'], true)) {
                return null;
            }

            $pembayaran = Pembayaran::where('id_pembayaran', $refund->id_pembayaran)
                ->lockForUpdate()
                ->first();

            if (!$pembayaran || $pembayaran->status_pembayaran !== 'paid' || !$pembayaran->id_transaksi) {
                $refund->status_refund = 'gagal';
                $refund->save();
                return null;
            }

            $totalRefundDipesan = (int) Refund::where('id_pembayaran', $pembayaran->id_pembayaran)
                ->whereIn('status_refund', [
                    'menunggu_diproses',
                    'diproses',
                    'berhasil',
                    'perlu_diproses_ulang',
                ])
                ->sum('nominal_refund');

            if ($totalRefundDipesan > (int) $pembayaran->jumlah_pembayaran) {
                $refund->status_refund = 'gagal';
                $refund->alasan = trim((string) $refund->alasan . ' | Ditolak sistem: total refund melebihi dana transaksi.');
                $refund->save();
                Log::critical('Total refund yang tercatat melebihi nominal pembayaran.', [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'total_refund' => $totalRefundDipesan,
                    'jumlah_pembayaran' => (int) $pembayaran->jumlah_pembayaran,
                ]);
                return null;
            }

            $percobaan = PercobaanRefund::create([
                'id_refund' => $refund->id_refund,
                'kunci_percobaan' => (string) Str::uuid(),
                'penyedia_pembayaran' => $pembayaran->penyedia_pembayaran ?: 'Midtrans',
                'nominal_refund' => (int) $refund->nominal_refund,
                'status_percobaan' => 'menunggu',
                'respons_gateway' => [
                    'refund_key' => 'TWFOOD-REFUND-' . $refund->id_refund,
                ],
                'dimulai_pada' => now(),
            ]);

            $refund->status_refund = 'diproses';
            $refund->diproses_pada = now();
            $refund->save();

            $pesanan = Pesanan::where('id_pesanan', $refund->id_pesanan)->first();
            if ($pesanan) {
                $this->catatAktivitas(
                    $pesanan,
                    $pembayaran,
                    'refund_dikirim_ke_midtrans',
                    'menunggu_diproses',
                    'diproses',
                    'Permintaan refund dikirim ke Midtrans dan menunggu konfirmasi penyedia pembayaran.'
                );
            }

            return [
                'refund_id' => $refund->id_refund,
                'pembayaran_id' => $pembayaran->id_pembayaran,
                'id_transaksi' => $pembayaran->id_transaksi,
                'nominal' => (int) $refund->nominal_refund,
                'alasan' => (string) ($refund->alasan ?? 'Pengembalian dana TWFOOD'),
                'attempt_id' => $percobaan->id_percobaan_refund,
                'refund_key' => 'TWFOOD-REFUND-' . $refund->id_refund,
            ];
        });

        if (!$data) {
            return;
        }

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            if (!Config::$serverKey) {
                throw new \RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
            }

            $statusGateway = (array) MidtransTransaction::status($data['id_transaksi']);

            // Jika refund_key yang sama sudah tercatat di Midtrans, jangan kirim ulang
            // permintaan refund. Tunggu konfirmasi bank atau sinkronkan hasilnya.
            $daftarRefundGateway = array_map(
                fn($item) => (array) $item,
                (array) ($statusGateway['refunds'] ?? [])
            );
            $refundSudahDikirim = collect($daftarRefundGateway)->contains(
                fn($item) => (string) ($item['refund_key'] ?? '') === $data['refund_key']
            );

            if ($refundSudahDikirim) {
                DB::transaction(function () use ($data, $statusGateway) {
                    $attempt = PercobaanRefund::where('id_percobaan_refund', $data['attempt_id'])
                        ->lockForUpdate()
                        ->first();
                    if ($attempt) {
                        $attempt->respons_gateway = [
                            'refund_key' => $data['refund_key'],
                            'status_gateway' => $statusGateway,
                            'catatan' => 'Refund dengan refund_key yang sama sudah ditemukan di Midtrans; tidak dikirim ulang.',
                        ];
                        $attempt->save();
                    }
                });
                $this->sinkronkanRefundDariGateway(
                    Pembayaran::findOrFail($data['pembayaran_id']),
                    $statusGateway
                );
                return;
            }

            $paymentType = strtolower((string) ($statusGateway['payment_type'] ?? ''));
            $supportedPaymentTypes = [
                'credit_card',
                'gopay',
                'shopeepay',
                'dana',
                'ovo',
                'qris',
                'kredivo',
                'akulaku',
            ];

            if (($statusGateway['transaction_status'] ?? null) !== 'settlement') {
                throw new \RuntimeException('Midtrans belum menunjukkan status settlement. Refund belum dikirim.');
            }

            if (!in_array($paymentType, $supportedPaymentTypes, true)) {
                throw new \RuntimeException('Metode pembayaran ' . ($paymentType ?: 'tidak diketahui') . ' tidak mendukung refund otomatis Midtrans. Refund perlu diproses manual melalui penyedia pembayaran.');
            }

            $refundResponse = (array) MidtransTransaction::refund(
                $data['id_transaksi'],
                [
                    'refund_key' => $data['refund_key'],
                    'amount' => $data['nominal'],
                    'reason' => Str::limit($data['alasan'], 255, ''),
                ]
            );

            if (isset($refundResponse['status_code']) && (string) $refundResponse['status_code'] !== '200') {
                throw new \RuntimeException(
                    'Midtrans menolak permintaan refund: ' . ($refundResponse['status_message'] ?? $refundResponse['status_code'])
                );
            }

            DB::transaction(function () use ($data, $refundResponse, $statusGateway) {
                $attempt = PercobaanRefund::where('id_percobaan_refund', $data['attempt_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $attempt->respons_gateway = [
                    'refund_key' => $data['refund_key'],
                    'status_sebelum_refund' => $statusGateway,
                    'respons_refund' => $refundResponse,
                ];
                $attempt->referensi_gateway = $refundResponse['refund_chargeback_id'] ?? null;
                $attempt->save();

                // Request diterima Midtrans belum berarti dana telah diterima nasabah.
                // Status tetap menunggu sampai webhook/status menyertakan bank_confirmed_at.
                $refund = Refund::where('id_refund', $data['refund_id'])->lockForUpdate()->firstOrFail();
                $refund->status_refund = 'diproses';
                $refund->save();
            });
        } catch (Throwable $e) {
            Log::error('Pengiriman refund Midtrans gagal atau belum dapat dipastikan.', [
                'id_refund' => $data['refund_id'],
                'id_transaksi' => $data['id_transaksi'],
                'error' => $e->getMessage(),
            ]);

            DB::transaction(function () use ($data, $e) {
                $attempt = PercobaanRefund::where('id_percobaan_refund', $data['attempt_id'])
                    ->lockForUpdate()
                    ->first();

                if ($attempt) {
                    $attempt->status_percobaan = 'gagal';
                    $attempt->pesan_error = Str::limit($e->getMessage(), 500, '');
                    $attempt->selesai_pada = now();
                    $attempt->save();
                }

                $refund = Refund::where('id_refund', $data['refund_id'])->lockForUpdate()->first();
                if ($refund && $refund->status_refund !== 'berhasil') {
                    $refund->status_refund = 'perlu_diproses_ulang';
                    $refund->save();
                }
            });
        }
    }


    /**
     * Sinkronkan refund dari status API Midtrans.
     * Refund dianggap berhasil setelah tersedia bank_confirmed_at.
     */
    public function sinkronkanRefundDariGateway(
        Pembayaran $pembayaran,
        array $statusGateway
    ): void {
        $refundGateway = array_map(
            fn($item) => (array) $item,
            (array) ($statusGateway['refunds'] ?? [])
        );

        if ($refundGateway === []) {
            return;
        }

        DB::transaction(function () use ($pembayaran, $refundGateway) {
            $pembayaran = Pembayaran::where(
                'id_pembayaran',
                $pembayaran->id_pembayaran
            )->lockForUpdate()->firstOrFail();

            foreach ($refundGateway as $itemGateway) {
                $refundKeyGateway = (string) ($itemGateway['refund_key'] ?? '');
                $chargebackId = $itemGateway['refund_chargeback_id'] ?? null;
                $nominalGateway = (int) round(
                    (float) ($itemGateway['refund_amount'] ?? 0)
                );

                if ($nominalGateway < 1) {
                    continue;
                }

                // Ambil seluruh percobaan refund untuk pembayaran ini.
                $attempts = PercobaanRefund::whereHas(
                    'refund',
                    fn($query) => $query->where(
                        'id_pembayaran',
                        $pembayaran->id_pembayaran
                    )
                )->lockForUpdate()->get();

                // Prioritas pertama: cocokkan refund_key lokal.
                $attempt = $attempts->first(
                    fn($candidate) =>
                    (string) data_get(
                        $candidate->respons_gateway,
                        'refund_key'
                    ) === $refundKeyGateway
                        && $refundKeyGateway !== ''
                );

                // Prioritas kedua: cocokkan referensi gateway.
                if (!$attempt && $chargebackId !== null) {
                    $attempt = $attempts->first(
                        fn($candidate) =>
                        (string) $candidate->referensi_gateway
                            === (string) $chargebackId
                    );
                }

                $refund = $attempt
                    ? Refund::where('id_refund', $attempt->id_refund)
                    ->where('id_pembayaran', $pembayaran->id_pembayaran)
                    ->lockForUpdate()
                    ->first()
                    : null;

                // Fallback untuk refund yang diajukan manual dari Dashboard.
                if (!$refund) {
                    $refundCandidates = Refund::where(
                        'id_pembayaran',
                        $pembayaran->id_pembayaran
                    )
                        ->where('nominal_refund', $nominalGateway)
                        ->whereIn('status_refund', [
                            'menunggu_diproses',
                            'diproses',
                            'perlu_diproses_ulang',
                        ])
                        ->lockForUpdate()
                        ->get();

                    // Jangan menebak jika ditemukan lebih dari satu refund.
                    if ($refundCandidates->count() !== 1) {
                        Log::warning(
                            'Refund Midtrans tidak dapat dicocokkan secara unik.',
                            [
                                'id_pembayaran' => $pembayaran->id_pembayaran,
                                'refund_key_gateway' => $refundKeyGateway,
                                'chargeback_id' => $chargebackId,
                                'nominal_gateway' => $nominalGateway,
                                'jumlah_kandidat' => $refundCandidates->count(),
                            ]
                        );

                        continue;
                    }

                    $refund = $refundCandidates->first();

                    // Ambil percobaan terbaru untuk refund yang ditemukan.
                    $attempt = $attempts
                        ->where('id_refund', $refund->id_refund)
                        ->sortByDesc('id_percobaan_refund')
                        ->first();
                }

                if (!$refund || !$attempt) {
                    Log::warning(
                        'Data refund atau percobaannya tidak ditemukan.',
                        [
                            'id_pembayaran' => $pembayaran->id_pembayaran,
                            'refund_key_gateway' => $refundKeyGateway,
                            'nominal_gateway' => $nominalGateway,
                        ]
                    );

                    continue;
                }

                // Hindari pencatatan ulang jika refund sudah berhasil.
                if ($refund->status_refund === 'berhasil') {
                    continue;
                }

                $attempt->respons_gateway = array_merge(
                    (array) $attempt->respons_gateway,
                    [
                        'konfirmasi_gateway' => $itemGateway,
                    ]
                );

                if ($chargebackId !== null) {
                    $attempt->referensi_gateway = (string) $chargebackId;
                }

                // Belum ada konfirmasi bank: tetap menunggu.
                if (empty($itemGateway['bank_confirmed_at'])) {
                    $attempt->save();
                    continue;
                }

                // Pastikan nominal gateway sesuai dengan refund lokal.
                if ($nominalGateway !== (int) $refund->nominal_refund) {
                    Log::warning(
                        'Nominal refund Midtrans tidak sama dengan data lokal.',
                        [
                            'id_refund' => $refund->id_refund,
                            'nominal_gateway' => $nominalGateway,
                            'nominal_lokal' => (int) $refund->nominal_refund,
                        ]
                    );

                    continue;
                }

                $attempt->status_percobaan = 'berhasil';
                $attempt->selesai_pada = now();
                $attempt->save();

                $statusSebelumRefund = $refund->status_refund;

                $refund->status_refund = 'berhasil';
                $refund->selesai_pada = now();
                $refund->save();

                $pesanan = Pesanan::where(
                    'id_pesanan',
                    $refund->id_pesanan
                )->lockForUpdate()->first();

                if (!$pesanan) {
                    continue;
                }

                $this->catatAktivitas(
                    $pesanan,
                    $pembayaran,
                    'refund_berhasil',
                    $statusSebelumRefund,
                    'berhasil',
                    'Midtrans mengonfirmasi refund melalui bank_confirmed_at.'
                );

                $this->notifikasiOperasional(
                    $pesanan,
                    'Refund berhasil dikonfirmasi',
                    'Refund sebesar Rp'
                        . number_format(
                            (int) $refund->nominal_refund,
                            0,
                            ',',
                            '.'
                        )
                        . ' untuk pesanan '
                        . $pesanan->nomor_pesanan
                        . ' telah dikonfirmasi oleh penyedia pembayaran.'
                );

                // Periksa apakah seluruh transaksi pembayaran ini sudah direfund.
                $totalRefundUntukPembayaran = (int) Refund::where(
                    'id_pembayaran',
                    $pembayaran->id_pembayaran
                )
                    ->where('status_refund', 'berhasil')
                    ->sum('nominal_refund');

                if (
                    $totalRefundUntukPembayaran
                    >= (int) $pembayaran->jumlah_pembayaran
                ) {
                    $pembayaran->status_pembayaran = 'refunded';
                    $pembayaran->save();
                }

                // Periksa apakah seluruh dana yang diterima sudah dikembalikan.
                $totalDanaDiterima = (int) Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->whereIn('jenis_pembayaran', [
                        'penuh',
                        'dp',
                        'pelunasan',
                    ])
                    ->whereIn('status_pembayaran', [
                        'paid',
                        'refunded',
                    ])
                    ->sum('jumlah_pembayaran');

                $totalRefundBerhasil = (int) Refund::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->where('status_refund', 'berhasil')
                    ->sum('nominal_refund');

                if (
                    $totalDanaDiterima > 0
                    && $totalRefundBerhasil >= $totalDanaDiterima
                    && $pesanan->status_pembayaran !== 'dikembalikan'
                ) {
                    $statusSebelum = $pesanan->status_pembayaran;

                    $pesanan->status_pembayaran = 'dikembalikan';
                    $pesanan->save();

                    $this->catatAktivitas(
                        $pesanan,
                        $pembayaran,
                        'seluruh_dana_pesanan_dikembalikan',
                        $statusSebelum,
                        'dikembalikan',
                        'Seluruh dana pembayaran pesanan telah dikonfirmasi dikembalikan.'
                    );

                    $this->notifikasiOperasional(
                        $pesanan,
                        'Seluruh dana pesanan dikembalikan',
                        'Seluruh dana yang telah dibayarkan untuk pesanan '
                            . $pesanan->nomor_pesanan
                            . ' sudah dikonfirmasi dikembalikan.'
                    );
                }
            }
        });
    }


    private function catatAktivitas(
        Pesanan $pesanan,
        ?Pembayaran $pembayaran,
        string $jenis,
        ?string $sebelum,
        ?string $sesudah,
        string $deskripsi
    ): void {
        RiwayatAktivitasTransaksi::create([
            'id_pengguna' => null,
            'jenis_aktor' => 'sistem',
            'id_pesanan' => $pesanan->id_pesanan,
            'id_pembayaran' => $pembayaran?->id_pembayaran,
            'jenis_aktivitas' => $jenis,
            'status_sebelum' => $sebelum,
            'status_sesudah' => $sesudah,
            'deskripsi' => $deskripsi,
            'created_at' => now(),
        ]);
    }

    private function notifikasiOperasional(Pesanan $pesanan, string $judul, string $pesan): void
    {
        $ids = collect([$pesanan->id_pengguna]);
        $idMitra = DB::table('pengaturan_mitra')
            ->join('pengguna', 'pengguna.id_pengguna', '=', 'pengaturan_mitra.id_pengguna')
            ->where('pengguna.peran', 'mitra')
            ->where('pengguna.status_aktif', true)
            ->pluck('pengguna.id_pengguna');

        foreach ($ids->merge($idMitra)->unique() as $idPengguna) {
            Notifikasi::create([
                'id_pengguna' => $idPengguna,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis' => 'pesanan',
                'jenis_referensi' => 'pesanan',
                'id_referensi' => $pesanan->id_pesanan,
                'sudah_dibaca' => false,
                'created_at' => now(),
            ]);
        }
    }
}
