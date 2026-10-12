
@extends('admin.layouts.app')

@section('title', 'Pengaturan Mitra - Admin TWFood')

@section('page-title', 'Pengaturan Mitra')

@section('content')

    <div style="max-width: 850px;">

        <h1>Pengaturan Mitra</h1>

        <p style="color: #666;">
            Atur ketentuan pembayaran uang muka (DP) dan notifikasi
            untuk operasional TWFOOD.
        </p>

        {{-- Pesan berhasil --}}
        @if (session('success'))
            <div style="
                background: #d1e7dd;
                color: #0f5132;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 5px;
            ">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pesan validasi --}}
        @if ($errors->any())
            <div style="
                background: #f8d7da;
                color: #842029;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 5px;
            ">
                <strong>Periksa kembali data berikut:</strong>

                <ul style="margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card" style="padding: 24px;">

            <form
                action="{{ route('admin.pengaturan-mitra.update') }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                {{-- Pengaturan DP --}}
                <h3 style="margin-bottom: 20px;">
                    Pengaturan Pembayaran DP
                </h3>

                <div style="margin-bottom: 24px;">
                    <label style="
                        display: block;
                        font-weight: bold;
                        margin-bottom: 8px;
                    ">
                        Status Pembayaran DP
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input
                            type="checkbox"
                            name="dp_aktif"
                            value="1"
                            @checked(old('dp_aktif', $pengaturan->dp_aktif))
                        >

                        Aktifkan pembayaran DP
                    </label>

                    <small style="color: #666;">
                        Jika dinonaktifkan, pelanggan tidak dapat memilih
                        pembayaran DP berdasarkan pengaturan ini.
                    </small>
                </div>

                <div style="margin-bottom: 24px;">
                    <label
                        for="minimal_quantity_dp"
                        style="
                            display: block;
                            font-weight: bold;
                            margin-bottom: 8px;
                        "
                    >
                        Minimum Jumlah Pembelian untuk DP
                    </label>

                    <input
                        type="number"
                        id="minimal_quantity_dp"
                        name="minimal_quantity_dp"
                        value="{{ old(
                            'minimal_quantity_dp',
                            $pengaturan->minimal_quantity_dp
                        ) }}"
                        min="1"
                        max="65535"
                        step="1"
                        required
                        style="width: 100%; padding: 10px;"
                    >

                    <small style="color: #666;">
                        Contoh: 10 unit. Jumlah ini digunakan sebagai
                        ambang minimum untuk memenuhi ketentuan DP.
                    </small>
                </div>

                <div style="margin-bottom: 28px;">
                    <label
                        for="persentase_dp"
                        style="
                            display: block;
                            font-weight: bold;
                            margin-bottom: 8px;
                        "
                    >
                        Persentase DP (%)
                    </label>

                    <input
                        type="number"
                        id="persentase_dp"
                        name="persentase_dp"
                        value="{{ old(
                            'persentase_dp',
                            $pengaturan->persentase_dp
                        ) }}"
                        min="0.01"
                        max="100"
                        step="0.01"
                        required
                        style="width: 100%; padding: 10px;"
                    >

                    <small style="color: #666;">
                        Masukkan nilai antara 0,01% hingga 100%.
                        Contoh: 50 berarti pelanggan membayar DP 50%
                        dari total pesanan yang memenuhi ketentuan.
                    </small>
                </div>

                <hr style="margin: 24px 0;">

                {{-- Pengaturan notifikasi --}}
                <h3 style="margin-bottom: 20px;">
                    Pengaturan Notifikasi
                </h3>

                <div style="margin-bottom: 28px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input
                            type="checkbox"
                            name="notifikasi_aktif"
                            value="1"
                            @checked(old(
                                'notifikasi_aktif',
                                $pengaturan->notifikasi_aktif
                            ))
                        >

                        Aktifkan notifikasi
                    </label>

                    <small style="color: #666;">
                        Pengaturan ini menyimpan preferensi notifikasi
                        Mitra. Pengiriman notifikasi belum diaktifkan
                        hanya dengan mencentang pilihan ini.
                    </small>
                </div>

                <button
                    type="submit"
                    style="
                        background: #206bc4;
                        color: white;
                        border: none;
                        padding: 10px 18px;
                        border-radius: 5px;
                        cursor: pointer;
                    "
                >
                    Simpan Pengaturan
                </button>

            </form>

        </div>

    </div>

@endsection
