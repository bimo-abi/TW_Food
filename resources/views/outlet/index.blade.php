@extends('layouts.public')

@section('title', 'Outlet TWFood')

@section('content')

    <section class="hero">
        <div class="container">
            <h1>Outlet TWFood</h1>
            <p>
                Temukan lokasi outlet TWFood dan informasi jam operasionalnya.
            </p>
        </div>
    </section>

    <section class="section">
        <div class="container">

            @forelse ($outlet as $item)

                <div class="card" style="margin-bottom: 30px;">

                    <h2>{{ $item->nama_outlet }}</h2>

                    <p>
                        <strong>Alamat:</strong><br>
                        {{ $item->alamat }}
                    </p>

                    @if ($item->nomor_telepon)
                        <p>
                            <strong>Telepon:</strong><br>
                            {{ $item->nomor_telepon }}
                        </p>
                    @endif

                    @if ($item->deskripsi)
                        <p>
                            {{ $item->deskripsi }}
                        </p>
                    @endif

                    {{-- JAM OPERASIONAL --}}
                    <div style="margin-top: 25px;">

                        <h3>Jam Operasional</h3>

                        <div style="margin-top: 15px;">

                            @foreach ($item->jamOperasional as $jam)
                                <div
                                    style="
                                    display: flex;
                                    justify-content: space-between;
                                    gap: 20px;
                                    padding: 10px 0;
                                    border-bottom: 1px solid #eee;
                                ">

                                    <strong style="text-transform: capitalize;">
                                        {{ $jam->hari }}
                                    </strong>

                                    @if ($jam->tutup)
                                        <span>Tutup</span>
                                    @else
                                        <span>
                                            {{ substr($jam->jam_buka, 0, 5) }}
                                            -
                                            {{ substr($jam->jam_tutup, 0, 5) }}
                                        </span>
                                    @endif

                                </div>
                            @endforeach

                        </div>

                    </div>

                    {{-- LOKASI --}}
                    <div style="margin-top: 25px;">

                        <h3>Lokasi Outlet</h3>

                        <p>
                            Kamu dapat melihat lokasi TWFood melalui Google Maps.
                        </p>

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($item->alamat) }}"
                            class="button" target="_blank" rel="noopener noreferrer">
                            Buka di Google Maps
                        </a>

                    </div>

                    {{-- WHATSAPP --}}
                    @if ($item->nomor_telepon)
                        <div style="margin-top: 15px;">

                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->nomor_telepon) }}" class="button"
                                target="_blank" rel="noopener noreferrer">
                                Hubungi via WhatsApp
                            </a>

                        </div>
                    @endif

                </div>

            @empty

                <div class="card">
                    <p>Data outlet belum tersedia.</p>
                </div>

            @endforelse

        </div>
    </section>

@endsection
