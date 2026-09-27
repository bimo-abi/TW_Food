# PROGRES PENGEMBANGAN TWFOOD

**Project:** TWFOOD — Sistem Informasi Penjualan dan Manajemen UMKM Berbasis Mobile dan Web  
**PRD Baseline:** Versi 1.0 — 27 September 2026  
**Backend:** Laravel 13  
**Database:** MySQL 8.4  
**Mobile:** Flutter + Dart 3.13.1  
**State Management:** Riverpod (akan digunakan pada pengembangan Flutter berikutnya)  
**Web:** Laravel Blade + Bootstrap  
**Admin:** Tabler + Bootstrap  
**Authentication API:** Laravel Sanctum  
**Payment:** Midtrans  
**API Testing:** Postman  
**Development:** VS Code + Laragon  
**Version Control:** Git + GitHub  

> Dokumen ini menjadi catatan progres kerja terakhir. Acuan requirement adalah PRD TWFOOD versi 1.0. PRD menetapkan satu backend Laravel, satu database MySQL, Public Website, satu aplikasi Flutter dengan role-based UI untuk Pelanggan/Mitra, dan Admin Website.

---

## 1. ATURAN PENGEMBANGAN YANG DIPEGANG

Pengembangan dilakukan dengan pola:

```text
Tentukan tujuan fitur
        ↓
Tentukan aktor
        ↓
Tentukan platform
        ↓
Tentukan flow
        ↓
Tentukan data/tabel
        ↓
Tentukan API
        ↓
Tentukan validasi
        ↓
Tentukan authorization
        ↓
Implementasi
        ↓
Test normal + negative case
        ↓
Perbaiki
        ↓
Retest
        ↓
Done
        ↓
Lanjut ke step berikutnya
```

Catatan penting:
- Tidak melompat ke step berikutnya sebelum step aktif selesai dan teruji.
- Backend Laravel menjadi sumber kebenaran.
- Flutter hanya client; validasi di Flutter membantu UX, sedangkan validasi final tetap dilakukan backend.
- Untuk operasi multi-tabel digunakan transaction jika diperlukan.
- Testing mencakup normal flow dan negative testing.

---

# 2. RINGKASAN STATUS FASE UTAMA

| Fase | Cakupan | Status |
|---|---|---|
| **Fase 1** | Fondasi Database, Laravel, authentication, API routing, Postman, struktur public/admin | ✅ Selesai |
| **Fase 2** | Public Website | ✅ Selesai |
| **Fase 3** | Flutter Pelanggan | 🟡 Sedang berjalan |
| **Fase 4** | Admin Website | ⏳ Akan dilanjutkan/dituntaskan |
| **Fase 5** | Flutter Mitra | ⏳ Belum |
| **Fase 6** | Retur | ⏳ Belum sebagai fase utama |
| **Fase 7** | Bisnis Lanjutan | ⏳ Belum |

**Posisi terakhir:** Fase 3 — Flutter Pelanggan, pada rangkaian Register.  
**Step terakhir yang sudah selesai:** Step 3.3.6.4.  
**Step yang akan dilanjutkan saat kembali:** Step 3.3.6.5.

---

# 3. ARSITEKTUR SISTEM YANG DISEPAKATI

```text
                         TWFOOD
                            │
                       Laravel 13
                            │
                       MySQL Database
                            │
       ┌────────────────────┼────────────────────┐
       │                    │                    │
       ↓                    ↓                    ↓
Public Website       Flutter Mobile       Admin Website
                         │
                   ┌─────┴─────┐
                   ↓           ↓
               Pelanggan      Mitra
```

## Aturan Public Website vs Flutter Pelanggan

Public Website dan Flutter Pelanggan menggunakan sumber data/backend yang sama dan memiliki fitur informasi publik yang sejalan.

Public Website berisi:
- Home
- Produk
- Detail Produk
- Resep
- Outlet
- Kontak
- Tentang TWFOOD
- Download Aplikasi

Public Website **tidak** menyediakan:
- Keranjang
- Checkout
- Pembayaran
- Pesanan
- Retur

