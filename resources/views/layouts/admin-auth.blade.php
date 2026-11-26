<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17120D">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('twfood-admin-login-theme');
                document.documentElement.setAttribute('data-theme', saved === 'light' ? 'light' : 'dark');
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <title>@yield('title', 'Admin - TWFood')</title>

    <link rel="stylesheet" href="{{ asset('assets/tabler/icons/tabler-icons.min.css') }}">
    @stack('styles')
</head>

<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
