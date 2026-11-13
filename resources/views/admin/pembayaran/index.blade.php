@extends('admin.layouts.app')

@section('title', 'Pembayaran - TWFood')

@section('content')

    <div class="page-header">
        <h1>Pembayaran</h1>
        <p>Kelola data pembayaran pesanan.</p>
    </div>

    <div class="content-card">

        {{-- TODO: filter / search --}}

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kolom 1</th>
                    <th>Kolom 2</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- TODO: ganti dengan @forelse ($data as $item) --}}
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Data belum tersedia.
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

@endsection
