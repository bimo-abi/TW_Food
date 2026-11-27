<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Laporan\ArrayReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanFilterRequest;
use App\Services\Laporan\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class LaporanExportController extends Controller
{
    public function download(string $jenis, string $format, LaporanFilterRequest $request, LaporanService $service)
    {
        abort_unless(in_array($jenis, ['ringkasan','penjualan','keuangan','transaksi','produk-stok','pelanggan','retur-refund','produksi'], true), 404);
        abort_unless(in_array($format, ['xlsx','pdf','csv'], true), 404);

        $filters = $request->filters();
        $rows = $service->exportRows($jenis, $filters);
        $filename = 'laporan-twfood-'.$jenis.'-'.$filters['tanggal_mulai'].'-'.$filters['tanggal_selesai'];
        $summary = $service->dashboard($filters);
        $title = strtoupper(str_replace('-', ' ', $jenis));

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");
                if ($rows !== []) {
                    fputcsv($output, array_keys($rows[0]), ';');
                    foreach ($rows as $row) {
                        fputcsv($output, array_values($row), ';');
                    }
                }
                fclose($output);
            }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        try {
            if ($format === 'xlsx') {
                if (!class_exists(Excel::class) || !class_exists(ArrayReportExport::class)) {
                    throw new \RuntimeException('Laravel Excel belum terpasang. Jalankan composer require maatwebsite/excel:^4.0.');
                }

                return Excel::download(new ArrayReportExport($rows), $filename.'.xlsx');
            }

            if (!class_exists(Pdf::class)) {
                throw new \RuntimeException('DomPDF belum terpasang. Jalankan composer require barryvdh/laravel-dompdf:^3.1.');
            }

            return Pdf::loadView('admin.laporan.pdf.report', compact('rows','summary','filters','title'))
                ->setPaper('a4', 'landscape')
                ->download($filename.'.pdf');
        } catch (Throwable $e) {
            Log::error('Ekspor laporan TWFOOD gagal.', [
                'jenis' => $jenis,
                'format' => $format,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unduhan '.$format.' gagal diproses. Periksa storage/logs/laravel.log; detail teknis sudah dicatat agar dapat diperbaiki tanpa menampilkan data internal ke pengguna.');
        }
    }
}