Flutter Pelanggan menyediakan fitur yang lebih lengkap, termasuk fitur transaksi dan akun:
- Register
- Login
- Home
- Produk
- Detail Produk
- Profil
- Alamat
- Keranjang
- Checkout
- Pembayaran
- Pesanan
- Tracking
- Retur
- Notifikasi

Catatan arsitektur: jangan membuat backend terpisah untuk Public Website dan Flutter. Keduanya menggunakan satu backend Laravel dan satu database.

---

# 4. FASE 1 — FONDASI

## 4.1 Review requirement dan database

Status: ✅ Selesai

Yang sudah dilakukan:
- PRD direview dan dijadikan baseline requirement.
- Struktur database direview dan disesuaikan dengan kebutuhan sistem.
- Migration Laravel diperbarui.
- Seeder diverifikasi.
- Model dan relationship direview.
- `migrate:fresh` dan `db:seed` berhasil.

## 4.2 Database baseline

Struktur database yang sudah dibangun mencakup tabel utama:

1. `pengguna`
2. `alamat`
3. `profil_bisnis`
4. `dokumen_mitra`
5. `produk`
6. `varian_produk`
7. `daftar_harga`
8. `keranjang`
9. `detail_keranjang`
10. `pesanan`
11. `detail_pesanan`
12. `pembayaran`
13. `retur`
14. `bukti_retur`
15. `detail_retur`
16. `transaksi_keuangan`
17. `promosi`
18. `promosi_varian`
19. `resep`
20. `resep_produk`
21. `konten_beranda`
22. `outlet`
23. `notifikasi`
24. `pengaturan_mitra`
25. `jam_operasional_outlet`

Tambahan yang sudah diterapkan:
- `dokumen_mitra` untuk dokumen verifikasi Mitra.
- `bukti_retur` untuk mendukung multiple foto/video retur.
- Field lama `retur.bukti_retur` tidak digunakan lagi.
- `pembayaran.id_transaksi` dibuat unique.
- Relationship `Return → BuktiRetur` menggunakan `hasMany`.
- Relationship jam operasional outlet diperbaiki.
- `diterima_pada` dan timezone sudah diuji.

## 4.3 Authentication Web

Status: ✅ Selesai

Yang sudah dibuat/diuji:
- `Pengguna` menggunakan `Authenticatable`.
- `getAuthPassword()` menggunakan `kata_sandi`.
- Auth provider diarahkan ke model `Pengguna`.
- Login web menggunakan email + password.
- Status akun aktif dicek saat login.
- Session diregenerate setelah login.
- Logout menghapus session dan token session.
- `AdminMiddleware` dibuat.
- Alias middleware `admin` didaftarkan di `bootstrap/app.php`.
- Mitra dapat masuk ke Admin Website.
- Pelanggan ditolak dengan HTTP 403.
- Guest diarahkan ke login.

## 4.4 API Infrastructure

Status: ✅ Selesai untuk fondasi

Sudah tersedia:
- `routes/api.php`
- `/api/test`
- API route untuk authentication.
- Skeleton endpoint retur.

Endpoint yang sekarang terdaftar:

```text
GET|HEAD   /api/test
POST       /api/register
POST       /api/login
POST       /api/logout
GET|HEAD   /api/me
POST       /api/pesanan/{id_pesanan}/retur
```

Catatan: endpoint retur masih berupa skeleton dan belum menjadi fitur retur final. Retur adalah Fase 6.

---

# 5. FASE 1.6 — LARAVEL SANCTUM

Status: ✅ Selesai

## Yang sudah dikerjakan

