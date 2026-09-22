@extends('admin.layouts.app')


@section('title', 'Produk - Admin TWFood')


@section('page-title', 'Produk')


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
                Produk
            </h1>

            <p>
                Kelola produk TWFood.
            </p>

        </div>


        <a href="{{ route('admin.produk.create') }}"
            style="
                background: #198754;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
            ">
            + Tambah Produk
        </a>

    </div>


    {{-- Pesan sukses --}}

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


    {{-- Error validasi --}}

    @if ($errors->any())

        <div
            style="
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

                    <th>
                        No
                    </th>

                    <th>
                        Foto
                    </th>

                    <th>
                        Nama Produk
                    </th>

                    <th>
                        Deskripsi
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($produk as $item)
                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>

                            @if ($item->foto_produk)
                                <img src="{{ asset('storage/' . $item->foto_produk) }}" alt="{{ $item->nama_produk }}"
                                    width="70" height="70"
                                    style="
                                        object-fit: cover;
                                        border-radius: 5px;
                                    ">
                            @else
                                Tidak ada foto
                            @endif

                        </td>


                        <td>
                            <strong>
                                {{ $item->nama_produk }}
                            </strong>
                        </td>


                        <td>

                            {{ $item->deskripsi ? Str::limit($item->deskripsi, 80) : '-' }}

                        </td>


                        <td>

                            @if ($item->status_aktif)
                                <span>
                                    Aktif
                                </span>
                            @else
                                <span>
                                    Tidak Aktif
                                </span>
                            @endif

                        </td>


                        <td>

                            <a href="{{ route('admin.produk.edit', $item->id_produk) }}">
                                Edit
                            </a>
                            <br>
                            <a href="{{ route('admin.varian.index', $item->id_produk) }}">
                                Varian
                            </a>
                            <br>
                            <form action="{{ route('admin.produk.toggle-status', $item->id_produk) }}" method="POST"
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


                            <form action="{{ route('admin.produk.destroy', $item->id_produk) }}" method="POST"
                                style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">

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
                            Belum ada produk.
                        </td>

                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

@endsection
