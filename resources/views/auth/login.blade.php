<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#8f0e0e">

    <title>Login Admin - TWFood</title>

    <link rel="stylesheet" href="{{ asset('assets/tabler/icons/tabler-icons.min.css') }}">

    <style>
        :root {
            color-scheme: light;
            --brand: #e01f20;
            --brand-dark: #8f0e0e;
            --ink: #321b1b;
            --muted: #8b7775;
            --line: #eee5e2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            padding: 32px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(ellipse at 12% 15%, rgba(224, 31, 32, 0.08), transparent 30%),
                #faf7f5;
            color: var(--ink);
            font-family: Arial, sans-serif;
        }

        .login-shell {
            width: min(100%, 1080px);
            min-height: 650px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            overflow: hidden;
            border: 1px solid rgba(89, 39, 31, 0.08);
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 28px 80px rgba(74, 34, 29, 0.13);
        }

        .brand-panel {
            position: relative;
            isolation: isolate;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 44px;
            background: linear-gradient(145deg, #a61216 0%, #d51f20 54%, #ed4936 100%);
            color: #fff8f2;
        }

        .brand-panel::before,
        .brand-panel::after {
            position: absolute;
            z-index: -1;
            border: 1px solid rgba(255, 248, 242, 0.15);
            border-radius: 50%;
            content: "";
        }

        .brand-panel::before {
            width: 440px;
            height: 440px;
            right: -205px;
            top: 105px;
        }

        .brand-panel::after {
            width: 340px;
            height: 340px;
            right: -155px;
            top: 155px;
        }

        .brand-lockup {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: inherit;
            text-decoration: none;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 24px;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-copy {
            position: relative;
            z-index: 1;
            max-width: 410px;
            margin-top: 56px;
        }

        .brand-eyebrow {
            margin: 0 0 16px;
            color: #ffe0d3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .brand-copy h1 {
            margin: 0;
            font-size: clamp(36px, 4vw, 48px);
            line-height: 1.12;
            letter-spacing: -1.7px;
        }

        .brand-copy > p:last-child {
            max-width: 350px;
            margin: 18px 0 0;
            color: rgba(255, 248, 242, 0.82);
            font-size: 15px;
            line-height: 1.75;
        }

        .product-showcase {
            position: relative;
            width: min(100%, 365px);
            min-height: 180px;
            align-self: center;
            margin: 24px 0 20px;
            display: grid;
            place-items: center;
        }

        .product-showcase::before {
            position: absolute;
            width: 245px;
            height: 245px;
            border-radius: 50%;
            background: rgba(255, 235, 214, 0.16);
            content: "";
        }

        .product-image {
            position: relative;
            z-index: 1;
            width: 158px;
            height: 170px;
            border-radius: 22px;
            object-fit: contain;
            filter: drop-shadow(0 18px 18px rgba(71, 13, 12, 0.24));
        }

        .product-note {
            position: absolute;
            right: 3px;
            bottom: 17px;
            z-index: 2;
            max-width: 190px;
            padding: 13px 16px;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 15px;
            background: rgba(255, 250, 245, 0.96);
            box-shadow: 0 12px 30px rgba(75, 17, 16, 0.16);
            color: var(--ink);
            font-size: 12px;
            line-height: 1.5;
        }

        .product-note strong {
            display: block;
            color: var(--brand-dark);
            font-size: 13px;
        }

        .brand-footer {
            display: flex;
            align-items: center;
            gap: 9px;
            color: rgba(255, 248, 242, 0.75);
            font-size: 12px;
        }

        .brand-footer i {
            font-size: 16px;
        }

        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px clamp(36px, 6vw, 76px);
        }

        .form-heading {
            margin-bottom: 34px;
        }

        .form-heading .eyebrow {
            margin: 0 0 12px;
            color: var(--brand);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .form-heading h2 {
            margin: 0;
            font-size: 32px;
            letter-spacing: -1px;
        }

        .form-heading p:last-child {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .error-summary {
            margin: 0 0 22px;
            padding: 13px 15px;
            border: 1px solid #f1c7c3;
            border-radius: 12px;
            background: #fff3f1;
            color: #9c2521;
            font-size: 13px;
            line-height: 1.55;
        }

        .error-summary p {
            margin: 0;
        }

        .error-summary p + p {
            margin-top: 4px;
        }

        .field {
            margin-bottom: 21px;
        }

        .field label {
            display: block;
            margin-bottom: 9px;
            color: #493735;
            font-size: 13px;
            font-weight: 700;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap > i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #a38c88;
            font-size: 18px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            height: 52px;
            padding: 0 15px 0 45px;
            border: 1px solid var(--line);
            border-radius: 12px;
            outline: none;
            background: #fffdfc;
            color: var(--ink);
            font: inherit;
            font-size: 14px;
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        .input-wrap input::placeholder {
            color: #b6a8a5;
        }

        .input-wrap input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(224, 31, 32, 0.1);
        }

        .input-wrap input[aria-invalid="true"] {
            border-color: #d54b43;
        }

        .field-error {
            margin: 7px 0 0;
            color: #b52e28;
            font-size: 12px;
        }

        .submit-button {
            width: 100%;
            min-height: 52px;
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(100deg, var(--brand-dark), var(--brand));
            box-shadow: 0 10px 20px rgba(185, 26, 27, 0.2);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            transition: transform 0.18s, box-shadow 0.18s;
        }

        .submit-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 13px 24px rgba(185, 26, 27, 0.27);
        }

        .submit-button:focus-visible {
            outline: 3px solid rgba(224, 31, 32, 0.3);
            outline-offset: 3px;
        }

        .secure-note {
            margin: 25px 0 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            color: #9b8986;
            font-size: 12px;
        }

        .secure-note i {
            color: #688f6b;
            font-size: 15px;
        }

        @media (min-width: 761px) and (max-height: 820px) {
            body {
                padding: 20px;
            }

            .brand-panel {
                padding: 32px;
            }

            .brand-copy {
                margin-top: 38px;
            }

            .product-showcase {
                min-height: 160px;
                margin: 14px 0;
            }

            .product-image {
                height: 150px;
            }

            .form-panel {
                padding-top: 42px;
                padding-bottom: 42px;
            }
        }

        @media (max-width: 760px) {
            body {
                padding: 20px;
            }

            .login-shell {
                max-width: 500px;
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 22px;
            }

            .brand-panel {
                min-height: 310px;
                padding: 28px;
            }

            .brand-copy {
                max-width: 400px;
                margin-top: 38px;
            }

            .brand-copy h1 {
                max-width: 360px;
                font-size: 34px;
            }

            .brand-copy > p:last-child {
                max-width: 320px;
                margin-top: 11px;
                font-size: 13px;
            }

            .product-showcase,
            .brand-footer {
                display: none;
            }

            .form-panel {
                padding: 38px 28px 32px;
            }

            .form-heading {
                margin-bottom: 26px;
            }

            .form-heading h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 380px) {
            body {
                padding: 12px;
            }

            .brand-panel {
                min-height: 275px;
                padding: 23px;
            }

            .form-panel {
                padding: 31px 23px 26px;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <section class="brand-panel" aria-label="Tentang TWFood">
            <a class="brand-lockup" href="{{ url('/') }}" aria-label="TWFood, kembali ke halaman utama">
                <span class="brand-mark" aria-hidden="true"><i class="ti ti-seeding"></i></span>
                <span class="brand-name">TWFood</span>
            </a>

            <div class="brand-copy">
                <p class="brand-eyebrow">Lezat dan Sehat</p>
                <h1>Kebaikan dari alam, untuk setiap hidangan.</h1>
                <p>Kelola produk, pesanan, dan cerita TWFood dari satu tempat.</p>
            </div>

            <div class="product-showcase" aria-label="Produk pilihan TWFood: Kaldu Jamur Tiram">
                <img
                    class="product-image"
                    src="{{ asset('storage/products/kaldu-jamur-tiram-shitake.png') }}"
                    alt="Kaldu Jamur Tiram TWFood"
                >
                <div class="product-note">
                    <strong>Rasa alami, pilihan keluarga</strong>
                    Temani kreasi masakan sehatmu.
                </div>
            </div>

            <div class="brand-footer">
                <i class="ti ti-heart" aria-hidden="true"></i>
                <span>Dibuat dengan sepenuh hati oleh TWFood</span>
            </div>
        </section>

        <section class="form-panel" aria-labelledby="login-title">
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

                <div class="field">
                    <label for="email">Alamat email</label>
                    <div class="input-wrap">
                        <i class="ti ti-mail" aria-hidden="true"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="username"
                            inputmode="email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @if ($errors->has('email')) aria-describedby="email-error" @endif
                            required
                            autofocus
                        >
                    </div>
                    @if ($errors->has('email'))
                        <p class="field-error" id="email-error">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                <div class="field">
                    <label for="kata_sandi">Kata sandi</label>
                    <div class="input-wrap">
                        <i class="ti ti-lock" aria-hidden="true"></i>
                        <input
                            type="password"
                            id="kata_sandi"
                            name="kata_sandi"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

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
</body>
</html>
