@extends('admin.layouts.app')


@section('title', 'Edit Jam Operasional - Admin TWFood')


@section('page-title', 'Edit Jam Operasional')


@section('content')

    <h1>
        Edit Jam Operasional
    </h1>


    <p>
        Outlet:
        <strong>
            {{ $jamOperasional->outlet->nama_outlet }}
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

        <form
            action="{{ route(
                'admin.jam-operasional.update',
                $jamOperasional->id_jam_operasional
            ) }}"
            method="POST"
        >

            @csrf

            @method('PUT')


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

                    <option
                        value="senin"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'senin'
                        )
                    >
                        Senin
                    </option>

                    <option
                        value="selasa"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'selasa'
                        )
                    >
                        Selasa
                    </option>

                    <option
                        value="rabu"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'rabu'
                        )
                    >
                        Rabu
                    </option>

                    <option
                        value="kamis"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'kamis'
                        )
                    >
                        Kamis
                    </option>

                    <option
                        value="jumat"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'jumat'
                        )
                    >
                        Jumat
                    </option>

                    <option
                        value="sabtu"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'sabtu'
                        )
                    >
                        Sabtu
                    </option>

                    <option
                        value="minggu"
                        @selected(
                            old('hari', $jamOperasional->hari) === 'minggu'
                        )
                    >
                        Minggu
                    </option>

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
                        @checked(
                            old(
                                'tutup',
                                $jamOperasional->tutup
                            )
                        )
                    >

                    Outlet Tutup

                </label>

            </div>


            {{-- Jam Buka --}}

            <div
                id="jam-buka-group"
                style="margin-bottom: 15px;"
            >

                <label>
                    Jam Buka
                </label>

                <br>

                <input
                    type="time"
                    name="jam_buka"
                    id="jam_buka"
                    value="{{
                        old(
                            'jam_buka',
                            $jamOperasional->jam_buka
                        )
                            ? substr(
                                old(
                                    'jam_buka',
                                    $jamOperasional->jam_buka
                                ),
                                0,
                                5
                            )
                            : ''
                    }}"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            {{-- Jam Tutup --}}

            <div
                id="jam-tutup-group"
                style="margin-bottom: 15px;"
            >

                <label>
                    Jam Tutup
                </label>

                <br>

                <input
                    type="time"
                    name="jam_tutup"
                    id="jam_tutup"
                    value="{{
                        old(
                            'jam_tutup',
                            $jamOperasional->jam_tutup
                        )
                            ? substr(
                                old(
                                    'jam_tutup',
                                    $jamOperasional->jam_tutup
                                ),
                                0,
                                5
                            )
                            : ''
                    }}"
                    style="
                        width: 100%;
                        padding: 10px;
                    "
                >

            </div>


            <button type="submit">
                Update Jam Operasional
            </button>


            <a
                href="{{
                    route(
                        'admin.jam-operasional.index',
                        $jamOperasional->id_outlet
                    )
                }}"
                style="margin-left: 10px;"
            >
                Kembali
            </a>

        </form>

    </div>


    <script>

        const tutupCheckbox =
            document.getElementById('tutup');

        const jamBukaGroup =
            document.getElementById('jam-buka-group');

        const jamTutupGroup =
            document.getElementById('jam-tutup-group');

        const jamBukaInput =
            document.getElementById('jam_buka');

        const jamTutupInput =
            document.getElementById('jam_tutup');


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
