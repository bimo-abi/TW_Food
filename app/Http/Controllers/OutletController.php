<?php

namespace App\Http\Controllers;

use App\Models\Outlet;

class OutletController extends Controller
{
    /**
     * Menampilkan informasi outlet pada public website.
     */
    public function index()
    {
        $outlets = Outlet::with([
            'jamOperasional' => function ($query) {
                $query->orderByRaw("
                    FIELD(
                        hari,
                        'senin',
                        'selasa',
                        'rabu',
                        'kamis',
                        'jumat',
                        'sabtu',
                        'minggu'
                    )
                ");
            }
        ])
            ->orderBy('nama_outlet')
            ->get();

        return view(
            'outlet.index',
            compact('outlets')
        );
    }
}
