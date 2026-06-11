# DOKUMENTASI KEAMANAN MTS AL-IHSAN
## Security Improvements Documentation

Tanggal Update: Juni 2026

---

## RINGKASAN PENINGKATAN KEAMANAN

Aplikasi MTS Al-Ihsan telah ditingkatkan keamanannya dengan implementasi fitur-fitur berikut:

### 1. **URL Masking & Hiding File Extensions** ✓
- Semua URL tidak lagi menampilkan ekstensi `.php`
- URL menjadi lebih user-friendly dan menyembunyikan teknologi yang digunakan
- Contoh perubahan:
  - `/mts-alihsan/modules/absensi/index.php` → `/mts-alihsan/absensi`
  - `/mts-alihsan/login.php` → `/mts-alihsan/login`
  - `/mts-alihsan/logout.php` → `/mts-alihsan/logout`

### 2. **Clean URL Router System** ✓
Sistem routing baru yang menangani URL yang lebih bersih:
- File: `config/router.php`
- Mengkonversi clean URL menjadi request internal
- Mendukung berbagai format URL tanpa mengekspos struktur file

### 3. **Enhanced .htaccess Protection** ✓
Implementasi `.htaccess` yang lebih komprehensif:

**Root .htaccess** (`/mts-alihsan/.htaccess`):
- ✓ Rewrite Engine untuk URL masking
- ✓ Blocking akses langsung ke file PHP kecuali index.php
- ✓ Blocking akses ke folder sensitif (config, includes, vendor)
- ✓ Blocking akses ke .git dan file konfigurasi
- ✓ Preventing directory browsing
- ✓ Security headers (X-Content-Type-Options, X-Frame-Options, dll)

**Sub-directory .htaccess files**:
- `/config/.htaccess` - Blokir akses ke file konfigurasi
- `/includes/.htaccess` - Blokir akses ke file include
- `/uploads/.htaccess` - Blokir eksekusi PHP dan script
- `/modules/.htaccess` - Blokir akses langsung ke file modul

### 4. **URL Helper Functions** ✓
Fungsi-fungsi baru dalam `config/functions.php`:

```php
// Generate clean URL
url($path, $params = [])

// Check if current page
isCurrentPage($path)

// Redirect to clean URL
redirectTo($path, $params = [])

// Generate module URL
moduleUrl($moduleName, $action = '', $params = [])

// Generate form action
formAction($module, $action = 'proses', $params = [])
```

### 5. **Routing System** ✓
File: `config/router.php`
- Class `Router` untuk menangani parsing URI
- Support untuk berbagai format URL:
  - `/dashboard` atau `/` untuk dashboard
  - `/login` untuk login page
  - `/logout` untuk logout
  - `/modules/absensi` atau `/absensi` untuk module
  - `/absensi/laporan` untuk sub-halaman module

