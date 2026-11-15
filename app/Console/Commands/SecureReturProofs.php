<?php

namespace App\Console\Commands;

use App\Models\BuktiRetur;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureReturProofs extends Command
{
    protected $signature = 'twfood:secure-retur-proofs';

    protected $description = 'Memindahkan bukti retur lama dari disk public ke disk local/private.';

    public function handle(): int
    {
        $moved = 0;
        $alreadyPrivate = 0;
        $failed = 0;

        BuktiRetur::query()->orderBy('id_bukti_retur')->chunkById(100, function ($items) use (&$moved, &$alreadyPrivate, &$failed) {
            foreach ($items as $item) {
                $path = $item->file_bukti;
                if (!is_string($path) || !str_starts_with($path, 'retur/')) {
                    $failed++;
                    $this->warn('Path bukti tidak valid untuk ID ' . $item->id_bukti_retur);
                    continue;
                }

                if (Storage::disk('local')->exists($path)) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    $alreadyPrivate++;
                    continue;
                }

                if (!Storage::disk('public')->exists($path)) {
                    $failed++;
                    $this->warn('File tidak ditemukan untuk ID ' . $item->id_bukti_retur);
                    continue;
                }

                $contents = Storage::disk('public')->get($path);
                if (!Storage::disk('local')->put($path, $contents)) {
                    $failed++;
                    $this->error('Gagal menyalin bukti ID ' . $item->id_bukti_retur);
                    continue;
                }

                Storage::disk('public')->delete($path);
                $moved++;
            }
        }, 'id_bukti_retur');

        $this->info("Bukti dipindahkan: {$moved}; sudah privat: {$alreadyPrivate}; gagal/perlu pemeriksaan: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
