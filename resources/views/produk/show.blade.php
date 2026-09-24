@extends('layouts.public')

@section('title', $produk->nama_produk)

@section('content')

<section class="hero">
    <div class="container">
        <h1>{{ $produk->nama_produk }}</h1>

        <p>
            Informasi lengkap produk TWFood.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="card">

            {{-- FOTO PRODUK --}}
            @if ($produk->foto_produk)
                <img
                    src="{{ asset('storage/' . $produk->foto_produk) }}"
                    alt="{{ $produk->nama_produk }}"
                    style="
                        width: 100%;
                        max-width: 500px;
                        height: 300px;
                        object-fit: contain;
                        display: block;
                        margin: 0 auto 25px;
                    "
                >
            @endif

            {{-- NAMA PRODUK --}}
            <h2>{{ $produk->nama_produk }}</h2>

            {{-- DESKRIPSI --}}
            @if ($produk->deskripsi)
                <p>
                    {{ $produk->deskripsi }}
                </p>
            @endif

            {{-- VARIAN --}}
            <div style="margin-top: 30px;">

                <h3>Varian dan Harga</h3>

                @forelse ($produk->varian as $varian)

                    <div
                        style="
                            padding: 15px 0;
                            border-bottom: 1px solid #eee;
                        "
                    >

                        <h4>
                            {{ $varian->nama_varian }}
                        </h4>

                        @if ($varian->berat_gram)
                            <p>
                                Berat:
                                {{ $varian->berat_gram }} gram
                            </p>
                        @endif

                        <p>
                            Satuan:
                            {{ $varian->satuan_jual }}
                        </p>

                        {{-- HARGA ECER --}}
                        @forelse ($varian->daftarHarga as $harga)

                            <p>
                                <strong>
                                    Harga Ecer:
                                </strong>

                                Rp {{ number_format($harga->harga, 0, ',', '.') }}
                                / {{ $varian->satuan_jual }}
                            </p>

                        @empty

                            <p>
                                Harga belum tersedia.
                            </p>

                        @endforelse

                    </div>

                @empty

                    <p>
                        Informasi varian belum tersedia.
                    </p>

                @endforelse

            </div>

            {{-- CTA APLIKASI --}}
            <div style="margin-top: 30px;">

                <p>
                    Ingin melakukan pembelian?
                    Gunakan aplikasi TWFood.
                </p>

                <a href="#" class="button">
                    Download Aplikasi
                </a>

            </div>

            {{-- KEMBALI --}}
            <div style="margin-top: 20px;">

                <a
                    href="{{ route('produk.public') }}"
                    class="button"
                >
                    ← Kembali ke Produk
                </a>

            </div>

        </div>

    </div>
</section>

@endsection
