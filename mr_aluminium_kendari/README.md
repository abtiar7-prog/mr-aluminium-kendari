# CV MR Aluminium Kendari - Website Application

## KONTRAKTOR INTERIOR & EKSTERIOR (ALUMINIUM, BESI & STAINLESS)

**Alamat:** Jl. Brigjen Katamso No.19 | Kel. Konda, Kec. Konda | Kota Kendari-Sulawesi Tenggara  
**Telepon:** 0852-5555-5686  
**Email:** mr.aluminiumkendari@gmail.com  

---

## 🚀 Cara Install

### 1. Persyaratan
- PHP 7.4 atau lebih baru
- MySQL/MariaDB
- Web Server (Apache/Nginx)
- mod_rewrite enabled

### 2. Setup Database
1. Buat database baru: `mr_aluminium_kendari`
2. Import file `database.sql` ke database tersebut
3. Sesuaikan koneksi database di `includes/config.php` jika diperlukan

### 3. Konfigurasi
- Default Admin Username: `admin`
- Default Admin Password: `mradmin2026`
- Ubah password segera setelah login pertama kali

### 4. Akses Admin Panel
- Admin panel tersembunyi dan dapat diakses dengan:
  - Klik pojok kanan bawah halaman 3x (triple click pada titik tersembunyi)
  - Atau langsung ke: `http://domain-anda.com/admin/login.php`

---

## 📁 Struktur Folder

```
mr_aluminium_kendari/
├── admin/              # Panel Admin
│   ├── login.php       # Halaman Login
│   ├── dashboard.php   # Dashboard Admin
│   ├── services.php    # Kelola Layanan
│   ├── projects.php    # Kelola Proyek
│   ├── gallery.php     # Kelola Galeri
│   ├── news.php        # Kelola Berita
│   ├── testimonials.php # Kelola Testimoni
│   ├── messages.php    # Kelola Pesan
│   ├── team.php        # Kelola Tim
│   ├── settings.php    # Pengaturan Website
│   └── logout.php      # Logout
├── includes/           # File konfigurasi & fungsi
│   └── config.php      # Konfigurasi utama
├── pages/              # Halaman publik tambahan
│   ├── contact-submit.php  # Handler form kontak
│   └── news-detail.php     # Detail berita
├── uploads/            # Folder upload
│   ├── logo/           # Logo website
│   ├── banner/         # Banner hero
│   ├── gallery/        # Foto galeri & proyek
│   └── news/           # Foto berita
├── assets/             # Asset website
│   ├── css/            # Stylesheet
│   ├── js/             # JavaScript
│   └── images/         # Gambar default
├── index.php           # Halaman utama
├── database.sql        # Struktur database
├── .htaccess           # Konfigurasi server
└── README.md           # Dokumentasi ini
```

---

## ✨ Fitur Website

### Frontend (Publik)
- ✅ Hero section dengan banner dinamis
- ✅ Layanan/produk dengan ikon Font Awesome
- ✅ Tentang Kami (Visi & Misi)
- ✅ Portfolio Proyek dengan filter
- ✅ Galeri Foto dengan lightbox
- ✅ Testimoni Klien (slider)
- ✅ Berita/Artikel
- ✅ Form Kontak dengan notifikasi
- ✅ WhatsApp Floating Button
- ✅ Peta Lokasi (Google Maps)
- ✅ Responsive Design (Mobile Friendly)
- ✅ Animasi AOS (Animate On Scroll)
- ✅ SEO Meta Tags

### Backend (Admin)
- ✅ Login dengan session security
- ✅ Dashboard dengan statistik
- ✅ Upload Logo Website
- ✅ Upload Banner Hero
- ✅ Upload Foto Galeri
- ✅ Kelola Berita/Artikel
- ✅ Kelola Proyek Portfolio
- ✅ Kelola Layanan
- ✅ Kelola Testimoni
- ✅ Kelola Tim
- ✅ Kelola Pesan Kontak
- ✅ Pengaturan SEO (Meta Tags)
- ✅ Pengaturan Media Sosial
- ✅ Ganti Password Admin
- ✅ Flash Messages (Toast Notifications)

---

## 🔐 Keamanan
- Password di-hash dengan bcrypt
- Session-based authentication
- SQL Injection prevention (Prepared Statements)
- XSS protection (htmlspecialchars)
- CSRF protection ready
- File upload validation (type & size)
- Hidden admin access point

---

## 📱 Teknologi
- **Backend:** PHP 7.4+ (Native, no framework)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Icons:** Font Awesome 6.5.1
- **Fonts:** Google Fonts (Poppins, Montserrat)
- **Animation:** AOS (Animate On Scroll)
- **Slider:** Swiper.js
- **No jQuery dependency**

---

## 📝 Lisensi
&copy; 2026 CV MR Aluminium Kendari. All Rights Reserved.

---

Dibuat dengan ❤️ untuk kontraktor aluminium terbaik di Kendari, Sulawesi Tenggara.
