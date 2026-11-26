{{-- resources/views/partials/header.blade.php --}}
<header class="tw-nav" data-public-header>
    <button class="tw-mobile-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="tw-public-menu">
        <span></span><span></span><span></span>
    </button>
    <a href="{{ route('home') }}" class="tw-logo" aria-label="Beranda TWFood">
        <img src="{{ asset('images/logo-twfood.png') }}" alt="TWFood">
        <span>
            <b>TW FOOD</b>
            <small>KULINER &amp; GROSIR SEGAR</small>
        </span>
    </a>

    <nav class="tw-menu" id="tw-public-menu" aria-label="Menu utama">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
        <a href="{{ route('produk.public') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">Produk</a>

        {{-- Halaman Bahan dan Grosir belum ada. Ganti URL-nya setelah dibuat,
             atau pakai '#' dulu supaya tidak muncul 404. --}}
        <a href="{{ url('/bahan') }}" class="{{ request()->is('bahan*') ? 'active' : '' }}">Bahan</a>

        <a href="{{ route('resep.public') }}" class="{{ request()->routeIs('resep.*') ? 'active' : '' }}">Resep</a>

        <a href="{{ url('/grosir') }}" class="{{ request()->is('grosir*') ? 'active' : '' }}">Grosir</a>

        <a href="{{ route('outlet.public') }}" class="{{ request()->routeIs('outlet.*') ? 'active' : '' }}">Outlet</a>
    </nav>

    <div class="tw-nav-actions">
        <button class="tw-theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema tampilan" title="Ganti tema">
            <span aria-hidden="true" data-theme-icon>◐</span><span data-theme-label>Tema</span>
        </button>
        <a href="https://play.google.com/" target="_blank" rel="noopener" class="tw-btn tw-btn-soft tw-btn-sm">Unduh Aplikasi</a>
    </div>
</header>
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script defer src="{{ asset('js/public-enhancements.js') }}"></script>