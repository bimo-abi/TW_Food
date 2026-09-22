<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Produk - TWFOOD</title>
</head>

<body>

    <h1>Edit Produk TWFOOD</h1>

    <form action="/produk/{{ $produk->id }}" method="POST">

        @csrf
        @method('PUT')

        <div>
            <label>Nama Produk</label>
            <br>

            <input
                type="text"
                name="nama_produk"
                value="{{ $produk->nama_produk }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Harga</label>
            <br>

            <input
                type="number"
                name="harga"
                value="{{ $produk->harga }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Stok</label>
            <br>

            <input
                type="number"
                name="stok"
                value="{{ $produk->stok }}"
                required
            >
        </div>

        <br>

        <button type="submit">
            Update Produk
        </button>

        <a href="/produk">
            Kembali
        </a>

    </form>

</body>
</html>
