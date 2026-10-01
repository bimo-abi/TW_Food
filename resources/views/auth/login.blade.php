<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - TWFood</title>

    <!-- CSS Bootstrap 5 -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        :root {
            --tw-green-dark: #1b4332;
            --tw-green-primary: #2d6a4f;
            --tw-green-light: #d8f3dc;
            --tw-brown-primary: #8c4a00;
            --tw-brown-hover: #6f3a00;
        }

        body {
            background-color: var(--tw-green-dark);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(82, 183, 136, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(45, 106, 79, 0.3) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='120' height='120' viewBox='0 0 120 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%252395d5b2' fill-opacity='0.07'%3E%3Cpath d='M60 10 C 65 30, 85 35, 105 35 C 85 55, 65 50, 60 70 C 55 50, 35 55, 15 35 C 35 35, 55 30, 60 10 Z'/%3E%3Ccircle cx='60' cy='95' r='5'/%3E%3Ccircle cx='10' cy='105' r='3'/%3E%3Ccircle cx='110' cy='15' r='4'/%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        .login-section {
            position: relative;
            z-index: 1;
        }

        .tw-badge-large {
            background-color: var(--tw-green-light);
            color: var(--tw-green-dark);
            font-weight: 700;
            padding: 10px 22px;
            border-radius: 50rem;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border: 2px solid rgba(255,255,255,0.4);
        }

        /* Tab Login / Daftar */
        .tw-tabs {
            display: flex;
            border: 1px solid #dee2e6;
            border-radius: 50rem;
            padding: 3px;
            gap: 3px;
        }

        .tw-tab {
            flex: 1;
            border: none;
            background: transparent;
            border-radius: 50rem;
            padding: 9px 0;
            font-weight: 600;
            color: #495057;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .tw-tab.active {
            background-color: var(--tw-brown-primary);
            color: #ffffff;
        }

        .tw-tab:focus-visible {
            outline: 3px solid rgba(45, 106, 79, 0.45);
            outline-offset: 1px;
        }

        .tw-panel { display: none; }
        .tw-panel.active { display: block; }

        .btn-tw-brown {
            background-color: var(--tw-brown-primary);
            color: #ffffff;
            border: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-tw-brown:hover {
            background-color: var(--tw-brown-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .form-control:focus {
            border-color: var(--tw-green-primary);
            box-shadow: 0 0 0 0.25rem rgba(45, 106, 79, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--tw-green-primary);
            border-color: var(--tw-green-primary);
        }

        .tw-link {
            color: var(--tw-green-primary);
            font-weight: 600;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
        }

        .tw-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

@php
    // Buka tab Daftar kalau validasi pendaftaran gagal
    $tabAwal = $errors->getBag('register')->any() ? 'daftar' : 'login';
@endphp

<div class="bg-pattern"></div>

<section class="min-vh-100 d-flex align-items-center py-5 login-section">
  <div class="container">
    <div class="row gy-4 align-items-center justify-content-center">

      <!-- Sisi Kiri: Branding -->
      <div class="col-12 col-md-6 col-xl-6 text-white pe-md-5">
        <div class="mb-4">
          <img src="{{ asset('img/logo-twfood.png') }}"
               alt="TWFood Logo"
               class="img-fluid bg-white p-2 rounded-3 shadow-sm mb-3"
               style="max-height: 60px;"
               onerror="this.style.display='none'">
          <br>
          <span class="tw-badge-large">🌱 PANEL ADMINISTRATOR</span>
        </div>

        <h1 class="display-4 fw-bold mb-3">TWFood <span style="color: #95d5b2;">Admin</span></h1>
        <hr class="border-light opacity-25 mb-4">

        <h2 class="h3 fw-semibold mb-3">Sistem Kelola & Manajerial Produk TWFood Jember</h2>
        <p class="lead opacity-75 mb-4" style="font-size: 1rem;">
          Akses khusus administrator untuk mengelola pesanan, stok varian, daftar harga, dan katalog produk olahan sehat berbasis tanaman (*plant-based*).
        </p>
      </div>

      <!-- Sisi Kanan: Kartu Login / Daftar -->
      <div class="col-12 col-md-6 col-xl-5">
        <div class="card border-0 rounded-4 shadow-lg">
          <div class="card-body p-4 p-md-5">

            <!-- Tombol tab -->
            <div class="tw-tabs mb-4" role="tablist">
              <button type="button" class="tw-tab {{ $tabAwal === 'login' ? 'active' : '' }}"
                      id="tab-login" role="tab" onclick="tampilkanTab('login')">Login</button>
              <button type="button" class="tw-tab {{ $tabAwal === 'daftar' ? 'active' : '' }}"
                      id="tab-daftar" role="tab" onclick="tampilkanTab('daftar')">Daftar</button>
            </div>

            @if (session('success'))
              <div class="alert alert-success mb-4 py-2 small" role="alert">
                {{ session('success') }}
              </div>
            @endif

            <!-- ===== PANEL LOGIN ===== -->
            <div class="tw-panel {{ $tabAwal === 'login' ? 'active' : '' }}" id="panel-login" role="tabpanel">
              <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Masuk Admin</h3>
                <p class="text-muted small mb-0">Silakan masukkan akun kredensial Anda</p>
              </div>

              @if ($errors->any())
                <div class="alert alert-danger mb-4 py-2 small" role="alert">
                  {{ $errors->first() }}
                </div>
              @endif

              <form action="{{ route('admin.login.process') }}" method="POST">
                @csrf
                <div class="row gy-3">
                  <div class="col-12">
                    <div class="form-floating">
                      <input type="email" class="form-control" name="email" id="login_email"
                             value="{{ $tabAwal === 'login' ? old('email') : '' }}"
                             placeholder="admin@twfood.com" required>
                      <label for="login_email" class="text-secondary">Email Admin</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="password" class="form-control" name="password" id="login_password"
                             placeholder="Password" required>
                      <label for="login_password" class="text-secondary">Password</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                      <label class="form-check-label text-secondary small" for="remember_me">Ingat Saya</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="d-grid mt-2">
                      <button class="btn btn-tw-brown btn-lg py-3 fw-bold fs-6 rounded-3" type="submit">Login Sekarang</button>
                    </div>
                  </div>
                </div>
              </form>

              <p class="text-center small text-secondary mt-4 mb-0">
                Belum punya akun?
                <button type="button" class="tw-link" onclick="tampilkanTab('daftar')">Daftar sekarang</button>
              </p>
            </div>

            <!-- ===== PANEL DAFTAR ===== -->
            <div class="tw-panel {{ $tabAwal === 'daftar' ? 'active' : '' }}" id="panel-daftar" role="tabpanel">
              <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Daftar Admin</h3>
                <p class="text-muted small mb-0">Buat akun admin baru dengan kode pendaftaran</p>
              </div>

              @if ($errors->getBag('register')->any())
                <div class="alert alert-danger mb-4 py-2 small" role="alert">
                  {{ $errors->getBag('register')->first() }}
                </div>
              @endif

              <form action="{{ route('admin.register.process') }}" method="POST">
                @csrf
                <div class="row gy-3">
                  <div class="col-12">
                    <div class="form-floating">
                      <input type="text" class="form-control" name="nama" id="reg_nama"
                             value="{{ $tabAwal === 'daftar' ? old('nama') : '' }}"
                             placeholder="Nama lengkap" required>
                      <label for="reg_nama" class="text-secondary">Nama lengkap</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="email" class="form-control" name="email" id="reg_email"
                             value="{{ $tabAwal === 'daftar' ? old('email') : '' }}"
                             placeholder="Email" required>
                      <label for="reg_email" class="text-secondary">Email</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="tel" class="form-control" name="nomor_telepon" id="reg_telepon"
                             value="{{ $tabAwal === 'daftar' ? old('nomor_telepon') : '' }}"
                             placeholder="Nomor telepon" maxlength="13" inputmode="numeric"
                             pattern="08[0-9]{8,11}"
                             title="Nomor telepon harus diawali 08, contoh: 081234567890"
                             required>
                      <label for="reg_telepon" class="text-secondary">Nomor telepon</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="password" class="form-control" name="kata_sandi" id="reg_sandi"
                             placeholder="Kata sandi" minlength="8"
                             pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                             title="Kata sandi minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka"
                             required>
                      <label for="reg_sandi" class="text-secondary">Kata sandi</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="password" class="form-control" name="kata_sandi_confirmation" id="reg_sandi2"
                             placeholder="Ulangi kata sandi" required>
                      <label for="reg_sandi2" class="text-secondary">Ulangi kata sandi</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <input type="password" class="form-control" name="kode_daftar" id="reg_kode"
                             placeholder="Kode pendaftaran" required>
                      <label for="reg_kode" class="text-secondary">Kode pendaftaran</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="d-grid mt-2">
                      <button class="btn btn-tw-brown btn-lg py-3 fw-bold fs-6 rounded-3" type="submit">Daftar Sekarang</button>
                    </div>
                  </div>
                </div>
              </form>

              <p class="text-center small text-secondary mt-4 mb-0">
                Sudah punya akun?
                <button type="button" class="tw-link" onclick="tampilkanTab('login')">Login sekarang</button>
              </p>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
    // Pindah antara tab Login dan Daftar tanpa memuat ulang halaman
    function tampilkanTab(nama) {
        ['login', 'daftar'].forEach(function (t) {
            var aktif = (t === nama);
            document.getElementById('tab-' + t).classList.toggle('active', aktif);
            document.getElementById('panel-' + t).classList.toggle('active', aktif);
        });
    }

    // Pesan peringatan browser untuk nomor telepon dan kata sandi
    var pesanKustom = {
        reg_telepon: 'Nomor telepon harus diawali 08',
        reg_sandi: 'Kata sandi minimal 8 karakter, dengan huruf besar, huruf kecil, dan angka'
    };

    Object.keys(pesanKustom).forEach(function (id) {
        var el = document.getElementById(id);

        el.addEventListener('invalid', function () {
            // Biarkan pesan bawaan untuk kolom kosong, pakai pesan kustom untuk format salah
            if (!el.validity.valueMissing) {
                el.setCustomValidity(pesanKustom[id]);
            }
        });

        el.addEventListener('input', function () {
            el.setCustomValidity('');
        });
    });
</script>

</body>
</html>