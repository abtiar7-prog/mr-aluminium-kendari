<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_logo') {
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['logo'], 'logo');
            if ($result['success']) {
                $oldLogo = getSetting('site_logo');
                if ($oldLogo && file_exists(UPLOAD_DIR . 'logo/' . $oldLogo)) {
                    unlink(UPLOAD_DIR . 'logo/' . $oldLogo);
                }
                setSetting('site_logo', $result['file']);
                setFlash('success', 'Logo berhasil diperbarui!');
            } else {
                setFlash('error', $result['message']);
            }
        }
    }

    if ($action === 'update_banner') {
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['banner'], 'banner');
            if ($result['success']) {
                $oldBanner = getSetting('site_banner');
                if ($oldBanner && file_exists(UPLOAD_DIR . 'banner/' . $oldBanner)) {
                    unlink(UPLOAD_DIR . 'banner/' . $oldBanner);
                }
                setSetting('site_banner', $result['file']);
                setFlash('success', 'Banner berhasil diperbarui!');
            } else {
                setFlash('error', $result['message']);
            }
        }
    }

    if ($action === 'update_about_image') {
        if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['about_image'], 'gallery');
            if ($result['success']) {
                $oldImage = getSetting('about_image');
                if ($oldImage && file_exists(UPLOAD_DIR . 'gallery/' . $oldImage)) {
                    unlink(UPLOAD_DIR . 'gallery/' . $oldImage);
                }
                setSetting('about_image', $result['file']);
                setFlash('success', 'Gambar Tentang Kami berhasil diperbarui!');
            } else {
                setFlash('error', $result['message']);
            }
        } else {
            setFlash('error', 'Gagal mengupload gambar. Pastikan file valid.');
        }
    }

    if ($action === 'update_favicon') {
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['favicon'], 'logo');
            if ($result['success']) {
                setSetting('site_favicon', $result['file']);
                setFlash('success', 'Favicon berhasil diperbarui!');
            } else {
                setFlash('error', $result['message']);
            }
        }
    }

    if ($action === 'update_about') {
        setSetting('about_title', $_POST['about_title'] ?? '');
        setSetting('about_content', $_POST['about_content'] ?? '');
        setSetting('company_vision', $_POST['company_vision'] ?? '');
        setSetting('company_mission', $_POST['company_mission'] ?? '');
        setSetting('office_hours', $_POST['office_hours'] ?? '');
        setFlash('success', 'Informasi perusahaan berhasil diperbarui!');
    }

    if ($action === 'update_seo') {
        setSetting('meta_title', $_POST['meta_title'] ?? '');
        setSetting('meta_description', $_POST['meta_description'] ?? '');
        setSetting('meta_keywords', $_POST['meta_keywords'] ?? '');
        setFlash('success', 'SEO berhasil diperbarui!');
    }

    if ($action === 'update_social') {
        setSetting('facebook_url', $_POST['facebook_url'] ?? '');
        setSetting('instagram_url', $_POST['instagram_url'] ?? '');
        setSetting('youtube_url', $_POST['youtube_url'] ?? '');
        setSetting('whatsapp_number', $_POST['whatsapp_number'] ?? '');
        setFlash('success', 'Media sosial berhasil diperbarui!');
    }

    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            setFlash('error', 'Password baru tidak cocok!');
        } elseif (strlen($newPassword) < 6) {
            setFlash('error', 'Password minimal 6 karakter!');
        } else {
            try {
                $stmt = $db->prepare("SELECT password FROM admin_users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $user = $stmt->fetch();

                if ($user && password_verify($currentPassword, $user['password'])) {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?")
                       ->execute([$hash, $_SESSION['admin_id']]);
                    setFlash('success', 'Password berhasil diubah!');
                } else {
                    setFlash('error', 'Password saat ini salah!');
                }
            } catch (Exception $e) {
                setFlash('error', 'Gagal mengubah password.');
            }
        }
    }

    redirect(ADMIN_URL . '/settings.php');
}

