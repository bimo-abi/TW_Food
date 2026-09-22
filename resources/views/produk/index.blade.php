<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produk TWFOOD</title>
</head>

<body>

    <h1>Daftar Produk TWFOOD</h1>
    <a href="/produk/create">
        + Tambah Produk
    </a>

    <br><br>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($produk as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_produk }}</td>
                    <td>
                        Rp {{ number_format($item->harga, 0, ',', '.') }}
                    </td>
                    <td>{{ $item->stok }}</td>
                    <td>

                        <a href="/produk/{{ $item->id }}/edit">
                            Edit
                        </a>

                        <form action="/produk/{{ $item->id }}" method="POST" style="display: inline;">

                            @csrf
                            @method('DELETE')

                            <button type="submit" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                Hapus
                            </button>

                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
