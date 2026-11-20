{{-- resources/views/outlet/index.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Outlet TWFood</title>

    {{-- 1) Gaya bersama: warna, header, footer --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    {{-- 2) Gaya khusus halaman outlet --}}
    <link rel="stylesheet" href="{{ asset('css/outlet.css') }}">
</head>
<body>

@include('partials.header')

<main class="outlet-page">

    {{-- ===== JUDUL ===== --}}
    <div class="outlet-head">
        <a href="{{ route('home') }}" class="outlet-back" aria-label="Kembali ke beranda">&larr;</a>
        <div>
            <h1>Outlet TWFood</h1>
            <p>Temukan lokasi outlet TWFood dan informasi jam operasionalnya.</p>
        </div>
    </div>

    @forelse ($outlets as $item)
        @php
            // ----- Waktu sekarang (zona WIB) untuk menandai hari ini & status buka -----
            $sekarang = \Carbon\Carbon::now('Asia/Jakarta');
            $hariIni  = strtolower($sekarang->copy()->locale('id')->dayName);
            $jamIni   = $sekarang->format('H:i');

            $jamHariIni = $item->jamOperasional->first(fn ($j) => strtolower($j->hari) === $hariIni);

            // true = buka, false = tutup, null = jam hari ini belum diisi
            $statusBuka = null;
            if ($jamHariIni) {
                if ($jamHariIni->tutup) {
                    $statusBuka = false;
                } else {
                    $buka  = substr($jamHariIni->jam_buka ?? '', 0, 5);
                    $tutup = substr($jamHariIni->jam_tutup ?? '', 0, 5);
                    $statusBuka = ($buka !== '' && $tutup !== '') ? ($jamIni >= $buka && $jamIni < $tutup) : null;
                }
            }

            // ----- Link peta & WhatsApp -----
            $query = ($item->latitude && $item->longitude)
                ? $item->latitude . ',' . $item->longitude
                : $item->alamat;

            $linkMaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
            $embedMap = 'https://maps.google.com/maps?q=' . urlencode($query) . '&z=16&output=embed';

            $wa = null;
            if ($item->nomor_telepon) {
                $wa = preg_replace('/\D/', '', $item->nomor_telepon);
                if (str_starts_with($wa, '0')) { $wa = '62' . substr($wa, 1); }
            }
        @endphp

        <section class="outlet-layout">

            {{-- ===== KOLOM KIRI: info + jam operasional ===== --}}
            <div class="outlet-left">

                {{-- Kartu info --}}
                <div class="outlet-card">
                    <div class="outlet-topline">
                        <span class="outlet-label">LOKASI KAMI</span>

                        @if ($statusBuka !== null)
                            <span class="outlet-status {{ $statusBuka ? 'buka' : 'tutup' }}">
                                <i></i> {{ $statusBuka ? 'Buka sekarang' : 'Sedang tutup' }}
                            </span>
                        @endif
                    </div>

                    <h2>{{ $item->nama_outlet }}</h2>

                    {{-- Jam hari ini --}}
                    @if ($jamHariIni)
                        <div class="outlet-today">
                            <small>Hari ini &middot; {{ ucfirst($hariIni) }}</small>
                            <strong>
                                @if ($jamHariIni->tutup)
                                    Tutup
                                @else
                                    {{ substr($jamHariIni->jam_buka ?? '', 0, 5) }} &ndash; {{ substr($jamHariIni->jam_tutup ?? '', 0, 5) }}
                                @endif
                            </strong>
                        </div>
                    @endif

                    <div class="outlet-row">
                        <span class="outlet-ico">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        </span>
                        <div>
                            <small>Alamat</small>
                            <p>{{ $item->alamat }}</p>
                        </div>
                    </div>

                    @if ($item->nomor_telepon)
                        <div class="outlet-row">
                            <span class="outlet-ico">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>
                            </span>
                            <div>
                                <small>Telepon</small>
                                <p>{{ $item->nomor_telepon }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($item->deskripsi)
                        <p class="outlet-desc">{{ $item->deskripsi }}</p>
                    @endif

                    <div class="outlet-actions">
                        <a href="{{ $linkMaps }}" class="outlet-btn maps" target="_blank" rel="noopener noreferrer">
                            Buka di Google Maps
                        </a>

                        @if ($wa)
                            <a href="https://wa.me/{{ $wa }}" class="outlet-btn wa" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('images/icon-wa.png') }}" alt="" width="20" height="20">
                                Hubungi via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Kartu jam operasional --}}
                <div class="outlet-card">
                    <h3 class="outlet-judul">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        Jam Operasional
                    </h3>

                    @if ($item->jamOperasional->count() > 0)
                        <ul class="jam-list">
                            @foreach ($item->jamOperasional as $jam)
                                @php $hariItu = strtolower($jam->hari) === $hariIni; @endphp

                                <li class="jam-row {{ $hariItu ? 'ini' : '' }} {{ $jam->tutup ? 'libur' : '' }}">
                                    <span class="jam-hari">
                                        {{ ucfirst($jam->hari) }}
                                        @if ($hariItu)<em>Hari ini</em>@endif
                                    </span>

                                    <span class="jam-waktu">
                                        @if ($jam->tutup)
                                            Tutup
                                        @else
                                            {{ substr($jam->jam_buka ?? '', 0, 5) }} &ndash; {{ substr($jam->jam_tutup ?? '', 0, 5) }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="outlet-kosong">Jam operasional belum tersedia.</p>
                    @endif
                </div>
            </div>

            {{-- ===== KOLOM KANAN: peta ===== --}}
            <div class="outlet-map">
                <iframe src="{{ $embedMap }}" title="Peta {{ $item->nama_outlet }}"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

        </section>

    @empty
        <div class="outlet-kosong-card">
            <p>Data outlet belum tersedia.</p>
        </div>
    @endforelse

</main>

@include('partials.footer')

</body>
</html>