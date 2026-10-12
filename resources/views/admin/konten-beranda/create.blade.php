@extends('admin.layouts.app')

@section('title', 'Tambah Konten Beranda - Admin TWFood')

@section('page-title', 'Tambah Konten Beranda')

@section('content')

    <h1>Tambah Konten Beranda</h1>
    <p>Tambahkan informasi yang akan ditampilkan pada halaman Home TWFOOD.</p>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div style="background: #f8d7da; color: #842029; padding: 12px; margin-bottom: 20px; border-radius: 5px;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="padding: 20px;">
        <form action="{{ route('admin.konten-beranda.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            {{-- Judul --}}
            <div style="margin-bottom: 15px;">
                <label for="judul">Judul Konten</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" maxlength="150"
                    placeholder="Contoh: Mengenal Bahan Kecap TWFOOD" required
                    style="width: 100%; padding: 10px; margin-top: 5px;">
            </div>

            {{-- Deskripsi --}}
            <div style="margin-bottom: 15px;">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Tuliskan informasi yang ingin disampaikan."
                    style="width: 100%; padding: 10px; margin-top: 5px;">{{ old('deskripsi') }}</textarea>
            </div>

            {{-- Gambar --}}
            <div style="margin-bottom: 15px;">
                <label for="gambar">Gambar Konten</label>
                <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"
                    style="display: block; margin-top: 5px;">

                <small>Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</small>
            </div>


            <div style="margin-bottom: 15px;">
                <label for="id_kategori">Kategori Konten</label>

                <select name="id_kategori" id="id_kategori" required style="width: 100%; padding: 10px;">
                    <option value="">-- Pilih Kategori --</option>

                    @foreach ($kategori as $item)
                        <option value="{{ $item->id_kategori }}" @selected(old('id_kategori') == $item->id_kategori)>
                            {{ $item->nama_kategori }}
                        </option>
                    @endforeach
                </select>

                @error('id_kategori')
                    <small style="color: red;">{{ $message }}</small>
                @enderror
            </div>

            {{-- Urutan --}}
            <div style="margin-bottom: 15px;">
                <label for="urutan">Urutan Tampilan</label>
                <input type="number" id="urutan" name="urutan" value="{{ old('urutan', 0) }}" min="0" max="65535" required
                    style="width: 100%; padding: 10px; margin-top: 5px;">

                <small>Angka lebih kecil akan ditampilkan lebih dahulu.</small>
            </div>
            <div style="margin-bottom: 20px;">
                <label>
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', true))>
                    Aktifkan konten
                </label>

                <p style="font-size: 12px; color: #666;">
                    Jika dicentang, konten berstatus aktif. Penayangan di Public Website
                    tetap harus mengikuti periode dan filter pada halaman publik.
                </p>
            </div>

            {{-- Tombol --}}
            <button type="submit">Simpan Konten</button>

            <a href="{{ route('admin.konten-beranda.index') }}" style="display: inline-block; margin-left: 8px;">
                <button type="button">Kembali</button>
            </a>

        </form>
    </div>

@endsection