// Get current settings
$settings = [
    'site_logo' => getSetting('site_logo'),
    'site_banner' => getSetting('site_banner'),
    'site_favicon' => getSetting('site_favicon'),
    'about_image' => getSetting('about_image'),
    'about_title' => getSetting('about_title'),
    'about_content' => getSetting('about_content'),
    'company_vision' => getSetting('company_vision'),
    'company_mission' => getSetting('company_mission'),
    'office_hours' => getSetting('office_hours'),
    'meta_title' => getSetting('meta_title'),
    'meta_description' => getSetting('meta_description'),
    'meta_keywords' => getSetting('meta_keywords'),
    'facebook_url' => getSetting('facebook_url'),
    'instagram_url' => getSetting('instagram_url'),
    'youtube_url' => getSetting('youtube_url'),
    'whatsapp_number' => getSetting('whatsapp_number'),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #1a5276; --primary-light: #2e86c1; --secondary: #e67e22;
            --dark: #1a1a2e; --light: #f8f9fa; --gray: #6c757d;
            --success: #27ae60; --danger: #e74c3c; --warning: #f39c12;
            --sidebar-width: 280px; --shadow: 0 2px 15px rgba(0,0,0,0.08); --radius: 12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: var(--dark); }
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: var(--sidebar-width); background: var(--dark);
            z-index: 1000; overflow-y: auto; transition: all 0.3s;
        }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header h3 { color: white; font-size: 1.1rem; }
        .sidebar-header p { color: var(--secondary); font-size: 0.75rem; margin-top: 5px; }
        .sidebar-menu { list-style: none; padding: 15px 0; }
        .sidebar-menu li { margin: 2px 0; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 25px; color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 0.9rem;
            transition: all 0.3s; border-left: 3px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.05); color: white; border-left-color: var(--secondary);
        }
        .sidebar-menu a i { width: 22px; text-align: center; }
        .sidebar-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .top-bar {
            background: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow); position: sticky; top: 0; z-index: 100;
        }
        .top-bar h2 { font-size: 1.3rem; color: var(--dark); }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .user-menu span { font-size: 0.9rem; color: var(--gray); }
        .user-menu a { color: var(--gray); text-decoration: none; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .user-menu a:hover { background: var(--light); color: var(--danger); }
        .content-area { padding: 30px; }

        .flash {
            padding: 15px 20px; border-radius: 10px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 12px; font-size: 0.9rem;
        }
        .flash-success { background: #d4edda; color: #155724; }
        .flash-error { background: #f8d7da; color: #721c24; }

        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
        .settings-card {
            background: white; border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .settings-card-header {
            padding: 20px 25px; border-bottom: 1px solid #e9ecef;
            display: flex; align-items: center; gap: 12px;
        }
        .settings-card-header i { color: var(--primary); font-size: 1.2rem; }
        .settings-card-header h3 { font-size: 1.1rem; color: var(--dark); }
        .settings-card-body { padding: 25px; }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            color: var(--dark); font-weight: 500; font-size: 0.9rem;
        }
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="password"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%; padding: 12px 16px;
            border: 2px solid #e9ecef; border-radius: 10px;
            font-family: inherit; font-size: 0.9rem;
            transition: all 0.3s; outline: none;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group small { color: var(--gray); font-size: 0.8rem; margin-top: 5px; display: block; }

        .preview-image {
            max-width: 200px; max-height: 150px; border-radius: 8px;
            border: 2px solid #e9ecef; margin-top: 10px;
        }
        .preview-banner {
            max-width: 100%; max-height: 200px; border-radius: 8px;
            border: 2px solid #e9ecef; margin-top: 10px;
        }
        .preview-about {
            max-width: 100%; max-height: 250px; border-radius: 8px;
            border: 2px solid #e9ecef; margin-top: 10px;
            object-fit: cover;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white; border: none; padding: 12px 30px;
            border-radius: 10px; font-family: inherit; font-weight: 600;
            cursor: pointer; transition: all 0.3s;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(26, 82, 118, 0.3); }

        .password-section {
            background: linear-gradient(135deg, #fff5f5, #fff);
            border: 2px solid #fee2e2; border-radius: var(--radius);
            padding: 25px;
        }
        .password-section h4 { color: var(--danger); margin-bottom: 20px; }

        @media (max-width: 768px) {
            .settings-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-shield-alt" style="color:var(--secondary);margin-right:8px;"></i>Admin Panel</h3>
            <p>CV MR Aluminium Kendari</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="services.php"><i class="fas fa-tools"></i> Layanan</a></li>
            <li><a href="projects.php"><i class="fas fa-project-diagram"></i> Proyek</a></li>
            <li><a href="gallery.php"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i> Berita</a></li>
            <li><a href="testimonials.php"><i class="fas fa-comments"></i> Testimoni</a></li>
            <li><a href="messages.php"><i class="fas fa-envelope"></i> Pesan</a></li>
            <li><a href="team.php"><i class="fas fa-users"></i> Tim</a></li>
            <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> Pengaturan</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <h2><i class="fas fa-cog" style="color:var(--primary);margin-right:10px;"></i>Pengaturan Website</h2>
            <div class="user-menu">
                <span><i class="fas fa-user-circle" style="margin-right:5px;color:var(--primary);"></i><?php echo $_SESSION['admin_name']; ?></span>
                <a href="../index.php" target="_blank" title="Lihat Website"><i class="fas fa-external-link-alt"></i></a>
                <a href="logout.php" title="Keluar"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>

        <div class="content-area">
            <?php if ($flash): ?>
            <div class="flash flash-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Logo Upload -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-image"></i>
                        <h3>Logo Website</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_logo">
                            <div class="form-group">
                                <label>Upload Logo Baru</label>
                                <input type="file" name="logo" accept="image/*" required>
                                <small>Format: JPG, PNG, GIF. Maksimal 5MB. Disarankan ukuran 200x200px.</small>
                                <?php if ($settings['site_logo']): ?>
                                <img src="../uploads/logo/<?php echo $settings['site_logo']; ?>" alt="Current Logo" class="preview-image">
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Logo</button>
                        </form>
                    </div>
                </div>

                <!-- Banner Upload -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-panorama"></i>
                        <h3>Banner Hero</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_banner">
                            <div class="form-group">
                                <label>Upload Banner Baru</label>
                                <input type="file" name="banner" accept="image/*" required>
                                <small>Format: JPG, PNG. Maksimal 5MB. Disarankan ukuran 1920x1080px.</small>
                                <?php if ($settings['site_banner']): ?>
                                <img src="../uploads/banner/<?php echo $settings['site_banner']; ?>" alt="Current Banner" class="preview-banner">
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Banner</button>
                        </form>
                    </div>
                </div>

                <!-- Gambar Tentang Kami -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-building"></i>
                        <h3>Gambar Tentang Kami</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_about_image">
                            <div class="form-group">
                                <label>Upload Gambar Baru</label>
                                <input type="file" name="about_image" accept="image/*" required>
                                <small>Format: JPG, PNG, GIF. Maksimal 5MB. Disarankan ukuran 600x800px (portrait) atau 800x600px (landscape).</small>
                                <?php if ($settings['about_image']): ?>
                                <img src="../uploads/gallery/<?php echo $settings['about_image']; ?>" alt="Gambar Tentang Kami" class="preview-about">
                                <?php else: ?>
                                <div style="padding: 30px; background: #f8f9fa; border-radius: 8px; text-align: center; color: var(--gray); margin-top: 10px;">
                                    <i class="fas fa-image" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                    <p>Belum ada gambar. Upload gambar untuk ditampilkan di section Tentang Kami.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Gambar</button>
                        </form>
                    </div>
                </div>

                <!-- About Info -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-info-circle"></i>
                        <h3>Informasi Perusahaan</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_about">
                            <div class="form-group">
                                <label>Judul Tentang Kami</label>
                                <input type="text" name="about_title" value="<?php echo htmlspecialchars($settings['about_title']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Deskripsi Perusahaan</label>
                                <textarea name="about_content" rows="4"><?php echo htmlspecialchars($settings['about_content']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Visi</label>
                                <textarea name="company_vision" rows="2"><?php echo htmlspecialchars($settings['company_vision']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Misi</label>
                                <textarea name="company_mission" rows="2"><?php echo htmlspecialchars($settings['company_mission']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Jam Operasional</label>
                                <input type="text" name="office_hours" value="<?php echo htmlspecialchars($settings['office_hours']); ?>">
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-search"></i>
                        <h3>SEO & Meta</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_seo">
                            <div class="form-group">
                                <label>Meta Title</label>
                                <input type="text" name="meta_title" value="<?php echo htmlspecialchars($settings['meta_title']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Meta Description</label>
                                <textarea name="meta_description" rows="3"><?php echo htmlspecialchars($settings['meta_description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Meta Keywords</label>
                                <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords']); ?>">
                                <small>Pisahkan dengan koma</small>
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan SEO</button>
                        </form>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-share-alt"></i>
                        <h3>Media Sosial</h3>
                    </div>
                    <div class="settings-card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_social">
                            <div class="form-group">
                                <label><i class="fab fa-facebook" style="color:#1877f2;margin-right:8px;"></i>Facebook URL</label>
                                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url']); ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fab fa-instagram" style="color:#e4405f;margin-right:8px;"></i>Instagram URL</label>
                                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url']); ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fab fa-youtube" style="color:#ff0000;margin-right:8px;"></i>YouTube URL</label>
                                <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url']); ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fab fa-whatsapp" style="color:#25d366;margin-right:8px;"></i>Nomor WhatsApp</label>
                                <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>">
                                <small>Format: 628xxxxxxxxxx (tanpa + atau spasi)</small>
                            </div>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="fas fa-lock"></i>
                        <h3>Ganti Password Admin</h3>
                    </div>
                    <div class="settings-card-body">
                        <div class="password-section">
                            <h4><i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>Keamanan</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_password">
                                <div class="form-group">
                                    <label>Password Saat Ini</label>
                                    <input type="password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" name="new_password" required minlength="6">
                                    <small>Minimal 6 karakter</small>
                                </div>
                                <div class="form-group">
                                    <label>Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn-save" style="background:linear-gradient(135deg,#e74c3c,#c0392b);">
                                    <i class="fas fa-key"></i> Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>