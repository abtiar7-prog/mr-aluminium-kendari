<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

// Handle actions
if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? 0;

    if ($_GET['action'] === 'delete' && $id) {
        $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        setFlash('success', 'Layanan berhasil dihapus!');
        redirect(ADMIN_URL . '/services.php');
    }

    if ($_GET['action'] === 'toggle' && $id) {
        $db->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        setFlash('success', 'Status layanan diperbarui!');
        redirect(ADMIN_URL . '/services.php');
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? 'fa-tools';
    $order_num = $_POST['order_num'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($id) {
        $db->prepare("UPDATE services SET title=?, description=?, icon=?, order_num=?, is_active=? WHERE id=?")
           ->execute([$title, $description, $icon, $order_num, $is_active, $id]);
        setFlash('success', 'Layanan berhasil diperbarui!');
    } else {
        $db->prepare("INSERT INTO services (title, description, icon, order_num, is_active) VALUES (?, ?, ?, ?, ?)")
           ->execute([$title, $description, $icon, $order_num, $is_active]);
        setFlash('success', 'Layanan berhasil ditambahkan!');
    }
    redirect(ADMIN_URL . '/services.php');
}

// Get all services
$services = $db->query("SELECT * FROM services ORDER BY order_num ASC, id DESC")->fetchAll();

// Get service for edit
$editService = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editService = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - <?php echo SITE_NAME; ?></title>
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
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--dark); z-index: 1000; overflow-y: auto; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header h3 { color: white; font-size: 1.1rem; }
        .sidebar-header p { color: var(--secondary); font-size: 0.75rem; margin-top: 5px; }
        .sidebar-menu { list-style: none; padding: 15px 0; }
        .sidebar-menu li { margin: 2px 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 25px; color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem; transition: all 0.3s; border-left: 3px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: var(--secondary); }
        .sidebar-menu a i { width: 22px; text-align: center; }
        .sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .top-bar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow); position: sticky; top: 0; z-index: 100; }
        .top-bar h2 { font-size: 1.3rem; color: var(--dark); }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .user-menu span { font-size: 0.9rem; color: var(--gray); }
        .user-menu a { color: var(--gray); text-decoration: none; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .user-menu a:hover { background: var(--light); color: var(--danger); }
        .content-area { padding: 30px; }
        .flash { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 0.9rem; }
        .flash-success { background: #d4edda; color: #155724; }
        .flash-error { background: #f8d7da; color: #721c24; }

        .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { font-size: 1.1rem; color: var(--dark); }
        .btn-add { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; border: none; cursor: pointer; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(39, 174, 96, 0.3); }
        .card-body { padding: 25px; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px 15px; background: var(--light); color: var(--gray); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 14px 15px; border-bottom: 1px solid #e9ecef; font-size: 0.9rem; vertical-align: middle; }
        .data-table tr:hover td { background: rgba(26, 82, 118, 0.02); }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .btn-sm { padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.3s; }
        .btn-edit { background: #e3f2fd; color: var(--primary); }
        .btn-edit:hover { background: var(--primary); color: white; }
        .btn-delete { background: #ffebee; color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }
        .btn-toggle { background: #fff3cd; color: #856404; }
        .btn-toggle:hover { background: var(--warning); color: white; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--dark); font-weight: 500; font-size: 0.9rem; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select {
            width: 100%; padding: 12px 16px; border: 2px solid #e9ecef; border-radius: 10px; font-family: inherit; font-size: 0.9rem; transition: all 0.3s; outline: none;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1); }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group small { color: var(--gray); font-size: 0.8rem; margin-top: 5px; display: block; }
        .btn-save { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-family: inherit; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(26, 82, 118, 0.3); }
        .btn-cancel { background: #e9ecef; color: var(--gray); border: none; padding: 12px 25px; border-radius: 10px; font-family: inherit; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .btn-cancel:hover { background: #dee2e6; }

        .icon-preview { width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; margin-top: 10px; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; border-radius: var(--radius); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.3); }
        .modal-header { padding: 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.2rem; color: var(--dark); }
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .modal-close:hover { background: var(--light); color: var(--danger); }
        .modal-body { padding: 25px; }
        .modal-footer { padding: 20px 25px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; justify-content: flex-end; }
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
            <li><a href="services.php" class="active"><i class="fas fa-tools"></i> Layanan</a></li>
            <li><a href="projects.php"><i class="fas fa-project-diagram"></i> Proyek</a></li>
            <li><a href="gallery.php"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i> Berita</a></li>
            <li><a href="testimonials.php"><i class="fas fa-comments"></i> Testimoni</a></li>
            <li><a href="messages.php"><i class="fas fa-envelope"></i> Pesan</a></li>
            <li><a href="team.php"><i class="fas fa-users"></i> Tim</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <h2><i class="fas fa-tools" style="color:var(--primary);margin-right:10px;"></i>Kelola Layanan</h2>
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

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Layanan</h3>
                    <button class="btn-add" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Layanan
                    </button>
                </div>
                <div class="card-body" style="padding:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Ikon</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $index => $service): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><div style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;"><i class="fas <?php echo $service['icon']; ?>"></i></div></td>
                                <td><strong><?php echo $service['title']; ?></strong></td>
                                <td><?php echo truncateText($service['description'], 60); ?></td>
                                <td><?php echo $service['order_num']; ?></td>
                                <td><span class="status-badge <?php echo $service['is_active'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $service['is_active'] ? 'Aktif' : 'Nonaktif'; ?></span></td>
                                <td style="text-align:center;">
                                    <a href="?edit=<?php echo $service['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=toggle&id=<?php echo $service['id']; ?>" class="btn-sm btn-toggle" title="Toggle Status"><i class="fas fa-toggle-<?php echo $service['is_active'] ? 'on' : 'off'; ?>"></i></a>
                                    <a href="?action=delete&id=<?php echo $service['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus layanan ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($services)): ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:40px;">Belum ada layanan. Klik "Tambah Layanan" untuk menambahkan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" id="serviceModal" <?php echo $editService ? 'style="display:flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-<?php echo $editService ? 'edit' : 'plus'; ?>" style="color:var(--primary);margin-right:10px;"></i><?php echo $editService ? 'Edit' : 'Tambah'; ?> Layanan</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editService['id'] ?? ''; ?>">
                    <div class="form-group">
                        <label>Judul Layanan</label>
                        <input type="text" name="title" value="<?php echo $editService['title'] ?? ''; ?>" required placeholder="Contoh: Kusen Aluminium">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="3" required placeholder="Deskripsikan layanan ini..."><?php echo $editService['description'] ?? ''; ?></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group">
                            <label>Ikon (Font Awesome)</label>
                            <input type="text" name="icon" value="<?php echo $editService['icon'] ?? 'fa-tools'; ?>" required placeholder="fa-tools">
                            <small>Masukkan class Font Awesome, contoh: fa-door-open, fa-columns</small>
                            <div class="icon-preview"><i class="fas <?php echo $editService['icon'] ?? 'fa-tools'; ?>"></i></div>
                        </div>
                        <div class="form-group">
                            <label>Urutan</label>
                            <input type="number" name="order_num" value="<?php echo $editService['order_num'] ?? '0'; ?>" min="0">
                            <small>Angka kecil = muncul di atas</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?php echo (!isset($editService) || $editService['is_active']) ? 'checked' : ''; ?> style="width:auto;">
                            <span>Aktifkan layanan ini</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="services.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('serviceModal').classList.add('active'); }
        function closeModal() { 
            document.getElementById('serviceModal').classList.remove('active'); 
            <?php if (!$editService): ?>window.location.href = 'services.php';<?php endif; ?>
        }

        // Update icon preview
        document.querySelector('input[name="icon"]').addEventListener('input', function() {
            document.querySelector('.icon-preview i').className = 'fas ' + this.value;
        });
    </script>
</body>
</html>
