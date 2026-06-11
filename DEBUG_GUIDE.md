# Error Handler & Debug Tools - MTS Al-Ihsan

## 📝 Pengenalan

Error handler dan debug tools telah diimplementasikan untuk membantu Anda mengidentifikasi masalah dengan mudah.

## 🚀 Cara Menggunakan

### 1. **Error Handler Otomatis**

Error handler akan otomatis menangkap semua error, warning, exception, dan fatal error. Informasi akan dicatat ke dalam file log.

**File:** `config/error-handler.php`

Fitur:
- ✅ Menangkap semua jenis error PHP
- ✅ Mencatat detail lengkap (file, line, message, stack trace)
- ✅ Menampilkan error dalam development mode
- ✅ Menyembunyikan detail di production
- ✅ Logging ke file untuk analisis

### 2. **Mode Development**

Ubah file `.env` untuk mengaktifkan debug mode:

```
APP_ENV=development
APP_DEBUG=true
```

**Dalam mode development:**
- Error ditampilkan di halaman dengan detail lengkap
- Stack trace terlihat
- File path dan line number ditampilkan
- Semua logs tercatat

**Dalam mode production:**
- Error tidak ditampilkan ke user
- Generic error message saja
- Semua detail dicatat di server
- User tidak melihat informasi sensitif

### 3. **Debug Dashboard**

Akses debug dashboard untuk melihat informasi sistem:

```
https://ilham.didzacorp.com/mts-alihsan/debug/
```

Menampilkan:
- PHP version & configuration
- Database information
- Server status
- Installed extensions
- File system permissions
- Error logs

### 4. **Error Logs Viewer**

Lihat semua error yang telah dicatat:

```
https://ilham.didzacorp.com/mts-alihsan/debug/logs.php
```

Fitur:
- 🔄 Real-time refresh
- 🗑️ Clear logs
- 📋 Scroll ke log terbaru
- 💾 Auto-save untuk setiap error

## 📂 Lokasi Log File

Error log disimpan di:
```
/mts-alihsan/logs/error.log
```

Buat folder `logs` jika belum ada (permissions: 755)

## 🔧 Troubleshooting

### Masalah: 500 Internal Server Error

**Solusi:**
1. Buka debug page: `https://ilham.didzacorp.com/mts-alihsan/debug/`
2. Klik "View Error Logs"
3. Lihat detail error terbaru
4. Cek file dan line number yang error

### Masalah: Folder logs tidak ada

**Solusi:**
```bash
mkdir -p /path/to/mts-alihsan/logs
chmod 755 /path/to/mts-alihsan/logs
```

### Masalah: Error tidak ditampilkan

**Solusi:**
1. Pastikan `.env` memiliki `APP_DEBUG=true`
2. Refresh browser (Ctrl+F5)
3. Cek di View Error Logs

## 🛡️ Security

**⚠️ PENTING:**

Pastikan di production (server live):
```
APP_ENV=production
APP_DEBUG=false
```

Debug page hanya accessible jika `APP_DEBUG=true`. Jadi tidak perlu khawatir tentang keamanan di production karena tidak ada yang bisa akses.

## 📊 File Structure

```
mts-alihsan/
├── config/
│   └── error-handler.php          # Error handler utama
├── debug/
│   ├── index.php                  # Debug dashboard
│   └── logs.php                   # Error logs viewer
├── logs/
│   └── error.log                  # Log file
├── .env                           # Environment configuration
└── index.php                      # Main entry point (load error-handler)
```

## 💡 Best Practices

1. **Selalu aktifkan debug di development**
   ```
   APP_DEBUG=true
   ```

2. **Cek logs secara berkala**
   - Buka `/mts-alihsan/debug/logs.php`
   - Lihat error atau warning

3. **Matikan debug di production**
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Setup log rotation** (untuk production)
   - Log file bisa jadi sangat besar
   - Implementasikan log rotation

## 🔍 Error Types yang Ditangani

| Type | Deskripsi |
|------|-----------|
| E_ERROR | Fatal error |
| E_WARNING | Warning |
| E_PARSE | Parse error |
| E_NOTICE | Notice |
| Exception | Any Exception |
| Fatal Error | Shutdown fatal error |

## 📞 Support

Jika masalah tidak teratasi:

1. Buka debug dashboard: `/debug/`
2. Cek info system
3. Lihat error logs
4. Screenshot error message
5. Hubungi administrator dengan informasi tersebut

---

**Status:** ✅ Active
**Version:** 1.0
**Last Updated:** Juni 2026
