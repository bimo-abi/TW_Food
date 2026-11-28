<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\PercobaanRefund;
use App\Models\Refund;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MidtransRefundService
{
    /**
     * Mengajukan refund Core API Midtrans. Pemanggilan jaringan dilakukan di luar
     * transaksi DB; refund_key disimpan lebih dahulu agar permintaan dapat direkonsiliasi.
     */
    public function proses(int $idRefund, int $idAktor): array
    {
        $context = DB::transaction(function () use ($idRefund, $idAktor) {
            $refund = Refund::query()->whereKey($idRefund)->lockForUpdate()->firstOrFail();
            $pembayaran = Pembayaran::query()->whereKey($refund->id_pembayaran)->lockForUpdate()->firstOrFail();

            if ($refund->status_refund === 'berhasil') {
                throw ValidationException::withMessages(['refund' => ['Refund ini sudah berhasil.']]);
            }
            if (!in_array($refund->status_refund, ['menunggu_diproses', 'gagal', 'perlu_diproses_ulang'], true)) {
                throw ValidationException::withMessages(['refund' => ['Status refund belum dapat diproses otomatis.']]);
            }
            if ($pembayaran->penyedia_pembayaran !== 'Midtrans' || !$pembayaran->id_transaksi) {
                throw ValidationException::withMessages(['pembayaran' => ['Refund otomatis hanya tersedia untuk transaksi Midtrans yang memiliki ID transaksi.']]);
            }
            if ($pembayaran->status_pembayaran !== 'paid') {
                throw ValidationException::withMessages(['pembayaran' => ['Pembayaran sumber harus berstatus paid sebelum refund diajukan.']]);
            }
            if ((int) $refund->nominal_refund < 1 || (int) $refund->nominal_refund > (int) $pembayaran->jumlah_pembayaran) {
                throw ValidationException::withMessages(['nominal_refund' => ['Nominal refund tidak valid untuk transaksi pembayaran sumber.']]);
            }

            $allocated = (int) Refund::query()
                ->where('id_pembayaran', $pembayaran->id_pembayaran)
                ->whereIn('status_refund', ['menunggu_diproses', 'diproses', 'berhasil', 'gagal', 'perlu_diproses_ulang'])
                ->sum('nominal_refund');
            if ($allocated > (int) $pembayaran->jumlah_pembayaran) {
                throw ValidationException::withMessages(['nominal_refund' => ['Total alokasi refund melampaui pembayaran sumber.']]);
            }

            $lastAttempt = PercobaanRefund::query()->where('id_refund', $refund->id_refund)
                ->latest('id_percobaan_refund')->first();
            $oldResponse = (array) ($lastAttempt?->respons_gateway ?? []);
            if ($lastAttempt && ($oldResponse['refund_key'] ?? null) && $lastAttempt->status_percobaan === 'menunggu') {
                throw ValidationException::withMessages(['refund' => ['Percobaan sebelumnya masih menunggu kepastian gateway. Sinkronkan Status API terlebih dahulu; jangan kirim ulang permintaan.']]);
            }

            $key = 'twf-' . $refund->id_refund . '-' . Str::lower(Str::random(20));
            $attempt = PercobaanRefund::create([
                'id_refund' => $refund->id_refund,
                'kunci_percobaan' => $key,
                'penyedia_pembayaran' => 'Midtrans Core API',
                'nominal_refund' => $refund->nominal_refund,
                'status_percobaan' => 'menunggu',
                'respons_gateway' => ['refund_key' => $key, 'mode' => 'otomatis', 'diajukan_oleh' => $idAktor],
                'dimulai_pada' => now(),
            ]);
            $refund->status_refund = 'diproses';
            $refund->diproses_pada = $refund->diproses_pada ?? now();
            $refund->save();

            return compact('refund', 'pembayaran', 'attempt', 'key');
        });

        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, 'Server key Midtrans belum dikonfigurasi.');
            throw ValidationException::withMessages(['midtrans' => ['Server key Midtrans belum dikonfigurasi.']]);
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        try {
            // Verifikasi status terlebih dahulu: Refund API hanya untuk Settlement.
            try {
                $statusResponse = Http::acceptJson()->withBasicAuth($serverKey, '')
                    ->timeout(20)->get($baseUrl . '/v2/' . rawurlencode($context['pembayaran']->id_transaksi) . '/status');
            } catch (ConnectionException $e) {
                // Refund POST has not been sent, so this attempt can safely be marked
                // failed and retried with a fresh key after connectivity is restored.
                $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, 'Status API Midtrans tidak dapat dihubungi sebelum permintaan refund dikirim.');
                throw ValidationException::withMessages(['midtrans' => ['Status API Midtrans tidak dapat dihubungi. Tidak ada permintaan refund yang dikirim; silakan coba lagi.']]);
            }
            if (!$statusResponse->successful()) {
                $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, 'Status API Midtrans mengembalikan HTTP ' . $statusResponse->status() . '.');
                throw ValidationException::withMessages(['midtrans' => ['Status transaksi Midtrans gagal diverifikasi. Tidak ada permintaan refund yang dikirim.']]);
            }
            $gatewayStatus = (array) $statusResponse->json();
            if (($gatewayStatus['transaction_status'] ?? null) !== 'settlement') {
                $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, 'Transaksi Midtrans tidak berstatus settlement.');
                throw ValidationException::withMessages(['midtrans' => ['Transaksi Midtrans belum berstatus settlement sehingga Refund API tidak dapat digunakan.']]);
            }

            $paymentType = strtolower((string) ($gatewayStatus['payment_type'] ?? ''));
            if (in_array($paymentType, ['shopeepay', 'ovo'], true)
                && (int) $context['refund']->nominal_refund !== (int) $context['pembayaran']->jumlah_pembayaran) {
                $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, 'Metode pembayaran tidak mendukung partial refund melalui endpoint ini.');
                throw ValidationException::withMessages(['nominal_refund' => ['Metode pembayaran ini tidak mendukung partial refund melalui Refund API.']]);
            }

            $request = [
                'refund_key' => $context['key'],
                'amount' => (int) $context['refund']->nominal_refund,
                'reason' => Str::limit((string) ($context['refund']->alasan ?: 'Refund TWFOOD'), 255, ''),
            ];
            $response = Http::acceptJson()->asJson()->withBasicAuth($serverKey, '')
                ->timeout(25)->post($baseUrl . '/v2/' . rawurlencode($context['pembayaran']->id_transaksi) . '/refund', $request);

            $body = (array) $response->json();
            $this->saveGatewayResponse($context, $body + ['http_status' => $response->status()]);
            if (!$response->successful()) {
                $this->markDefiniteFailure($context['refund']->id_refund, $context['attempt']->id_percobaan_refund, (string) ($body['status_message'] ?? 'Midtrans menolak permintaan refund.'));
                throw ValidationException::withMessages(['midtrans' => [(string) ($body['status_message'] ?? 'Permintaan refund ditolak Midtrans.')]]);
            }

            // Accepted is not the same as bank-confirmed. Status API reconciliation
            // changes this attempt/refund to berhasil only when bank_confirmed_at exists.
            return [
                'refund' => $context['refund']->fresh(['pembayaran', 'percobaanRefund']),
                'attempt' => $context['attempt']->fresh(),
                'gateway' => $body,
                'message' => 'Permintaan refund diterima Midtrans dan menunggu konfirmasi bank.',
            ];
        } catch (ConnectionException $e) {
            // A timeout is ambiguous: keep the same refund_key and do not retry blindly.
            $this->keepPending($context, ['koneksi_error' => $e->getMessage()]);
            return [
                'refund' => $context['refund']->fresh(['pembayaran', 'percobaanRefund']),
                'attempt' => $context['attempt']->fresh(),
                'gateway' => null,
                'message' => 'Koneksi terputus saat mengirim refund. Status tetap menunggu verifikasi; jangan ajukan ulang sebelum Status API diperiksa.',
            ];
        }
    }

    private function saveGatewayResponse(array $context, array $body): void
    {
        DB::transaction(function () use ($context, $body) {
            $attempt = PercobaanRefund::query()->whereKey($context['attempt']->id_percobaan_refund)->lockForUpdate()->firstOrFail();
            $attempt->respons_gateway = array_merge((array) $attempt->respons_gateway, ['respons_pengajuan' => $body]);
            if (!empty($body['refund_chargeback_id'])) $attempt->referensi_gateway = (string) $body['refund_chargeback_id'];
            $attempt->save();
        });
    }

    private function keepPending(array $context, array $details): void
    {
        DB::transaction(function () use ($context, $details) {
            $attempt = PercobaanRefund::query()->whereKey($context['attempt']->id_percobaan_refund)->lockForUpdate()->firstOrFail();
            $attempt->respons_gateway = array_merge((array) $attempt->respons_gateway, ['ketidakpastian' => $details]);
            $attempt->save();
        });
    }

    private function markDefiniteFailure(int $refundId, int $attemptId, string $message): void
    {
        DB::transaction(function () use ($refundId, $attemptId, $message) {
            $refund = Refund::query()->whereKey($refundId)->lockForUpdate()->firstOrFail();
            $attempt = PercobaanRefund::query()->whereKey($attemptId)->lockForUpdate()->firstOrFail();
            $attempt->status_percobaan = 'gagal';
            $attempt->pesan_error = $message;
            $attempt->selesai_pada = now();
            $attempt->save();
            $refund->status_refund = 'gagal';
            $refund->save();
        });
    }
}
