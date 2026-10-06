@extends('admin.layouts.app')


@section('title', 'Tambah Jam Operasional - Admin TWFood')


@section('page-title', 'Tambah Jam Operasional')


@section('content')

    <h1>
        Tambah Jam Operasional
    </h1>


    <p>
        Outlet:
        <strong>
            {{ $outlet->nama_outlet }}
        </strong>
    </p>


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


    <div class="card">

        @if (count($hariTersedia) > 0)

            <form
                action="{{ route('admin.jam-operasional.store', $outlet->id_outlet) }}"
                method="POST"
            >

                @csrf


                {{-- Hari --}}

                <div style="margin-bottom: 15px;">

                    <label>
                        Hari
                    </label>

                    <br>

                    <select
                        name="hari"
                        id="hari"
                        required
                        style="
                            width: 100%;
                            padding: 10px;
                        "
                    >

                        <option value="">
                            -- Pilih Hari --
                        </option>

                        @foreach ($hariTersedia as $hari)

                            <option
                                value="{{ $hari }}"
                                @selected(old('hari') === $hari)
                            >

                                {{ ucfirst($hari) }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- Tutup --}}

                <div style="margin-bottom: 15px;">

                    <label>
                        <input
                            type="checkbox"
                            name="tutup"
                            value="1"
                            id="tutup"
                            @checked(old('tutup'))
                        >

                        Outlet Tutup
                    </label>

                </div>


                {{-- Jam Buka --}}

                <div
                    style="margin-bottom: 15px;"
                    id="jam-buka-group"
                >

                    <label>
                        Jam Buka
                    </label>

                    <br>

                    <input
                        type="time"
                        name="jam_buka"
                        id="jam_buka"
                        value="{{ old('jam_buka') }}"
                        style="
                            width: 100%;
                            padding: 10px;
                        "
                    >

                </div>


                {{-- Jam Tutup --}}

                <div
                    style="margin-bottom: 15px;"
                    id="jam-tutup-group"
                >

                    <label>
                        Jam Tutup
                    </label>

                    <br>

                    <input
                        type="time"
                        name="jam_tutup"
                        id="jam_tutup"
                        value="{{ old('jam_tutup') }}"
                        style="
                            width: 100%;
                            padding: 10px;
                        "
                    >

                </div>


                <button type="submit">
                    Simpan Jam Operasional
                </button>


                <a
                    href="{{ route('admin.jam-operasional.index', $outlet->id_outlet) }}"
                    style="margin-left: 10px;"
                >
                    Kembali
                </a>

            </form>

        @else

            <p>
                Semua hari sudah memiliki jadwal jam operasional.
            </p>

            <a
                href="{{ route('admin.jam-operasional.index', $outlet->id_outlet) }}"
            >
                ← Kembali ke Jam Operasional
            </a>

        @endif

    </div>


    <script>

        const tutupCheckbox = document.getElementById('tutup');

        const jamBukaGroup = document.getElementById('jam-buka-group');
        const jamTutupGroup = document.getElementById('jam-tutup-group');

        const jamBukaInput = document.getElementById('jam_buka');
        const jamTutupInput = document.getElementById('jam_tutup');


        function updateJamField() {

            if (tutupCheckbox.checked) {

                jamBukaGroup.style.display = 'none';
                jamTutupGroup.style.display = 'none';

                jamBukaInput.value = '';
                jamTutupInput.value = '';

                jamBukaInput.required = false;
                jamTutupInput.required = false;

            } else {

                jamBukaGroup.style.display = 'block';
                jamTutupGroup.style.display = 'block';

                jamBukaInput.required = true;
                jamTutupInput.required = true;

            }

        }


        tutupCheckbox.addEventListener(
            'change',
            updateJamField
        );


        updateJamField();

    </script>

@endsection
