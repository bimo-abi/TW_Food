@extends('admin.layouts.app')


@section('title', 'Resep - Admin TWFood')


@section('page-title', 'Resep')


@section('content')

    <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            ">

        <div>

            <h1>
                Resep
            </h1>

            <p>
                Kelola resep TWFood.
            </p>

        </div>


        <a href="{{ route('admin.resep.create') }}" style="
                        background: #198754;
                        color: white;
                        padding: 10px 15px;
                        border-radius: 5px;
                        text-decoration: none;
                    ">
            + Tambah Resep
        </a>

    </div>


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
                        Judul
                    </th>

                    <th>
                        Deskripsi
                    </th>

                    <th>
                        Waktu Memasak
                    </th>

                    <th>
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($resep as $item)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>


                                <td>

                                    @if ($item->foto)

                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->judul }}" width="80" height="80"
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
                                        {{ $item->judul }}
                                    </strong>

                                </td>


                                <td>

                                    {{ $item->deskripsi
                        ? Str::limit($item->deskripsi, 100)
                        : '-'
                                                            }}

                                </td>


                                <td>

                                    @if ($item->waktu_memasak)

                                        {{ $item->waktu_memasak }} menit

                                    @else

                                        -

                                    @endif

                                </td>


                                <td>
                                    <a href="{{ route('admin.resep.produk.index', $item->id_resep) }}">
                                        Produk Resep
                                    </a>

                                    <br>

                                    <a href="{{ route(
                        'admin.resep.edit',
                        $item->id_resep
                    ) }}">
                                        Edit
                                    </a>


                                    <br>


                                    <form action="{{ route(
                        'admin.resep.destroy',
                        $item->id_resep
                    ) }}" method="POST" style="display: inline;" onsubmit="
                                                                    return confirm(
                                                                        'Yakin ingin menghapus resep ini?'
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

                        <td colspan="6">
                            Belum ada resep.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection
