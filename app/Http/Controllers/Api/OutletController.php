<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;

class OutletController extends Controller
{
    /**
     * Menampilkan daftar outlet
     * beserta jam operasional.
     */
    public function index()
    {
        $outlet = Outlet::query()
            ->with([
                'jamOperasional',
            ])
            ->get();

        return response()->json([
            'message' => 'Daftar outlet berhasil diambil.',
            'data' => $outlet,
        ], 200);
    }
}
