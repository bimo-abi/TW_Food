{{-- resources/views/layouts/public.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'TWFood - Lezat dan Sehat')
    </title>

    {{-- CSS LAMA untuk halaman yang belum dibuat ulang (resep, kontak, detail). Warna sudah memakai variabel tema (--tw-*) sehingga ikut berganti gelap/terang.
         Aturan navbar dan footer lama sudah dihapus karena diganti header/footer baru. --}}
    <style>
        :root {
            --text-light: #FFFFFF;
            --text-dark: var(--tw-tx);
            --main-color: var(--tw-or);
            --main-dark: var(--tw-or-d);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--tw-tx);
            background: var(--tw-bg);
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
            background: var(--tw-sf);
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 20px;
            color: var(--tw-mu);
        }

        /* BUTTON */
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: var(--tw-or);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background-color: var(--tw-or-d);
            opacity: 0.95;
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
            color: var(--tw-mu);
        }

        /* PRODUCT */
        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .product-card {
            border: 1px solid var(--tw-bd);
            border-radius: 8px;
            overflow: hidden;
            background: var(--tw-sf);
        }

        .product-image {
            display: block;
            align-content: center;
            align-items: center;
            text-align: center;
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: var(--tw-bd);
        }

        .product-content {
            padding: 20px;
        }

        .product-content h3 {
            margin-top: 0;
        }

        .product-content p {
            color: var(--tw-mu);
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
            border: 1px solid var(--tw-bd);
            border-radius: 8px;
        }

        .content-card h3 {
            margin-top: 0;
        }

        /* OUTLET */
        .outlet {
            padding: 30px;
            border: 1px solid var(--tw-bd);
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

    {{-- 1) Gaya bersama: warna, header, footer, tombol (public/css/layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    {{-- 2) Gaya khusus tiap halaman (mis. home.css) diisi lewat @push('styles') di halaman itu --}}
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