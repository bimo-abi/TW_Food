@extends('admin.layouts.app')

@section('title', 'Edit Campaign - TWFood')
@section('page-title', 'Edit Campaign')

@section('content')
    <div class="text-secondary mb-4">
        Periksa kembali periode dan persentase diskon setiap varian sebelum menyimpan perubahan.
    </div>

    <form method="POST" action="{{ route('admin.promosi.update', $promosi->id_promosi) }}">
        @csrf
        @method('PUT')
        @include('admin.promosi._form')
    </form>
@endsection
