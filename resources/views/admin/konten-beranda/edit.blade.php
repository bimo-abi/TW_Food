@extends('admin.layouts.app')

@section('title', 'Edit Konten Beranda - Admin TWFood')

@section('page-title', 'Edit Konten Beranda')

@section('content')

    <h1>Edit Konten Beranda</h1>
    <p>Perbarui informasi yang ditampilkan pada halaman Home TWFOOD.</p>

    {{-- Error validasi --}}
    @if ($errors->any())
        <div style="background: #f8d7da; color: #842029; padding: 12px; margin-bottom: 20px; border-radius: 5px;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" style="padding: 20px;">
        <form action="{{ route('admin.konten-beranda.update', $konten->id_konten) }}" method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div style="margin-bottom: 15px;">
                <label for="judul">Judul Konten</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $konten->judul) }}" maxlength="150"
                    required style="width: 100%; padding: 10px; margin-top: 5px;">
            </div>

            {{-- Deskripsi --}}
            <div style="margin-bottom: 15px;">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="5"
                    style="width: 100%; padding: 10px; margin-top: 5px;">{{ old('deskripsi', $konten->deskripsi) }}</textarea>
            </div>

            {{-- Gambar saat ini --}}
            <div style="margin-bottom: 15px;">
                <label>Gambar Saat Ini</label>
                <div style="margin-top: 8px;">
                    @if ($konten->gambar)
                        <img src="{{ asset('storage/' . $konten->gambar) }}" alt="{{ $konten->judul }}" width="180"
                            style="max-height: 150px; object-fit: cover; border-radius: 5px;">
                    @else
                        <p>Belum ada gambar.</p>
                    @endif
                </div>
            </div>

            {{-- Ganti gambar --}}
            <div style="margin-bottom: 15px;">
                <label for="gambar">Ganti Gambar</label>
                <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"
                    style="display: block; margin-top: 5px;">

                <small>
                    Kosongkan jika gambar tidak ingin diganti.
                    Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                </small>
            </div>

            //kategori
            <div style="margin-bottom: 15px;">
                <label for="id_kategori">Kategori Konten</label>

                <select name="id_kategori" id="id_kategori" required style="width: 100%; padding: 10px;">
                    <option value="">-- Pilih Kategori --</option>

                    @foreach ($kategori as $item)
                        <option value="{{ $item->id_kategori }}" @selected(old('id_kategori', $konten->id_kategori) == $item->id_kategori)>
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
                <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $konten->urutan) }}" min="0"
                    max="65535" required style="width: 100%; padding: 10px; margin-top: 5px;">
            </div>
            {{-- Status Aktif --}}
            <div style="margin-bottom: 20px;">
                <label>
                    <input type="checkbox" name="status_aktif" value="1" @checked(old('status_aktif', $konten->status_aktif))>
                    Aktifkan konten
                </label>

                <p style="font-size: 12px; color: #666;">
                    Konten nonaktif tidak boleh ditampilkan pada Public Website.
                </p>
            </div>

            {{-- Tombol --}}
            <button type="submit">Simpan Perubahan</button>

            <a href="{{ route('admin.konten-beranda.index') }}" style="display: inline-block; margin-left: 8px;">
                <button type="button">Kembali</button>
            </a>

        </form>
    </div>

@endsection
