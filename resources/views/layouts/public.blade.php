<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'TWFood - Lezat dan Sehat')
    </title>

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

        /* Thumbnail & empty state */
.produk-thumb {
    width: 64px;
    height: 64px;
    object-fit: cover;
}

.produk-empty-icon {
    font-size: 2rem;
}

/* Switch status */
.produk-status-form {
    display: inline-flex;
    align-items: center;
    width: 150px;
}

.produk-status-switch {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
}

.produk-status-switch .form-check-input {
    margin: 0;
    flex-shrink: 0;
    cursor: pointer;
}

.produk-status-switch .form-check-input:checked {
    background-color: #2fb344;
    border-color: #2fb344;
}

.produk-status-switch .form-check-input:not(:checked) {
    background-color: #d63939;
    border-color: #d63939;
}

.produk-status-label {
    width: 64px;
    font-size: 0.875rem;
    font-weight: 700;
    white-space: nowrap;
}

.produk-status-label.is-aktif    { color: #2fb344; }
.produk-status-label.is-nonaktif { color: #d63939; }
    </style>

    {{-- 1) Gaya bersama: warna, header, footer, tombol (public/css/layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    {{-- 2) Gaya khusus tiap halaman (mis. home.css) diisi lewat @push('styles') di halaman itu --}}
    @stack('styles')
</head>

<body class="@yield('body_class')">

    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    @stack('scripts')
</body>

</html>
