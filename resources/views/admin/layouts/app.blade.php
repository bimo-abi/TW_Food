<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin TWFood')</title>

    {{-- Tabler UI: sudah menyertakan Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css">

    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/tabler-icons.min.css">

    <style>
        body {
            margin: 0;
            background: #f5f7fb;
            color: #182433;
            font-family: Arial, sans-serif;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            flex-shrink: 0;
            background: #fff;
            border-right: 1px solid #e6e8eb;
            padding: 24px 16px;
        }

        .sidebar-brand {
            display: block;
            margin: 0 12px 28px;
            color: #206bc4;
            font-size: 25px;
            font-weight: 700;
            text-decoration: none;
        }

        .sidebar-label {
            margin: 20px 12px 8px;
            color: #8a97a6;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 6px;
            color: #354052;
            font-size: 14px;
            text-decoration: none;
            transition: background .15s, color .15s;
        }

        .sidebar-menu a:hover {
            background: #f0f6ff;
            color: #206bc4;
        }

        .sidebar-menu a i {
            font-size: 19px;
        }

        .main-content {
            min-width: 0;
            flex: 1;
        }

        .topbar {
            min-height: 72px;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-bottom: 1px solid #e6e8eb;
        }

        .topbar h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .page-content {
            padding: 28px;
        }

        .logout-button {
            border: 1px solid #d63939;
            border-radius: 6px;
            padding: 8px 14px;
            background: #d63939;
            color: #fff;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #b52b2b;
        }

        /* Kompatibilitas dasar dengan halaman CRUD lama */
        .page-content .card {
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            background: #fff;
        }

        .page-content table {
            width: 100%;
        }

        .page-content img {
            max-width: 100%;
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: 0;
                border-bottom: 1px solid #e6e8eb;
            }

            .sidebar-menu {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .topbar {
                padding: 16px;
            }

            .page-content {
                padding: 16px;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

    <div class="admin-wrapper">

        {{-- SIDEBAR --}}
        <aside class="sidebar">

            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <i class="ti ti-leaf"></i>
                TWFood
            </a>

            <div class="sidebar-label">Menu Utama</div>

            <nav class="sidebar-menu">

                <a href="{{ route('admin.dashboard') }}">
                    <i class="ti ti-dashboard"></i>
                    Dashboard
                </a>

                <a href="{{ route('admin.produk.index') }}">
                    <i class="ti ti-package"></i>
                    Produk
                </a>

                <a href="{{ route('admin.resep.index') }}">
                    <i class="ti ti-book-2"></i>
                    Resep
                </a>

                <a href="{{ route('admin.pesanan.index') }}">
                    <i class="ti ti-shopping-cart"></i>
                    Pesanan
                </a>

                <a href="{{ route('admin.outlet.index') }}">
                    <i class="ti ti-building-store"></i>
                    Outlet
                </a>

                <a href="{{ route('admin.pengaturan-mitra.index') }}">
                    <i class="ti ti-settings"></i>
                    Pengaturan Mitra
                </a>

                <a href="{{ route('admin.konten-beranda.index') }}">
                    <i class="ti ti-layout-dashboard"></i>
                    Konten Beranda
                </a>

                <a href="{{ route('admin.kategori-konten.index') }}">
                    <i class="ti ti-category"></i>
                    Kategori Konten
                </a>

            </nav>

            <div class="sidebar-label">Menu Lainnya</div>

            <nav class="sidebar-menu">
                <a href="#">
                    <i class="ti ti-users"></i>
                    Pelanggan
                </a>

                <a href="#">
                    <i class="ti ti-discount-2"></i>
                    Promosi
                </a>

                <a href="#">
                    <i class="ti ti-report-money"></i>
                    Keuangan
                </a>
            </nav>

        </aside>

        {{-- KONTEN UTAMA --}}
        <main class="main-content">

            <header class="topbar">

                <h3>@yield('page-title', 'Dashboard')</h3>

                <div class="topbar-user">
                    <span>
                        {{ auth()->user()->nama }}
                    </span>

                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                        @csrf

                        <button type="submit" class="logout-button">
                            <i class="ti ti-logout"></i>
                            Logout
                        </button>
                    </form>
                </div>

            </header>

            <section class="page-content">
                @yield('content')
            </section>

        </main>

    </div>

    {{-- Tabler JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>

</body>

</html>
