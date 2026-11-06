{{-- resources/views/layouts/public.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'TWFood - Lezat dan Sehat')
    </title>

    {{-- CSS LAMA: tetap dipakai halaman yang belum diubah (produk, resep, outlet, kontak, detail).
         Aturan navbar dan footer lama sudah dihapus karena diganti header/footer baru. --}}
    <style>
        :root {
            --text-light: #FFF5F1;
            --text-dark: #3A001E;
            --main-color: #E01F20;
            --main-dark: #8F0E0E;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
        }

        /* CONTAINER */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }

        /* HERO */
        .hero {
            padding: 100px 20px;
            text-align: center;
            background: #f5f5f5;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 20px;
            color: #666;
        }

        /* BUTTON */
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: var(--main-color);
            color: var(--text-light);
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: var(--main-dark);
            opacity: 0.85;
        }

        /* SECTION */
        .section {
            padding: 70px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #666;
        }

        /* PRODUCT */
        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .product-image {
            display: block;
            align-content: center;
            align-items: center;
            text-align: center;
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #f5f5f5;
        }

        .product-content {
            padding: 20px;
        }

        .product-content h3 {
            margin-top: 0;
        }

        .product-content p {
            color: #666;
            line-height: 1.5;
        }

        /* CONTENT */
        .content-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .content-card {
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .content-card h3 {
            margin-top: 0;
        }

        /* OUTLET */
        .outlet {
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .products {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .products {
                grid-template-columns: 1fr;
            }

            .content-list {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 36px;
            }
        }

        /* RESEP */
        .resep-deskripsi {
            text-align: left;
            max-width: 700px;
            margin: 0 auto 24px;
            line-height: 1.7;
        }

        .resep-deskripsi p {
            margin: 0 0 12px;
        }

        .resep-deskripsi ul,
        .resep-deskripsi ol {
            padding-left: 24px;
            margin: 0 0 12px;
        }

        .resep-deskripsi a {
            color: #206bc4;
            text-decoration: underline;
        }
    </style>

    {{-- CSS TEMA BARU (file: public/css/twfood-theme.css), dipasang setelah CSS lama --}}
    <link rel="stylesheet" href="{{ asset('css/twfood-theme.css') }}">

    @stack('styles')
</head>

{{-- Halaman yang sudah memakai desain baru mengisi @section('body_class', 'tw').
     Halaman lain dibiarkan kosong supaya tampilannya tidak rusak. --}}
<body class="@yield('body_class')">

    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    @stack('scripts')
</body>

</html>