{{-- resources/views/home.blade.php  (VERSI FINAL) --}}
@extends('layouts.public')

@section('title', 'TWFood')
@section('body_class', 'tw')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

    @php
        $fotoPublik = function (array $namaFile) {
            foreach ($namaFile as $f) {
                if (file_exists(public_path('images/' . $f))) {
                    return asset('images/' . rawurlencode($f));
                }
            }
            return null;
        };

        $kunciUnggulan = ['kaldu jamur tiram', 'kecap jamur tiram', 'kerupuk jamur tiram'];
        $unggulan = collect($kunciUnggulan)
            ->map(fn ($kunci) => $produk->first(
                fn ($p) => str_contains(strtolower($p->nama_produk), $kunci)
            ))
            ->filter()
            ->values();

        // Kalau ada yang tidak ketemu, lengkapi dengan produk lain sampai 3
        if ($unggulan->count() < 3) {
            $unggulan = $unggulan->concat($produk->reject(fn ($p) => $unggulan->contains($p)))->take(3);
        }

        $fotoUnggulan = [
            'kaldu'   => ['kaldu-jamur-tiram-shitake.jpg'],
            'kecap'   => ['kecap-jamur-tiram.jpg'],
            'kerupuk' => ['kerupuk-jamur-tiram.jpg'],
        ];

        // Foto hero: public/images/foto-beranda.jpg
        $heroFoto = $fotoPublik(['foto-beranda.jpg', 'Foto-beranda.jpg', 'hero-twfood.jpg']);
        $produkPertama = $produk->first();
        if (!$heroFoto && $produkPertama && $produkPertama->foto_produk
            && file_exists(public_path('storage/' . $produkPertama->foto_produk))) {
            $heroFoto = asset('storage/' . $produkPertama->foto_produk);
        }
    @endphp

    {{-- ===================== HERO ===================== --}}
    <section class="tw-hero">
        <div>
            <span class="tw-badge">KULINER &amp; GROSIR SEGAR TERPERCAYA</span>

            <h1>Kebutuhan Kuliner Harian &amp; Pasokan Usaha Lebih Hemat</h1>

            <p class="tw-lead">
                Pesan eceran tanpa minimal order atau dapatkan harga grosir terbaik
                langsung untuk kebutuhan dapur dan resto Anda.
            </p>

            <p style="margin:28px 0 14px">
                <a href="{{ route('produk.public') }}" class="tw-btn tw-btn-primary">Masuk Sekarang &rarr;</a>
            </p>

            <a href="{{ route('resep.public') }}" class="tw-link">Jelajahi menu terlebih dahulu</a>

            <p class="tw-terms">
                Dengan melanjutkan Anda menyetujui <b>Syarat &amp; Ketentuan TWFOOD v1.3</b>
            </p>
        </div>

        <div class="tw-hero-img {{ $heroFoto ? '' : 'kosong' }}">
            @if ($heroFoto)
                <img src="{{ $heroFoto }}" alt="Produk TWFood">
            @endif
            <span class="tw-tag">Langsung dari sumber</span>
        </div>
    </section>

    {{-- ===================== KEUNGGULAN ===================== --}}
    <section class="tw-sec" aria-label="Keunggulan">
        <div class="tw-grid-3">
            <div class="tw-box">
                <span>🛒</span>
                <b>Eceran tanpa minimal order</b>
                <p>Beli satuan untuk kebutuhan keluarga dan acara pribadi.</p>
            </div>
            <div class="tw-box">
                <span>🏪</span>
                <b>Harga grosir untuk usaha</b>
                <p>Akses kuota grosir, pesanan partai besar, dan pre-order berkala.</p>
            </div>
            <div class="tw-box">
                <span>🚚</span>
                <b>Dikirim cepat</b>
                <p>Bahan segar diambil langsung dari sumber dan dikirim hari ini.</p>
            </div>
        </div>
    </section>

    {{-- ===================== PRODUK UNGGULAN (3 produk) ===================== --}}
    <section class="tw-sec">
        <h2>Produk Unggulan</h2>
        <p>Olahan plant-based dari jamur dan hasil tani Jember.</p>

        <div class="tw-grid-3 tw-grid-produk">
            @forelse ($unggulan as $item)
                @php
                    // Kategori (Produk belum punya kolom kategori, jadi kosong dulu)
                    $k = data_get($item, 'kategori.nama');
                    if (!is_string($k)) { $k = data_get($item, 'kategori'); }
                    $kategori = is_string($k) ? $k : '';

                    // Foto: 1) dari public/images sesuai nama produk
                    $fotoItem = null;
                    foreach ($fotoUnggulan as $kunci => $daftarFile) {
                        if (str_contains(strtolower($item->nama_produk), $kunci)) {
                            $fotoItem = $fotoPublik($daftarFile);
                            break;
                        }
                    }

                    //       2) kalau tidak ada, foto upload dari admin (hanya jika file-nya benar-benar ada)
                    if (!$fotoItem && $item->foto_produk
                        && file_exists(public_path('storage/' . $item->foto_produk))) {
                        $fotoItem = asset('storage/' . $item->foto_produk);
                    }
                @endphp

                <article class="tw-pcard">
                    <div class="tw-pcard-img">
                        @if ($fotoItem)
                            <img src="{{ $fotoItem }}" alt="{{ $item->nama_produk }}" loading="lazy">
                        @endif
                    </div>

                    <div class="tw-pcard-body">
                        @if ($kategori)
                            <span class="tw-cat">{{ $kategori }}</span>
                        @endif

                        <h3>{{ $item->nama_produk }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($item->deskripsi ?? 'Produk TWFood.', 200) }}</p>

                        <div class="tw-pcard-foot">
                            <a href="{{ route('produk.public') }}" class="tw-btn tw-btn-lihat tw-btn-block">
                                Lihat Produk &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <p>Belum ada produk yang tersedia.</p>
            @endforelse
        </div>
    </section>

    {{-- ===================== TENTANG TW FOOD ===================== --}}
    <section class="tw-sec">
        <div class="tw-box tw-about">
            <h2>Tentang TW FOOD</h2>
            <p>
                PT Tewil Indonesia Sejahtera (TWFood) berdiri sejak 2017 sebagai produsen olahan sehat
                plant-based yang ekonomis dan praktis, berbasis di Jember, Jawa Timur. Produknya antara
                lain kaldu jamur, kecap jamur, kerupuk jamur, dan stik jantung pisang, dibuat dari bahan
                alami langsung dari petani Jember tanpa MSG dan pengawet kimia.
            </p>
        </div>
    </section>

@endsection