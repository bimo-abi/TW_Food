@extends('admin.layouts.app')


@section('title', 'Edit Outlet - Admin TWFood')


@section('page-title', 'Edit Outlet')


@section('content')

    <h1>
        Edit Outlet
    </h1>


    <p>
        Perbarui data outlet TWFood.
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
            action="{{ route('admin.outlet.update', $outlet->id_outlet) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            @method('PUT')


            {{-- Nama Outlet --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Nama Outlet
                </label>

                <br>

                <input
                    type="text"
                    name="nama_outlet"
                    value="{{ old('nama_outlet', $outlet->nama_outlet) }}"
                    maxlength="150"
                    required
                    style="width: 100%; padding: 10px;"
                >

            </div>


            {{-- Alamat --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Alamat
                </label>

                <br>

                <textarea
                    name="alamat"
                    rows="4"
                    required
                    style="width: 100%; padding: 10px;"
                >{{ old('alamat', $outlet->alamat) }}</textarea>

            </div>


            {{-- Nomor Telepon --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Nomor Telepon
                </label>

                <br>

                <input
                    type="text"
                    name="nomor_telepon"
                    value="{{ old('nomor_telepon', $outlet->nomor_telepon) }}"
                    maxlength="20"
                    style="width: 100%; padding: 10px;"
                >

                <small>
                    Maksimal 20 karakter.
                </small>

            </div>


            {{-- Latitude --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Latitude
                </label>

                <br>

                <input
                    type="number"
                    name="latitude"
                    value="{{ old('latitude', $outlet->latitude) }}"
                    min="-90"
                    max="90"
                    step="0.00000001"
                    style="width: 100%; padding: 10px;"
                >

                <small>
                    Contoh: -8.17242300
                </small>

            </div>


            {{-- Longitude --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Longitude
                </label>

                <br>

                <input
                    type="number"
                    name="longitude"
                    value="{{ old('longitude', $outlet->longitude) }}"
                    min="-180"
                    max="180"
                    step="0.00000001"
                    style="width: 100%; padding: 10px;"
                >

                <small>
                    Contoh: 113.70208300
                </small>

            </div>


            {{-- Deskripsi --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Deskripsi
                </label>

                <br>

                <textarea
                    name="deskripsi"
                    rows="5"
                    style="width: 100%; padding: 10px;"
                >{{ old('deskripsi', $outlet->deskripsi) }}</textarea>

            </div>


            {{-- Foto Saat Ini --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Foto Outlet Saat Ini
                </label>

                <br>

                @if ($outlet->foto)

                    <img
                        src="{{ asset('storage/' . $outlet->foto) }}"
                        alt="{{ $outlet->nama_outlet }}"
                        width="150"
                        height="150"
                        style="
                            object-fit: cover;
                            border-radius: 5px;
                            margin-top: 10px;
                        "
                    >

                @else

                    <p>
                        Belum ada foto.
                    </p>

                @endif

            </div>


            {{-- Ganti Foto --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Ganti Foto Outlet
                </label>

                <br>

                <input
                    type="file"
                    name="foto"
                    accept="image/jpeg,image/png,image/webp"
                >

                <br>

                <small>
                    Kosongkan jika tidak ingin mengganti foto.
                    Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
                </small>

            </div>


            {{-- Tombol --}}

            <button type="submit">
                Update Outlet
            </button>


            <a
                href="{{ route('admin.outlet.index') }}"
                style="margin-left: 10px;"
            >
                Kembali
            </a>

        </form>

    </div>

@endsection
