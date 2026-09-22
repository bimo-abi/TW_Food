@extends('layouts.public')

@section('title', 'TWFood - Lezat dan Sehat')

@section('content')


//HERO

    <section class="hero">

        <div class="container">

            <h1>
                TWFood
            </h1>

            <p>
                Lezat dan Sehat
            </p>

            <p>
                Menghadirkan berbagai produk
                olahan pangan plant-based.
            </p>


            <a href="{{ route('produk.public') }}" class="button">
                Lihat Produk
            </a>

        </div>

    </section>




//KENALI PRODUK KAMI


    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Kenali Produk Kami
                </h2>

                <p>
                    Berbagai produk olahan TWFood
                    untuk menemani kebutuhan Anda.
                </p>

            </div>


            <div class="products">

                @forelse ($produk as $item)
                    <div class="product-card">


                        {{-- FOTO PRODUK --}}

                        @if ($item->foto_produk)
                            <img src="{{ asset('storage/' . $item->foto_produk) }}"
                                alt="{{ $item->nama_produk }}" class="product-image">
                        @else
                            <div class="product-image"></div>
                        @endif


                        <div class="product-content">

                            <h3>
                                {{ $item->nama_produk }}
                            </h3>

                            <p>
                                {{ $item->deskripsi ?? 'Produk TWFood.' }}
                            </p>


                            <a href="{{ route('produk.public') }}" class="button">
                                Lihat Detail
                            </a>

                        </div>

                    </div>

                @empty

                    <p>
                        Belum ada produk yang tersedia.
                    </p>
                @endforelse

            </div>


            <div style="text-align:center; margin-top:40px;">

                <a href="{{ route('produk.public') }}" class="button">
                    Lihat Semua Produk
                </a>

            </div>

        </div>

    </section>


    {{-- =====================================================
         //TENTANG TWFOOD
    ====================================================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Tentang TWFood
                </h2>

            </div>


            <div class="content-list">

                @forelse ($konten as $item)
                    <div class="content-card">

                        <h3>
                            {{ $item->judul }}
                        </h3>

                        <p>
                            {{ $item->deskripsi }}
                        </p>

                    </div>

                @empty

                    <p>
                        Informasi TWFood belum tersedia.
                    </p>
                @endforelse

            </div>

        </div>

    </section>



    {{-- =====================================================
         RESEP
    ====================================================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Resep
                </h2>

                <p>
                    Temukan berbagai inspirasi resep
                    menggunakan produk TWFood.
                </p>


                <a href="{{ route('resep.public') }}" class="button">
                    Lihat Resep
                </a>

            </div>

        </div>

    </section>



    {{-- =====================================================
         OUTLET
    ====================================================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Outlet TWFood
                </h2>

            </div>


            @if ($outlet)

                <div class="outlet">

                    <h3>
                        {{ $outlet->nama_outlet }}
                    </h3>

                    <p>
                        {{ $outlet->alamat }}
                    </p>

                    @if ($outlet->nomor_telepon)
                        <p>
                            WhatsApp:
                            {{ $outlet->nomor_telepon }}
                        </p>
                    @endif

                    <p>
                        Jam buka:
                        {{ $outlet->jam_buka }}
                        -
                        {{ $outlet->jam_tutup }}
                    </p>


                    <a href="{{ route('outlet.public') }}" class="button">
                        Lihat Outlet
                    </a>

                </div>
            @else
                <p>
                    Informasi outlet belum tersedia.
                </p>

            @endif

        </div>

    </section>



    {{-- =====================================================
         DOWNLOAD APLIKASI
    ====================================================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h2>
                    Gunakan Aplikasi TWFood
                </h2>

                <p>
                    Pembelian dan transaksi nantinya
                    dilakukan melalui aplikasi TWFood.
                </p>


                <a href="#" class="button">
                    Download Aplikasi
                </a>

            </div>

        </div>

    </section>


@endsection
