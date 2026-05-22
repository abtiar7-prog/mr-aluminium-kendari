-- ============================================================
-- CV MR ALUMINIUM KENDARI - DATABASE COMPLETE
-- Untuk Laragon (MySQL/MariaDB)
-- ============================================================

-- Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS mr_aluminium_kendari 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Gunakan database
USE mr_aluminium_kendari;

-- ============================================================
-- TABEL SETTINGS
-- ============================================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL ADMIN USERS
-- ============================================================
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    avatar VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL SERVICES (LAYANAN)
-- ============================================================
DROP TABLE IF EXISTS services;
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fa-tools',
    image VARCHAR(255),
    order_num INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL PROJECTS (PROYEK)
-- ============================================================
DROP TABLE IF EXISTS projects;
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    client_name VARCHAR(200),
    location VARCHAR(200),
    completion_date DATE,
    image VARCHAR(255),
    gallery TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL NEWS (BERITA)
-- ============================================================
DROP TABLE IF EXISTS news;
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    slug VARCHAR(300) UNIQUE,
    content TEXT,
    excerpt TEXT,
    image VARCHAR(255),
    author VARCHAR(100),
    views INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL GALLERY
-- ============================================================
DROP TABLE IF EXISTS gallery;
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    category VARCHAR(100),
    image VARCHAR(255) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL TESTIMONIALS
-- ============================================================
DROP TABLE IF EXISTS testimonials;
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    position VARCHAR(200),
    company VARCHAR(200),
    content TEXT,
    rating INT DEFAULT 5,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL MESSAGES (PESAN KONTAK)
-- ============================================================
DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL TEAM (TIM)
-- ============================================================
DROP TABLE IF EXISTS team;
CREATE TABLE team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    position VARCHAR(200),
    description TEXT,
    image VARCHAR(255),
    order_num INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DATA DEFAULT
-- ============================================================

