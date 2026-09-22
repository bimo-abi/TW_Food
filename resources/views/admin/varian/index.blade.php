@extends('admin.layouts.app')


@section('title', 'Varian Produk - Admin TWFood')


@section('page-title', 'Varian Produk')


@section('content')

    <div
        style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    ">

        <div>

            <h1>
                Varian Produk
            </h1>

            <p>
                Produk:
                <strong>{{ $produk->nama_produk }}</strong>
            </p>

        </div>


        <a href="{{ route('admin.varian.create', $produk->id_produk) }}"
            style="
                background: #198754;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
            ">
            + Tambah Varian
        </a>

    </div>


    @if (session('success'))
        <div
            style="
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
        <div
            style="
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

        <table>

            <thead>

                <tr>

                    <th>No</th>
                    <th>Nama Varian</th>
                    <th>Berat</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Pre-Order</th>
                    <th>Status</th>
                    <th>Aksi</th>

                </tr>

            </thead>


            <tbody>

                @forelse ($varian as $item)
                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>
                            <strong>
                                {{ $item->nama_varian }}
                            </strong>
                        </td>


                        <td>
                            {{ $item->berat_gram ?? '-' }}
                            @if ($item->berat_gram)
                                gram
                            @endif
                        </td>


                        <td>
                            {{ $item->satuan_jual }}
                        </td>


                        <td>
                            {{ $item->stok }}
                        </td>


                        <td>

                            @if ($item->tersedia_pre_order)
                                Ya
                            @else
                                Tidak
                            @endif

                        </td>


                        <td>

                            @if ($item->status_aktif)
                                Aktif
                            @else
                                Tidak Aktif
                            @endif

                        </td>


                        <td>

                            <a href="{{ route('admin.harga.index', $item->id_varian) }}">
                                Harga
                            </a>

                            <br>

                            <a href="{{ route('admin.varian.edit', $item->id_varian) }}">
                                Edit
                            </a>

                            <br>

                            <a href="{{ route('admin.stok.edit', $item->id_varian) }}">
                                Stok & Pre-Order
                            </a>

                            <br>


                            <form action="{{ route('admin.varian.toggle-status', $item->id_varian) }}" method="POST"
                                style="display: inline;">

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


                            <form action="{{ route('admin.varian.destroy', $item->id_varian) }}" method="POST"
                                style="display: inline;"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus varian ini?'
                                    )
                                ">

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

                        <td colspan="8">
                            Belum ada varian.
                        </td>

                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>


    <br>


    <a href="{{ route('admin.produk.index') }}">
        ← Kembali ke Produk
    </a>

@endsection
