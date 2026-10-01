@extends('admin.layouts.app')

@section('title', 'Detail Pesanan')

@section('content')

    <div class="container">

        <h1>Detail Pesanan</h1>

        {{-- INFORMASI PESANAN --}}
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
                <strong>Jenis Pesanan:</strong>
                {{ ucfirst($pesanan->jenis_pesanan) }}
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


        {{-- PESAN BERHASIL --}}
        @if (session('success'))
            <div class="card" style="margin-top: 20px;">

                <p>
                    {{ session('success') }}
                </p>

            </div>
        @endif


        {{-- PESAN ERROR --}}
        @if ($errors->any())

            <div class="card" style="margin-top: 20px;">

                <strong>Terjadi kesalahan:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- TINDAKAN PESANAN --}}
        <div class="card" style="margin-top: 20px;">

            <h2>Tindakan Pesanan</h2>

            <p>
                Status saat ini:
                <strong>
                    {{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}
                </strong>
            </p>


            <div
                style="
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 20px;
            ">

                @if ($pesanan->status_pesanan === 'pesanan_diterima')

                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="sedang_diproses" class="button">
                            Proses Pesanan
                        </button>
                    </form>


                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                            Batalkan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'sedang_diproses')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="pesanan_siap" class="button">
                            Pesanan Siap
                        </button>
                    </form>


                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                            Batalkan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'pesanan_siap')
                    @if ($pesanan->jenis_pesanan === 'delivery')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="menunggu_pengiriman" class="button">
                                Menunggu Pengiriman
                            </button>
                        </form>
                    @elseif ($pesanan->jenis_pesanan === 'pickup')
                        <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <button type="submit" name="status_pesanan" value="siap_diambil" class="button">
                                Siap Diambil
                            </button>
                        </form>
                    @endif


                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                            Batalkan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'menunggu_pengiriman')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dalam_pengiriman" class="button">
                            Mulai Pengiriman
                        </button>
                    </form>


                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="dibatalkan" class="button">
                            Batalkan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'dalam_pengiriman')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="diterima" class="button">
                            Tandai Diterima
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'diterima')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="selesai" class="button">
                            Selesaikan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'siap_diambil')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="pesanan_diambil" class="button">
                            Tandai Pesanan Diambil
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'pesanan_diambil')
                    <form action="{{ route('admin.pesanan.update-status', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <button type="submit" name="status_pesanan" value="selesai" class="button">
                            Selesaikan Pesanan
                        </button>
                    </form>
                @elseif ($pesanan->status_pesanan === 'selesai')
                    <p>
                        Pesanan telah selesai.
                    </p>
                @elseif ($pesanan->status_pesanan === 'dibatalkan')
                    <p>
                        Pesanan telah dibatalkan.
                    </p>

                @endif

            </div>

        </div>

        @if ($pesanan->jenis_pesanan === 'delivery')
            <div class="card mt-4">
                <div class="card-header">
                    <strong>Informasi Pengiriman</strong>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <strong>Kurir:</strong>

                        @if ($pesanan->kurir === 'gosend')
                            GoSend
                        @elseif ($pesanan->kurir === 'jnt')
                            J&T
                        @else
                            -
                        @endif
                    </div>

                    {{-- <div class="mb-3">
                        <strong>Nomor Resi:</strong>

                        @if ($pesanan->nomor_resi)
                            {{ $pesanan->nomor_resi }}
                        @else
                            <span class="text-muted">
                                Belum diisi
                            </span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Tautan Pelacakan:</strong>

                        @if ($pesanan->tautan_pelacakan)
                            <a href="{{ $pesanan->tautan_pelacakan }}" target="_blank" rel="noopener noreferrer">
                                Lihat Pelacakan
                            </a>
                        @else
                            <span class="text-muted">
                                Belum diisi
                            </span>
                        @endif
                    </div> --}}
                    @if ($pesanan->kurir === 'gosend')

                        <div class="mb-3">
                            <strong>Tautan Pelacakan:</strong>

                            @if ($pesanan->tautan_pelacakan)
                                <a href="{{ $pesanan->tautan_pelacakan }}" target="_blank" rel="noopener noreferrer">
                                    Lihat Tracking GoSend
                                </a>
                            @else
                                <span class="text-muted">
                                    Belum diisi
                                </span>
                            @endif
                        </div>
                    @elseif ($pesanan->kurir === 'jnt')
                        <div class="mb-3">
                            <strong>Nomor Resi:</strong>

                            @if ($pesanan->nomor_resi)
                                {{ $pesanan->nomor_resi }}
                            @else
                                <span class="text-muted">
                                    Belum diisi
                                </span>
                            @endif
                        </div>

                    @endif

                    <hr>

                    <form action="{{ route('admin.pesanan.update-pengiriman', $pesanan->id_pesanan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- <div class="mb-3">
                            <label for="nomor_resi" class="form-label">
                                Nomor Resi
                            </label>

                            <input type="text" name="nomor_resi" id="nomor_resi" class="form-control"
                                value="{{ old('nomor_resi', $pesanan->nomor_resi) }}" placeholder="Masukkan nomor resi">
                        </div>

                        <div class="mb-3">
                            <label for="tautan_pelacakan" class="form-label">
                                Tautan Pelacakan
                            </label>

                            <input type="url" name="tautan_pelacakan" id="tautan_pelacakan" class="form-control"
                                value="{{ old('tautan_pelacakan', $pesanan->tautan_pelacakan) }}"
                                placeholder="https://...">
                        </div> --}}
                        @if ($pesanan->kurir === 'gosend')
                            <div class="mb-3">
                                <label for="tautan_pelacakan" class="form-label">
                                    Tautan Pelacakan GoSend
                                </label>

                                <input type="url" name="tautan_pelacakan" id="tautan_pelacakan" class="form-control"
                                    value="{{ old('tautan_pelacakan', $pesanan->tautan_pelacakan) }}"
                                    placeholder="Masukkan link tracking GoSend">

                                <small class="text-muted">
                                    Masukkan tautan tracking yang diberikan oleh GoSend.
                                </small>
                            </div>
                        @elseif ($pesanan->kurir === 'jnt')
                            <div class="mb-3">
                                <label for="nomor_resi" class="form-label">
                                    Nomor Resi J&T
                                </label>

                                <input type="text" name="nomor_resi" id="nomor_resi" class="form-control"
                                    value="{{ old('nomor_resi', $pesanan->nomor_resi) }}"
                                    placeholder="Masukkan nomor resi J&T">

                                <small class="text-muted">
                                    Masukkan nomor resi/AWB yang diberikan oleh J&T.
                                </small>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            Simpan Informasi Pengiriman
                        </button>
                    </form>

                </div>
            </div>
        @endif

        {{-- DETAIL PRODUK --}}
        <div class="card" style="margin-top: 20px;">

            <h2>Produk</h2>

            @forelse ($pesanan->detailPesanan as $detail)
                <div
                    style="
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

            @empty

                <p>
                    Belum ada detail produk pada pesanan ini.
                </p>
            @endforelse

        </div>


        {{-- ALAMAT PENGIRIMAN --}}
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


        {{-- KEMBALI --}}
        <div style="margin-top: 20px;">

            <a href="{{ route('admin.pesanan.index') }}" class="button">
                ← Kembali ke Pesanan
            </a>

        </div>

    </div>

@endsection