-- Settings
INSERT INTO settings (setting_key, value) VALUES
('site_logo', ''),
('site_banner', ''),
('site_favicon', ''),
('meta_title', 'CV MR Aluminium Kendari - Kontraktor Interior & Eksterior'),
('meta_description', 'Spesialis Aluminium, Besi & Stainless Steel di Kendari, Sulawesi Tenggara'),
('meta_keywords', 'aluminium kendari, kontraktor kendari, interior eksterior, stainless steel, besi kendari, kusen aluminium, partisi aluminium'),
('about_title', 'Tentang Kami'),
('about_content', 'CV MR Aluminium Kendari adalah perusahaan kontraktor terpercaya yang bergerak di bidang interior dan eksterior dengan spesialisasi pada material aluminium, besi, dan stainless steel. Kami telah melayani berbagai proyek di Kendari dan sekitarnya dengan komitmen pada kualitas dan kepuasan pelanggan.

Dengan pengalaman lebih dari 10 tahun, kami siap membantu mewujudkan proyek bangunan Anda dengan hasil terbaik.'),
('about_image', ''),
('company_vision', 'Menjadi kontraktor interior dan eksterior terdepan di Sulawesi Tenggara dengan standar kualitas internasional.'),
('company_mission', 'Memberikan solusi konstruksi terbaik dengan material berkualitas, tenaga ahli profesional, dan pelayanan prima untuk kepuasan pelanggan.'),
('whatsapp_number', '6285255555686'),
('facebook_url', ''),
('instagram_url', ''),
('youtube_url', ''),
('office_hours', 'Senin - Sabtu: 08.00 - 17.00 WITA');

-- Admin Users (Password: mradmin2026)
INSERT INTO admin_users (username, password, name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'mr.aluminiumkendari@gmail.com');

-- Services (Layanan)
INSERT INTO services (title, description, icon, order_num) VALUES
('Kusen Aluminium', 'Pembuatan dan pemasangan kusen aluminium berkualitas untuk rumah, kantor, dan gedung komersial. Tahan lama dan tampilan modern.', 'fa-door-open', 1),
('Partisi Aluminium', 'Partisi ruangan aluminium untuk kantor, rumah sakit, dan ruang komersial dengan berbagai pilihan desain dan warna.', 'fa-columns', 2),
('Canopy & Atap', 'Pembuatan canopy, atap, dan pelindung dengan material aluminium dan besi yang kuat dan tahan cuaca.', 'fa-umbrella', 3),
('Railing & Tangga', 'Railing tangga, balkon, dan pembatas dengan material besi dan stainless steel yang elegan dan aman.', 'fa-stairs', 4),
('Kitchen Set', 'Kitchen set custom dengan material aluminium dan stainless steel yang hygienis, tahan lama, dan mudah dibersihkan.', 'fa-kitchen-set', 5),
('Pintu & Jendela', 'Pembuatan pintu dan jendela aluminium dengan berbagai model sliding, swing, dan folding.', 'fa-window-maximize', 6),
('Pagar & Gerbang', 'Pembuatan pagar dan gerbang dengan desain custom menggunakan material besi dan stainless steel.', 'fa-border-all', 7),
('Aksesoris Aluminium', 'Berbagai aksesoris dan perlengkapan aluminium untuk kebutuhan interior dan eksterior bangunan.', 'fa-screwdriver-wrench', 8);

-- Team (Tim)
INSERT INTO team (name, position, description, order_num) VALUES
('Tim Profesional MR Aluminium', 'Kontraktor & Tenaga Ahli', 'Tim kami terdiri dari tenaga ahli berpengalaman dalam bidang aluminium, besi, dan stainless steel.', 1);

-- Testimonials (Testimoni)
INSERT INTO testimonials (name, position, company, content, rating) VALUES
('Bapak Ahmad', 'Pemilik Rumah', 'Kendari', 'Pelayanan sangat memuaskan, hasil pekerjaan rapi dan berkualitas. Harga bersaing dan tepat waktu. Sangat recommended!', 5),
('Ibu Siti Rahayu', 'Manager', 'Perusahaan Kendari', 'CV MR Aluminium sangat profesional dalam menangani proyek kami. Hasil melebihi ekspektasi. Terima kasih!', 5),
('Pak Budi Santoso', 'Kontraktor', 'Kendari', 'Sudah beberapa kali bekerja sama, selalu puas dengan hasilnya. Material berkualitas dan pengerjaan rapi.', 5);

-- News (Berita)
INSERT INTO news (title, slug, content, excerpt, author, views, is_active) VALUES
('Tips Memilih Kusen Aluminium Berkualitas', 'tips-memilih-kusen-aluminium-berkualitas', 'Kusen aluminium menjadi pilihan populer untuk rumah modern. Berikut tips memilih kusen aluminium berkualitas:

1. Perhatikan ketebalan aluminium
2. Pilih warna yang sesuai dengan konsep rumah
3. Pastikan ada garansi produk
4. Gunakan jasa pemasangan profesional

Dengan memperhatikan hal-hal di atas, Anda akan mendapatkan kusen aluminium yang tahan lama dan estetis.', 'Panduan lengkap memilih kusen aluminium berkualitas untuk rumah Anda. Pelajari tips penting sebelum membeli.', 'Admin', 25, 1),
('Keunggulan Stainless Steel untuk Kitchen Set', 'keunggulan-stainless-steel-untuk-kitchen-set', 'Stainless steel menjadi material favorit untuk kitchen set modern. Berikut keunggulannya:

- Tahan karat dan korosi
- Mudah dibersihkan
- Hygienis dan aman untuk makanan
- Tahan lama dan kuat
- Tampilan modern dan elegan

CV MR Aluminium menyediakan kitchen set stainless steel custom sesuai kebutuhan Anda.', 'Kenapa stainless steel jadi pilihan terbaik untuk kitchen set? Simak keunggulannya di sini.', 'Admin', 18, 1),
('Proyek Baru: Pemasangan Partisi Kantor', 'proyek-baru-pemasangan-partisi-kantor', 'Kami baru saja menyelesaikan proyek pemasangan partisi aluminium untuk kantor di area Kendari.

Spesifikasi proyek:
- Partisi aluminium frame
- Kaca tempered 8mm
- Sistem sliding door
- Finishing powder coating

Proyek selesai tepat waktu dengan hasil memuaskan. Terima kasih atas kepercayaannya!', 'Update proyek terbaru kami: pemasangan partisi aluminium untuk kantor di Kendari.', 'Admin', 32, 1);

-- Gallery (Galeri - placeholder)
INSERT INTO gallery (title, category, image, description, is_active) VALUES
('Kusen Aluminium Modern', 'Aluminium', 'placeholder1.jpg', 'Hasil pemasangan kusen aluminium dengan desain modern', 1),
('Kitchen Set Stainless', 'Stainless', 'placeholder2.jpg', 'Kitchen set custom dengan material stainless steel', 1),
('Partisi Ruangan', 'Interior', 'placeholder3.jpg', 'Partisi aluminium untuk ruang kantor', 1),
('Canopy Rumah', 'Eksterior', 'placeholder4.jpg', 'Canopy aluminium untuk pelindung teras rumah', 1);

-- Projects (Proyek - placeholder)
INSERT INTO projects (title, category, description, client_name, location, completion_date, image, is_featured, is_active) VALUES
('Pemasangan Kusen Rumah Tinggal', 'Rumah', 'Pemasangan kusen aluminium untuk rumah tinggal 2 lantai dengan total 20 unit jendela dan 5 unit pintu.', 'Bapak Ahmad', 'Kel. Konda, Kendari', '2025-12-15', 'project1.jpg', 1, 1),
('Kitchen Set Cafe', 'Komersial', 'Pembuatan kitchen set custom untuk cafe dengan material stainless steel dan aluminium.', 'Cafe Nusantara', 'Jl. Sudirman, Kendari', '2025-11-20', 'project2.jpg', 1, 1),
('Partisi Kantor BUMN', 'Komersial', 'Pemasangan partisi aluminium dan kaca untuk ruang kantor dengan sistem sliding door.', 'PT. Pelindo', 'Kendari', '2025-10-10', 'project3.jpg', 1, 1),
('Railing Tangga Minimalis', 'Interior', 'Pembuatan railing tangga dengan material besi hollow dan cat powder coating.', 'Ibu Siti', 'Poasia, Kendari', '2025-09-05', 'project4.jpg', 0, 1);

-- Messages (Pesan contoh)
INSERT INTO messages (name, email, phone, subject, message, is_read) VALUES
('Andi Wijaya', 'andi@email.com', '081234567890', 'Tanya Harga Kusen', 'Selamat pagi, saya ingin menanyakan harga kusen aluminium untuk rumah saya. Butuh sekitar 10 jendela dan 3 pintu. Terima kasih.', 0),
('Rina Susanti', 'rina@email.com', '082345678901', 'Konsultasi Kitchen Set', 'Halo, saya ingin konsultasi tentang kitchen set untuk rumah baru saya. Apakah bisa datang ke lokasi untuk survey?', 0);

-- ============================================================
-- SELESAI
-- ============================================================
