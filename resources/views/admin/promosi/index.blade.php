@extends('admin.layouts.app')

@section('title', 'Campaign - TWFood')
@section('page-title', 'Campaign')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Admin/promosi.css') }}">
@endpush

@section('content')
    @php
        $periode = function ($mulai, $selesai) {
            $m = $mulai->copy()->locale('id');
            $s = $selesai->copy()->locale('id');
            if ($m->isSameDay($s)) {
                return $m->translatedFormat('j M Y') . ' (1 hari)';
            }
            $hari = $m->diffInDays($s) + 1;
            return $m->translatedFormat('j M') . ' – ' . $s->translatedFormat('j M Y') . ' (' . $hari . ' hari)';
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">
            Kelola Campaign diskon produk dan varian berdasarkan periode yang kamu tentukan.
        </div>

        <a href="{{ route('admin.promosi.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>
            Tambah Campaign
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- ===== Filter ===== --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.promosi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="q" class="form-label">Cari nama Campaign</label>
                    <input type="search" id="q" name="q" class="form-control"
                        value="{{ $filter['q'] ?? '' }}" maxlength="150" placeholder="Contoh: Promo 11.11">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="semua" @selected($status === 'semua')>Semua</option>
                        <option value="aktif" @selected($status === 'aktif')>Sedang berlangsung</option>
                        <option value="akan_datang" @selected($status === 'akan_datang')>Akan datang</option>
                        <option value="berakhir" @selected($status === 'berakhir')>Sudah berakhir</option>
                        <option value="nonaktif" @selected($status === 'nonaktif')>Dinonaktifkan</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('admin.promosi.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Daftar Campaign ===== --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Campaign</h3>
            <div class="card-actions text-secondary">{{ $promosi->total() }} Campaign</div>
        </div>

        @if ($promosi->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-discount-2 d-block mb-3" style="font-size: 2.8rem; color: #F7941D;"></i>
                <h3 class="mb-1">Belum ada Campaign</h3>
                <p class="text-secondary mb-3">
                    Mulai dengan membuat Campaign diskon untuk produk dan varian pilihan.
                </p>
                <a href="{{ route('admin.promosi.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Buat Campaign Pertama
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Potongan</th>
                            <th>Pengaturan</th>
                            <th>Periode</th>
                            <th>Varian</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promosi as $item)
                            @php
                                if (!$item->status_aktif) {
                                    $labelStatus = 'Dinonaktifkan'; $warna = 'bg-secondary-lt';
                                } elseif ($item->tanggal_mulai->isFuture()) {
                                    $labelStatus = 'Akan Datang'; $warna = 'bg-azure-lt';
                                } elseif ($item->tanggal_selesai->lt(today())) {
                                    $labelStatus = 'Berakhir'; $warna = 'bg-danger-lt';
                                } else {
                                    $labelStatus = 'Berlangsung'; $warna = 'bg-success-lt';
                                }

                                $potongan = match ($item->jenis_promosi) {
                                    'diskon_persen' => $item->varian->contains(fn($v) => $v->nilai_persen !== null) ? 'Diskon per varian' : ('Diskon ' . rtrim(rtrim(number_format((float) $item->nilai_persen, 2, ',', '.'), '0'), ',') . '%'),
                                    'diskon_nominal' => 'Potong Rp ' . number_format((int) $item->nilai_nominal, 0, ',', '.'),
                                    'harga_khusus' => 'Harga Rp ' . number_format((int) $item->nilai_nominal, 0, ',', '.'),
                                    default => '-',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="promo-nama">{{ $item->nama_promosi }}</div>
                                    @if ($item->deskripsi)
                                        <div class="promo-deskripsi">{{ \Illuminate\Support\Str::limit($item->deskripsi, 70) }}</div>
                                    @endif
                                </td>
                                <td><span class="promo-potongan">{{ $potongan }}</span></td>
                                <td class="text-secondary">
                                    {{ $item->jenis_promosi === 'diskon_persen' && $item->varian->contains(fn($v) => $v->nilai_persen !== null) ? 'Diskon khusus tiap varian' : ($item->minimal_pembelian !== null ? 'Min. ' . number_format((int) $item->minimal_pembelian, 0, ',', '.') . ' item' : 'Data promosi lama') }}
                                </td>
                                <td class="text-nowrap">{{ $periode($item->tanggal_mulai, $item->tanggal_selesai) }}</td>
                                <td>{{ $item->varian_count }} varian</td>
                                <td><span class="badge {{ $warna }}">{{ $labelStatus }}</span></td>
                                <td>
                                    <div class="promo-aksi">
                                        <a href="{{ route('admin.promosi.edit', $item->id_promosi) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit me-1"></i>Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.promosi.toggle-status', $item->id_promosi) }}">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-icon btn-outline-secondary"
                                                title="{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                aria-label="{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="ti ti-power"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.promosi.destroy', $item->id_promosi) }}"
                                            onsubmit="return confirm('Hapus Campaign ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-icon btn-outline-danger" title="Hapus" aria-label="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($promosi->hasPages())
                <div class="card-footer d-flex align-items-center">
                    {{ $promosi->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </div>
@endsection