1. Laravel Sanctum dicek dan awalnya belum terpasang.
2. Sanctum dipasang:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
```

3. Migration dijalankan:

```bash
php artisan migrate
```

4. Tabel `personal_access_tokens` diverifikasi.
5. `Pengguna` menggunakan:

```php
use Laravel\Sanctum\HasApiTokens;
```

6. `createToken()` dan `tokens()` diverifikasi.
7. `AuthController` dibuat.
8. Register API dibuat.
9. Login API dibuat.
10. Logout API dibuat dengan `auth:sanctum`.
11. Endpoint `/api/me` dibuat dengan `auth:sanctum`.
12. Token berhasil dibuat saat login.
13. Token digunakan sebagai Bearer Token.
14. Logout mencabut token aktif.
15. Token yang sudah dicabut tidak dapat digunakan kembali.
16. Akses tanpa token ditolak.
17. Register duplicate email ditolak.
18. Route list diverifikasi dengan `php artisan route:list --path=api`.
19. `php artisan optimize:clear` berhasil.

## Response Register

Register pelanggan Umum sudah dapat membuat user dengan:

```text
peran = pelanggan
jenis_pelanggan = umum
status_aktif = true
```

## Register Toko/Horeca

Backend sudah disesuaikan agar:
- `jenis_pelanggan` menerima `umum`, `toko`, `horeca`.
- Toko/Horeca membutuhkan `profil_bisnis`.
- `peran` tetap ditentukan backend sebagai `pelanggan`.
- Penyimpanan `pengguna` + `profil_bisnis` menggunakan transaction.

---

# 6. FASE 2 — PUBLIC WEBSITE

Status: ✅ Selesai berdasarkan progres proyek sebelum masuk Fase 3

Bagian Public Website yang sudah dikerjakan:
- Home
- Produk
- Detail Produk
- Resep
- Outlet
- Kontak
- Download Aplikasi
- Tentang TWFOOD
- Redirect root/public flow sudah disesuaikan.
- Halaman resep sudah tersedia dan terhubung ke navigasi.
- Outlet menggunakan data `outlet` dan `jam_operasional_outlet`.

Aturan penting:
- Public Website hanya informasional.
- Tidak ada keranjang/checkout/pembayaran/pesanan/retur di Public Website.

---

# 7. FASE 3 — FLUTTER PELANGGAN

Status: 🟡 Sedang berjalan

PRD menempatkan Fase 3 untuk membangun Flutter Pelanggan: register, login, produk, profil, alamat, keranjang, checkout, pembayaran, pesanan, dan tracking.

## 7.1 Persiapan Project Flutter

Project Flutter resmi TWFOOD:

```text
C:\Users\bimbi\StudioProjects\tw_food
```

Jangan menggunakan project latihan `flutter_application_1` untuk project utama TWFOOD.

### Environment

Sudah diverifikasi:

```text
Flutter 3.47.1
Dart 3.13.1
```

### Device

HP Android fisik sudah terdeteksi melalui ADB:

```text
7T7X8DD6LRQS9T6T
Android 16 (API 36)
android-arm64
```

USB Debugging aktif dan status ADB:

```text
7T7X8DD6LRQS9T6T    device
```

Tidak menggunakan emulator untuk workflow utama.

---

# 8. FASE 3 — STEP 3.1
## Persiapan Flutter Pelanggan

Status: ✅ Selesai

Yang sudah dilakukan:

### Step 3.1.1
- Flutter version diverifikasi.
- `flutter doctor` diverifikasi.
- Project Flutter resmi ditentukan.

### Step 3.1.2
```bash
flutter pub get
```
berhasil dengan:

```text
Got dependencies!
```

### Step 3.1.3
```bash
flutter devices
```
berhasil mendeteksi HP.

### Step 3.1.4
```bash
flutter run -d 7T7X8DD6LRQS9T6T
```
berhasil.

Flutter Demo Home Page tampil di HP.

---

# 9. FASE 3 — STEP 3.2
## Koneksi Flutter → Laravel API

Status: ✅ Selesai

### Step 3.2.1 — ADB

```bash
adb devices
```
berhasil:

```text
7T7X8DD6LRQS9T6T    device
```

### Step 3.2.2 — ADB Reverse

```bash
adb reverse tcp:8000 tcp:8000
```
berhasil.

### Step 3.2.3 — Laravel

Laravel dijalankan dengan:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

berhasil.

### Step 3.2.4 — API dari HP

Endpoint:

```text
http://127.0.0.1:8000/api/test
```

berhasil dibuka dari HP melalui ADB reverse dan mengembalikan JSON:

```json
{
  "message": "API TWFOOD berhasil terhubung."
}
```

### Step 3.2.5 — HTTP package

Package berhasil ditambahkan:

```bash
flutter pub add http
```

Versi yang terpasang:

```text
http 1.6.0
```

### Step 3.2.6 — API Config

File:

```text
lib/config/api_config.dart
```

Isi inti:

```dart
class ApiConfig {
  static const String baseUrl = 'http://127.0.0.1:8000/api';
}
```

### Step 3.2.7 — ApiService

File:

```text
lib/services/api_service.dart
```

Sudah dibuat dengan method:
- `get()`
- `post()`

`post()` menggunakan header JSON dan `jsonEncode()`.

### Step 3.2.8 — GET Test

Flutter berhasil memanggil:

```text
GET /api/test
```

dan menerima response Laravel di HP.

Kesimpulan: jalur Flutter → HTTP → ADB reverse → Laravel API sudah terbukti bekerja.

---

# 10. FASE 3 — STEP 3.3
## REGISTER PELANGGAN

Status: 🟡 Sedang berjalan

## Step 3.3.1 — Struktur & Flow Register

Status: ✅ Selesai

Flow register yang disepakati:

```text
Register
   ↓
