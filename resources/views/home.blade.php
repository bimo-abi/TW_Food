<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TW FOOD - Kuliner &amp; Grosir Segar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- 1) Gaya bersama: warna, header & footer --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    {{-- 2) Gaya khusus beranda (dimuat setelah layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>

@include('partials.header')

@php
    // Ubah nomor ini kalau nomor WhatsApp TWFood berganti
    $waNumber = '6283893395318';

    // Ganti $produk kalau nama variabel di controller-mu berbeda.
    $semuaProduk = collect($produk ?? $produks ?? []);

    // Produk unggulan yang ditampilkan, urut sesuai desain.
    // Cocokkan dengan sebagian nama produk di database (huruf besar/kecil tidak berpengaruh).
    $unggulanNama = ['kaldu jamur tiram', 'kecap jamur tiram', 'kerupuk jamur tiram'];

    $featured = collect($unggulanNama)
        ->map(fn($kunci) => $semuaProduk->first(
            fn($p) => str_contains(strtolower(data_get($p, 'nama_produk', data_get($p, 'nama', ''))), $kunci)
        ))
        ->filter()
        ->values();

    // Kalau ada yang tidak ketemu, lengkapi dengan produk lain sampai 3
    if ($featured->count() < 3) {
        $featured = $featured->concat(
            $semuaProduk->reject(fn($p) => $featured->contains($p))
        )->take(3);
    }

    // Keunggulan: [emoji cadangan, file ikon di public/images, judul, deskripsi]
    $features = [
        ['🛒', 'icon-keranjang.png', 'Eceran tanpa minimal order', 'Beli satuan untuk kebutuhan keluarga dan acara pribadi'],
        ['🏪', 'icon-toko.png',      'Harga grosir untuk usaha',    'Akses kuota grosir, pesanan partai besar, dan pre-order berkala.'],
        ['🚚', 'icon-truk.png',      'Dikirim cepat',               'Bahan segera diambil langsung dari sumber dan dikirim hari ini.'],
    ];
@endphp

<main class="home">

    {{-- ===== HERO ===== --}}
    <section class="home-hero home-container">
        <div class="home-hero__text">
            <span class="home-badge">KULINER &amp; GROSIR SEGAR TERPERCAYA</span>

            <h1>Kebutuhan Kuliner Harian &amp; Pasokan Usaha Lebih Hemat</h1>

            <p>Pesan eceran tanpa minimal order atau dapatkan harga grosir terbaik langsung untuk kebutuhan dapur dan resto anda.</p>

            <a href="{{ route('produk.public') }}" class="home-btn home-btn--orange">Masuk Sekarang &rarr;</a>

            <a href="{{ route('produk.public') }}" class="home-hero__link">Jelajahi menu terlebih dahulu</a>
            <small class="home-hero__terms">Dengan melanjutkan anda menyetujui Syarat &amp; Ketentuan TWFOOD v1.3</small>
        </div>

        <div class="home-hero__image">
            <span class="home-hero__tag">Langsung dari sumber</span>
            {{-- Foto ada di public/images/Foto-beranda.jpg --}}
            <img src="{{ asset('images/Foto-beranda.jpg') }}" alt="Kaldu Jamur Tiram TWFood"
                 onerror="this.style.display='none'">
        </div>
    </section>

    {{-- ===== 3 KEUNGGULAN ===== --}}
    <section class="home-container home-features">
        @foreach($features as [$emoji, $icon, $judul, $isi])
            <div class="home-card home-feature">
                <span class="home-feature__icon">
                    {{-- Kalau file ikon belum ada, otomatis diganti emoji --}}
                    <img src="{{ asset('images/' . $icon) }}" alt=""
                         onerror="this.replaceWith(document.createTextNode('{{ $emoji }}'))">
                </span>
                <h3>{{ $judul }}</h3>
                <p>{{ $isi }}</p>
            </div>
        @endforeach
    </section>

    {{-- ===== PRODUK UNGGULAN ===== --}}
    <section class="home-container home-section">
        <h2>Produk Unggulan</h2>
        <p class="home-section__sub">Olahan plant-based dari jamur dan hasil tani Jember.</p>

        <div class="home-products">
            @forelse($featured as $p)
                @php
                    // Semua nama kolom ada di sini. Kalau di databasemu beda, cukup ubah di blok ini.
                    $nama         = data_get($p, 'nama_produk', data_get($p, 'nama', 'Produk'));
                    $deskripsi    = data_get($p, 'deskripsi_produk', data_get($p, 'deskripsi', ''));
                    $foto         = data_get($p, 'foto', data_get($p, 'gambar'));
                    $kategori     = data_get($p, 'kategori.nama', data_get($p, 'kategori', 'Produk'));
                    $eceran       = data_get($p, 'harga_eceran', data_get($p, 'harga'));
                    $grosir       = data_get($p, 'harga_grosir');
                    $minGrosir    = data_get($p, 'minimal_grosir', data_get($p, 'min_grosir'));
                    $satuan       = data_get($p, 'satuan', 'Pc');
                    $satuanGrosir = data_get($p, 'satuan_grosir', $satuan);

                    // Foto produk: pakai foto upload (storage) kalau ada,
                    // kalau tidak, pakai foto di public/images sesuai kata kunci nama produk.
                    $fotoLokal = collect([
                        'kaldu'   => 'kaldu-jamur-tiram-shitake.jpg',
                        'kecap'   => 'kecap-jamur-tiram.jpg',
                        'kerupuk' => 'kerupuk-jamur-tiram.jpg',
                    ])->first(fn($file, $kunci) => str_contains(strtolower($nama), $kunci));

                    $fotoUrl = $foto
                        ? asset('storage/' . $foto)
                        : ($fotoLokal ? asset('images/' . $fotoLokal) : null);

                    // Varian ukuran (mis. Kecap: M, P, A). Relasi $p->varian dengan kolom: kode, harga, harga_grosir
                    $varian = collect(data_get($p, 'varian', []));
                    $fmt = fn($n) => 'Rp' . number_format((float) $n, 0, ',', '.');

                    $hargaVarian  = $varian->filter(fn($v) => data_get($v, 'harga'))
                                           ->map(fn($v) => data_get($v, 'kode') . ' ' . $fmt(data_get($v, 'harga')))
                                           ->implode(' · ');
                    $grosirVarian = $varian->filter(fn($v) => data_get($v, 'harga_grosir'))
                                           ->map(fn($v) => data_get($v, 'kode') . ' ' . $fmt(data_get($v, 'harga_grosir')))
                                           ->implode(' · ');

                    $pesan = urlencode('Halo TWFood, saya mau pesan ' . $nama);
                @endphp

                <article class="home-card home-product">
                    <div class="home-product__photo">
                        @if($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="{{ $nama }}" loading="lazy"
                                 onerror="this.style.display='none'">
                        @endif
                    </div>

                    <div class="home-product__body">
                        <span class="home-product__cat">{{ $kategori }}</span>
                        <h3>{{ $nama }}</h3>
                        <p class="home-product__desc">{{ \Illuminate\Support\Str::limit($deskripsi, 200) }}</p>

                        <div class="home-product__price">
                            @if($hargaVarian)
                                <strong>{{ $hargaVarian }}</strong>
                                <small>Harga eceran</small>
                            @elseif($eceran)
                                <strong>{{ $fmt($eceran) }}/{{ $satuan }}</strong>
                                <small>Harga eceran</small>
                            @else
                                <strong>Hubungi kami</strong>
                            @endif
                        </div>

                        @if($grosirVarian)
                            <div class="home-product__grosir">Grosir: {{ $grosirVarian }}</div>
                        @elseif($grosir)
                            <div class="home-product__grosir">
                                Grosir: {{ $fmt($grosir) }}/{{ $satuanGrosir }}
                                @if($minGrosir) (min. {{ $minGrosir }}) @endif
                            </div>
                        @endif

                        <a href="https://wa.me/{{ $waNumber }}?text={{ $pesan }}" target="_blank" rel="noopener"
                           class="home-btn home-btn--wa">
                            <img src="{{ asset('images/icon-wa.png') }}" alt="" width="20" height="20">
                            Pesan Via WhatsApp
                        </a>
                    </div>
                </article>
            @empty
                <p class="home-empty">Produk belum tersedia.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== TENTANG ===== --}}
    <section class="home-container home-section">
        <div class="home-card home-about">
            <h2>Tentang TW FOOD</h2>
            <p>PT Tewil Indonesia Sejahtera (TWFood) berdiri sejak 2017 sebagai produsen olahan sehat plant-based yang ekonomis dan praktis, berbasis di Jember, Jawa Timur. Produknya antara lain kaldu jamur, kecap jamur, kerupuk jamur, dan stik jantung pisang, dibuat dari bahan alami langsung dari petani Jember tanpa MSG dan pengawet kimia.</p>
        </div>
    </section>

</main>

@include('partials.footer')

</body>
</html>