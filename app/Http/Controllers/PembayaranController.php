<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('admin.pembayaran.index');
    }
}
