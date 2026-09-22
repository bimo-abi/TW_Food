@extends('layouts.public')

@section('title', 'Produk - TWFood')

@section('content')


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h1>
                    Produk TWFood
                </h1>

                <p>
                    Kenali berbagai produk
                    olahan TWFood.
                </p>

            </div>


            {{-- =================================================
                 PRODUK
            ================================================== --}}

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


                            {{-- NAMA PRODUK --}}

                            <h3>
                                {{ $item->nama_produk }}
                            </h3>


                            {{-- DESKRIPSI --}}

                            <p>
                                {{ $item->deskripsi ?? 'Produk TWFood.' }}
                            </p>


                            {{-- VARIAN --}}

                            @forelse ($item->varian as $varian)
                                <div
                                    style="
                                        margin-top:20px;
                                        padding-top:15px;
                                        border-top:1px solid #eee;
                                    ">

                                    <strong>
                                        {{ $varian->nama_varian }}
                                    </strong>


                                    @forelse ($varian->daftarHarga
                                            as $harga)
                                        <p>

                                            Rp
                                            {{ number_format($harga->harga, 0, ',', '.') }}

                                            /
                                            {{ $varian->satuan_jual }}

                                        </p>

                                    @empty

                                        <p>
                                            Harga belum tersedia.
                                        </p>
                                    @endforelse

                                </div>

                            @empty

                                <p>
                                    Varian belum tersedia.
                                </p>
                            @endforelse


                            {{-- DOWNLOAD APP --}}

                            <a href="#" class="button">
                                Download Aplikasi
                            </a>


                        </div>

                    </div>

                @empty

                    <p>
                        Belum ada produk.
                    </p>

                @endforelse

            </div>

        </div>

    </section>


@endsection
