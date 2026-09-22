<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - TWFood</title>
</head>

<body>

    <h1>Login Admin TWFood</h1>

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="/admin/login" method="POST">

        @csrf

        <div>
            <label for="email">Email</label>
            <br>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </div>

        <br>

        <div>
            <label for="kata_sandi">Password</label>
            <br>

            <input
                type="password"
                id="kata_sandi"
                name="kata_sandi"
                required
            >
        </div>

        <br>

        <button type="submit">
            Login
        </button>

    </form>

</body>
</html>
