@extends('admin.layouts.app')


@section('title', 'Tambah Varian - Admin TWFood')


@section('page-title', 'Tambah Varian')


@section('content')

    <h1>
        Tambah Varian
    </h1>

    <p>
        Produk:
        <strong>{{ $produk->nama_produk }}</strong>
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
                'admin.varian.store',
                $produk->id_produk
            ) }}"
            method="POST"
        >

            @csrf


            <div style="margin-bottom: 15px;">

                <label>
                    Nama Varian
                </label>

                <br>

                <input
                    type="text"
                    name="nama_varian"
                    value="{{ old('nama_varian') }}"
                    placeholder="Contoh: M, P, A, Reguler"
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
    value="{{ old('berat_gram') }}"
    min="0"
    max="65535"
    oninvalid="this.setCustomValidity('Berat maksimal 65.535 gram.')"
    oninput="this.setCustomValidity('')"
    placeholder="Contoh: 185"
    style="width: 100%; padding: 10px;"
>

            </div>


           <div style="margin-bottom: 15px;">

    <label>
        Satuan Jual
    </label>

    <br>

    <input
        type="text"
        name="satuan_jual"
        value="{{ old('satuan_jual') }}"
        placeholder="Contoh: pc, pack, kg"
        required
        style="width: 100%; padding: 10px;"
    >

</div>


            <div style="margin-bottom: 15px;">

                <label>
                    Stok
                </label>

                <br>

                <input
    type="number"
    name="stok"
    value="{{ old('stok', 0) }}"
    min="0"
    max="16777215"
    oninvalid="this.setCustomValidity('Stok maksimal 16.777.215.')"
    oninput="this.setCustomValidity('')"
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
                        {{ old('tersedia_pre_order') ? 'checked' : '' }}
                    >

                    Tersedia Pre-Order

                </label>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Tanggal Mulai Pre-Order
                </label>

                <br>

                <input
    type="date"
    name="tanggal_mulai_pre_order"
    value="{{ old('tanggal_mulai_pre_order') }}"
    min="{{ date('Y-m-d') }}"
    style="padding: 10px;"
>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Tanggal Selesai Pre-Order
                </label>

                <br>

                <input
    type="date"
    name="tanggal_selesai_pre_order"
    value="{{ old('tanggal_selesai_pre_order') }}"
    min="{{ date('Y-m-d') }}"
    style="padding: 10px;"
>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Estimasi Tersedia
                </label>

                <br>

                <input
    type="date"
    name="tanggal_selesai_pre_order"
    value="{{ old('tanggal_selesai_pre_order') }}"
    min="{{ date('Y-m-d') }}"
    style="padding: 10px;"
>

            </div>


            <button type="submit">
                Simpan Varian
            </button>


            <a
                href="{{ route(
                    'admin.varian.index',
                    $produk->id_produk
                ) }}"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
