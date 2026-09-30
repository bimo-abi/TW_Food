@extends('admin.layouts.app')


@section('title', 'Produk Resep - Admin TWFood')


@section('page-title', 'Produk Resep')


@section('content')

    <h1>
        Kelola Produk Resep
    </h1>

    <p>
        Resep:
        <strong>{{ $resep->judul }}</strong>
    </p>


    {{-- Pesan sukses --}}

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


    {{-- Pesan error --}}

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


    {{-- Error validasi --}}

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


    {{-- Form tambah produk --}}

    <div class="card" style="margin-bottom: 20px;">

        <h2>
            Tambah Produk ke Resep
        </h2>


        <form action="{{ route('admin.resep.produk.store', $resep->id_resep) }}" method="POST">

            @csrf


            <div style="margin-bottom: 15px;">

                <label>
                    Produk
                </label>

                <br>

                <select name="id_produk" required style="width: 100%; padding: 10px;">

                    <option value="">
                        -- Pilih Produk --
                    </option>

                    @foreach ($produk as $item)

                        <option value="{{ $item->id_produk }}" {{ old('id_produk') == $item->id_produk ? 'selected' : '' }}>
                            {{ $item->nama_produk }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div style="margin-bottom: 15px;">

                <label>
                    Jumlah
                </label>

                <br>

                <input type="text" name="jumlah" value="{{ old('jumlah') }}" placeholder="Contoh: 2 sdm, 100 ml, 1 bungkus"
                    required maxlength="100" style="width: 100%; padding: 10px;">

            </div>


            <button type="submit">
                Tambah Produk
            </button>

        </form>

    </div>


    {{-- Daftar produk dalam resep --}}

    <div class="card">

        <h2>
            Produk yang Digunakan
        </h2>


        <table>

            <thead>

                <tr>

                    <th>
                        No
                    </th>

                    <th>
                        Produk
                    </th>

                    <th>
                        Jumlah
                    </th>

                    <th>
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($resepProduk as $item)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>


                                <td>

                                    @if ($item->produk)

                                        {{ $item->produk->nama_produk }}

                                    @else

                                        Produk tidak ditemukan

                                    @endif

                                </td>


                                <td>
                                    {{ $item->jumlah }}
                                </td>


                                <td>

                                    <form action="{{ route(
                        'admin.resep.produk.destroy',
                        $item->id_resep_produk
                    ) }}" method="POST" style="display: inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus produk ini dari resep?')">

                                        @csrf

                                        @method('DELETE')

                                        <button type="submit">
                                            Hapus
                                        </button>

                                    </form>

                                </td>

                            </tr>

                @empty

                    <tr>

                        <td colspan="4">
                            Belum ada produk yang digunakan dalam resep ini.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <br>


    <a href="{{ route('admin.resep.index') }}">
        ← Kembali ke Resep
    </a>

@endsection
