@extends('admin.layouts.app')


@section('title', 'Tambah Produk - Admin TWFood')


@section('page-title', 'Tambah Produk')


@section('content')

    <h1>
        Tambah Produk
    </h1>


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
            action="{{ route('admin.produk.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            <div style="margin-bottom: 15px;">

                <label>
                    Nama Produk
                </label>

                <br>

                <input
                    type="text"
                    name="nama_produk"
                    value="{{ old('nama_produk') }}"
                    required
                    style="width: 100%; padding: 10px;"
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Deskripsi
                </label>

                <br>

                <textarea
                    name="deskripsi"
                    rows="5"
                    style="width: 100%; padding: 10px;"
                >{{ old('deskripsi') }}</textarea>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Foto Produk
                </label>

                <br>

                <input
                    type="file"
                    name="foto_produk"
                    accept="image/*"
                >

            </div>


            <button type="submit">
                Simpan Produk
            </button>


            <a
                href="{{ route('admin.produk.index') }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
