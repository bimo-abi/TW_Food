<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk - TWFood</title>

    {{-- 1) Gaya bersama: warna, header & footer --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    {{-- 2) Gaya khusus halaman produk (dimuat setelah layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/produk.css') }}">
</head>
<body>

@include('partials.header')

@php
    // Link unduh aplikasi TWFood (Play Store / APK). Ganti '#' kalau linknya sudah ada.
    $linkAplikasi = '#';

    // ===== KATEGORI PRODUK (sesuai Figma) =====
    // Kiri : nama produk (huruf besar/kecil boleh beda). Kanan : kategori.
    // Dipakai kalau database belum punya kolom kategori yang terbaca.
    // Produk baru: tambah satu baris di sini.
    $daftarKategori = [
        'Kaldu Jamur Tiram dan Shitake' => 'Bumbu',
        'Kaldu Jamur Tiram MPASI'       => 'Bumbu',
        'Kecap Jamur Tiram'             => 'Bumbu',
        'Abon Jamur Tiram'              => 'Camilan',
        'Jamur Tiram Krispi'            => 'Camilan',
        'Stik Jantung Pisang'           => 'Camilan',
        'Kerupuk Jamur Tiram'           => 'Camilan',
        'Mie Instan Jamur Tiram'        => 'Mie Instan',
    ];
    $daftarKategori = array_change_key_case($daftarKategori, CASE_LOWER);

    // Urutan tombol kategori di halaman
    $urutanKategori = ['Bumbu', 'Camilan', 'Mie Instan'];

    // Fungsi kecil: ambil nama kategori produk sebagai teks (kosong kalau tidak ada)
    $ambilKategori = function ($p) use ($daftarKategori) {
        $k = data_get($p, 'kategori.nama');
        if (!is_string($k)) { $k = data_get($p, 'kategori'); }
        if (is_string($k) && $k !== '') { return $k; }
        return $daftarKategori[strtolower($p->nama_produk)] ?? '';
    };

    $daftarFoto = [
        'Kaldu Jamur Tiram dan Shitake' => 'kaldu-jamur-tiram-shitake.jpg',
        'Kaldu Jamur Tiram MPASI'       => 'kaldu-jamur-tiram-mpasi.jpg',
        'Kecap Jamur Tiram'             => 'kecap-jamur-tiram.jpg',
        'Abon Jamur Tiram'              => 'abon-jamur-tiram.jpg',
        'Jamur Tiram Krispi'            => 'jamur-tiram-krispi.jpg',
        'Stik Jantung Pisang'           => 'stik-jantung-pisang.jpg',
        'Kerupuk Jamur Tiram'           => 'kerupuk-jamur-tiram.jpg',
        'Mie Instan Jamur Tiram'        => 'mie-instan-jamur-tiram.jpg',
    ];
    $daftarFoto = array_change_key_case($daftarFoto, CASE_LOWER);

    // Daftar kategori untuk tombol filter (otomatis dari produk yang ada)
    $kategoriList = collect($produk)->map($ambilKategori)->filter()->unique()
        ->sortBy(function ($k) use ($urutanKategori) {
            $i = array_search($k, $urutanKategori);
            return $i === false ? 99 : $i;
        })->values();
@endphp

<div class="produk-page">
    <section class="produk-wrap">

        {{-- ===== JUDUL ===== --}}
        <div class="produk-head">
            <a href="{{ url('/') }}" class="produk-back" aria-label="Kembali ke beranda">&larr;</a>
            <div>
                <h1>Daftar Produk</h1>
                {{-- Ubah tanggal ini kalau harga diperbarui --}}
                <p>Harga per 1 Agustus 2026</p>
            </div>
        </div>

        {{-- ===== INFO: produk untuk resep tertentu (dari tombol "Beli bahannya") ===== --}}
        @if (!empty($resepDipilih))
            <div class="produk-info">
                <span>Produk untuk membuat <strong>{{ $resepDipilih->judul }}</strong></span>
                <a href="{{ route('produk.public') }}">Lihat semua produk</a>
            </div>
        @endif

        {{-- ===== CARI & FILTER ===== --}}
        <input type="search" id="produk-cari" class="produk-cari" placeholder="Cari produk, Misalnya kaldu" autocomplete="off">

        <div class="produk-chips">
            <button type="button" class="produk-chip aktif" data-kat="">Semua</button>
            @foreach ($kategoriList as $k)
                <button type="button" class="produk-chip" data-kat="{{ $k }}">{{ $k }}</button>
            @endforeach
        </div>

        {{-- ===== DAFTAR PRODUK ===== --}}
        <div class="produk-grid">

            @forelse ($produk as $item)
                @php
                    $kat = $ambilKategori($item);

                    // Kumpulkan harga eceran & grosir dari tiap varian
                    $ecer = [];
                    $grosir = [];
                    foreach ($item->varian as $v) {
                        $e = $v->daftarHarga->where('jenis_harga', 'ecer')->first();
                        $g = $v->daftarHarga->where('jenis_harga', 'grosir')->first();
                        if ($e) {
                            // Kalau varian punya berat (berat_gram), tampil "/ 185 gr". Kalau tidak, tampil satuan ("/pc").
                            $unit = $v->berat_gram
                                ? ' / ' . number_format($v->berat_gram, 0, ',', '.') . ' gr'
                                : '/' . $v->satuan_jual;
                            $ecer[] = ['nama' => $v->nama_varian, 'harga' => $e->harga, 'satuan' => $v->satuan_jual, 'unit' => $unit];
                        }
                        if ($g) {
                            // Minimal pembelian grosir, contoh: "2 lusin" / "1 kg" (kosong kalau tidak ada)
                            $satMin  = $g->satuan_minimal;
                            $minText = ($g->minimal_pembelian > 1 || $satMin)
                                ? trim($g->minimal_pembelian . ' ' . $satMin)
                                : '';

                            // Kalau minimalnya dihitung per kg/gram, harga grosir juga per kg/gram.
                            // Selain itu, pakai satuan varian (Pc, dll).
                            $satHarga = in_array(strtolower((string) $satMin), ['kg', 'gr', 'gram'])
                                ? $satMin
                                : $v->satuan_jual;

                            $grosir[] = [
                                'nama'   => $v->nama_varian,
                                'harga'  => $g->harga,
                                'satuan' => $satHarga,
                                'min'    => $minText,
                            ];
                        }
                    }

                    // Foto: 1) dari daftar di atas (public/images/), 2) kalau tidak ada, foto upload admin
                    $fotoUrl = null;
                    $namaFoto = $daftarFoto[strtolower($item->nama_produk)] ?? null;
                    if ($namaFoto && file_exists(public_path('images/' . $namaFoto))) {
                        $fotoUrl = asset('images/' . $namaFoto);
                    } elseif ($item->foto_produk && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->foto_produk)) {
                        $fotoUrl = asset('storage/' . $item->foto_produk);
                    }
                @endphp

                <article class="produk-card" data-kat="{{ $kat }}"
                         data-cari="{{ strtolower($item->nama_produk . ' ' . ($item->deskripsi ?? '')) }}">

                    {{-- Foto --}}
                    <div class="produk-img">
                        @if ($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="{{ $item->nama_produk }}"
                                 loading="lazy" onerror="this.style.display='none'">
                        @endif
                    </div>

                    <div class="produk-body">
                        @if ($kat !== '')
                            <span class="produk-kat">{{ $kat }}</span>
                        @endif

                        <h3>{{ $item->nama_produk }}</h3>

                        <p class="produk-desc">{{ $item->deskripsi ?? 'Produk TWFood.' }}</p>

                        <div class="produk-foot">

                            {{-- Harga eceran --}}
                            <div class="produk-harga">
                                @if (count($ecer) === 1)
                                    Rp {{ number_format($ecer[0]['harga'], 0, ',', '.') }}{{ $ecer[0]['unit'] }}
                                    <em>Harga eceran</em>
                                @elseif (count($ecer) > 1)
                                    @foreach ($ecer as $i => $e)
                                        {{ $i > 0 ? '·' : '' }}
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($e['nama'], 0, 1)) }}
                                        Rp{{ number_format($e['harga'], 0, ',', '.') }}
                                    @endforeach
                                    <em>Harga eceran</em>
                                @else
                                    Hubungi kami
                                @endif
                            </div>

                            {{-- Harga grosir --}}
                            @if (count($grosir) === 1)
                                <span class="produk-grosir">
                                    Grosir: Rp{{ number_format($grosir[0]['harga'], 0, ',', '.') }}/{{ $grosir[0]['satuan'] }}
                                    @if ($grosir[0]['min']) (min. {{ $grosir[0]['min'] }}) @endif
                                </span>
                            @elseif (count($grosir) > 1)
                                <span class="produk-grosir">
                                    Grosir:
                                    @foreach ($grosir as $i => $g)
                                        {{ $i > 0 ? '·' : '' }}
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($g['nama'], 0, 1)) }}
                                        Rp{{ number_format($g['harga'], 0, ',', '.') }}
                                    @endforeach
                                </span>
                            @endif

                            {{-- Tombol beli di aplikasi --}}
                            <a href="{{ $linkAplikasi }}" class="produk-app">
                                Beli di Aplikasi
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="produk-kosong">Belum ada produk.</p>
            @endforelse

        </div>

        <p class="produk-kosong" id="produk-kosong-cari" hidden>Produk tidak ditemukan.</p>

    </section>
</div>

@include('partials.footer')

<script>
    // Cari + filter kategori (jalan di browser, tanpa reload)
    (function () {
        var input  = document.getElementById('produk-cari');
        var chips  = document.querySelectorAll('.produk-chip');
        var kartu  = document.querySelectorAll('.produk-card');
        var kosong = document.getElementById('produk-kosong-cari');
        var aktif  = '';

        function terapkan() {
            var kata = input.value.trim().toLowerCase();
            var tampil = 0;
            kartu.forEach(function (k) {
                var cocokKat  = aktif === '' || k.dataset.kat === aktif;
                var cocokCari = kata === '' || k.dataset.cari.indexOf(kata) !== -1;
                var ok = cocokKat && cocokCari;
                k.style.display = ok ? '' : 'none';
                if (ok) tampil++;
            });
            kosong.hidden = tampil > 0 || kartu.length === 0;
        }

        input.addEventListener('input', terapkan);
        chips.forEach(function (c) {
            c.addEventListener('click', function () {
                chips.forEach(function (x) { x.classList.remove('aktif'); });
                c.classList.add('aktif');
                aktif = c.dataset.kat;
                terapkan();
            });
        });
    })();
</script>

</body>
</html>