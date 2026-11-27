<?php

namespace App\Exports\Laporan;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ArrayReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly array $rows) {}

    public function collection(): Collection
    {
        return collect($this->rows)->map(fn (array $row) => array_values($row));
    }

    public function headings(): array
    {
        return isset($this->rows[0]) ? array_keys($this->rows[0]) : ['Tidak ada data'];
    }
}
