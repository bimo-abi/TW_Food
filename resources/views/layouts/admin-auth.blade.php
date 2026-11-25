<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#8f0e0e">

    <title>@yield('title', 'Admin - TWFood')</title>

    <link rel="stylesheet" href="{{ asset('assets/tabler/icons/tabler-icons.min.css') }}">
    @stack('styles')
</head>

<body>
    @yield('content')
</body>
</html>
