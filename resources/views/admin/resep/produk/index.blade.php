@extends('admin.layouts.app')

@section('title', 'Produk Resep - Admin TWFood')
@section('page-title', 'Produk Resep')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">
            Atur produk yang dipakai pada resep
            <span class="fw-bold text-dark">{{ $resep->judul }}</span>.
        </div>
        <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali ke Resep
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

    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title">Tambah Produk ke Resep</h3></div>
        <div class="card-body">
            <form action="{{ route('admin.resep.produk.store', $resep->id_resep) }}" method="POST" class="row g-3 align-items-start">
                @csrf
                <div class="col-md-5">
                    <label for="id_produk" class="form-label required">Produk</label>
                    <select name="id_produk" id="id_produk" class="form-select @error('id_produk') is-invalid @enderror" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produk as $item)
                            <option value="{{ $item->id_produk }}" @selected(old('id_produk') == $item->id_produk)>{{ $item->nama_produk }}</option>
                        @endforeach
                    </select>
                    @error('id_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label for="jumlah" class="form-label required">Jumlah / takaran</label>
                    <input type="text" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
                        value="{{ old('jumlah') }}" placeholder="Contoh: 2 sdm, 100 ml, 1 bungkus" maxlength="100" required>
                    @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i>Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Produk yang Digunakan</h3>
            <div class="card-actions"><span class="badge bg-secondary-lt">{{ $resepProduk->count() }} produk</span></div>
        </div>

        @if ($resepProduk->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-package-off d-block mb-3" style="font-size: 2.6rem; color: #F7941D;"></i>
                <h3 class="mb-1">Belum ada produk</h3>
                <p class="text-secondary mb-0">Pilih produk di atas untuk menambahkannya ke resep ini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No.</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resepProduk as $item)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">
                                    @if ($item->produk)
                                        {{ $item->produk->nama_produk }}
                                    @else
                                        <span class="text-danger">Produk tidak ditemukan</span>
                                    @endif
                                </td>
                                <td>{{ $item->jumlah }}</td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <form action="{{ route('admin.resep.produk.destroy', $item->id_resep_produk) }}" method="POST"
                                            class="d-inline-flex"
                                            onsubmit="return confirm('Yakin ingin menghapus produk ini dari resep?')">
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
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection