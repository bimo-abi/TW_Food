@extends('admin.layouts.app')


@section('title', 'Edit Harga - Admin TWFood')


@section('page-title', 'Edit Harga')


@section('content')

    <h1>
        Edit Daftar Harga
    </h1>

    <p>
        Produk:
        <strong>
            {{ $harga->varian->produk->nama_produk }}
        </strong>
    </p>

    <p>
        Varian:
        <strong>
            {{ $harga->varian->nama_varian }}
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
                'admin.harga.update',
                $harga->id_daftar_harga
            ) }}"
            method="POST"
        >

            @csrf

            @method('PUT')


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

                    <option
                        value="ecer"
                        {{ $harga->jenis_harga === 'ecer'
                            ? 'selected'
                            : '' }}
                    >
                        Ecer
                    </option>

                    <option
                        value="grosir"
                        {{ $harga->jenis_harga === 'grosir'
                            ? 'selected'
                            : '' }}
                    >
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
                    value="{{ old(
                        'harga',
                        $harga->harga
                    ) }}"
                    min="0"
                    required
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
                        $harga->minimal_pembelian
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
                    value="{{ old(
                        'satuan_minimal',
                        $harga->satuan_minimal
                    ) }}"
                    placeholder="Contoh: pc, doz, kg"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            <button type="submit">
                Update Harga
            </button>


            <a
                href="{{ route(
                    'admin.harga.index',
                    $harga->id_varian
                ) }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
