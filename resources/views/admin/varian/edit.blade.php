@extends('admin.layouts.app')


@section('title', 'Edit Varian - Admin TWFood')


@section('page-title', 'Edit Varian')


@section('content')

    <h1>
        Edit Varian
    </h1>


    <p>
        Produk:
        <strong>
            {{ $varian->produk->nama_produk }}
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


    @if (session('error'))

        <div style="
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        ">

            {{ session('error') }}

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


            {{-- NAMA VARIAN --}}

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
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            {{-- BERAT --}}

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
                    max="65535"
                    oninvalid="this.setCustomValidity('Berat maksimal 65.535 gram.')"
                    oninput="this.setCustomValidity('')"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            {{-- SATUAN JUAL --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Satuan Jual
                </label>

                <br>

                <select
                    name="satuan_jual"
                    required
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

                    <option
                        value="pc"
                        {{ old(
                            'satuan_jual',
                            $varian->satuan_jual
                        ) == 'pc'
                            ? 'selected'
                            : '' }}
                    >
                        pc
                    </option>

                    <option
                        value="pack"
                        {{ old(
                            'satuan_jual',
                            $varian->satuan_jual
                        ) == 'pack'
                            ? 'selected'
                            : '' }}
                    >
                        pack
                    </option>

                    <option
                        value="kg"
                        {{ old(
                            'satuan_jual',
                            $varian->satuan_jual
                        ) == 'kg'
                            ? 'selected'
                            : '' }}
                    >
                        kg
                    </option>

                </select>

            </div>


            {{-- STOK --}}

            <div style="margin-bottom: 15px;">

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
                    max="16777215"
                    oninvalid="this.setCustomValidity('Stok maksimal 16.777.215.')"
                    oninput="this.setCustomValidity('')"
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

            <div style="margin-bottom: 15px;">

                <label>

                    <input
                        type="checkbox"
                        id="tersedia_pre_order"
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


            {{-- TANGGAL MULAI PRE-ORDER --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Tanggal Mulai Pre-Order
                </label>

                <br>

                <input
                    type="date"
                    id="tanggal_mulai_pre_order"
                    name="tanggal_mulai_pre_order"
                    value="{{ old(
                        'tanggal_mulai_pre_order',
                        optional(
                            $varian->tanggal_mulai_pre_order
                        )->format('Y-m-d')
                    ) }}"
                    min="{{ now()->format('Y-m-d') }}"
                    style="
                        padding: 10px;
                    "
                >

            </div>


            {{-- TANGGAL SELESAI PRE-ORDER --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Tanggal Selesai Pre-Order
                </label>

                <br>

                <input
                    type="date"
                    id="tanggal_selesai_pre_order"
                    name="tanggal_selesai_pre_order"
                    value="{{ old(
                        'tanggal_selesai_pre_order',
                        optional(
                            $varian->tanggal_selesai_pre_order
                        )->format('Y-m-d')
                    ) }}"
                    style="
                        padding: 10px;
                    "
                >

            </div>


            {{-- ESTIMASI TERSEDIA --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Estimasi Tersedia
                </label>

                <br>

                <input
                    type="date"
                    id="estimasi_tersedia"
                    name="estimasi_tersedia"
                    value="{{ old(
                        'estimasi_tersedia',
                        optional(
                            $varian->estimasi_tersedia
                        )->format('Y-m-d')
                    ) }}"
                    style="
                        padding: 10px;
                    "
                >

            </div>


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


    {{-- LOGIKA REQUIRED UNTUK PRE-ORDER --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const checkbox =
                document.getElementById('tersedia_pre_order');

            const tanggalMulai =
                document.getElementById(
                    'tanggal_mulai_pre_order'
                );

            const tanggalSelesai =
                document.getElementById(
                    'tanggal_selesai_pre_order'
                );

            const estimasi =
                document.getElementById(
                    'estimasi_tersedia'
                );


            function updateRequired() {

                const aktif = checkbox.checked;

                tanggalMulai.required = aktif;
                tanggalSelesai.required = aktif;
                estimasi.required = aktif;
            }


            checkbox.addEventListener(
                'change',
                updateRequired
            );


            updateRequired();

        });

    </script>

@endsection