Data dasar
   ↓
Pilih jenis pelanggan
   ├── Umum
   ├── Toko
   └── Horeca
```

Untuk Toko/Horeca, form akan dilengkapi profil bisnis.

## Step 3.3.2 — Model User

Status: ✅ Selesai

File:

```text
lib/models/user.dart
```

Model mencakup:

```text
idPengguna
nama
email
nomorTelepon
peran
jenisPelanggan
statusAktif
```

Sudah diuji dengan:

```bash
flutter analyze
```

Hasil:

```text
No issues found!
```

## Step 3.3.3 — UI Register

Status: ✅ Selesai

File:

```text
lib/pages/auth/register_page.dart
```

UI sudah memiliki:
- Nama
- Email
- Nomor Telepon
- Password
- Konfirmasi Password
- Dropdown Umum/Toko/Horeca
- Tombol Daftar
- Show/hide password

## Step 3.3.4 — Validasi Form

Status: ✅ Selesai dan diuji

Validasi Flutter yang sudah dibuat:
- Nama wajib.
- Email wajib.
- Format email.
- Nomor telepon wajib.
- Password wajib.
- Password minimal 8 karakter.
- Konfirmasi password harus sama.
- Jenis pelanggan wajib dipilih.

Normal dan negative test berhasil.

## Step 3.3.5 — Penyesuaian Register API Laravel

Status: ✅ Selesai dan diuji melalui Postman

Backend sekarang:
- menerima `jenis_pelanggan`.
- menerima `umum`, `toko`, `horeca`.
- mewajibkan `profil_bisnis` untuk Toko/Horeca.
- tetap menetapkan `peran = pelanggan` dari backend.
- menggunakan transaction untuk `pengguna` + `profil_bisnis`.

Normal dan negative test Postman sudah selesai.

## Step 3.3.6.1 — POST pada ApiService

Status: ✅ Selesai

`ApiService.post()` sudah dibuat dan analyzer aman.

## Step 3.3.6.2 — AuthService

Status: ✅ Selesai

File:

```text
lib/services/auth_service.dart
```

Sudah memiliki method:

```text
register()
```

yang mengirim data register ke:

```text
POST /api/register
```

Analyzer sudah aman.

## Step 3.3.6.3 — Hubungkan Register Page ke AuthService

Status: ✅ Selesai

Tombol `DAFTAR` sudah memanggil `AuthService.register()`.

Aturan sementara:
- Umum dapat dikirim melalui Flutter.
- Toko/Horeca ditahan sementara karena form profil bisnis Flutter belum dibuat.

Analyzer sudah aman.

## Step 3.3.6.4 — Test Register Umum Flutter → Laravel

Status: ✅ Selesai dan teruji

Yang berhasil:
- Flutter mengirim register.
- Laravel menerima request.
- Data tersimpan ke MySQL.
- `peran = pelanggan`.
- `jenis_pelanggan = umum`.
- Password tersimpan dalam bentuk hash.
- Duplicate email ditolak oleh backend.

---

# 11. STEP AKTIF SAAT TERAKHIR BERHENTI

## Step 3.3.6.5 — Menampilkan Error API di Flutter

Status: 🟠 **Belum dites / dilanjutkan saat kembali**

Kode sudah direncanakan untuk menambahkan:

```dart
import 'dart:convert';
```

dan helper:

```dart
String _getApiErrorMessage(String responseBody)
```

Tujuannya agar response Laravel seperti:

```json
{
  "message": "Data registrasi tidak valid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

tidak hanya ditampilkan sebagai:

```text
Status: 422
```

tetapi pesan error yang lebih informatif dapat ditampilkan di Flutter.

### Langkah berikutnya saat kembali

1. Terapkan perubahan `register_page.dart` untuk membaca `response.body`.
2. Jalankan:

```bash
flutter analyze
```

3. Jika analyzer aman, jalankan aplikasi di HP.
4. Test register normal.
5. Test duplicate email.
6. Pastikan pesan dari Laravel tampil di Flutter.

**Jangan langsung melompat ke Toko/Horeca sebelum Step 3.3.6.5 selesai dan dites.**

---

# 12. RENCANA SETELAH REGISTER SELESAI

Setelah seluruh Register Pelanggan selesai:

## Step 3.4 — Login Pelanggan

Rencana teknis:

```text
Login Page
   ↓
AuthService.login()
   ↓
POST /api/login
   ↓
Laravel
   ↓
Sanctum token
   ↓
Flutter menerima token
```

Kemudian token akan dipakai untuk endpoint yang membutuhkan authentication.

## Step 3.5 — Token & `/api/me`

Yang akan dilakukan di Flutter:
- menyimpan token dengan mekanisme yang sesuai.
- mengirim Bearer Token.
- mengambil profil melalui `/api/me`.
- menangani token tidak valid/expired atau revoked sesuai response backend.

## Step 3.6 — Home Flutter

Mengikuti informasi yang tersedia pada Public Website agar data dan pengalaman informasional tetap konsisten.

## Step 3.7 — Produk

- daftar produk.
- detail produk.
- varian.
- jenis harga sesuai jenis pelanggan.

## Step 3.8 — Profil

Mengikuti data user dan kebutuhan profil pelanggan.

## Step 3.9 — Alamat

Aturan PRD:
- maksimum 3 alamat per pelanggan.
- satu alamat utama.
- duplicate address untuk user yang sama ditolak.

## Step 3.10 dan seterusnya — Modul transaksi

Urutan besar:

```text
Keranjang
   ↓
Checkout
   ↓
Pickup / Delivery
   ↓
GoSend / J&T
   ↓
Pembayaran Midtrans
   ↓
Pesanan
   ↓
Tracking
```

Backend harus menghitung ulang harga, subtotal, total, diskon, quantity, dan aturan pembayaran/DP.

---

# 13. FASE 4 — ADMIN WEBSITE

Status: ⏳ Dituntaskan setelah modul Flutter Pelanggan/fondasi terkait siap.

Cakupan PRD:
- Dashboard
- Verifikasi Mitra
- Produk
- Varian
- Harga
- Stok
- Pre-order
- Pesanan
- Pembayaran
- Retur
- Promosi
- Keuangan
- Laporan
- Analitik
- Resep
- Konten Beranda
- Outlet
- Jam Operasional
- Pengaturan Mitra

Template: Tabler + Bootstrap.

---

# 14. FASE 5 — FLUTTER MITRA

Status: ⏳ Belum

Cakupan:
- Login
- Dashboard
- Pesanan
- Status Pesanan
- Stok
- Pengiriman
- Retur
- Notifikasi

Flutter Mitra akan berada dalam satu aplikasi Flutter yang sama dengan Flutter Pelanggan, tetapi menggunakan role-based UI.

---

# 15. FASE 6 — RETUR

Status: ⏳ Belum sebagai fase utama

Catatan: route skeleton sudah ada, tetapi logika final belum diimplementasikan.

Aturan utama:
- Pelanggan yang mengajukan retur.
- Mitra/Admin hanya memproses.
- User harus terautentikasi.
- Role harus pelanggan.
- Pesanan harus milik pelanggan.
- Status pesanan harus `diterima`.
- `diterima_pada` harus tersedia.
- Pengajuan maksimal 24 jam.
- Item harus berasal dari pesanan.
- Jumlah retur > 0.
- Jumlah kumulatif retur tidak boleh melebihi jumlah pembelian.
- Alasan wajib.
- Foto/video divalidasi tipe, ukuran, jumlah, dan akses.
- Penyimpanan retur/detail atomic.
- Multiple return diperbolehkan selama quantity masih tersisa dan syarat lain terpenuhi.
- Refund dilakukan melalui Midtrans setelah retur disetujui.

---

# 16. FASE 7 — BISNIS LANJUTAN

Status: ⏳ Belum

Cakupan:
- Promosi
- Keuangan
- Laporan
- Analitik
- Resep
- Konten
- Outlet
- Pengaturan
- Kalkulator Ekspor
- Notifikasi

Kalkulator ekspor pada tahap awal hanya melakukan perhitungan dan tidak menyimpan quotation/transaksi.

---

# 17. TESTING YANG HARUS TERUS DIJAGA

Setiap fitur wajib mempertimbangkan:

```text
Normal flow
Invalid input
Unauthorized access
Ownership violation
Quantity violation
Invalid status transition
Invalid courier/tracking
Expired return
Duplicate data
Transaction rollback
```

Negative testing yang ditekankan PRD antara lain:
- quantity negatif/nol.
- quantity melebihi stok.
- retur melebihi pembelian.
- retur kumulatif melebihi pembelian.
- retur setelah 24 jam.
- retur sebelum status `diterima`.
- akses pesanan pelanggan lain.
- pelanggan mengakses endpoint Mitra.
- Mitra mengakses data tidak berwenang.
- GoSend tanpa tracking.
- J&T tanpa resi.
- perubahan kurir tidak sah.
- manipulasi harga/diskon/total dari client.

---

# 18. CHECKPOINT TERAKHIR

## Sudah selesai

```text
FASE 1 ✅
   ├── Database
   ├── Laravel foundation
   ├── Web authentication
   ├── API routing
   ├── Sanctum
   ├── Register API
   ├── Login API
   ├── Logout API
   └── /api/me

FASE 2 ✅
   └── Public Website

FASE 3 🟡
   ├── Step 3.1 ✅
   ├── Step 3.2 ✅
   ├── Step 3.3.1 ✅
   ├── Step 3.3.2 ✅
   ├── Step 3.3.3 ✅
   ├── Step 3.3.4 ✅
   ├── Step 3.3.5 ✅
   ├── Step 3.3.6.1 ✅
   ├── Step 3.3.6.2 ✅
   ├── Step 3.3.6.3 ✅
   ├── Step 3.3.6.4 ✅
   └── Step 3.3.6.5 ⏸️ BERHENTI DI SINI
```

## Saat melanjutkan

```text
LANJUT DARI:
FASE 3 → STEP 3.3.6.5

Jangan mengulang:
- Setup Flutter
- ADB
- ADB reverse
- ApiConfig
- ApiService GET
- Http package
- Model User
- UI dasar Register
- Register API Laravel
- AuthService
- Test Register Umum
```

---

# 19. REFERENSI REQUIREMENT PRD

PRD baseline yang digunakan dalam pengembangan ini:

**TWFOOD — Sistem Informasi Penjualan dan Manajemen UMKM Berbasis Mobile dan Web**  
**Versi 1.0 | Requirement Baseline | 27 September 2026**

Bagian penting yang menjadi acuan:
- Stack dan arsitektur sistem.
- Role Pelanggan/Mitra dan jenis Pelanggan Umum/Toko/Horeca.
- Authentication dan registrasi.
- Public Website tanpa transaksi.
- Flutter Pelanggan.
- Produk, varian, harga, stok.
- Checkout, delivery, pembayaran, tracking.
- Retur dan refund.
- Security, API principles, testing dan negative testing.
- Roadmap Fase 1–7.

---

## STATUS TERAKHIR

**Tanggal checkpoint:** 27 September 2026  
**Posisi:** Fase 3 — Flutter Pelanggan  
**Step terakhir selesai:** 3.3.6.4  
**Step berikutnya:** 3.3.6.5 — menampilkan error API dengan lebih informatif di Flutter  
**Status project:** siap dilanjutkan dari checkpoint ini.
