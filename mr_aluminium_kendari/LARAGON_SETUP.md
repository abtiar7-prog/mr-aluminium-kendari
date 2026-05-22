# 🚀 Panduan Setup di Laragon

## Langkah 1: Extract File
1. Extract `mr_aluminium_kendari.zip` ke folder:
   ```
   C:\laragon\www\mr_aluminium_kendari\
   ```

## Langkah 2: Buat Database
1. Klik tombol **"Database"** di Laragon (atau buka phpMyAdmin)
2. Buka browser ke: `http://localhost/phpmyadmin`
3. Klik **"New"** (database baru)
4. Ketik nama: `mr_aluminium_kendari`
5. Klik **"Create"**
6. Pilih database `mr_aluminium_kendari`
7. Klik tab **"Import"**
8. Klik **"Choose File"** → pilih file `database.sql`
9. Klik **"Go"** (di bawah)

## Langkah 3: Cek Koneksi Database
Buka file: `includes/config.php`

**Default Laragon (biasanya tanpa password):**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Kosong = tanpa password
define('DB_NAME', 'mr_aluminium_kendari');
```

**Jika Laragon Anda pakai password root:**
```php
define('DB_PASS', 'root');    // atau password yang Anda setting
```

## Langkah 4: Akses Website
- **Website:** `http://localhost/mr_aluminium_kendari`
- **Admin:** `http://localhost/mr_aluminium_kendari/admin/login.php`
- **phpMyAdmin:** `http://localhost/phpmyadmin`

## Langkah 5: Login Admin
| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `mradmin2026` |

> ⚠️ **PENTING:** Ganti password segera setelah login pertama kali!

## 🔧 Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"
**Solusi:** Password root salah. Coba:
1. Buka Laragon → Klik kanan → MySQL → Change root password
2. Atau edit `config.php` dan sesuaikan `DB_PASS`

### Error: "Unknown database 'mr_aluminium_kendari'"
**Solusi:** Database belum dibuat. Ulangi Langkah 2.

### Error: "Table doesn't exist"
**Solusi:** Import `database.sql` belum berhasil. Ulangi Langkah 2.

### Website blank/putih
**Solusi:**
1. Cek apakah Apache & MySQL running di Laragon
2. Cek error log: `C:\laragon\var\log\apache_error.log`
3. Pastikan PHP extension `pdo_mysql` aktif

## 📁 Struktur Folder di Laragon
```
C:\laragon\www\mr_aluminium_kendari\
├── admin\              # Panel Admin
├── includes\           # Config & functions
├── pages\              # Public pages
├── uploads\            # Uploaded files
├── assets\            # CSS, JS, images
├── index.php          # Homepage
├── database.sql       # Import ke MySQL
└── .htaccess          # Server config
```

## 🌐 URL Akses
| Akses | URL |
|-------|-----|
| Homepage | `http://localhost/mr_aluminium_kendari` |
| Admin Login | `http://localhost/mr_aluminium_kendari/admin/login.php` |
| phpMyAdmin | `http://localhost/phpmyadmin` |

---
Selamat menggunakan! 🎉
