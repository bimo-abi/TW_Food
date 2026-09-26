@extends('admin.layouts.app')

@section('title', 'Pesanan')

@section('content')

    <div class="container">

        <h1>Pesanan</h1>

        <p>
            Daftar pesanan pelanggan TWFood.
        </p>

        @if ($pesanan->isEmpty())

            <div class="card">
                <p>Belum ada pesanan.</p>
            </div>
        @else
            <div class="card">

                <table width="100%" cellpadding="10" cellspacing="0">

                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Pengiriman</th>
                            <th>Status Pesanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($pesanan as $item)
                            <tr>

                                <td>
                                    {{ $item->nomor_pesanan }}
                                </td>

                                <td>
                                    {{ $item->pengguna->nama ?? '-' }}
                                </td>

                                <td>
                                    Rp {{ number_format($item->total_pesanan, 0, ',', '.') }}
                                </td>

                                <td>
                                    {{ ucwords(str_replace('_', ' ', $item->status_pembayaran)) }}
                                </td>

                                <td>
                                    @if ($item->jenis_pesanan === 'pickup')
                                        Pickup
                                    @elseif ($item->jenis_pesanan === 'delivery')
                                        @if ($item->kurir === 'gosend')
                                            GoSend
                                        @elseif ($item->kurir === 'jnt')
                                            J&T
                                        @else
                                            Delivery
                                        @endif

                                        <br>

                                        @if ($item->nomor_resi)
                                            <small>
                                                Resi: {{ $item->nomor_resi }}
                                            </small>
                                        @else
                                            <small>
                                                Resi: Belum diisi
                                            </small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ ucwords(str_replace('_', ' ', $item->status_pesanan)) }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.pesanan.show', $item->id_pesanan) }}" class="button">
                                        Detail
                                    </a>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

@endsection
