<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17120D">
    <script>
        (() => {
            try {
                const saved = localStorage.getItem('twfood-theme');
                const theme = saved === 'light' || saved === 'dark' ? saved :
                    (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
                document.documentElement.dataset.theme = theme;
            } catch (_) { document.documentElement.dataset.theme = 'dark'; }
        })();
    </script>

    <title>
        @yield('title', 'TWFood - Lezat dan Sehat')
    </title>



    {{-- 1) Gaya bersama: warna, header, footer, tombol (public/css/layout.css) --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    {{-- 2) Gaya khusus tiap halaman (mis. home.css) diisi lewat @push('styles') di halaman itu --}}
    @stack('styles')
</head>

<body class="@yield('body_class')">

    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    @stack('scripts')
</body>

</html>
