@extends('admin.layouts.app')

@section('title', 'Tambah Promosi - TWFood')
@section('page-title', 'Tambah Promosi')

@section('content')
    <div class="text-secondary mb-4">
        Isi langkah 1 sampai 5. Ringkasan di kanan akan terisi otomatis sebelum kamu menyimpan.
    </div>

    <form method="POST" action="{{ route('admin.promosi.store') }}">
        @csrf
        @include('admin.promosi._form')
    </form>
@endsection