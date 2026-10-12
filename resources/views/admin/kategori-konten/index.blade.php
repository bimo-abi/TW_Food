
@extends('admin.layouts.app')

@section('content')
<div style="padding: 20px;">

    <h1>Kategori Konten</h1>

    <p>Kelola kategori untuk Konten Beranda TWFOOD.</p>

    @if (session('success'))
        <div style="padding: 10px; margin-bottom: 15px; background: #d1e7dd; color: #0f5132;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="padding: 10px; margin-bottom: 15px; background: #f8d7da; color: #842029;">
            {{ session('error') }}
        </div>
    @endif

    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.kategori-konten.create') }}"
           style="display: inline-block; padding: 10px 15px; background: #198754; color: white; text-decoration: none;">
            + Tambah Kategori
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd;">No.</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Nama Kategori</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Slug</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Deskripsi</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($kategori as $item)
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $kategori->firstItem() + $loop->index }}
                    </td>

                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $item->nama_kategori }}
                    </td>

                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $item->slug }}
                    </td>

                    <td style="padding: 10px; border: 1px solid #ddd;">
                        {{ $item->deskripsi ?? '-' }}
                    </td>

                    <td style="padding: 10px; border: 1px solid #ddd;">
                        @if ($item->status_aktif)
                            <span style="color: green;">Aktif</span>
                        @else
                            <span style="color: red;">Nonaktif</span>
                        @endif
                    </td>

                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <a href="{{ route('admin.kategori-konten.edit', $item->id_kategori) }}">
                            Edit
                        </a>

                        <form
                            action="{{ route('admin.kategori-konten.destroy', $item->id_kategori) }}"
                            method="POST"
                            style="display: inline;"
                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    style="margin-left: 8px; color: red; cursor: pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 15px; text-align: center;">
                        Belum ada kategori konten.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $kategori->links() }}
    </div>

</div>
@endsection
