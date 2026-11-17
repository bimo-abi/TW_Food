<?php

namespace App\Http\Controllers;

use App\Models\Promosi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromosiController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:semua,aktif,nonaktif,akan_datang,berakhir'],
        ]);

        $query = Promosi::query()
            ->withCount('varian')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id_promosi');

        if (!empty($filter['q'])) {
            $query->where('nama_promosi', 'like', '%' . $filter['q'] . '%');
        }

        $status = $filter['status'] ?? 'semua';

        if ($status === 'aktif') {
            $query->where('status_aktif', true)
                ->whereDate('tanggal_mulai', '<=', today())
                ->whereDate('tanggal_selesai', '>=', today());
        } elseif ($status === 'nonaktif') {
            $query->where('status_aktif', false);
        } elseif ($status === 'akan_datang') {
            $query->where('status_aktif', true)
                ->whereDate('tanggal_mulai', '>', today());
        } elseif ($status === 'berakhir') {
            $query->whereDate('tanggal_selesai', '<', today());
        }

        $promosi = $query->paginate(15)->withQueryString();

        return view('admin.promosi.index', compact('promosi', 'filter', 'status'));
    }
}
