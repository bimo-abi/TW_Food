<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ReturController extends Controller
{
    public function index()
    {
        return view('admin.retur.index');
    }
}
