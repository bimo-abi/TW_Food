<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'TWFood - Lezat dan Sehat')
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
        }


        /* =========================
           NAVBAR
        ========================= */

        nav {
            padding: 20px 40px;
            border-bottom: 1px solid #ddd;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .logo a {
            text-decoration: none;
            color: inherit;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
        }

        .nav-menu a:hover {
            text-decoration: underline;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }


        /* =========================
           HERO
        ========================= */

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


        /* =========================
           BUTTON
        ========================= */

        .button {
            display: inline-block;

            margin-top: 20px;

            padding: 12px 20px;

            background: #333;
            color: white;

            text-decoration: none;

            border-radius: 5px;
        }

        .button:hover {
            opacity: 0.85;
        }


        /* =========================
           SECTION
        ========================= */

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


        /* =========================
           PRODUCT
        ========================= */

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


        /* =========================
           CONTENT
        ========================= */

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


        /* =========================
           OUTLET
        ========================= */

        .outlet {
            padding: 30px;

            border: 1px solid #ddd;
            border-radius: 8px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            padding: 40px 0;
            background: #f5f5f5;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .products {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            nav {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

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

    </style>

</head>


<body>


    {{-- =====================================================
         NAVBAR
    ====================================================== --}}

    <nav>

        <div class="logo">

            <a href="{{ route('home') }}">
                TWFOOD
            </a>

        </div>


        <div class="nav-menu">

            <a href="{{ route('home') }}">
                Home
            </a>

            <a href="{{ route('produk.public') }}">
                Produk
            </a>

            <a href="{{ route('resep.public') }}">
                Resep
            </a>

            <a href="{{ route('outlet.public') }}">
                Outlet
            </a>

            <a href="{{ route('kontak.public') }}">
                Kontak
            </a>

            <a href="#">
                Download Aplikasi
            </a>

        </div>

    </nav>


    {{-- =====================================================
         CONTENT
    ====================================================== --}}

    @yield('content')


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <footer>

        <div class="container">

            <strong>
                TWFOOD
            </strong>

            <p>
                Lezat dan Sehat
            </p>

            <p>
                &copy; {{ date('Y') }} TWFood.
                All rights reserved.
            </p>

        </div>

    </footer>


</body>

</html>
