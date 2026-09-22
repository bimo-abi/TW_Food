@extends('admin.layouts.app')


@section('title', 'Dashboard Admin - TWFood')


@section('page-title', 'Dashboard')


@section('content')

    <h1>
        Dashboard
    </h1>

    <p>
        Selamat datang,
        <strong>{{ auth()->user()->nama }}</strong>.
    </p>


    {{-- STATISTIK --}}

    <div class="cards">

        <div class="card">

            <h3>
                Total Produk
            </h3>

            <div class="number">
                {{ $totalProduk }}
            </div>

        </div>


        <div class="card">

            <h3>
                Total Varian
            </h3>

            <div class="number">
                {{ $totalVarian }}
            </div>

        </div>


        <div class="card">

            <h3>
                Total Pesanan
            </h3>

            <div class="number">
                {{ $totalPesanan }}
            </div>

        </div>


        <div class="card">

            <h3>
                Pesanan Baru
            </h3>

            <div class="number">
                {{ $pesananBaru }}
            </div>

        </div>


        <div class="card">

            <h3>
                Total Stok
            </h3>

            <div class="number">
                {{ $totalStok }}
            </div>

        </div>


        <div class="card">

            <h3>
                Total Pelanggan
            </h3>

            <div class="number">
                {{ $totalPelanggan }}
            </div>

        </div>

    </div>


    <br>


    {{-- PESANAN TERBARU --}}

    <div class="card">

        <h2>
            Pesanan Terbaru
        </h2>


        <table>

            <thead>

                <tr>

                    <th>
                        No. Pesanan
                    </th>

                    <th>
                        Pelanggan
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Pembayaran
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($pesananTerbaru as $pesanan)
                    <tr>

                        <td>
                            {{ $pesanan->nomor_pesanan }}
                        </td>

                        <td>
                            {{ $pesanan->pengguna->nama ?? '-' }}
                        </td>

                        <td>
                            Rp
                            {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}
                        </td>

                        <td>
                            {{ $pesanan->status_pembayaran }}
                        </td>

                        <td>
                            {{ $pesanan->status_pesanan }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5">
                            Belum ada pesanan.
                        </td>

                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

@endsection 
