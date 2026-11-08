<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bahan &amp; Komposisi - TWFood</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bahan.css') }}">
</head>
<body>

@include('partials.header')

<div class="bahan-page">
    <section class="bahan-wrap">

        {{-- ===== JUDUL ===== --}}
        <div class="bahan-head">
            <a href="{{ url('/') }}" class="bahan-back" aria-label="Kembali ke beranda">&larr;</a>
            <div>
                <h1>Bahan &amp; Komposisi</h1>
                <p>Bahan utama tiap produk sesuai katalog produk TW FOOD.</p>
            </div>
        </div>

        {{-- ===== PERHATIAN ALERGI ===== --}}
        <div class="bahan-alergi">
            Perhatian alergi: berdasarkan bahan yang tercantum, Kaldu Jamur mengandung soya,
            Kecap Jamur mengandung wijen, Mie Instan mengandung telur, dan Kaldu MPASI mengandung udang.
            Daftar ini belum tentu lengkap, selalu periksa label kemasan.
        </div>

        {{-- ===== DAFTAR PRODUK ===== --}}
        <div class="bahan-grid">
            @foreach ($daftarBahan as $b)
                <article class="bahan-card">

                    <div class="bahan-img">
                        @if (file_exists(public_path('images/' . $b['foto'])))
                            <img src="{{ asset('images/' . $b['foto']) }}" alt="{{ $b['nama'] }}" loading="lazy"
                                 onerror="this.style.display='none'">
                        @endif
                    </div>

                    <div class="bahan-body">
                        <span class="bahan-kat">{{ $b['kategori'] }}</span>
                        <h3>{{ $b['nama'] }}</h3>

                        <p class="bahan-label">Bahan utama</p>

                        @if (count($b['bahan']) > 0)
                            <div class="bahan-chips">
                                @foreach ($b['bahan'] as $item)
                                    <span>{{ $item }}</span>
                                @endforeach
                            </div>
                        @elseif ($b['ket_bahan'])
                            <p class="bahan-ket">{{ $b['ket_bahan'] }}</p>
                        @endif

                        <div class="bahan-foot">
                            <p class="bahan-simpan">Masa simpan: {{ $b['simpan'] }}</p>
                            <p class="bahan-catatan">{{ $b['catatan'] }}</p>

                            <a href="{{ route('produk.public') }}" class="bahan-btn">Lihat Produk</a>
                        </div>
                    </div>

                </article>
            @endforeach
        </div>

    </section>
</div>

@include('partials.footer')

</body>
</html>