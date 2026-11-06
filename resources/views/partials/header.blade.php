{{-- resources/views/partials/header.blade.php --}}
<header class="tw-nav">
    <a href="{{ route('home') }}" class="tw-logo" aria-label="Beranda TWFood">
        <img src="{{ asset('images/logo-twfood.png') }}" alt="TWFood">
        <span>
            <b>TW FOOD</b>
            <small>KULINER &amp; GROSIR SEGAR</small>
        </span>
    </a>

    <div class="tw-menu" role="navigation" aria-label="Menu utama">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
        <a href="{{ route('produk.public') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">Produk</a>

        {{-- Halaman Bahan dan Grosir belum ada. Ganti URL-nya setelah dibuat,
             atau pakai '#' dulu supaya tidak muncul 404. --}}
        <a href="{{ url('/bahan') }}" class="{{ request()->is('bahan*') ? 'active' : '' }}">Bahan</a>

        <a href="{{ route('resep.public') }}" class="{{ request()->routeIs('resep.*') ? 'active' : '' }}">Resep</a>

        <a href="{{ url('/grosir') }}" class="{{ request()->is('grosir*') ? 'active' : '' }}">Grosir</a>

        <a href="{{ route('outlet.public') }}" class="{{ request()->routeIs('outlet.*') ? 'active' : '' }}">Outlet</a>
    </div>

    <a href="https://play.google.com/" target="_blank" rel="noopener" class="tw-btn tw-btn-soft tw-btn-sm">Unduh Aplikasi</a>
</header>