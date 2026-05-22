<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();
$flash = getFlash();

// Mark as read
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$_GET['id']]);
    setFlash('success', 'Pesan ditandai sebagai dibaca!');
    redirect(ADMIN_URL . '/messages.php');
}

// Delete message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $db->prepare("DELETE FROM messages WHERE id = ?")->execute([$_GET['id']]);
    setFlash('success', 'Pesan berhasil dihapus!');
    redirect(ADMIN_URL . '/messages.php');
}

// Get all messages
$messages = $db->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

// Get unread count
$unreadCount = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Masuk - <?php echo SITE_NAME; ?></title>
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
        .sidebar-menu .badge { margin-left: auto; background: var(--danger); color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
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
        .unread-badge { background: var(--danger); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .card-body { padding: 0; }
        .message-list { list-style: none; }
        .message-item {
            padding: 20px 25px; border-bottom: 1px solid #e9ecef;
            transition: all 0.3s; cursor: pointer;
        }
        .message-item:hover { background: rgba(26, 82, 118, 0.02); }
        .message-item.unread { background: rgba(26, 82, 118, 0.04); border-left: 4px solid var(--primary); }
        .message-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .message-sender { display: flex; align-items: center; gap: 12px; }
        .sender-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 1rem;
        }
        .sender-info h4 { font-size: 1rem; color: var(--dark); margin-bottom: 2px; }
        .sender-info p { font-size: 0.8rem; color: var(--gray); }
        .message-date { font-size: 0.8rem; color: var(--gray); }
        .message-subject { font-size: 0.95rem; color: var(--primary); font-weight: 600; margin-bottom: 8px; }
        .message-body { font-size: 0.9rem; color: var(--gray); line-height: 1.6; margin-bottom: 12px; }
        .message-actions { display: flex; gap: 10px; }
        .btn-sm { padding: 6px 14px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.3s; }
        .btn-read { background: #e3f2fd; color: var(--primary); }
        .btn-read:hover { background: var(--primary); color: white; }
        .btn-delete { background: #ffebee; color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }
        .btn-reply { background: #e8f5e9; color: var(--success); }
        .btn-reply:hover { background: var(--success); color: white; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--gray); }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; display: block; color: var(--primary); opacity: 0.3; }

        /* Message Detail Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; border-radius: var(--radius); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 80px rgba(0,0,0,0.3); }
        .modal-header { padding: 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.2rem; color: var(--dark); }
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; width: 35px; height: 35px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .modal-close:hover { background: var(--light); color: var(--danger); }
        .modal-body { padding: 25px; }
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
            <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Pesan <?php if($unreadCount>0): ?><span class="badge"><?php echo $unreadCount; ?></span><?php endif; ?></a></li>
            <li><a href="team.php"><i class="fas fa-users"></i> Tim</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <h2><i class="fas fa-envelope" style="color:var(--primary);margin-right:10px;"></i>Pesan Masuk</h2>
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
                    <h3>Daftar Pesan</h3>
                    <?php if ($unreadCount > 0): ?>
                    <span class="unread-badge"><?php echo $unreadCount; ?> pesan baru</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <ul class="message-list">
                        <?php foreach ($messages as $msg): ?>
                        <li class="message-item <?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                            <div class="message-header">
                                <div class="message-sender">
                                    <div class="sender-avatar"><?php echo strtoupper(substr($msg['name'], 0, 1)); ?></div>
                                    <div class="sender-info">
                                        <h4><?php echo $msg['name']; ?></h4>
                                        <p><i class="fas fa-envelope" style="margin-right:5px;"></i><?php echo $msg['email']; ?> <?php if($msg['phone']): ?>| <i class="fas fa-phone" style="margin-right:5px;"></i><?php echo $msg['phone']; ?><?php endif; ?></p>
                                    </div>
                                </div>
                                <span class="message-date"><i class="far fa-clock" style="margin-right:5px;"></i><?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-subject"><?php echo $msg['subject'] ?: '(Tanpa Subjek)'; ?></div>
                            <div class="message-body"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                            <div class="message-actions">
                                <?php if (!$msg['is_read']): ?>
                                <a href="?action=read&id=<?php echo $msg['id']; ?>" class="btn-sm btn-read"><i class="fas fa-check"></i> Tandai Dibaca</a>
                                <?php endif; ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $msg['phone'] ?: COMPANY_WHATSAPP); ?>?text=Halo%20<?php echo urlencode($msg['name']); ?>,%20terima%20kasih%20atas%20pesan%20Anda." target="_blank" class="btn-sm btn-reply"><i class="fab fa-whatsapp"></i> Balas WA</a>
                                <a href="mailto:<?php echo $msg['email']; ?>?subject=Re:%20<?php echo urlencode($msg['subject'] ?: 'Pesan dari MR Aluminium'); ?>" class="btn-sm btn-read"><i class="fas fa-reply"></i> Balas Email</a>
                                <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus pesan ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($messages)): ?>
                        <li class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada pesan masuk</p>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
