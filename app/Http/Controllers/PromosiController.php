<?php

namespace App\Http\Controllers;

use App\Models\Promosi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PromosiController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:semua,aktif,nonaktif,akan_datang,berakhir'],
        ]);
        $query = Promosi::query()->withCount('varian')->with('varian')
            ->orderByDesc('tanggal_mulai')->orderByDesc('id_promosi');
        if (!empty($filter['q'])) $query->where('nama_promosi', 'like', '%' . $filter['q'] . '%');
        $status = $filter['status'] ?? 'semua';
        if ($status === 'aktif') $query->where('status_aktif', true)->whereDate('tanggal_mulai', '<=', today())->whereDate('tanggal_selesai', '>=', today());
        elseif ($status === 'nonaktif') $query->where('status_aktif', false);
        elseif ($status === 'akan_datang') $query->where('status_aktif', true)->whereDate('tanggal_mulai', '>', today());
        elseif ($status === 'berakhir') $query->whereDate('tanggal_selesai', '<', today());
        $promosi = $query->paginate(15)->withQueryString();
        return view('admin.promosi.index', compact('promosi', 'filter', 'status'));
    }

    public function create(): View
    {
        return view('admin.promosi.create', [
            'promosi' => new Promosi(['jenis_promosi' => 'diskon_persen', 'status_aktif' => true]),
            'daftarVarian' => $this->daftarVarian(),
            'varianTerpilih' => [], 'tanggalPilihan' => request('tanggal'), 'autoIsi' => true,
            'diskonVarian' => [], 'legacyReadOnly' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        DB::transaction(function () use ($data) {
            $varian = $data['varian']; $diskon = $data['diskon'];
            unset($data['varian'], $data['diskon']);
            $this->validasiBentrok($varian, $data['tanggal_mulai'], $data['tanggal_selesai'], (bool) $data['status_aktif']);
            $promosi = Promosi::create($data + ['jenis_promosi' => 'diskon_persen', 'nilai_persen' => null, 'nilai_nominal' => null, 'minimal_pembelian' => null]);
            $this->simpanVarian($promosi, $varian, $diskon);
        }, 3);
        return redirect()->route('admin.promosi.index')->with('success', 'Campaign berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $promosi = Promosi::with('varian')->findOrFail($id);
        $legacyReadOnly = $promosi->jenis_promosi !== 'diskon_persen';
        $diskonVarian = $promosi->varian->mapWithKeys(fn ($v) => [$v->id_varian => $v->nilai_persen ?? $promosi->nilai_persen])->all();
        return view('admin.promosi.edit', [
            'promosi' => $promosi,
            'daftarVarian' => $this->daftarVarian(), 'varianTerpilih' => $promosi->varian->pluck('id_varian')->all(),
            'tanggalPilihan' => $promosi->tanggal_mulai->toDateString(), 'autoIsi' => false,
            'diskonVarian' => $diskonVarian, 'legacyReadOnly' => $legacyReadOnly,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $promosi = Promosi::with('varian')->findOrFail($id);
        if ($promosi->jenis_promosi !== 'diskon_persen') {
            return back()->withErrors(['campaign' => 'Promosi lama dengan potongan nominal/harga khusus tidak dapat dikonversi otomatis. Buat Campaign baru agar riwayat lama tetap utuh.']);
        }
        $data = $this->validasi($request);
        DB::transaction(function () use ($promosi, $data) {
            $varian = $data['varian']; $diskon = $data['diskon'];
            unset($data['varian'], $data['diskon']);
            $promosi = Promosi::whereKey($promosi->getKey())->lockForUpdate()->firstOrFail();
            $this->validasiBentrok($varian, $data['tanggal_mulai'], $data['tanggal_selesai'], (bool) $data['status_aktif'], $promosi->id_promosi);
            $promosi->update($data + ['jenis_promosi' => 'diskon_persen', 'nilai_persen' => null, 'nilai_nominal' => null, 'minimal_pembelian' => null]);
            $promosi->varian()->delete();
            $this->simpanVarian($promosi, $varian, $diskon);
        }, 3);
        return redirect()->route('admin.promosi.index')->with('success', 'Campaign berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $promosi = Promosi::findOrFail($id);
        // Snapshot transaksi tidak memakai foreign key, sehingga riwayat tetap utuh.
        DB::transaction(function () use ($promosi) { $promosi->varian()->delete(); $promosi->delete(); });
        return redirect()->route('admin.promosi.index')->with('success', 'Campaign berhasil dihapus. Riwayat transaksi tetap tersimpan.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $promosi = Promosi::with('varian')->whereKey($id)->lockForUpdate()->firstOrFail();
                $aktif = !$promosi->status_aktif;
                if ($aktif) {
                    if ($promosi->jenis_promosi !== 'diskon_persen') {
                        throw ValidationException::withMessages(['campaign' => 'Promosi lama tidak dapat diaktifkan melalui sistem Campaign baru.']);
                    }
                    $this->validasiBentrok($promosi->varian->pluck('id_varian')->all(), $promosi->tanggal_mulai->toDateString(), $promosi->tanggal_selesai->toDateString(), true, $id);
                }
                $promosi->update(['status_aktif' => $aktif]);
            }, 3);
        } catch (ValidationException $e) { return back()->withErrors($e->errors()); }
        return back()->with('success', 'Status Campaign berhasil diubah.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama_promosi' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'varian' => ['required', 'array', 'min:1'],
            'varian.*' => ['required', 'integer', 'distinct', 'exists:varian_produk,id_varian'],
            'diskon' => ['required', 'array'],
            'diskon.*' => ['required', 'numeric', 'gt:0', 'lte:100'],
        ], [
            'varian.required' => 'Pilih minimal satu varian produk.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'diskon.*.required' => 'Isi persentase diskon untuk setiap varian terpilih.',
            'diskon.*.lte' => 'Diskon tidak boleh melebihi 100%.',
        ]);
        $idTerpilih = array_map('strval', $data['varian']);
        foreach ($idTerpilih as $id) {
            if (!array_key_exists($id, $data['diskon'])) {
                throw ValidationException::withMessages(['diskon' => 'Setiap varian yang dipilih wajib memiliki persentase diskon.']);
            }
        }
        $data['diskon'] = array_intersect_key($data['diskon'], array_flip($idTerpilih));
        $data['status_aktif'] = $request->boolean('status_aktif');
        return $data;
    }

    private function validasiBentrok(array $idVarian, string $mulai, string $selesai, bool $aktif, ?int $kecuali = null): void
    {
        if (!$aktif) return;
        $ids = array_values(array_unique(array_map('intval', $idVarian)));
        sort($ids);
        // Lock urutan varian yang konsisten untuk mengurangi risiko race saat dua admin menyimpan bersamaan.
        DB::table('varian_produk')->whereIn('id_varian', $ids)->orderBy('id_varian')->lockForUpdate()->get();
        $bentrok = DB::table('promosi_varian as pv')
            ->join('promosi as p', 'p.id_promosi', '=', 'pv.id_promosi')
            ->whereIn('pv.id_varian', $ids)->where('p.status_aktif', true)
            ->whereDate('p.tanggal_mulai', '<=', $selesai)
            ->whereDate('p.tanggal_selesai', '>=', $mulai)
            ->when($kecuali, fn ($q) => $q->where('p.id_promosi', '<>', $kecuali))
            ->select('pv.id_varian', 'p.nama_promosi', 'p.tanggal_mulai', 'p.tanggal_selesai')->first();
        if ($bentrok) {
            throw ValidationException::withMessages(['periode' => "Periode bertabrakan dengan Campaign '{$bentrok->nama_promosi}' pada varian ID {$bentrok->id_varian} ({$bentrok->tanggal_mulai} s.d. {$bentrok->tanggal_selesai})."]);
        }
    }

    private function simpanVarian(Promosi $promosi, array $idVarian, array $diskon): void
    {
        foreach ($idVarian as $id) {
            $promosi->varian()->create(['id_varian' => $id, 'nilai_persen' => $diskon[$id] ?? $diskon[(string) $id]]);
        }
    }

    private function daftarVarian()
    {
        return DB::table('varian_produk as v')
            ->join('produk as p', 'p.id_produk', '=', 'v.id_produk')
            ->join('daftar_harga as h', function ($join) {
                $join->on('h.id_varian', '=', 'v.id_varian')->where('h.jenis_harga', '=', 'ecer')->where('h.status_aktif', '=', 1);
            })
            ->where('v.status_aktif', true)->where('p.status_aktif', true)
            ->orderBy('p.nama_produk')->orderBy('v.nama_varian')
            ->select('v.id_varian', 'v.nama_varian', 'p.nama_produk', 'h.harga as harga_ecer')
            ->get()->groupBy('nama_produk');
    }

}
