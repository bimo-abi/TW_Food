@extends('admin.layouts.app')

@section('title', 'Detail Pesanan')

@section('content')

    <div class="container">

        <h1>Detail Pesanan</h1>

        <div class="card">

            <h2>{{ $pesanan->nomor_pesanan }}</h2>

            <p>
                <strong>Pelanggan:</strong>
                {{ $pesanan->pengguna->nama ?? '-' }}
            </p>

            <p>
                <strong>Email:</strong>
                {{ $pesanan->pengguna->email ?? '-' }}
            </p>

            <p>
                <strong>Status Pesanan:</strong>
                {{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}
            </p>

            <p>
                <strong>Status Pembayaran:</strong>
                {{ ucwords(str_replace('_', ' ', $pesanan->status_pembayaran)) }}
            </p>

        </div>

        @if (session('success'))
            <div class="card" style="margin-top: 20px;">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="card" style="margin-top: 20px;">

            <h2>Update Status Pesanan</h2>

            <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">

                @csrf
                @method('PUT')

                <div style="margin-top: 15px;">

                    <label for="status_pesanan">
                        Status Pesanan
                    </label>

                    <div style="margin-top: 20px;">

                        <h3>Tindakan Pesanan</h3>

                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST"
                            style="display: flex; gap: 10px; flex-wrap: wrap;">
                            @csrf
                            @method('PUT')

                            @if ($pesanan->status_pesanan === 'pesanan_diterima')

                                <button type="submit" name="status_pesanan" value="sedang_diproses" class="button">
                                    Proses Pesanan
                                </button>

                                <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                                    Batalkan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'sedang_diproses')
                                <button type="submit" name="status_pesanan" value="pesanan_siap" class="button">
                                    Pesanan Siap
                                </button>

                                <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                                    Batalkan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'pesanan_siap')
                                @if ($pesanan->jenis_pesanan === 'delivery')
                                    <button type="submit" name="status_pesanan" value="menunggu_pengiriman" class="button">
                                        Menunggu Pengiriman
                                    </button>
                                @elseif ($pesanan->jenis_pesanan === 'pickup')
                                    <button type="submit" name="status_pesanan" value="siap_diambil" class="button">
                                        Siap Diambil
                                    </button>
                                @endif

                                <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                                    Batalkan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'menunggu_pengiriman')
                                <button type="submit" name="status_pesanan" value="dalam_pengiriman" class="button">
                                    Mulai Pengiriman
                                </button>

                                <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                                    Batalkan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'dalam_pengiriman')
                                <button type="submit" name="status_pesanan" value="diterima" class="button">
                                    Tandai Diterima
                                </button>
                            @elseif ($pesanan->status_pesanan === 'diterima')
                                <button type="submit" name="status_pesanan" value="selesai" class="button">
                                    Selesaikan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'siap_diambil')
                                <button type="submit" name="status_pesanan" value="pesanan_diambil" class="button">
                                    Tandai Pesanan Diambil
                                </button>
                            @elseif ($pesanan->status_pesanan === 'pesanan_diambil')
                                <button type="submit" name="status_pesanan" value="selesai" class="button">
                                    Selesaikan Pesanan
                                </button>
                            @elseif ($pesanan->status_pesanan === 'selesai')
                                <p>Pesanan telah selesai.</p>
                            @elseif ($pesanan->status_pesanan === 'dibatalkan')
                                <p>Pesanan telah dibatalkan.</p>

                            @endif

                        </form>

                    </div>

                </div>

                <button type="submit" class="button" style="margin-top: 15px;">
                    Simpan Status
                </button>

            </form>

        </div>

        {{-- DETAIL PRODUK --}}
        <div class="card" style="margin-top: 20px;">

            <h2>Produk</h2>

            @foreach ($pesanan->detailPesanan as $detail)
                <div style="
                padding: 15px 0;
                border-bottom: 1px solid #eee;
            ">

                    <h3>
                        {{ $detail->nama_produk_saat_pesan }}
                    </h3>

                    <p>
                        Varian:
                        {{ $detail->nama_varian_saat_pesan }}
                    </p>

                    <p>
                        Harga:
                        Rp {{ number_format($detail->harga_saat_pesan, 0, ',', '.') }}
                    </p>

                    <p>
                        Jumlah:
                        {{ $detail->jumlah }}
                    </p>

                    <p>
                        Subtotal:
                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                    </p>

                </div>
            @endforeach

        </div>

        {{-- ALAMAT --}}
        @if ($pesanan->alamat)
            <div class="card" style="margin-top: 20px;">

                <h2>Alamat Pengiriman</h2>

                <p>
                    <strong>
                        {{ $pesanan->alamat->nama_penerima }}
                    </strong>
                </p>

                <p>
                    {{ $pesanan->alamat->nomor_telepon }}
                </p>

                <p>
                    {{ $pesanan->alamat->alamat_lengkap }},
                    {{ $pesanan->alamat->kota }},
                    {{ $pesanan->alamat->provinsi }}
                    {{ $pesanan->alamat->kode_pos }}
                </p>

            </div>
        @endif

        {{-- TOTAL --}}
        <div class="card" style="margin-top: 20px;">

            <h2>Total Pesanan</h2>

            <p>
                Subtotal:
                Rp {{ number_format($pesanan->subtotal_produk, 0, ',', '.') }}
            </p>

            <p>
                Diskon:
                Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}
            </p>

            <h3>
                Total:
                Rp {{ number_format($pesanan->total_pesanan, 0, ',', '.') }}
            </h3>

        </div>

        <div style="margin-top: 20px;">

            <a href="{{ route('admin.pesanan.index') }}" class="button">
                ← Kembali ke Pesanan
            </a>

        </div>

    </div>

@endsection
