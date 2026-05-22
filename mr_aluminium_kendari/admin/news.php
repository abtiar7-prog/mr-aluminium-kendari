<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $img = $stmt->fetch();
    if ($img && $img['image'] && file_exists(UPLOAD_DIR . 'news/' . $img['image'])) {
        unlink(UPLOAD_DIR . 'news/' . $img['image']);
    }
    $db->prepare("DELETE FROM news WHERE id = ?")->execute([$_GET['id']]);
    setFlash('success', 'Berita berhasil dihapus!');
    redirect(ADMIN_URL . '/news.php');
}

// Handle toggle
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $db->prepare("UPDATE news SET is_active = NOT is_active WHERE id = ?")->execute([$_GET['id']]);
    setFlash('success', 'Status berita diperbarui!');
    redirect(ADMIN_URL . '/news.php');
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $author = $_POST['author'] ?? $_SESSION['admin_name'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['image'], 'news');
        if ($result['success']) {
            $image = $result['file'];
        }
    }

    if ($id) {
        if ($image) {
            $db->prepare("UPDATE news SET title=?, slug=?, content=?, excerpt=?, author=?, image=?, is_active=? WHERE id=?")
               ->execute([$title, $slug, $content, $excerpt, $author, $image, $is_active, $id]);
        } else {
            $db->prepare("UPDATE news SET title=?, slug=?, content=?, excerpt=?, author=?, is_active=? WHERE id=?")
               ->execute([$title, $slug, $content, $excerpt, $author, $is_active, $id]);
        }
        setFlash('success', 'Berita berhasil diperbarui!');
    } else {
        $db->prepare("INSERT INTO news (title, slug, content, excerpt, author, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)")
           ->execute([$title, $slug, $content, $excerpt, $author, $image, $is_active]);
        setFlash('success', 'Berita berhasil ditambahkan!');
    }
    redirect(ADMIN_URL . '/news.php');
}

// Get all news
$news = $db->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();

// Get news for edit
$editNews = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editNews = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #1a5276; --primary-light: #2e86c1; --secondary: #e67e22; --dark: #1a1a2e; --light: #f8f9fa; --gray: #6c757d; --success: #27ae60; --danger: #e74c3c; --warning: #f39c12; --sidebar-width: 280px; --shadow: 0 2px 15px rgba(0,0,0,0.08); --radius: 12px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: var(--dark); }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--dark); z-index: 1000; overflow-y: auto; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header h3 { color: white; font-size: 1.1rem; }
        .sidebar-header p { color: var(--secondary); font-size: 0.75rem; margin-top: 5px; }
        .sidebar-menu { list-style: none; padding: 15px 0; }
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
        .card-body { padding: 0; }
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
        .news-thumb { width: 80px; height: 55px; object-fit: cover; border-radius: 6px; border: 2px solid #e9ecef; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: var(--dark); font-weight: 500; font-size: 0.9rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px 16px; border: 2px solid #e9ecef; border-radius: 10px; font-family: inherit; font-size: 0.9rem; transition: all 0.3s; outline: none; }
        .form-group input:focus, .form-group textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1); }
        .form-group textarea { resize: vertical; }
        .form-group small { color: var(--gray); font-size: 0.8rem; margin-top: 5px; display: block; }
        .btn-save { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-family: inherit; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(26, 82, 118, 0.3); }
        .btn-cancel { background: #e9ecef; color: var(--gray); border: none; padding: 12px 25px; border-radius: 10px; font-family: inherit; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .btn-cancel:hover { background: #dee2e6; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; border-radius: var(--radius); width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.3); }
        .modal-header { padding: 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.2rem; color: var(--dark); }
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .modal-close:hover { background: var(--light); color: var(--danger); }
        .modal-body { padding: 25px; }
        .modal-footer { padding: 20px 25px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; justify-content: flex-end; }
        .preview-img { max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e9ecef; margin-top: 10px; }
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
            <li><a href="news.php" class="active"><i class="fas fa-newspaper"></i> Berita</a></li>
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
            <h2><i class="fas fa-newspaper" style="color:var(--primary);margin-right:10px;"></i>Kelola Berita</h2>
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
                    <h3>Daftar Berita</h3>
                    <button class="btn-add" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Berita
                    </button>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Views</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $item['image'] ? '../uploads/news/' . $item['image'] : '../assets/images/news-default.jpg'; ?>" alt="" class="news-thumb" onerror="this.src='https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=100'">
                                </td>
                                <td>
                                    <strong><?php echo truncateText($item['title'], 40); ?></strong><br>
                                    <small style="color:var(--gray);"><?php echo truncateText($item['excerpt'] ?: $item['content'], 50); ?></small>
                                </td>
                                <td><?php echo $item['author']; ?></td>
                                <td><?php echo $item['views']; ?> views</td>
                                <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                                <td><span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?></span></td>
                                <td style="text-align:center;">
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="?action=toggle&id=<?php echo $item['id']; ?>" class="btn-sm btn-toggle" title="Toggle Status"><i class="fas fa-toggle-<?php echo $item['is_active'] ? 'on' : 'off'; ?>"></i></a>
                                    <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus berita ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($news)): ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:40px;">Belum ada berita. Klik "Tambah Berita" untuk menambahkan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal-overlay" id="newsModal" <?php echo $editNews ? 'style="display:flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-<?php echo $editNews ? 'edit' : 'plus'; ?>" style="color:var(--primary);margin-right:10px;"></i><?php echo $editNews ? 'Edit' : 'Tambah'; ?> Berita</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editNews['id'] ?? ''; ?>">
                    <div class="form-group">
                        <label>Judul Berita</label>
                        <input type="text" name="title" value="<?php echo $editNews['title'] ?? ''; ?>" required placeholder="Judul berita">
                    </div>
                    <div class="form-group">
                        <label>Ringkasan (Excerpt)</label>
                        <textarea name="excerpt" rows="2" placeholder="Ringkasan singkat berita..."><?php echo $editNews['excerpt'] ?? ''; ?></textarea>
                        <small>Akan ditampilkan di halaman beranda. Jika kosong, akan otomatis diambil dari konten.</small>
                    </div>
                    <div class="form-group">
                        <label>Konten Lengkap</label>
                        <textarea name="content" rows="8" required placeholder="Tulis konten berita lengkap di sini..."><?php echo $editNews['content'] ?? ''; ?></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group">
                            <label>Penulis</label>
                            <input type="text" name="author" value="<?php echo $editNews['author'] ?? $_SESSION['admin_name']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Foto Thumbnail</label>
                            <input type="file" name="image" accept="image/*" <?php echo $editNews ? '' : ''; ?>>
                            <small>Format: JPG, PNG. Maksimal 5MB.</small>
                            <?php if ($editNews && $editNews['image']): ?>
                            <img src="../uploads/news/<?php echo $editNews['image']; ?>" alt="" class="preview-img">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?php echo (!isset($editNews) || $editNews['is_active']) ? 'checked' : ''; ?> style="width:auto;">
                            <span>Publikasikan berita ini</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="news.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('newsModal').classList.add('active'); }
        function closeModal() { 
            document.getElementById('newsModal').classList.remove('active'); 
            <?php if (!$editNews): ?>window.location.href = 'news.php';<?php endif; ?>
        }
    </script>
</body>
</html>
