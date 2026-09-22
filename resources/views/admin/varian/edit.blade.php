@extends('admin.layouts.app')


@section('title', 'Edit Varian - Admin TWFood')


@section('page-title', 'Edit Varian')


@section('content')

    <h1>
        Edit Varian
    </h1>

    <p>
        Produk:
        <strong>{{ $varian->produk->nama_produk }}</strong>
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
                'admin.varian.update',
                $varian->id_varian
            ) }}"
            method="POST"
        >

            @csrf
            @method('PUT')


            <div style="margin-bottom: 15px;">

                <label>
                    Nama Varian
                </label>

                <br>

                <input
                    type="text"
                    name="nama_varian"
                    value="{{ old(
                        'nama_varian',
                        $varian->nama_varian
                    ) }}"
                    required
                    style="width: 100%; padding: 10px;"
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Berat (gram)
                </label>

                <br>

                <input
                    type="number"
                    name="berat_gram"
                    value="{{ old(
                        'berat_gram',
                        $varian->berat_gram
                    ) }}"
                    min="0"
                    style="width: 100%; padding: 10px;"
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Satuan Jual
                </label>

                <br>

                <select
                    name="satuan_jual"
                    required
                    style="width: 100%; padding: 10px;"
                >

                    <option
                        value="pc"
                        {{ $varian->satuan_jual == 'pc'
                            ? 'selected'
                            : '' }}
                    >
                        pc
                    </option>

                    <option
                        value="pack"
                        {{ $varian->satuan_jual == 'pack'
                            ? 'selected'
                            : '' }}
                    >
                        pack
                    </option>

                    <option
                        value="kg"
                        {{ $varian->satuan_jual == 'kg'
                            ? 'selected'
                            : '' }}
                    >
                        kg
                    </option>

                </select>

            </div>


            {{-- <div style="margin-bottom: 15px;">

                <label>
                    Stok
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
                    style="width: 100%; padding: 10px;"
                >

            </div>


            <div style="margin-bottom: 15px;">

                <label>

                    <input
                        type="checkbox"
                        name="tersedia_pre_order"
                        value="1"
                        {{ $varian->tersedia_pre_order
                            ? 'checked'
                            : '' }}
                    >

                    Tersedia Pre-Order

                </label>

            </div> --}}


            {{-- <div style="margin-bottom: 15px;">

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

            </div> --}}


            {{-- <div style="margin-bottom: 15px;">

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

            </div> --}}


            {{-- <div style="margin-bottom: 15px;">

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

            </div> --}}


            <button type="submit">
                Update Varian
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
