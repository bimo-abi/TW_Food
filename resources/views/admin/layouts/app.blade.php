<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Admin TWFood')
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 240px;
            background: #ffffff;
            border-right: 1px solid #ddd;
            padding: 20px;
        }

        .sidebar h2 {
            margin-top: 0;
            margin-bottom: 30px;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar-menu a {
            text-decoration: none;
            color: #333;
            padding: 12px;
            border-radius: 5px;
        }

        .sidebar-menu a:hover {
            background: #f0f0f0;
        }

        /* CONTENT */

        .main-content {
            flex: 1;
        }

        /* TOPBAR */

        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #ddd;
            padding: 15px 25px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h3 {
            margin: 0;
        }

        .logout-button {
            background: #dc3545;
            color: white;

            border: none;
            border-radius: 5px;

            padding: 9px 15px;

            cursor: pointer;
        }

        /* PAGE */

        .page-content {
            padding: 30px;
        }

        .card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }

        .cards {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }

        .card h3 {
            margin-top: 0;
            color: #555;
        }

        .number {
            font-size: 30px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }
    </style>

</head>


<body>

    <div class="admin-wrapper">


        {{-- SIDEBAR --}}

        <aside class="sidebar">

            <h2>
                TWFood
            </h2>


            <nav class="sidebar-menu">

                <a href="{{ route('admin.dashboard') }}">
                    Dashboard
                </a>

                <a href="{{ route('admin.produk.index') }}">
                    Produk
                </a>

                <a href="#">
                    Pesanan
                </a>

                <a href="#">
                    Pelanggan
                </a>

                <a href="#">
                    Promosi
                </a>

                <a href="#">
                    Keuangan
                </a>

                <a href="#">
                    Konten Beranda
                </a>

                <a href="#">
                    Outlet
                </a>

            </nav>

        </aside>


        {{-- MAIN CONTENT --}}

        <main class="main-content">


            {{-- TOPBAR --}}

            <header class="topbar">

                <h3>
                    @yield('page-title', 'Dashboard')
                </h3>


                <div>

                    <span>
                        {{ auth()->user()->nama }}
                    </span>


                    <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">

                        @csrf

                        <button type="submit" class="logout-button">
                            Logout
                        </button>

                    </form>

                </div>

            </header>


            {{-- PAGE CONTENT --}}

            <section class="page-content">

                @yield('content')

            </section>


        </main>

    </div>

</body>

</html>