### 6. **Security Headers** ✓
Implementasi security headers di semua response:
- `X-Content-Type-Options: nosniff` - Cegah MIME type sniffing
- `X-Frame-Options: SAMEORIGIN` - Cegah clickjacking
- `X-XSS-Protection: 1; mode=block` - Enable XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin` - Control referrer info

### 7. **Protected Directories** ✓
Direktori-direktori sensitif dilindungi dari akses langsung:
- `/config/` - File konfigurasi dan database
- `/includes/` - File-file include
- `/uploads/` - File yang diupload
- `/vendor/` - Package Composer
- `/.git/` - Git repository

### 8. **Module Access Control** ✓
- Module files hanya dapat diakses melalui router utama
- Direct access ke module files diblokir
- Setiap request harus melalui index.php

---

## IMPLEMENTASI DAN PENGGUNAAN

### Menggunakan URL Helper Functions

**Sebelumnya (tidak aman, ekstensi terbuka):**
```php
<a href="<?= BASE_URL ?>modules/absensi/index.php">Absensi</a>
<a href="<?= BASE_URL ?>modules/kelas/laporan.php">Laporan</a>
<a href="<?= BASE_URL ?>login.php">Login</a>
```

**Sekarang (aman, clean URL):**
```php
<a href="<?= moduleUrl('absensi') ?>">Absensi</a>
<a href="<?= moduleUrl('kelas', 'laporan') ?>">Laporan</a>
<a href="<?= url('login') ?>">Login</a>
```

### Redirect ke URL Bersih

**Sebelumnya:**
```php
header('Location: ' . BASE_URL);
```

**Sekarang:**
```php
redirectTo('dashboard');
redirectTo('login');
redirectTo('absensi', ['kelas_id' => 5]);
```

### Sidebar yang sudah diperbarui

File `includes/sidebar.php` telah diperbarui menggunakan fungsi URL helper:
- `url('dashboard')` - URL dashboard
- `moduleUrl('absensi')` - URL modul absensi
- `moduleUrl('kelas', 'laporan')` - URL laporan kelas
- `url('logout')` - URL logout

---

## STRUKTUR URL BARU

| Halaman | URL Lama | URL Baru |
|---------|----------|----------|
| Dashboard | `/mts-alihsan/index.php` | `/mts-alihsan/` atau `/mts-alihsan/dashboard` |
| Login | `/mts-alihsan/login.php` | `/mts-alihsan/login` |
| Logout | `/mts-alihsan/logout.php` | `/mts-alihsan/logout` |
| Absensi | `/mts-alihsan/modules/absensi/index.php` | `/mts-alihsan/absensi` |
| Absensi Bulanan | `/mts-alihsan/modules/absensi/absensi_bulanan.php` | `/mts-alihsan/absensi/absensi_bulanan` |
| Kelas | `/mts-alihsan/modules/kelas/index.php` | `/mts-alihsan/kelas` |
| Siswa | `/mts-alihsan/modules/siswa/index.php` | `/mts-alihsan/siswa` |
| Pelanggaran | `/mts-alihsan/modules/pelanggaran/index.php` | `/mts-alihsan/pelanggaran` |
| Izin | `/mts-alihsan/modules/izin/index.php` | `/mts-alihsan/izin` |

---

## CHECKLIST IMPLEMENTASI

- [x] Membuat router system di `config/router.php`
- [x] Menambahkan URL helper functions di `config/functions.php`
- [x] Update main `index.php` dengan routing logic
- [x] Update `.htaccess` dengan comprehensive rewrite rules
- [x] Membuat `.htaccess` untuk `/config` directory
- [x] Membuat `.htaccess` untuk `/includes` directory
- [x] Membuat `.htaccess` untuk `/uploads` directory
- [x] Membuat `.htaccess` untuk `/modules` directory
- [x] Update `login.php` dengan redirect helper
- [x] Update `includes/sidebar.php` dengan URL helpers
- [x] Implementasi security headers di .htaccess

---

## PERINGATAN & CATATAN PENTING

⚠️ **Important Notes:**

1. **Pastikan Apache mod_rewrite diaktifkan**
   - .htaccess memerlukan `mod_rewrite` module
   - Contact hosting provider jika tidak berfungsi

2. **Test semua links setelah implementasi**
   - Pastikan tidak ada broken links
   - Check browser console untuk error

3. **Form Actions**
   - Form action masih dapat menggunakan path langsung
   - Contoh: `action="modules/absensi/proses.php"`
   - .htaccess akan mengarahkan ke router

4. **AJAX Requests**
   - Update AJAX endpoints jika ada
   - Gunakan URL helper untuk consistency

5. **Cache Clearing**
   - Clear browser cache setelah update
   - Clear server cache jika ada

---

## TESTING CHECKLIST

- [ ] Akses dashboard tanpa error
- [ ] Login berfungsi dengan URL bersih
- [ ] Semua sidebar links berfungsi
- [ ] Module pages dapat diakses
- [ ] Form submissions bekerja
- [ ] File uploads berfungsi
- [ ] Logout berfungsi
- [ ] Session management berfungsi
- [ ] 404 errors handled properly
- [ ] Browser console clear dari errors

---

## DUKUNGAN & TROUBLESHOOTING

### Jika ada broken links atau 404:

1. Cek apakah `.htaccess` sudah tersimpan dengan benar
2. Pastikan Apache `mod_rewrite` enabled
3. Clear browser cache (Ctrl+F5)
4. Check `config/router.php` untuk route logic
5. Verify module folders ada di `/modules/`

### Debug Mode

Untuk debugging, dapat menambahkan logging di `config/router.php`:
```php
error_log("Route: " . $this->getUri());
error_log("Module: " . ($this->getModule() ?? 'none'));
error_log("Page: " . $this->getPage());
```

---

## Kontribusi & Update

Dokumentasi ini akan diupdate seiring dengan perubahan keamanan lebih lanjut.

**Informasi Lebih Lanjut:**
- Documentation: `/mts-alihsan/SECURITY.md`
- Router: `/mts-alihsan/config/router.php`
- URL Helpers: `/mts-alihsan/config/functions.php`

---

**Status: Active ✓**
**Last Updated: Juni 2026**
**Version: 1.0**
