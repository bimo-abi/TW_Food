@extends('admin.layouts.app')


@section('title', 'Daftar Harga - Admin TWFood')


@section('page-title', 'Daftar Harga')


@section('content')

    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    ">

        <div>

            <h1>
                Daftar Harga
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

        </div>


        <a
            href="{{ route(
                'admin.harga.create',
                $varian->id_varian
            ) }}"
            style="
                background: #198754;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
            "
        >
            + Tambah Harga
        </a>

    </div>


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

        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>Jenis Harga</th>
                    <th>Harga</th>
                    <th>Minimal Pembelian</th>
                    <th>Status</th>
                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

                @forelse ($harga as $item)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>

                            @if ($item->jenis_harga === 'ecer')

                                Ecer

                            @else

                                Grosir

                            @endif

                        </td>


                        <td>

                            Rp
                            {{ number_format(
                                $item->harga,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>


                        <td>

                            {{ $item->minimal_pembelian }}

                            {{ $item->satuan_minimal ?? '' }}

                        </td>


                        <td>

                            @if ($item->status_aktif)
                                Aktif
                            @else
                                Tidak Aktif
                            @endif

                        </td>


                        <td>

                            <a
                                href="{{ route(
                                    'admin.harga.edit',
                                    $item->id_daftar_harga
                                ) }}"
                            >
                                Edit
                            </a>


                            <br>


                            <form
                                action="{{ route(
                                    'admin.harga.toggle-status',
                                    $item->id_daftar_harga
                                ) }}"
                                method="POST"
                                style="display: inline;"
                            >

                                @csrf

                                @method('PATCH')

                                <button type="submit">

                                    @if ($item->status_aktif)
                                        Nonaktifkan
                                    @else
                                        Aktifkan
                                    @endif

                                </button>

                            </form>


                            <br>


                            <form
                                action="{{ route(
                                    'admin.harga.destroy',
                                    $item->id_daftar_harga
                                ) }}"
                                method="POST"
                                style="display: inline;"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus harga ini?'
                                    )
                                "
                            >

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

                        <td colspan="6">
                            Belum ada daftar harga.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <br>


    <a
        href="{{ route(
            'admin.varian.index',
            $varian->id_varian
        ) }}"
    >
        ← Kembali ke Varian
    </a>

@endsection
