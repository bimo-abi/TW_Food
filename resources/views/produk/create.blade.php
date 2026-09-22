<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk - TWFOOD</title>
</head>

<body>

    <h1>Tambah Produk TWFOOD</h1>

    <form action="/produk" method="POST">

        @csrf

        <div>
            <label>Nama Produk</label>
            <br>
            <input type="text" name="nama_produk" required>
        </div>

        <br>

        <div>
            <label>Harga</label>
            <br>
            <input type="number" name="harga" required>
        </div>

        <br>

        <div>
            <label>Stok</label>
            <br>
            <input type="number" name="stok" required>
        </div>

        <br>

        <button type="submit">
            Simpan Produk
        </button>

        <a href="/produk">
            Kembali
        </a>

    </form>

</body>
</html>
