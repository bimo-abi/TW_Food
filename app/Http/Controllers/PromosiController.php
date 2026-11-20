<?php

namespace App\Http\Controllers;

use App\Models\Promosi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $kalender = $this->kalenderKembar();

        return view('admin.promosi.index', compact('promosi', 'filter', 'status', 'kalender'));
    }

    public function create(): View
    {
        return view('admin.promosi.create', [
            'promosi' => new Promosi(['jenis_promosi' => 'diskon_persen', 'status_aktif' => true]),
            'tanggalKembar' => $this->tanggalKembar(),
            'daftarVarian' => $this->daftarVarian(),
            'varianTerpilih' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $varian = $data['varian'];
            unset($data['varian']);

            $promosi = Promosi::create($data);
            $this->simpanVarian($promosi, $varian);
        });

        return redirect()->route('admin.promosi.index')
            ->with('success', 'Promosi berhasil ditambahkan.');
    }

    public function edit(int $id): View
    {
        $promosi = Promosi::with('varian')->findOrFail($id);

        return view('admin.promosi.edit', [
            'promosi' => $promosi,
            'tanggalKembar' => $this->tanggalKembar(),
            'daftarVarian' => $this->daftarVarian(),
            'varianTerpilih' => $promosi->varian->pluck('id_varian')->all(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $promosi = Promosi::findOrFail($id);
        $data = $this->validasi($request);

        DB::transaction(function () use ($promosi, $data) {
            $varian = $data['varian'];
            unset($data['varian']);

            $promosi->update($data);
            $promosi->varian()->delete();
            $this->simpanVarian($promosi, $varian);
        });

        return redirect()->route('admin.promosi.index')
            ->with('success', 'Promosi berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $promosi = Promosi::findOrFail($id);

        DB::transaction(function () use ($promosi) {
            $promosi->varian()->delete();
            $promosi->delete();
        });

        return redirect()->route('admin.promosi.index')
            ->with('success', 'Promosi berhasil dihapus.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $promosi = Promosi::findOrFail($id);
        $promosi->update(['status_aktif' => !$promosi->status_aktif]);

        return back()->with('success', 'Status promosi diubah.');
    }

    // ---------- helper ----------

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama_promosi' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'jenis_promosi' => ['required', 'in:diskon_persen,diskon_nominal,harga_khusus'],
            'nilai_persen' => ['nullable', 'required_if:jenis_promosi,diskon_persen', 'numeric', 'gt:0', 'max:100'],
            'nilai_nominal' => ['nullable', 'required_unless:jenis_promosi,diskon_persen', 'integer', 'min:1', 'max:16777215'],
            'minimal_pembelian' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'varian' => ['required', 'array', 'min:1'],
            'varian.*' => ['integer', 'exists:varian_produk,id_varian'],
        ], [
            'varian.required' => 'Pilih minimal satu varian produk.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        // kosongkan nilai yang tidak dipakai jenisnya
        if ($data['jenis_promosi'] === 'diskon_persen') {
            $data['nilai_nominal'] = null;
        } else {
            $data['nilai_persen'] = null;
        }

        $data['status_aktif'] = $request->boolean('status_aktif');

        return $data;
    }

    private function simpanVarian(Promosi $promosi, array $idVarian): void
    {
        foreach (array_unique($idVarian) as $id) {
            $promosi->varian()->create(['id_varian' => $id]);
        }
    }

    private function daftarVarian()
    {
        return DB::table('varian_produk')
            ->join('produk', 'produk.id_produk', '=', 'varian_produk.id_produk')
            ->where('varian_produk.status_aktif', true)
            ->orderBy('produk.nama_produk')
            ->orderBy('varian_produk.nama_varian')
            ->select('varian_produk.id_varian', 'varian_produk.nama_varian', 'produk.nama_produk')
            ->get()
            ->groupBy('nama_produk');
    }

    /** 12 tanggal kembar: tahun ini, atau tahun depan kalau sudah lewat. */
    private function tanggalKembar(): array
    {
        $hasil = [];
        foreach (range(1, 12) as $m) {
            $tgl = Carbon::create(today()->year, $m, $m)->startOfDay();
            if ($tgl->lt(today())) {
                $tgl = $tgl->addYear();
            }
            $hasil[] = [
                'kode' => "$m.$m",
                'tanggal' => $tgl->toDateString(),
                'label' => "$m.$m  ·  " . $tgl->locale('id')->translatedFormat('j F Y'),
            ];
        }
        return $hasil;
    }

    /** Kalender 12 kartu untuk halaman index (tahun berjalan). */
    private function kalenderKembar(): array
    {
        $tahun = today()->year;
        $semua = Promosi::query()
            ->whereYear('tanggal_selesai', '>=', $tahun)
            ->whereYear('tanggal_mulai', '<=', $tahun)
            ->get();

        $hasil = [];
        foreach (range(1, 12) as $m) {
            $tgl = Carbon::create($tahun, $m, $m)->startOfDay();
            $promo = $semua->first(
                fn($p) => $tgl->between($p->tanggal_mulai, $p->tanggal_selesai)
            );
            $hasil[] = [
                'kode' => "$m.$m",
                'tanggal' => $tgl,
                'lewat' => $tgl->lt(today()),
                'hariIni' => $tgl->isToday(),
                'promo' => $promo,
            ];
        }
        return $hasil;
    }
}