<?php

namespace App\Http\Controllers;

use App\Models\Outlet;

class OutletController extends Controller
{
    public function index()
    {
        $outlet = Outlet::with('jamOperasional')
            ->orderBy('nama_outlet')
            ->get();

        return view('outlet.index', compact('outlet'));
    }
}
