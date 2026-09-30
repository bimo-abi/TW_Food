<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - TWFood</title>
    
    <!-- CSS Bootstrap 5 -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
    <style>
        /* Custom Theme TWFood */
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

        /* Motif Background Organik Dedaunan & Glowing Effect */
        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            background-image: 
                /* Radial gradient untuk pencahayaan lembut */
                radial-gradient(circle at 20% 30%, rgba(82, 183, 136, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(45, 106, 79, 0.3) 0%, transparent 50%),
                /* Motif Dedaunan Organik SVG (Plant-based Theme) */
                url("data:image/svg+xml,%3Csvg width='120' height='120' viewBox='0 0 120 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%252395d5b2' fill-opacity='0.07'%3E%3Cpath d='M60 10 C 65 30, 85 35, 105 35 C 85 55, 65 50, 60 70 C 55 50, 35 55, 15 35 C 35 35, 55 30, 60 10 Z'/%3E%3Ccircle cx='60' cy='95' r='5'/%3E%3Ccircle cx='10' cy='105' r='3'/%3E%3Ccircle cx='110' cy='15' r='4'/%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        /* Container Login di atas Pattern */
        .login-section {
            position: relative;
            z-index: 1;
        }

        /* Badge Panel Administrator (Diperbesar & Ditegaskan) */
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
    </style>
</head>
<body>

<!-- Element Motif Background -->
<div class="bg-pattern"></div>

<section class="min-vh-100 d-flex align-items-center py-5 login-section">
  <div class="container">
    <div class="row gy-4 align-items-center justify-content-center">
      
      <!-- Sisi Kiri: Branding TWFood -->
      <div class="col-12 col-md-6 col-xl-6 text-white pe-md-5">
        <div class="mb-4">
          <!-- Optional Logo (ditampilkan jika file gambar ada) -->
          <img src="{{ asset('img/logo-twfood.png') }}" 
               alt="TWFood Logo" 
               class="img-fluid bg-white p-2 rounded-3 shadow-sm mb-3" 
               style="max-height: 60px;"
               onerror="this.style.display='none'">
          
          <br>
          <!-- Badge Diperbesar & Lebih Jelas -->
          <span class="tw-badge-large">
            🌱 PANEL ADMINISTRATOR
          </span>
        </div>
        
        <h1 class="display-4 fw-bold mb-3">TWFood <span style="color: #95d5b2;">Admin</span></h1>
        <hr class="border-light opacity-25 mb-4">
        
        <h2 class="h3 fw-semibold mb-3">Sistem Kelola & Manajerial Produk TWFood Jember</h2>
        <p class="lead opacity-75 mb-4" style="font-size: 1rem;">
          Akses khusus administrator untuk mengelola pesanan, stok varian, daftar harga, dan katalog produk olahan sehat berbasis tanaman (*plant-based*).
        </p>
      </div>

      <!-- Sisi Kanan: Form Login Card -->
      <div class="col-12 col-md-6 col-xl-5">
        <div class="card border-0 rounded-4 shadow-lg">
          <div class="card-body p-4 p-md-5">
            
            <div class="mb-4 text-center text-md-start">
              <h3 class="fw-bold text-dark mb-1">Masuk Admin</h3>
              <p class="text-muted small">Silakan masukkan akun kredensial Anda</p>
            </div>

            <!-- Pesan Error jika login gagal -->
            @if ($errors->any())
              <div class="alert alert-danger mb-4 py-2 small" role="alert">
                {{ $errors->first() }}
              </div>
            @endif

            <form action="{{ route('admin.login.process') }}" method="POST">
              @csrf
              <div class="row gy-3">
                
                <!-- Input Email -->
                <div class="col-12">
                  <div class="form-floating">
                    <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" placeholder="admin@twfood.com" required>
                    <label for="email" class="text-secondary">Email Admin</label>
                  </div>
                </div>

                <!-- Input Password -->
                <div class="col-12">
                  <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    <label for="password" class="text-secondary">Password</label>
                  </div>
                </div>

                <!-- Remember Me -->
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                    <label class="form-check-label text-secondary small" for="remember_me">
                      Ingat Saya
                    </label>
                  </div>
                </div>

                <!-- Tombol Submit -->
                <div class="col-12">
                  <div class="d-grid mt-2">
                    <button class="btn btn-tw-brown btn-lg py-3 fw-bold fs-6 rounded-3" type="submit">Login Sekarang</button>
                  </div>
                </div>

              </div>
            </form>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

</body>
</html>