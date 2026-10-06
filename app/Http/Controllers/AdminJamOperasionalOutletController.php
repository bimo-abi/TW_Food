<?php

namespace App\Http\Controllers;

use App\Models\JamOperasionalOutlet;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class AdminJamOperasionalOutletController extends Controller
{
    /**
     * Menampilkan daftar jam operasional outlet.
     */
    public function index($idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        $jamOperasional = JamOperasionalOutlet::where(
            'id_outlet',
            $outlet->id_outlet
        )
            ->orderByRaw("
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
            ")
            ->get();

        return view(
            'admin.jam-operasional.index',
            compact(
                'outlet',
                'jamOperasional'
            )
        );
    }


    /**
     * Menampilkan form tambah jam operasional.
     */
    public function create($idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        $hariYangSudahAda = JamOperasionalOutlet::where(
            'id_outlet',
            $outlet->id_outlet
        )
            ->pluck('hari')
            ->toArray();

        $semuaHari = [
            'senin',
            'selasa',
            'rabu',
            'kamis',
            'jumat',
            'sabtu',
            'minggu',
        ];

        $hariTersedia = array_values(
            array_diff(
                $semuaHari,
                $hariYangSudahAda
            )
        );

        return view(
            'admin.jam-operasional.create',
            compact(
                'outlet',
                'hariTersedia'
            )
        );
    }


    /**
     * Menyimpan jam operasional baru.
     */
    public function store(Request $request, $idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        $data = $request->validate([
            'hari' => [
                'required',
                Rule::in([
                    'senin',
                    'selasa',
                    'rabu',
                    'kamis',
                    'jumat',
                    'sabtu',
                    'minggu',
                ]),
                Rule::unique('jam_operasional_outlet', 'hari')
                    ->where(function ($query) use ($outlet) {
                        return $query->where(
                            'id_outlet',
                            $outlet->id_outlet
                        );
                    }),
            ],

            'jam_buka' => [
                'nullable',
                'date_format:H:i',
                'required_if:tutup,0',
            ],

            'jam_tutup' => [
                'nullable',
                'date_format:H:i',
                'required_if:tutup,0',
            ],

            'tutup' => [
                'nullable',
                'boolean',
            ],
        ], [
            'hari.required' =>
            'Hari wajib dipilih.',

            'hari.in' =>
            'Hari yang dipilih tidak valid.',

            'hari.unique' =>
            'Jam operasional untuk hari tersebut sudah tersedia.',

            'jam_buka.required_if' =>
            'Jam buka wajib diisi jika outlet tidak tutup.',

            'jam_buka.date_format' =>
            'Format jam buka harus HH:MM.',

            'jam_tutup.required_if' =>
            'Jam tutup wajib diisi jika outlet tidak tutup.',

            'jam_tutup.date_format' =>
            'Format jam tutup harus HH:MM.',
        ]);

        $data['id_outlet'] = $outlet->id_outlet;
        $data['tutup'] = $request->boolean('tutup');

        if ($data['tutup']) {
            $data['jam_buka'] = null;
            $data['jam_tutup'] = null;
        }

        if (
            !$data['tutup']
            && $data['jam_tutup'] <= $data['jam_buka']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_tutup' =>
                    'Jam tutup harus setelah jam buka.',
                ]);
        }

        try {
            JamOperasionalOutlet::create($data);

            return redirect()
                ->route(
                    'admin.jam-operasional.index',
                    $outlet->id_outlet
                )
                ->with(
                    'success',
                    'Jam operasional berhasil ditambahkan.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Jam operasional gagal ditambahkan. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Menampilkan form edit jam operasional.
     */
    public function edit($idJamOperasional)
    {
        $jamOperasional = JamOperasionalOutlet::with('outlet')
            ->findOrFail($idJamOperasional);

        return view(
            'admin.jam-operasional.edit',
            compact('jamOperasional')
        );
    }


    /**
     * Mengupdate jam operasional.
     */
    public function update(
        Request $request,
        $idJamOperasional
    ) {
        $jamOperasional = JamOperasionalOutlet::findOrFail(
            $idJamOperasional
        );

        $data = $request->validate([
            'hari' => [
                'required',
                Rule::in([
                    'senin',
                    'selasa',
                    'rabu',
                    'kamis',
                    'jumat',
                    'sabtu',
                    'minggu',
                ]),
                Rule::unique(
                    'jam_operasional_outlet',
                    'hari'
                )
                    ->where(function ($query) use (
                        $jamOperasional
                    ) {
                        return $query->where(
                            'id_outlet',
                            $jamOperasional->id_outlet
                        );
                    })
                    ->ignore(
                        $jamOperasional->id_jam_operasional,
                        'id_jam_operasional'
                    ),
            ],

            'jam_buka' => [
                'nullable',
                'date_format:H:i',
                'required_if:tutup,0',
            ],

            'jam_tutup' => [
                'nullable',
                'date_format:H:i',
                'required_if:tutup,0',
            ],

            'tutup' => [
                'nullable',
                'boolean',
            ],
        ], [
            'hari.required' =>
            'Hari wajib dipilih.',

            'hari.in' =>
            'Hari yang dipilih tidak valid.',

            'hari.unique' =>
            'Jam operasional untuk hari tersebut sudah tersedia.',

            'jam_buka.required_if' =>
            'Jam buka wajib diisi jika outlet tidak tutup.',

            'jam_buka.date_format' =>
            'Format jam buka harus HH:MM.',

            'jam_tutup.required_if' =>
            'Jam tutup wajib diisi jika outlet tidak tutup.',

            'jam_tutup.date_format' =>
            'Format jam tutup harus HH:MM.',
        ]);

        $data['tutup'] = $request->boolean('tutup');

        if ($data['tutup']) {
            $data['jam_buka'] = null;
            $data['jam_tutup'] = null;
        }

        if (
            !$data['tutup']
            && $data['jam_tutup'] <= $data['jam_buka']
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_tutup' =>
                    'Jam tutup harus setelah jam buka.',
                ]);
        }

        try {
            $jamOperasional->update($data);

            return redirect()
                ->route(
                    'admin.jam-operasional.index',
                    $jamOperasional->id_outlet
                )
                ->with(
                    'success',
                    'Jam operasional berhasil diperbarui.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Jam operasional gagal diperbarui. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Menghapus jam operasional.
     */
    public function destroy($idJamOperasional)
    {
        $jamOperasional = JamOperasionalOutlet::findOrFail(
            $idJamOperasional
        );

        $idOutlet = $jamOperasional->id_outlet;

        try {
            $jamOperasional->delete();

            return redirect()
                ->route(
                    'admin.jam-operasional.index',
                    $idOutlet
                )
                ->with(
                    'success',
                    'Jam operasional berhasil dihapus.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Jam operasional gagal dihapus. Terjadi kesalahan saat menghapus data.'
            );
        }
    }
}
