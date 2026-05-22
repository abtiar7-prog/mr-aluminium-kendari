<?php
// ============================================================
// CV MR ALUMINIUM KENDARI - CONFIGURATION
// ============================================================

// Database Configuration - Sesuaikan dengan setting Laragon Anda
// Default Laragon: host=localhost, user=root, password=(kosong)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Kosong untuk Laragon default, atau 'root' jika ada password
define('DB_NAME', 'mr_aluminium_kendari');

// Site Configuration
define('SITE_NAME', 'CV MR Aluminium Kendari');
define('SITE_TAGLINE', 'KONTRAKTOR INTERIOR & EKSTERIOR');
define('SITE_DESCRIPTION', 'Spesialis Aluminium, Besi & Stainless Steel');
define('SITE_URL', 'http://localhost/mr_aluminium_kendari');
define('ADMIN_URL', SITE_URL . '/admin');

// Contact Information
define('COMPANY_ADDRESS', 'Jl. Brigjen Katamso No.19 | Kel. Konda, Kec. Konda | Kota Kendari-Sulawesi Tenggara');
define('COMPANY_PHONE', '0852-5555-5686');
define('COMPANY_EMAIL', 'mr.aluminiumkendari@gmail.com');
define('COMPANY_WHATSAPP', '6285255555686');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Admin Credentials (Default - BISA diubah di admin panel setelah login)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'mradmin2026');

// Session Configuration - Pastikan session belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting - Tampilkan error untuk debugging (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Makassar');

// ============================================================
// DATABASE CONNECTION
// ============================================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Coba buat database otomatis jika belum ada
            try {
                $pdo_temp = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
                $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Koneksi ulang setelah database dibuat
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e2) {
                die("<div style='padding:20px;font-family:Arial;background:#fee2e2;color:#dc2626;border-radius:8px;'>
                    <h3>Gagal Koneksi Database</h3>
                    <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <hr style='margin:15px 0;border-color:#fca5a5;'>
                    <p><strong>Solusi:</strong></p>
                    <ol style='line-height:1.8;'>
                        <li>Buka <strong>phpMyAdmin</strong> (klik 'Database' di Laragon)</li>
                        <li>Buat database baru dengan nama: <code>mr_aluminium_kendari</code></li>
                        <li>Import file <code>database.sql</code> ke database tersebut</li>
                        <li>Pastikan config.php sudah benar:<br>
                            &nbsp;&nbsp;- DB_HOST: localhost<br>
                            &nbsp;&nbsp;- DB_USER: root<br>
                            &nbsp;&nbsp;- DB_PASS: (kosong jika tanpa password)<br>
                            &nbsp;&nbsp;- DB_NAME: mr_aluminium_kendari
                        </li>
                    </ol>
                </div>");
            }
        }
    }
    return $pdo;
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function uploadFile($file, $directory) {
    $targetDir = UPLOAD_DIR . $directory . '/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (!in_array($fileType, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Format file tidak didukung.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB).'];
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'file' => $fileName];
    }

    return ['success' => false, 'message' => 'Gagal mengupload file.'];
}

function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function setSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (Exception $e) {
        return false;
    }
}

function formatDate($date, $format = 'd F Y') {
    $months = [
        'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
        'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
        'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
        'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
    ];
    $formatted = date($format, strtotime($date));
    return strtr($formatted, $months);
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Flash Message
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
