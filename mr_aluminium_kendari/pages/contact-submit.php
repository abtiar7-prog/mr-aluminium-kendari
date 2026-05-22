<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Nama, email, dan pesan wajib diisi']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $subject, $message]);

    echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan pesan']);
}
