<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Harga Grosir - TWFood</title>

    {{-- 1) Gaya bersama: warna, header & footer --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    {{-- 2) Gaya khusus halaman grosir (dimuat setelah layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/grosir.css') }}">
</head>
<body>

@include('partials.header')

@php
    // ===== PENGATURAN (ubah di sini) =====
    // Link unduh/pesan lewat aplikasi TWFood. Ganti '#' kalau linknya sudah ada.
    $linkAplikasi = '#';

    // Nomor WhatsApp TWFood, format internasional tanpa + dan tanpa spasi.
    $nomorWa = '6283893395318';

    // Tanggal harga. Kosongkan (null) supaya otomatis dari tanggal update harga terbaru di database.
    // Contoh kalau mau tetap: $tanggalManual = '1 Agustus 2026';
    $tanggalManual = null;
    $tanggalTampil = $tanggalManual ?: ($tanggalHarga ?? null);

    // Urutan produk di tabel (sesuai Figma). Produk yang tidak ada di daftar ini ikut di bawah.
    $urutan = [
        'Kaldu Jamur Tiram dan Shitake',
        'Kaldu Jamur Tiram MPASI',
        'Kecap Jamur Tiram',
        'Jamur Tiram Krispi',
        'Stik Jantung Pisang',
        'Abon Jamur Tiram',
        'Kerupuk Jamur Tiram',
        'Mie Instan Jamur Tiram',
    ];
    $urutan = array_map('strtolower', $urutan);

    // Urutan varian kalau ada beberapa (Kecap: M = manis, P = pedas, A = asin)
    $urutVarian = ['m' => 1, 'p' => 2, 'a' => 3];

    // Link WhatsApp dengan pesan yang sudah terisi
    $linkWa = function (string $pesan) use ($nomorWa) {
        return 'https://wa.me/' . $nomorWa . '?text=' . rawurlencode($pesan);
    };

    // Susun baris tabel dari data produk
    $baris = [];
    foreach ($produk as $item) {
        $ecer = [];
        $grosir = [];
        $minimal = '';

        foreach ($item->varian as $v) {
            $e = $v->daftarHarga->where('jenis_harga', 'ecer')->first();
            $g = $v->daftarHarga->where('jenis_harga', 'grosir')->first();
            $inisial = strtolower(mb_substr((string) $v->nama_varian, 0, 1));

            if ($e) {
                // Varian punya berat -> "/ 185 gr", kalau tidak -> "/pc"
                $unit = $v->berat_gram
                    ? ' / ' . number_format($v->berat_gram, 0, ',', '.') . ' gr'
                    : '/' . strtolower($v->satuan_jual);
                $ecer[] = ['nama' => $v->nama_varian, 'ord' => $urutVarian[$inisial] ?? 9,
                           'harga' => $e->harga, 'unit' => $unit];
            }
            if ($g) {
                $satMin = $g->satuan_minimal;
                // Minimal per kg/gram -> harga grosir juga per kg/gram. Selain itu pakai satuan varian.
                $satHarga = in_array(strtolower((string) $satMin), ['kg', 'gr', 'gram'])
                    ? $satMin
                    : strtolower($v->satuan_jual);
                $grosir[] = ['nama' => $v->nama_varian, 'ord' => $urutVarian[$inisial] ?? 9,
                             'harga' => $g->harga, 'satuan' => $satHarga];

                // Minimal grosir, contoh "2 lusin" / "1 kg". Kosong kalau tidak ada syarat.
                if ($minimal === '' && ($g->minimal_pembelian > 1 || $satMin)) {
                    $minimal = trim($g->minimal_pembelian . ' ' . strtolower($satMin ?: $v->satuan_jual));
                }
            }
        }

        // Hanya produk yang punya harga grosir
        if (count($grosir) === 0) {
            continue;
        }

        usort($ecer, fn ($a, $b) => $a['ord'] <=> $b['ord']);
        usort($grosir, fn ($a, $b) => $a['ord'] <=> $b['ord']);

        $pos = array_search(strtolower($item->nama_produk), $urutan);
        $baris[] = [
            'nama'    => $item->nama_produk,
            'ecer'    => $ecer,
            'grosir'  => $grosir,
            'minimal' => $minimal,
            'pos'     => $pos === false ? 99 : $pos,
        ];
    }
    usort($baris, fn ($a, $b) => $a['pos'] <=> $b['pos']);

    // Tampilkan harga: satu varian -> "Rp20.000/pc"; beberapa -> "M Rp12.000 · P Rp15.000 · A Rp10.000"
    $tampilHarga = function (array $daftar, string $kunciUnit) {
        if (count($daftar) === 0) {
            return '–';
        }
        if (count($daftar) === 1) {
            $d = $daftar[0];
            $unit = $kunciUnit === 'unit' ? $d['unit'] : '/' . $d['satuan'];
            return 'Rp' . number_format($d['harga'], 0, ',', '.') . $unit;
        }
        $bagian = [];
        foreach ($daftar as $d) {
            $bagian[] = strtoupper(mb_substr((string) $d['nama'], 0, 1)) . ' Rp' . number_format($d['harga'], 0, ',', '.');
        }
        return implode(' · ', $bagian);
    };
@endphp

<div class="grosir-page">
    <section class="grosir-wrap">

        {{-- ===== JUDUL ===== --}}
        <div class="grosir-head">
            <a href="{{ url('/') }}" class="grosir-back" aria-label="Kembali ke beranda">&larr;</a>
            <div>
                <h1>Harga Grosir untuk Usaha</h1>
                <p>Untuk restoran, katering, warung makan, toko, dan reseller.
                    @if ($tanggalTampil) Harga per {{ $tanggalTampil }}, @endif
                    PT Tewil Indonesia Sejahtera.</p>
            </div>
        </div>

        {{-- ===== TABEL HARGA ===== --}}
        <div class="grosir-tabel-wrap">
            <table class="grosir-tabel">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga eceran</th>
                        <th>Harga grosir</th>
                        <th>Minimal grosir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($baris as $b)
                        <tr>
                            <td data-label="Produk" class="grosir-nama">{{ $b['nama'] }}</td>
                            <td data-label="Harga eceran">{{ $tampilHarga($b['ecer'], 'unit') }}</td>
                            <td data-label="Harga grosir">{{ $tampilHarga($b['grosir'], 'satuan') }}</td>
                            <td data-label="Minimal grosir">
                                @if ($b['minimal'] !== '')
                                    Min. {{ $b['minimal'] }}
                                @else
                                    <a class="grosir-tanya"
                                       href="{{ $linkWa('Halo TW Food, saya mau tanya syarat minimal grosir untuk ' . $b['nama'] . '.') }}"
                                       target="_blank" rel="noopener">Tanya via WA</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="grosir-kosong">
                            <td colspan="4">Belum ada harga grosir.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="grosir-catatan">
            Kecap Jamur Tiram: M, P, A adalah varian manis, pedas, dan asin.
            Harga dan syarat minimal dapat berubah, konfirmasi stok lewat WhatsApp.
        </p>

        {{-- ===== LANGKAH PESAN ===== --}}
        <div class="grosir-langkah">
            <div class="grosir-kartu">
                <h3><span>1</span> Pilih Produk</h3>
                <p>Tentukan produk dan jumlah sesuai syarat minimal grosir.</p>
            </div>
            <div class="grosir-kartu">
                <h3><span>2</span> Pesan di Aplikasi</h3>
                <p>Kirim pesanan lewat aplikasi TW Food, atau tanya dulu lewat WhatsApp ke +62 838-9339-5318.</p>
            </div>
            <div class="grosir-kartu">
                <h3><span>3</span> Konfirmasi</h3>
                <p>Kami konfirmasi stok, harga, dan pengiriman sebelum pesanan diproses.</p>
            </div>
        </div>

        {{-- ===== TOMBOL ===== --}}
        <div class="grosir-aksi">
            <a href="{{ $linkAplikasi }}" class="grosir-btn grosir-btn-app">Pesan Grosir di Aplikasi</a>
            <a href="{{ $linkWa('Halo TW Food, saya mau pesan grosir. Berikut daftar pesanan saya:') }}"
               class="grosir-btn grosir-btn-wa" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.2A8.5 8.5 0 1 1 20 11.5Z"/><path d="M9 8.5c.3 2.6 2.4 5 5.5 6l1.2-1.3-1.9-1-.8.7c-.8-.4-1.6-1.2-2-2l.7-.8-1-1.9L9 8.5Z" fill="currentColor" stroke="none"/></svg>
                Tanya via WhatsApp
            </a>
            {{-- Unduh PDF: dibuat langsung di browser oleh public/js/grosir-pdf.js (isinya = tabel di atas) --}}
            <button type="button" id="grosir-unduh" class="grosir-btn grosir-btn-cetak"
                    data-kontak="WhatsApp +62 838-9339-5318 | twfoodid@gmail.com">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
                Unduh PDF
            </button>
        </div>

    </section>
</div>

@include('partials.footer')

<script src="{{ asset('js/grosir-pdf.js') }}"></script>

</body>
</html>