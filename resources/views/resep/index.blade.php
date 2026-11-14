<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resep - TWFood</title>

    {{-- 1) Gaya bersama: warna, header & footer --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    {{-- 2) Gaya khusus halaman resep (dimuat setelah layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/resep.css') }}">
</head>
<body>

@include('partials.header')

@php
    // Foto produk TWFood (public/images/) untuk gambar kartu kalau resep tidak punya foto sendiri.
    // Kiri: nama produk (huruf besar/kecil boleh beda). Kanan: nama file.
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

    // Foto per resep (nama file di public/images/). Kiri: judul resep (huruf besar/kecil boleh beda).
    // Resep baru: tambah satu baris di sini, atau upload foto resep lewat admin.
    $fotoResep = [
        'Sup Kuah Kaldu Jamur'    => 'kaldu-jamur-tiram-shitake.jpg',
        'Nasi Goreng Kecap Jamur'  => 'nasi-goreng-kecap-jamur.jpg',
        'Mie Kelor Tumis Sayur'   => 'mie-instan-jamur-tiram.jpg',
        'Bubur dengan Abon Jamur' => 'bubur-abon-jamur.png',
        'Jamur Tiram Krispi Pedas' => 'jamur-tiram-krispi-pedas.jpg',
    ];
    $fotoResep = array_change_key_case($fotoResep, CASE_LOWER);

    // Pecah teks per baris jadi daftar. Tanda "-", "•", atau "1." di depan baris dibuang.
    $pecah = function ($teks) {
        $hasil = [];
        foreach (preg_split('/\r\n|\r|\n/', strip_tags((string) $teks)) as $baris) {
            $baris = trim(preg_replace('/^\s*(?:[-•*·]+|\d+[.)])\s*/u', '', trim($baris)));
            if ($baris !== '') {
                $hasil[] = $baris;
            }
        }
        return $hasil;
    };
@endphp

<div class="resep-page">
    <section class="resep-wrap">

        {{-- ===== JUDUL ===== --}}
        <div class="resep-head">
            <a href="{{ url('/') }}" class="resep-back" aria-label="Kembali ke beranda">&larr;</a>
            <div>
                <h1>Resep</h1>
                <p>Contoh ide masakan dari produk TW FOOD. Takaran bisa disesuaikan selera.</p>
            </div>
        </div>

        {{-- ===== DAFTAR RESEP ===== --}}
        <div class="resep-grid">

            @forelse ($resep as $item)
                @php
                    $daftarBahan = $pecah($item->bahan);
                    $daftarLangkah = $pecah($item->langkah_pembuatan);

                    // Foto: 1) foto resep (upload admin), 2) daftar $fotoResep di atas,
                    //       3) foto produk TWFood yang dipakai di resep
                    $fotoUrl = null;
                    $fileResep = $fotoResep[strtolower(trim($item->judul))] ?? null;
                    if ($item->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->foto)) {
                        $fotoUrl = asset('storage/' . $item->foto);
                    } elseif ($fileResep && file_exists(public_path('images/' . $fileResep))) {
                        $fotoUrl = asset('images/' . $fileResep);
                    } else {
                        foreach ($item->produk as $rp) {
                            $nama = strtolower((string) data_get($rp, 'produk.nama_produk', ''));
                            $file = $daftarFoto[$nama] ?? null;
                            if ($file && file_exists(public_path('images/' . $file))) {
                                $fotoUrl = asset('images/' . $file);
                                break;
                            }
                        }
                    }

                    // Porsi: tampil kalau tabel resep punya kolom "porsi"
                    $porsi = data_get($item, 'porsi');
                @endphp

                <article class="resep-card">

                    <div class="resep-img">
                        @if ($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="{{ $item->judul }}" loading="lazy"
                                 onerror="this.style.display='none'">
                        @else
                            <span class="resep-emoji">🍲</span>
                        @endif
                    </div>

                    <div class="resep-body">
                        <h3>{{ $item->judul }}</h3>

                        <p class="resep-meta">
                            @if ($item->waktu_memasak)
                                <span>⏱ {{ $item->waktu_memasak }} menit</span>
                            @endif
                            @if ($porsi)
                                <span>🍽 {{ $porsi }} porsi</span>
                            @endif
                        </p>

                        {{-- Bahan --}}
                        @if (count($daftarBahan) > 0)
                            <h4>Bahan</h4>
                            <ul class="resep-bahan">
                                @foreach ($daftarBahan as $b)
                                    <li>{{ $b }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Langkah (dibuka lewat tombol) --}}
                        @if (count($daftarLangkah) > 0)
                            <details class="resep-langkah">
                                <summary>Lihat langkah</summary>
                                <ol>
                                    @foreach ($daftarLangkah as $l)
                                        <li>{{ $l }}</li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif

                        <a href="{{ route('produk.public', ['resep' => $item->id_resep]) }}" class="resep-btn">Beli bahannya &rarr;</a>
                    </div>

                </article>
            @empty
                <p class="resep-kosong">Belum ada resep.</p>
            @endforelse

        </div>

    </section>
</div>

@include('partials.footer')

</body>
</html>