@extends('admin.layouts.app')

@section('title', 'Outlet - Admin TWFood')
@section('page-title', 'Outlet')

@push('styles')
    <style>
        .ol-thumb { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid #eceff3; flex-shrink: 0; }
        .ol-thumb-kosong {
            width: 48px; height: 48px; border-radius: 12px; background: #FDECEC; color: #E31E24;
            display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .ol-nama { font-weight: 700; line-height: 1.25; }
        .ol-sub { font-size: 12.5px; color: #9aa1ad; }
        .ol-alamat {
            max-width: 280px; font-size: 13px; color: #4b5563; line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .ol-status { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
        .ol-status i { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        .ol-status.buka { background: #E8F7EE; color: #1B8A4B; }
        .ol-status.tutup { background: #FDECEC; color: #E31E24; }
        .ol-status.belum { background: #F1F3F6; color: #6b7280; }
        .ol-jam { font-size: 12.5px; color: #6b7280; margin-top: 3px; }
        .ol-cari { max-width: 340px; }
    </style>
@endpush

@section('content')
    @php
        $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
        $hariIni  = strtolower($sekarang->copy()->locale('id')->dayName);
        $jamIni   = $sekarang->format('H:i');

        // Hitung status tiap outlet sekali saja
        $info = [];
        $jumlahBuka = 0;
        $jumlahBelumLengkap = 0;
        foreach ($outlets as $o) {
            $jamHari = $o->jamOperasional->first(fn ($j) => strtolower($j->hari) === $hariIni);
            $status = null;
            $teksJam = null;
            if ($jamHari) {
                if ($jamHari->tutup) {
                    $status = false;
                    $teksJam = 'Libur hari ini';
                } else {
                    $buka  = substr($jamHari->jam_buka ?? '', 0, 5);
                    $tutup = substr($jamHari->jam_tutup ?? '', 0, 5);
                    $teksJam = $buka . ' – ' . $tutup;
                    if ($buka !== '' && $tutup !== '') {
                        $status = ($jamIni >= $buka && $jamIni < $tutup);
                    }
                }
            }
            $totalHari = $o->jamOperasional->count();
            if ($status === true) { $jumlahBuka++; }
            if ($totalHari < 7) { $jumlahBelumLengkap++; }
            $info[$o->id_outlet] = compact('status', 'teksJam', 'totalHari');
        }
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">Kelola outlet dan cabang TWFood beserta jam operasionalnya.</div>
        <a href="{{ route('admin.outlet.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Tambah Outlet
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Ringkasan ===== --}}
    <div class="row row-cards mb-4">
        <div class="col-sm-4">
            <div class="card card-sm kpi kpi-merah">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Total Outlet</div>
                            <div class="h1 mb-0">{{ $outlets->count() }}</div>
                        </div>
                        <div class="col-auto"><span class="bg-red-lt avatar"><i class="ti ti-building-store icon"></i></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-sm kpi kpi-hijau">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Buka Sekarang</div>
                            <div class="h1 mb-0">{{ $jumlahBuka }}</div>
                        </div>
                        <div class="col-auto"><span class="bg-green-lt avatar"><i class="ti ti-door-enter icon"></i></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-sm kpi kpi-oranye">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-secondary">Jam Belum Lengkap</div>
                            <div class="h1 mb-0">{{ $jumlahBelumLengkap }}</div>
                        </div>
                        <div class="col-auto"><span class="bg-orange-lt avatar"><i class="ti ti-clock-exclamation icon"></i></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        @if ($outlets->isEmpty())
            <div class="card-body text-center py-5">
                <i class="ti ti-building-store d-block mb-3" style="font-size: 2.6rem; color: #F7941D;"></i>
                <h3 class="mb-1">Belum ada outlet</h3>
                <p class="text-secondary mb-3">Tambahkan outlet pertama untuk melengkapi informasi lokasi TWFood.</p>
                <a href="{{ route('admin.outlet.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Tambah Outlet
                </a>
            </div>
        @else
            <div class="card-header">
                <h3 class="card-title">Daftar Outlet</h3>
                <div class="card-actions ol-cari">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="text" id="cari-outlet" class="form-control" placeholder="Cari nama atau alamat...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="tabel-outlet">
                    <thead>
                        <tr>
                            <th>Outlet</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th>Hari Ini</th>
                            <th>Jam Operasional</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outlets as $item)
                            @php $i = $info[$item->id_outlet]; @endphp
                            <tr data-cari="{{ strtolower($item->nama_outlet . ' ' . $item->alamat) }}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($item->foto)
                                            <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto {{ $item->nama_outlet }}" class="ol-thumb" loading="lazy">
                                        @else
                                            <span class="ol-thumb-kosong"><i class="ti ti-building-store"></i></span>
                                        @endif
                                        <div>
                                            <div class="ol-nama">{{ $item->nama_outlet }}</div>
                                            @if ($item->link_google_maps)
                                                <a href="{{ $item->link_google_maps }}" target="_blank" rel="noopener noreferrer" class="ol-sub">
                                                    <i class="ti ti-map-2"></i> Lihat di Maps
                                                </a>
                                            @else
                                                <span class="ol-sub">Link Maps belum diisi</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td><div class="ol-alamat">{{ $item->alamat }}</div></td>

                                <td class="text-nowrap">
                                    {{ $item->nomor_telepon ?: '-' }}
                                </td>

                                <td>
                                    @if ($i['status'] === true)
                                        <span class="ol-status buka"><i></i>Buka</span>
                                    @elseif ($i['status'] === false)
                                        <span class="ol-status tutup"><i></i>Tutup</span>
                                    @else
                                        <span class="ol-status belum"><i></i>Belum diatur</span>
                                    @endif
                                    @if ($i['teksJam'])
                                        <div class="ol-jam">{{ $i['teksJam'] }}</div>
                                    @endif
                                </td>

                                <td>
                                    @if ($i['totalHari'] >= 7)
                                        <span class="badge bg-success-lt">Lengkap · 7 hari</span>
                                    @elseif ($i['totalHari'] > 0)
                                        <span class="badge bg-orange-lt">{{ $i['totalHari'] }} dari 7 hari</span>
                                    @else
                                        <span class="badge bg-danger-lt">Belum diisi</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-nowrap align-items-center justify-content-end gap-2">
                                        <a href="{{ route('admin.jam-operasional.index', $item->id_outlet) }}"
                                            class="btn btn-sm btn-outline-secondary text-nowrap">
                                            <i class="ti ti-clock me-1"></i>Jam
                                        </a>
                                        <a href="{{ route('admin.outlet.edit', $item->id_outlet) }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap">
                                            <i class="ti ti-edit me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('admin.outlet.destroy', $item->id_outlet) }}" method="POST"
                                            class="d-inline-flex" onsubmit="return confirm('Yakin ingin menghapus outlet ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus" aria-label="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        <tr id="outlet-tidak-ada" class="d-none">
                            <td colspan="6" class="text-center text-secondary py-4">Tidak ada outlet yang cocok dengan pencarian.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var cari = document.getElementById('cari-outlet');
        if (!cari) return;
        var baris = document.querySelectorAll('#tabel-outlet tbody tr[data-cari]');
        var kosong = document.getElementById('outlet-tidak-ada');
        cari.addEventListener('input', function () {
            var q = cari.value.trim().toLowerCase();
            var ada = 0;
            baris.forEach(function (r) {
                var cocok = r.dataset.cari.indexOf(q) !== -1;
                r.classList.toggle('d-none', !cocok);
                if (cocok) ada++;
            });
            kosong.classList.toggle('d-none', ada !== 0);
        });
    });
    </script>
@endsection