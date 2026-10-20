@extends('admin.layouts.app')

@section('title', 'Produk Resep - Admin TWFood')
@section('page-title', 'Produk Resep')

@section('content')
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Pengelolaan Resep</div>
                <h2 class="page-title">Kelola Produk Resep</h2>
                <div class="text-secondary mt-1">
                    Resep:
                    <span class="fw-bold">{{ $resep->judul }}</span>
                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali ke Resep
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <i class="ti ti-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
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

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Tambah Produk ke Resep</h3>
        </div>

        <form action="{{ route('admin.resep.produk.store', $resep->id_resep) }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="mb-3">
                    <label for="id_produk" class="form-label required">Produk</label>

                    <select
                        name="id_produk"
                        id="id_produk"
                        class="form-select @error('id_produk') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Pilih Produk --</option>

                        @foreach ($produk as $item)
                            <option
                                value="{{ $item->id_produk }}"
                                {{ old('id_produk') == $item->id_produk ? 'selected' : '' }}
                            >
                                {{ $item->nama_produk }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-0">
                    <label for="jumlah" class="form-label required">Jumlah</label>

                    <input
                        type="text"
                        name="jumlah"
                        id="jumlah"
                        class="form-control @error('jumlah') is-invalid @enderror"
                        value="{{ old('jumlah') }}"
                        placeholder="Contoh: 2 sdm, 100 ml, 1 bungkus"
                        maxlength="100"
                        required
                    >

                    @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-hint">
                        Masukkan jumlah atau takaran produk yang digunakan dalam resep.
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Produk
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Produk yang Digunakan</h3>
        </div>

        @if ($resepProduk->count() === 0)
            <div class="card-body text-center py-5">
                <i class="ti ti-package-off d-block mb-3" style="font-size: 3rem;"></i>
                <h3 class="mb-1">Belum Ada Produk</h3>
                <p class="text-secondary mb-0">
                    Belum ada produk yang digunakan dalam resep ini.
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($resepProduk as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-bold">
                                    @if ($item->produk)
                                        {{ $item->produk->nama_produk }}
                                    @else
                                        <span class="text-danger">Produk tidak ditemukan</span>
                                    @endif
                                </td>

                                <td>{{ $item->jumlah }}</td>

                                <td>
                                    <div class="d-flex justify-content-center">
                                        <form
                                            action="{{ route('admin.resep.produk.destroy', $item->id_resep_produk) }}"
                                            method="POST"
                                            class="d-inline-flex"
                                            onsubmit="return confirm('Yakin ingin menghapus produk ini dari resep?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                            >
                                                <i class="ti ti-trash me-1"></i>
                                                Hapus
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
