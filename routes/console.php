<?php

use App\Models\Pembayaran;
use App\Services\MidtransPaymentReconciliationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('twfood:rekonsiliasi-pembayaran', function () {
    if (!config('services.midtrans.server_key')) {
        $this->error('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        return 1;
    }

    $jumlahDiperiksa = 0;
    $jumlahDiperbarui = 0;
    $jumlahMasihPending = 0;
    $jumlahDiabaikan = 0;
    $jumlahTidakValid = 0;
    $jumlahGagal = 0;

    Pembayaran::query()
        ->where('status_pembayaran', 'pending')
        ->whereNotNull('id_transaksi')
        ->orderBy('id_pembayaran')
        ->chunkById(100, function ($pembayaranList) use (
            &$jumlahDiperiksa,
            &$jumlahDiperbarui,
            &$jumlahMasihPending,
            &$jumlahDiabaikan,
            &$jumlahTidakValid,
            &$jumlahGagal
        ) {
            foreach ($pembayaranList as $pembayaran) {
                $jumlahDiperiksa++;

                try {
                    // Tidak membuat payload/signature/request webhook buatan.
                    // Service mengambil status langsung dari Midtrans Status API.
                    $hasil = app(MidtransPaymentReconciliationService::class)
                        ->reconcile($pembayaran);

                    $statusLokal = $pembayaran->fresh()->status_pembayaran;

                    if ($hasil['status'] === 'applied') {
                        $jumlahDiperbarui++;
                        $this->info("{$pembayaran->id_transaksi}: status diperbarui menjadi {$statusLokal}.");
                    } elseif ($hasil['status'] === 'invalid') {
                        $jumlahTidakValid++;
                        $this->error("{$pembayaran->id_transaksi}: {$hasil['message']}");
                    } elseif ($statusLokal === 'pending') {
                        $jumlahMasihPending++;
                        $this->warn("{$pembayaran->id_transaksi}: masih pending di Midtrans.");
                    } else {
                        $jumlahDiabaikan++;
                        $this->line("{$pembayaran->id_transaksi}: tidak ada perubahan ({$statusLokal}).");
                    }
                } catch (\Throwable $e) {
                    $jumlahGagal++;
                    Log::error('Rekonsiliasi pembayaran gagal.', [
                        'id_pembayaran' => $pembayaran->id_pembayaran,
                        'id_transaksi' => $pembayaran->id_transaksi,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("{$pembayaran->id_transaksi}: gagal diperiksa melalui Midtrans Status API.");
                }
            }
        }, 'id_pembayaran');

    $this->newLine();
    $this->info('Rekonsiliasi pembayaran selesai.');
    $this->line("Diperiksa: {$jumlahDiperiksa}");
    $this->line("Status diperbarui: {$jumlahDiperbarui}");
    $this->line("Masih pending: {$jumlahMasihPending}");
    $this->line("Diabaikan: {$jumlahDiabaikan}");
    $this->line("Data gateway tidak valid: {$jumlahTidakValid}");
    $this->line("Gagal diperiksa: {$jumlahGagal}");

    return ($jumlahGagal + $jumlahTidakValid) > 0 ? 1 : 0;
})->purpose('Memeriksa ulang seluruh pembayaran pending melalui Midtrans Status API');
