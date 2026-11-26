@extends('layouts.admin-auth')

@section('title', 'Login Admin - TWFood')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Admin/admin-login.css') }}">
@endpush

@section('content')
    <main class="login-shell">
        @include('auth.partials.brand-panel')

        <section class="form-panel" aria-labelledby="login-title">
            <div class="login-toolbar">
                <button class="theme-toggle" type="button" id="login-theme-toggle" aria-label="Aktifkan tema terang" aria-pressed="false">
                    <i class="ti ti-sun" aria-hidden="true"></i>
                    <span class="theme-toggle-label">Mode terang</span>
                </button>
            </div>

            <div class="form-heading">
                <p class="eyebrow">Portal Admin</p>
                <h2 id="login-title">Selamat datang</h2>
                <p>Masuk untuk melanjutkan pengelolaan TWFood.</p>
            </div>

            @if ($errors->any())
                <div class="error-summary" role="alert" aria-live="polite">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.login.process') }}" method="POST">
                @csrf

                <x-admin.auth-field
                    name="email"
                    type="email"
                    label="Alamat email"
                    icon="mail"
                    :value="old('email')"
                    placeholder="nama@email.com"
                    autocomplete="username"
                    inputmode="email"
                    required
                    autofocus
                />

                <x-admin.auth-field
                    name="kata_sandi"
                    type="password"
                    label="Kata sandi"
                    icon="lock"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                    required
                />

                <button class="submit-button" type="submit">
                    <span>Masuk ke Dashboard</span>
                    <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <p class="secure-note">
                <i class="ti ti-shield-check" aria-hidden="true"></i>
                Akses aman khusus admin TWFood
            </p>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/Admin/admin-login.js') }}" defer></script>
@endpush
