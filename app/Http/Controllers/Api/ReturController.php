<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    public function store(Request $request, $id_pesanan)
    {
        return response()->json([
            'message' => 'Endpoint pengajuan retur berhasil dipanggil.',
            'id_pesanan' => $id_pesanan,
        ]);
    }
}
