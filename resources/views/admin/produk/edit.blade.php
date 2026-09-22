@extends('admin.layouts.app')


@section('title', 'Edit Produk - Admin TWFood')


@section('page-title', 'Edit Produk')


@section('content')

    <h1>
        Edit Produk
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
            action="{{ route('admin.produk.update', $produk->id_produk) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            @method('PUT')


            <div style="margin-bottom: 15px;">

                <label>
                    Nama Produk
                </label>

                <br>

                <input
                    type="text"
                    name="nama_produk"
                    value="{{ old('nama_produk', $produk->nama_produk) }}"
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
                >{{ old('deskripsi', $produk->deskripsi) }}</textarea>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Foto Produk Saat Ini
                </label>

                <br>

                @if ($produk->foto_produk)

                    <img
                        src="{{ asset('storage/' . $produk->foto_produk) }}"
                        alt="{{ $produk->nama_produk }}"
                        width="120"
                        style="margin-top: 10px;"
                    >

                @else

                    <p>
                        Belum ada foto.
                    </p>

                @endif

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Ganti Foto Produk
                </label>

                <br>

                <input
                    type="file"
                    name="foto_produk"
                    accept="image/*"
                >

            </div>


            <button type="submit">
                Update Produk
            </button>


            <a
                href="{{ route('admin.produk.index') }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
