@extends('admin.layouts.app')

@section('title', 'Tambah Promosi - TWFood')
@section('page-title', 'Tambah Promosi')

@section('content')
    <div class="page-header mb-3">
        <h1>Tambah Promosi</h1>
    </div>

    <form method="POST" action="{{ route('admin.promosi.store') }}">
        @csrf
        @include('admin.promosi._form')
    </form>
@endsection