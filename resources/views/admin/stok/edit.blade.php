@extends('admin.layouts.app')


@section('title', 'Stok & Pre-Order - Admin TWFood')


@section('page-title', 'Stok & Pre-Order')


@section('content')

    <h1>
        Stok & Pre-Order
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


    @if (session('success'))

        <div style="
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        ">

            {{ session('success') }}

        </div>

    @endif


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
                'admin.stok.update',
                $varian->id_varian
            ) }}"
            method="POST"
        >

            @csrf

            @method('PUT')


            {{-- STOK --}}

            <div style="margin-bottom: 20px;">

                <label>
                    <strong>Stok</strong>
                </label>

                <br>

                <input
                    type="number"
                    name="stok"
                    value="{{ old(
                        'stok',
                        $varian->stok
                    ) }}"
                    min="0"
                    required
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

                <small>
                    Masukkan jumlah stok yang tersedia.
                </small>

            </div>


            {{-- PRE-ORDER --}}

            <div style="margin-bottom: 20px;">

                <label>

                    <input
                        type="checkbox"
                        name="tersedia_pre_order"
                        value="1"
                        {{ old(
                            'tersedia_pre_order',
                            $varian->tersedia_pre_order
                        ) ? 'checked' : '' }}
                    >

                    <strong>
                        Varian tersedia untuk Pre-Order
                    </strong>

                </label>

            </div>


            {{-- TANGGAL MULAI --}}

            <div style="margin-bottom: 20px;">

                <label>
                    Tanggal Mulai Pre-Order
                </label>

                <br>

                <input
                    type="date"
                    name="tanggal_mulai_pre_order"
                    value="{{ old(
                        'tanggal_mulai_pre_order',
                        optional(
                            $varian->tanggal_mulai_pre_order
                        )->format('Y-m-d')
                    ) }}"
                    style="padding: 10px;"
                >

            </div>


            {{-- TANGGAL SELESAI --}}

            <div style="margin-bottom: 20px;">

                <label>
                    Tanggal Selesai Pre-Order
                </label>

                <br>

                <input
                    type="date"
                    name="tanggal_selesai_pre_order"
                    value="{{ old(
                        'tanggal_selesai_pre_order',
                        optional(
                            $varian->tanggal_selesai_pre_order
                        )->format('Y-m-d')
                    ) }}"
                    style="padding: 10px;"
                >

            </div>


            {{-- ESTIMASI --}}

            <div style="margin-bottom: 20px;">

                <label>
                    Estimasi Tersedia
                </label>

                <br>

                <input
                    type="date"
                    name="estimasi_tersedia"
                    value="{{ old(
                        'estimasi_tersedia',
                        optional(
                            $varian->estimasi_tersedia
                        )->format('Y-m-d')
                    ) }}"
                    style="padding: 10px;"
                >

            </div>


            <button type="submit">
                Simpan
            </button>


            <a
                href="{{ route(
                    'admin.varian.index',
                    $varian->id_produk
                ) }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
