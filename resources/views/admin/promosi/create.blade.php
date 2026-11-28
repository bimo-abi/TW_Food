@extends('admin.layouts.app')

@section('title', 'Buat Campaign - TWFood')
@section('page-title', 'Buat Campaign')

@section('content')
    <div class="text-secondary mb-4">
        Atur informasi dan periode Campaign, pilih varian produk, lalu tentukan diskon untuk setiap varian.
    </div>

    <form method="POST" action="{{ route('admin.promosi.store') }}">
        @csrf
        @include('admin.promosi._form')
    </form>
@endsection
