<?php
require_once '../includes/config.php';

// Jika sudah login, redirect ke dashboard
if (isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$error = '';
$debug_info = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Method 1: Coba login dengan database
    $db_login_success = false;
    try {
        $db = getDB();

        // Cek apakah tabel admin_users ada
        $tables = $db->query("SHOW TABLES LIKE 'admin_users'")->fetchAll();

        if (count($tables) > 0) {
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Cek password dengan password_verify (untuk hash bcrypt)
                if (password_verify($password, $user['password'])) {
                    $db_login_success = true;
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];

                    // Update last login
                    $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")
                       ->execute([$user['id']]);

                    $login_success = true;
                } else {
                    // Coba plain text comparison (untuk backward compatibility)
                    if ($password === $user['password']) {
                        $db_login_success = true;
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_name'] = $user['name'];

                        // Update last login
                        $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")
                           ->execute([$user['id']]);

                        $login_success = true;
                    }
                }
            }
        } else {
            $debug_info .= 'Tabel admin_users tidak ditemukan. ';
        }
    } catch (Exception $e) {
        $debug_info .= 'DB Error: ' . $e->getMessage() . '. ';
    }

    // Method 2: Fallback ke default credentials (jika DB gagal)
    if (!$db_login_success) {
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Administrator';
            $_SESSION['fallback_mode'] = true; // Flag bahwa ini mode fallback
            $login_success = true;
        }
    }

    if ($login_success) {
        redirect(ADMIN_URL . '/dashboard.php');
    } else {
        $error = 'Username atau password salah!';
        if ($debug_info) {
            $error .= ' (Debug: ' . $debug_info . ')';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1a5276 0%, #154360 50%, #1a1a2e 100%);
            position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/></svg>');
            background-size: 80px;
        }
        .login-container {
            position: relative; z-index: 2;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%; max-width: 420px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .login-header { text-align: center; margin-bottom: 35px; }
        .login-header .logo-icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, #1a5276, #2e86c1);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; color: white; font-size: 1.8rem;
            box-shadow: 0 10px 30px rgba(26, 82, 118, 0.3);
        }
        .login-header h2 { color: #1a1a2e; font-size: 1.5rem; margin-bottom: 8px; }
        .login-header p { color: #6c757d; font-size: 0.9rem; }
        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            color: #1a1a2e; font-weight: 500; font-size: 0.9rem;
        }
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: #6c757d;
            transition: all 0.3s;
        }
        .form-group input {
            width: 100%; padding: 14px 16px 14px 45px;
            border: 2px solid #e9ecef; border-radius: 12px;
            font-family: inherit; font-size: 0.95rem;
            transition: all 0.3s; outline: none;
            background: #f8f9fa;
        }
        .form-group input:focus {
            border-color: #1a5276; background: white;
            box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1);
        }
        .password-toggle {
            position: absolute; right: 16px; top: 50%;
            transform: translateY(-50%); cursor: pointer;
            color: #6c757d; font-size: 0.9rem;
        }
        .btn-login {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #1a5276, #2e86c1);
            color: white; border: none; border-radius: 12px;
            font-family: inherit; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(26, 82, 118, 0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(26, 82, 118, 0.4);
        }
        .error-msg {
            background: #fee2e2; color: #dc2626;
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px;
        }
        .info-msg {
            background: #dbeafe; color: #1e40af;
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.85rem;
            display: flex; align-items: center; gap: 10px;
        }
        .success-msg {
            background: #d4edda; color: #155724;
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.9rem;
            display: flex; align-items: center; gap: 10px;
        }
        .back-link {
            text-align: center; margin-top: 25px;
        }
        .back-link a {
            color: #6c757d; text-decoration: none;
            font-size: 0.9rem; display: inline-flex;
            align-items: center; gap: 8px; transition: all 0.3s;
        }
        .back-link a:hover { color: #1a5276; }
        .security-note {
            text-align: center; margin-top: 20px;
            padding-top: 20px; border-top: 1px solid #e9ecef;
            font-size: 0.75rem; color: #adb5bd;
        }
        .security-note i { margin-right: 5px; }
        .default-creds {
            background: #f0f9ff; border: 1px dashed #7dd3fc;
            padding: 15px; border-radius: 10px; margin-bottom: 20px;
            font-size: 0.85rem; color: #0369a1;
        }
        .default-creds strong { display: block; margin-bottom: 5px; }
        .default-creds code {
            background: #e0f2fe; padding: 2px 6px; border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon"><i class="fas fa-shield-alt"></i></div>
            <h2>Admin Panel</h2>
            <p>CV MR Aluminium Kendari</p>
        </div>

        <div class="default-creds">
            <strong><i class="fas fa-key"></i> Default Login:</strong>
            Username: <code>admin</code> | Password: <code>mradmin2026</code>
        </div>

        <?php if ($error): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="Masukkan username" required autofocus value="admin">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required value="mradmin2026">
                    <i class="fas fa-lock" style="left:16px;"></i>
                    <span class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="back-link">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
        </div>

        <div class="security-note">
            <i class="fas fa-lock"></i> Halaman ini dilindungi. Akses hanya untuk admin yang berwenang.
        </div>
    </div>

    <script>
        function togglePassword() {
            const pass = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pass.type === 'password') {
                pass.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pass.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
