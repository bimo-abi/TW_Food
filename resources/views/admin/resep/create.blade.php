@extends('admin.layouts.app')


@section('title', 'Tambah Resep - Admin TWFood')


@section('page-title', 'Tambah Resep')


@section('content')

    <h1>
        Tambah Resep
    </h1>


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


    <div class="card">

        <form action="{{ route('admin.resep.store') }}" method="POST" enctype="multipart/form-data">

            @csrf


            {{-- Judul --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Judul Resep
                </label>

                <br>

                <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Tumis Jamur Tiram" required
                    maxlength="100" style="
                            width: 100%;
                            padding: 10px;
                        ">

            </div>


            {{-- Deskripsi --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Deskripsi
                </label>

                <br>

                <textarea name="deskripsi" rows="5" maxlength="5000" placeholder="Masukkan deskripsi resep" style="
                            width: 100%;
                            padding: 10px;
                        ">{{ old('deskripsi') }}</textarea>

            </div>


            {{-- Foto --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Foto Resep
                </label>

                <br>

                <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">

                <p>
                    Format: JPG, JPEG, PNG, WEBP.
                    Maksimal 2 MB.
                </p>

            </div>


            {{-- Bahan --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Bahan
                </label>

                <br>

                <textarea name="bahan" rows="8" maxlength="10000" placeholder="Tuliskan bahan-bahan yang diperlukan" style="
                            width: 100%;
                            padding: 10px;
                        ">{{ old('bahan') }}</textarea>

            </div>


            {{-- Langkah Pembuatan --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Langkah Pembuatan
                </label>

                <br>

                <textarea name="langkah_pembuatan" rows="10" maxlength="10000"
                    placeholder="Tuliskan langkah-langkah pembuatan resep" style="
                            width: 100%;
                            padding: 10px;
                        ">{{ old('langkah_pembuatan') }}</textarea>

            </div>


            {{-- Waktu Memasak --}}

            <div style="margin-bottom: 15px;">

                <label>
                    Waktu Memasak (menit)
                </label>

                <br>

                <input type="number" name="waktu_memasak" value="{{ old('waktu_memasak') }}" min="1" max="1440"
                    placeholder="Contoh: 30" style="
                            width: 100%;
                            padding: 10px;
                        ">

            </div>


            {{-- Tombol --}}

            <button type="submit">
                Simpan Resep
            </button>


            <a href="{{ route('admin.resep.index') }}">
                Kembali
            </a>

        </form>

    </div>

@endsection
