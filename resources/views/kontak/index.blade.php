@extends('layouts.public')

@section('title', 'Kontak - TWFood')

@section('content')

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h1>
                    Kontak TWFood
                </h1>

                <p>
                    Hubungi TWFood untuk informasi lebih lanjut.
                </p>

            </div>


            @if ($outlet)

                <div class="content-list">

                    {{-- =========================
                         ALAMAT
                    ========================== --}}

                    <div class="content-card">

                        <h3>
                            Alamat
                        </h3>

                        <p>
                            {{ $outlet->alamat }}
                        </p>

                    </div>


                    {{-- =========================
                         WHATSAPP
                    ========================== --}}

                    <div class="content-card">

                        <h3>
                            WhatsApp
                        </h3>

                        @if ($outlet->nomor_telepon)
                            <p>
                                {{ $outlet->nomor_telepon }}
                            </p>

                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $outlet->nomor_telepon) }}" class="button"
                                target="_blank">
                                Hubungi via WhatsApp
                            </a>
                        @else
                            <p>
                                Nomor WhatsApp belum tersedia.
                            </p>
                        @endif

                    </div>


                    {{-- =========================
                         JAM OPERASIONAL
                    ========================== --}}

                    <div class="content-card">

                        <h3>
                            Jam Operasional
                        </h3>

                        <p>

                            {{ $outlet->jam_buka }}
                            -
                            {{ $outlet->jam_tutup }}

                        </p>

                    </div>


                    {{-- =========================
                         OUTLET
                    ========================== --}}

                    <div class="content-card">

                        <h3>
                            Outlet
                        </h3>

                        <p>
                            {{ $outlet->nama_outlet }}
                        </p>

                        <a href="{{ route('outlet.public') }}" class="button">
                            Lihat Outlet
                        </a>

                    </div>

                </div>
            @else
                <div class="content-card">

                    <h2>
                        Informasi Kontak Belum Tersedia
                    </h2>

                    <p>
                        Data kontak TWFood belum tersedia.
                    </p>

                </div>

            @endif

        </div>

    </section>


    {{-- =========================
         DOWNLOAD APLIKASI
    ========================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Ingin Melakukan Pembelian?
                </h2>

                <p>
                    Pembelian dan transaksi TWFood
                    dilakukan melalui aplikasi.
                </p>

                <a href="#" class="button">
                    Download Aplikasi
                </a>

            </div>

        </div>

    </section>

@endsection
