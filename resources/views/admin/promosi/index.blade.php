@extends('admin.layouts.app')

@section('title', 'Promosi - TWFood')
@section('page-title', 'Promosi')

@section('content')
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1>Promosi</h1>
            <p class="text-secondary mb-0">Pantau promosi, periode berlaku, dan varian produk yang terkait.</p>
        </div>
        <a href="{{ route('admin.promosi.create') }}" class="btn btn-primary">+ Tambah Promosi</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Kalender Tanggal Kembar {{ today()->year }}</h3></div>
        <div class="card-body">
            <div class="row g-2">
                @foreach ($kalender as $k)
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="border rounded p-2 h-100 {{ $k['lewat'] ? 'bg-light' : '' }}">
                            <div class="fw-bold">{{ $k['kode'] }}
                                @if ($k['hariIni']) <span class="badge bg-success-lt">Hari ini</span> @endif
                            </div>
                            <div class="text-secondary small mb-2">{{ $k['tanggal']->locale('id')->translatedFormat('j F') }}</div>
                            @if ($k['promo'])
                                <a href="{{ route('admin.promosi.edit', $k['promo']->id_promosi) }}"
                                   class="badge bg-success-lt text-decoration-none">Ada promo</a>
                            @elseif ($k['lewat'])
                                <span class="text-secondary small">Terlewat</span>
                            @else
                                <a href="{{ route('admin.promosi.create') }}" class="small">+ Buat promo</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.promosi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="q" class="form-label">Cari Nama Promosi</label>
                    <input
                        type="search"
                        id="q"
                        name="q"
                        class="form-control"
                        value="{{ $filter['q'] ?? '' }}"
                        maxlength="150"
                        placeholder="Masukkan nama promosi"
                    >
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status Periode</label>
                    <select id="status" name="status" class="form-select">
                        <option value="semua" @selected($status === 'semua')>Semua</option>
                        <option value="aktif" @selected($status === 'aktif')>Sedang Berlangsung</option>
                        <option value="akan_datang" @selected($status === 'akan_datang')>Akan Datang</option>
                        <option value="berakhir" @selected($status === 'berakhir')>Berakhir</option>
                        <option value="nonaktif" @selected($status === 'nonaktif')>Dinonaktifkan</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.promosi.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Promosi</h3>
            <div class="card-actions text-secondary">
                {{ $promosi->total() }} promosi
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Promosi</th>
                        <th>Jenis</th>
                        <th>Nilai Promosi</th>
                        <th>Minimal Pembelian</th>
                        <th>Periode</th>
                        <th>Varian Terkait</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promosi as $item)
                        @php
                            if (!$item->status_aktif) {
                                $labelStatus = 'Dinonaktifkan';
                                $warnaStatus = 'secondary';
                            } elseif ($item->tanggal_mulai->isFuture()) {
                                $labelStatus = 'Akan Datang';
                                $warnaStatus = 'azure';
                            } elseif ($item->tanggal_selesai->lt(today())) {
                                $labelStatus = 'Berakhir';
                                $warnaStatus = 'danger';
                            } else {
                                $labelStatus = 'Berlangsung';
                                $warnaStatus = 'success';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->nama_promosi }}</div>
                                @if ($item->deskripsi)
                                    <div class="text-secondary small">{{ $item->deskripsi }}</div>
                                @endif
                            </td>
                            <td>
                                @switch($item->jenis_promosi)
                                    @case('diskon_persen')
                                        Diskon Persen
                                        @break
                                    @case('diskon_nominal')
                                        Diskon Nominal
                                        @break
                                    @case('harga_khusus')
                                        Harga Khusus
                                        @break
                                    @default
                                        {{ str_replace('_', ' ', $item->jenis_promosi) }}
                                @endswitch
                            </td>
                            <td>
                                @if ($item->jenis_promosi === 'diskon_persen')
                                    {{ rtrim(rtrim(number_format((float) $item->nilai_persen, 2, ',', '.'), '0'), ',') }}%
                                @elseif ($item->nilai_nominal !== null)
                                    Rp{{ number_format((int) $item->nilai_nominal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $item->minimal_pembelian !== null
                                    ? number_format((int) $item->minimal_pembelian, 0, ',', '.') . ' item'
                                    : '-' }}
                            </td>
                            <td>
                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                <span class="text-secondary">s.d.</span>
                                {{ $item->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td>{{ $item->varian_count }}</td>
                            <td><span class="badge bg-{{ $warnaStatus }}-lt">{{ $labelStatus }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.promosi.edit', $item->id_promosi) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.promosi.toggle-status', $item->id_promosi) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary">{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.promosi.destroy', $item->id_promosi) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus promosi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">
                                Tidak ada data promosi yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex align-items-center">
            {{ $promosi->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection