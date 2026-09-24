@extends('layouts.public')

@section('title', 'Resep - TWFood')

@section('content')

    <section class="section">

        <div class="container">

            <div class="section-title">

                <h1>
                    Resep TWFood
                </h1>

                <p>
                    Temukan berbagai inspirasi resep
                    menggunakan produk TWFood.
                </p>

            </div>


            <div class="content-list">

                @forelse ($resep as $item)

                    <div class="content-card">

                        <h3>
                            {{ $item->judul }}
                        </h3>


                        @if ($item->deskripsi)
                            <p>
                                {{ $item->deskripsi }}
                            </p>
                        @endif


                        @if ($item->waktu_memasak)
                            <p>

                                <strong>
                                    Waktu memasak:
                                </strong>

                                {{ $item->waktu_memasak }}
                                menit

                            </p>
                        @endif


                        @if ($item->produk->count() > 0)
                            <p>

                                <strong>
                                    Produk TWFood:
                                </strong>

                                @foreach ($item->produk as $produk)
                                    {{ $produk->produk->nama_produk }}

                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach

                            </p>
                        @endif


                        <a href="{{ route('resep.detail', $item->id_resep) }}"
                            class="button">
                            Lihat Resep
                        </a>

                    </div>

                @empty

                    <div class="content-card">

                        <h3>
                            Belum Ada Resep
                        </h3>

                        <p>
                            Data resep belum tersedia.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>

    </section>

@endsection
