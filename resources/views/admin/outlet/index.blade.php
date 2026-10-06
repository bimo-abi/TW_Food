@extends('admin.layouts.app')


@section('title', 'Outlet - Admin TWFood')


@section('page-title', 'Outlet')


@section('content')

    <div
        style="
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        "
    >

        <div>

            <h1>
                Outlet
            </h1>

            <p>
                Kelola data outlet TWFood.
            </p>

        </div>


        <a
            href="{{ route('admin.outlet.create') }}"
            style="
                background: #198754;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
            "
        >
            + Tambah Outlet
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
            "
        >

            {{ session('success') }}

        </div>

    @endif


    {{-- Pesan error --}}

    @if (session('error'))

        <div
            style="
                background: #f8d7da;
                color: #842029;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 5px;
            "
        >

            {{ session('error') }}

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
            "
        >

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
                        Nama Outlet
                    </th>

                    <th>
                        Alamat
                    </th>

                    <th>
                        Telepon
                    </th>

                    <th>
                        Latitude
                    </th>

                    <th>
                        Longitude
                    </th>

                    <th>
                        Deskripsi
                    </th>

                    <th>
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($outlets as $item)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>

                            @if ($item->foto)

                                <img
                                    src="{{ asset('storage/' . $item->foto) }}"
                                    alt="{{ $item->nama_outlet }}"
                                    width="70"
                                    height="70"
                                    style="
                                        object-fit: cover;
                                        border-radius: 5px;
                                    "
                                >

                            @else

                                Tidak ada foto

                            @endif

                        </td>


                        <td>

                            <strong>
                                {{ $item->nama_outlet }}
                            </strong>

                        </td>


                        <td>
                            {{ $item->alamat }}
                        </td>


                        <td>
                            {{ $item->nomor_telepon ?? '-' }}
                        </td>


                        <td>
                            {{ $item->latitude ?? '-' }}
                        </td>


                        <td>
                            {{ $item->longitude ?? '-' }}
                        </td>


                        <td>
                            {{ $item->deskripsi ?? '-' }}
                        </td>


                        <td>

                            <a
                                href="{{ route('admin.outlet.edit', $item->id_outlet) }}"
                            >
                                Edit
                            </a>

                            <br>
                            <a
                                href="{{ route('admin.jam-operasional.index', $item->id_outlet) }}"
                            >
                                Jam Operasional
                            </a>
                            <br>
                            <form
                                action="{{ route('admin.outlet.destroy', $item->id_outlet) }}"
                                method="POST"
                                style="display: inline;"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus outlet ini?'
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

                        <td colspan="9">
                            Belum ada outlet.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

@endsection
