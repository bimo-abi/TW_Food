@extends('admin.layouts.app')


@section('title', 'Jam Operasional - Admin TWFood')


@section('page-title', 'Jam Operasional')


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
                Jam Operasional
            </h1>

            <p>
                Outlet:
                <strong>
                    {{ $outlet->nama_outlet }}
                </strong>
            </p>

        </div>


        <a
            href="{{ route('admin.jam-operasional.create', $outlet->id_outlet) }}"
            style="
                background: #198754;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                text-decoration: none;
            "
        >
            + Tambah Jam Operasional
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
                        Hari
                    </th>

                    <th>
                        Jam Buka
                    </th>

                    <th>
                        Jam Tutup
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

                @forelse ($jamOperasional as $item)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>

                            @switch($item->hari)

                                @case('senin')
                                    Senin
                                    @break

                                @case('selasa')
                                    Selasa
                                    @break

                                @case('rabu')
                                    Rabu
                                    @break

                                @case('kamis')
                                    Kamis
                                    @break

                                @case('jumat')
                                    Jumat
                                    @break

                                @case('sabtu')
                                    Sabtu
                                    @break

                                @case('minggu')
                                    Minggu
                                    @break

                            @endswitch

                        </td>


                        <td>

                            @if ($item->tutup)

                                -

                            @else

                                {{ $item->jam_buka }}

                            @endif

                        </td>


                        <td>

                            @if ($item->tutup)

                                -

                            @else

                                {{ $item->jam_tutup }}

                            @endif

                        </td>


                        <td>

                            @if ($item->tutup)

                                Tutup

                            @else

                                Buka

                            @endif

                        </td>


                        <td>

                            <a
                                href="{{ route('admin.jam-operasional.edit', $item->id_jam_operasional) }}"
                            >
                                Edit
                            </a>


                            <br>


                            <form
                                action="{{ route('admin.jam-operasional.destroy', $item->id_jam_operasional) }}"
                                method="POST"
                                style="display: inline;"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus jam operasional ini?'
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
                            Belum ada jam operasional.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <br>


    <a
        href="{{ route('admin.outlet.index') }}"
    >
        ← Kembali ke Outlet
    </a>

@endsection
