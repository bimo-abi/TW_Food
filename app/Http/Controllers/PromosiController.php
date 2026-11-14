<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PromosiController extends Controller
{
    public function index()
    {
        return view('admin.promosi.index');
    }
}
