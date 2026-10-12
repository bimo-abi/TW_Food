
@extends('admin.layouts.app')

@section('title', 'Konten Beranda - Admin TWFood')

@section('page-title', 'Konten Beranda')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1>Konten Beranda</h1>
            <p>Kelola informasi yang ditampilkan pada halaman Home TWFOOD.</p>
        </div>

        <a href="{{ route('admin.konten-beranda.create') }}"
            style="background: #198754; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">
            + Tambah Konten
        </a>
    </div>

    {{-- Pesan sukses --}}
    @if (session('success'))
        <div style="background: #d1e7dd; color: #0f5132; padding: 12px; margin-bottom: 20px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan error --}}
    @if (session('error'))
        <div style="background: #f8d7da; color: #842029; padding: 12px; margin-bottom: 20px; border-radius: 5px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Gambar</th>
                    <th>Judul</th>
                    <th>Jenis Konten</th>
                    <th>Status</th>
                    <th>Urutan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($konten as $item)
                    <tr>
                        <td>{{ $konten->firstItem() + $loop->index }}</td>

                        <td>
                            @if ($item->gambar)
                                <img src="{{ asset('storage/' . $item->gambar) }}"
                                    alt="{{ $item->judul }}"
                                    width="80"
                                    height="60"
                                    style="object-fit: cover; border-radius: 5px;">
                            @else
                                Tidak ada gambar
                            @endif
                        </td>

                        <td>
                            <strong>{{ $item->judul }}</strong>
                            <br>
                            <small>
                                {{ \Illuminate\Support\Str::limit($item->deskripsi ?? '', 60) }}
                            </small>
                        </td>

                        <td>
                            @switch($item->jenis_konten)
                                @case('informasi_produk')
                                    Informasi Produk
                                    @break
                                @case('informasi_bahan')
                                    Informasi Bahan
                                    @break
                                @case('informasi_merek')
                                    Informasi Merek
                                    @break
                                @case('acara')
                                    Acara
                                    @break
                                @default
                                    {{ $item->jenis_konten }}
                            @endswitch
                        </td>

                        <td>
                            @if ($item->status_aktif)
                                <span style="color: #198754; font-weight: bold;">Aktif</span>
                            @else
                                <span style="color: #dc3545; font-weight: bold;">Nonaktif</span>
                            @endif
                        </td>

                        <td>{{ $item->urutan }}</td>

                        <td>
                            <a href="{{ route('admin.konten-beranda.edit', $item->id_konten) }}">
                                <button type="button">Edit</button>
                            </a>

                            <form action="{{ route('admin.konten-beranda.destroy', $item->id_konten) }}"
                                method="POST"
                                style="display: inline;"
                                onsubmit="return confirm('Yakin ingin menghapus konten ini?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada Konten Beranda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $konten->links() }}
        </div>
    </div>

@endsection
