<?php

namespace App\Http\Controllers;

use App\Models\Outlet;

class KontakController extends Controller
{
    /**
     * Menampilkan informasi kontak TWFood.
     */
    public function index()
    {
        $outlet = Outlet::first();

        return view('kontak.index', compact('outlet'));
    }
}
