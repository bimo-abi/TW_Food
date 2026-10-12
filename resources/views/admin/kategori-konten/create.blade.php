
@extends('admin.layouts.app')

@section('content')
<div style="padding: 20px;">

    <h1>Tambah Kategori Konten</h1>
    <p>Tambahkan kategori baru untuk Konten Beranda TWFOOD.</p>

    @if ($errors->any())
        <div style="padding: 10px; margin-bottom: 15px; background: #f8d7da; color: #842029;">
            <ul style="margin: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.kategori-konten.store') }}"
        method="POST"
    >
        @csrf

        <div style="margin-bottom: 15px;">
            <label for="nama_kategori">Nama Kategori</label>

            <input
                type="text"
                name="nama_kategori"
                id="nama_kategori"
                value="{{ old('nama_kategori') }}"
                maxlength="100"
                required
                style="display: block; width: 100%; padding: 10px; box-sizing: border-box;"
            >
        </div>

        <div style="margin-bottom: 15px;">
            <label for="deskripsi">Deskripsi</label>

            <textarea
                name="deskripsi"
                id="deskripsi"
                rows="4"
                style="display: block; width: 100%; padding: 10px; box-sizing: border-box;"
            >{{ old('deskripsi') }}</textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label>
                <input
                    type="checkbox"
                    name="status_aktif"
                    value="1"
                    @checked(old('status_aktif', true))
                >
                Kategori Aktif
            </label>
        </div>

        <button
            type="submit"
            style="padding: 10px 15px; background: #198754; color: white; border: none; cursor: pointer;"
        >
            Simpan Kategori
        </button>

        <a
            href="{{ route('admin.kategori-konten.index') }}"
            style="margin-left: 10px;"
        >
            Kembali
        </a>

    </form>

</div>
@endsection
