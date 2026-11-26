@extends('admin.layouts.app')

@section('title', 'Edit Resep - Admin TWFood')
@section('page-title', 'Edit Resep')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">Perbarui informasi resep TWFood.</div>
        <a href="{{ route('admin.resep.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @include('admin.resep.form', ['resep' => $resep])
@endsection
