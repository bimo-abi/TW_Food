@extends('admin.layouts.app')

@section('title', 'Edit Promosi - TWFood')
@section('page-title', 'Edit Promosi')

@section('content')
    <div class="text-secondary mb-4">
        Ubah detail promosi, lalu simpan. Perubahan langsung berlaku.
    </div>

    <form method="POST" action="{{ route('admin.promosi.update', $promosi->id_promosi) }}">
        @csrf
        @method('PUT')
        @include('admin.promosi._form')
    </form>
@endsection