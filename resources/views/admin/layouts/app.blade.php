<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin TWFood')</title>

    {{-- Tabler Core CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/tabler/css/tabler.min.css') }}">

    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="{{ asset('assets/tabler/icons/tabler-icons.min.css') }}">

    {{-- Admin layout CSS (pindah css admin ke public/css/admin/admin.css)--}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/tema.css') }}">
    @stack('styles')

</head>

<body>
    <div class="admin-wrapper">

        {{-- SIDEBAR --}}
        <aside class="sidebar">

            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                <span class="tw-logo">TWfood</span>
                <span class="tw-logo-teks">TWFood<small>Tewil Food</small></span>
            </a>

            <div class="sidebar-label">Menu Utama</div>

            <nav class="sidebar-menu">

                {{-- 1. Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="ti ti-dashboard"></i>
                    <span>Dashboard</span>
                </a>

                {{-- Verifikasi Pelanggan Toko dan Horeca --}}
                <a href="{{ route('admin.verifikasi-pelanggan.index') }}"
                    class="{{ request()->routeIs('admin.verifikasi-pelanggan.*') ? 'active' : '' }}">
                    <i class="ti ti-user-check"></i>
                    <span>Verifikasi Pelanggan</span>
                </a>

                {{-- 3. Produk (termasuk Varian, Harga, Stok, Pre-order) --}}
                <a href="{{ route('admin.produk.index') }}"
                    class="{{ request()->routeIs('admin.produk.*') || request()->routeIs('admin.varian.*') || request()->routeIs('admin.harga.*') ? 'active' : '' }}">
                    <i class="ti ti-package"></i>
                    <span>Produk</span>
                </a>

                {{-- 4. Pesanan dipisahkan berdasarkan tipe pemenuhan --}}
                <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => 'reguler']) }}"
                    class="{{ request()->routeIs('admin.pesanan.*') && request('jenis_pemenuhan', 'reguler') === 'reguler' ? 'active' : '' }}">
                    <i class="ti ti-shopping-cart"></i>
                    <span>Pesanan Reguler</span>
                </a>

                <a href="{{ route('admin.pesanan.index', ['jenis_pemenuhan' => 'pre_order']) }}"
                    class="{{ request()->routeIs('admin.pesanan.*') && request('jenis_pemenuhan') === 'pre_order' ? 'active' : '' }}">
                    <i class="ti ti-calendar-event"></i>
                    <span>Pesanan Pre-order</span>
                </a>

                {{-- 5. Pembayaran --}}
                {{-- <a href="{{ route('admin.pembayaran.index') }}"
                    class="{{ request()->routeIs('admin.pembayaran.*') ? 'active' : '' }}">
                    <i class="ti ti-credit-card"></i>
                    <span>Pembayaran</span>
                </a> --}}

                {{-- 6. Retur --}}
                <a href="{{ route('admin.retur.index') }}"
                    class="{{ request()->routeIs('admin.retur.*') ? 'active' : '' }}">
                    <i class="ti ti-package-export"></i>
                    <span>Retur</span>
                </a>

                {{-- 7. Promosi --}}
                <a href="{{ route('admin.promosi.index') }}"
                    class="{{ request()->routeIs('admin.promosi.*') ? 'active' : '' }}">
                    <i class="ti ti-discount-2"></i>
                    <span>Promosi</span>
                </a>

                {{-- 9. Laporan --}}
                <a href="{{ route('admin.laporan.index') }}"
                    class="{{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                    <i class="ti ti-file-report"></i>
                    <span>Laporan</span>
                </a>
                {{-- 10. Analitik --}}
                {{-- <a href="#">
                    <i class="ti ti-chart-bar"></i>
                    <span>Analitik</span>
                </a> --}}

                {{-- 11. Resep --}}
                <a href="{{ route('admin.resep.index') }}"
                    class="{{ request()->routeIs('admin.resep.*') ? 'active' : '' }}">
                    <i class="ti ti-book-2"></i>
                    <span>Resep</span>
                </a>

                {{-- 12. Konten Beranda --}}
                {{-- <a href="{{ route('admin.konten-beranda.index') }}"
                    class="{{ request()->routeIs('admin.konten-beranda.*') ? 'active' : '' }}">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>Konten Beranda</span>
                </a> --}}

                {{-- 13. Outlet (termasuk Jam Operasional) --}}
                <a href="{{ route('admin.outlet.index') }}"
                    class="{{ request()->routeIs('admin.outlet.*') || request()->routeIs('admin.jam-operasional.*') ? 'active' : '' }}">
                    <i class="ti ti-building-store"></i>
                    <span>Outlet</span>
                </a>

                {{-- 14. Pengaturan Mitra --}}
                <a href="{{ route('admin.pengaturan-mitra.index') }}"
                    class="{{ request()->routeIs('admin.pengaturan-mitra.*') ? 'active' : '' }}">
                    <i class="ti ti-settings"></i>
                    <span>Pengaturan Mitra</span>
                </a>

            </nav>

        </aside>
        {{-- KONTEN UTAMA --}}
        <main class="main-content">

            <header class="topbar">
                <h3>@yield('page-title', 'Dashboard')</h3>

                <div class="topbar-user">
                    <span>{{ auth()->user()->nama }}</span>

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

    <script src="{{ asset('assets/tabler/js/tabler.min.js') }}"></script>

    @stack('scripts')

</body>

</html>