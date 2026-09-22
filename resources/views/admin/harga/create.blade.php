@extends('admin.layouts.app')


@section('title', 'Tambah Harga - Admin TWFood')


@section('page-title', 'Tambah Harga')


@section('content')

    <h1>
        Tambah Daftar Harga
    </h1>

    <p>
        Produk:
        <strong>
            {{ $varian->produk->nama_produk }}
        </strong>
    </p>

    <p>
        Varian:
        <strong>
            {{ $varian->nama_varian }}
        </strong>
    </p>


    @if ($errors->any())

        <div style="
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        ">

            @foreach ($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif


    <div class="card">

        <form
            action="{{ route(
                'admin.harga.store',
                $varian->id_varian
            ) }}"
            method="POST"
        >

            @csrf


            <div style="margin-bottom: 15px;">

                <label>
                    Jenis Harga
                </label>

                <br>

                <select
                    name="jenis_harga"
                    required
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

                    <option value="">
                        -- Pilih Jenis Harga --
                    </option>

                    <option value="ecer">
                        Ecer
                    </option>

                    <option value="grosir">
                        Grosir
                    </option>

                </select>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Harga
                </label>

                <br>

                <input
                    type="number"
                    name="harga"
                    value="{{ old('harga') }}"
                    min="0"
                    required
                    placeholder="Contoh: 12000"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Minimal Pembelian
                </label>

                <br>

                <input
                    type="number"
                    name="minimal_pembelian"
                    value="{{ old(
                        'minimal_pembelian',
                        1
                    ) }}"
                    min="1"
                    required
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Satuan Minimal
                </label>

                <br>

                <input
                    type="text"
                    name="satuan_minimal"
                    value="{{ old('satuan_minimal') }}"
                    placeholder="Contoh: pc, doz, kg"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            <button type="submit">
                Simpan Harga
            </button>


            <a
                href="{{ route(
                    'admin.harga.index',
                    $varian->id_varian
                ) }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
