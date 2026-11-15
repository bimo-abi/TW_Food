<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $data = Notifikasi::query()
            ->where('id_pengguna', $request->user()->id_pengguna)
            ->whereNull('diarsipkan_pada')
            ->latest('created_at')
            ->paginate(min((int) $request->query('per_page', 20), 50));

        return response()->json(['message' => 'Notifikasi berhasil diambil.', 'data' => $data]);
    }

    public function markRead(Request $request, int $id_notifikasi)
    {
        $notifikasi = Notifikasi::query()
            ->where('id_pengguna', $request->user()->id_pengguna)
            ->where('id_notifikasi', $id_notifikasi)
            ->firstOrFail();

        if (!$notifikasi->sudah_dibaca) {
            $notifikasi->sudah_dibaca = true;
            $notifikasi->save();
        }

        return response()->json(['message' => 'Notifikasi ditandai telah dibaca.', 'data' => $notifikasi]);
    }

    public function markAllRead(Request $request)
    {
        $jumlah = Notifikasi::query()
            ->where('id_pengguna', $request->user()->id_pengguna)
            ->whereNull('diarsipkan_pada')
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true]);

        return response()->json(['message' => 'Seluruh notifikasi aktif ditandai telah dibaca.', 'jumlah_diperbarui' => $jumlah]);
    }
}
