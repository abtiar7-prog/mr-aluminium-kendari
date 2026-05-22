<?php
require_once '../includes/config.php';
requireAdmin();

$db = getDB();

// Statistics
$stats = [
    'services' => $db->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn(),
    'projects' => $db->query("SELECT COUNT(*) FROM projects WHERE is_active = 1")->fetchColumn(),
    'news' => $db->query("SELECT COUNT(*) FROM news WHERE is_active = 1")->fetchColumn(),
    'gallery' => $db->query("SELECT COUNT(*) FROM gallery WHERE is_active = 1")->fetchColumn(),
    'messages' => $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn(),
    'testimonials' => $db->query("SELECT COUNT(*) FROM testimonials WHERE is_active = 1")->fetchColumn()
];

// Recent messages
$recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent projects
$recentProjects = $db->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5")->fetchAll();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #1a5276;
            --primary-light: #2e86c1;
            --secondary: #e67e22;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --sidebar-width: 280px;
            --shadow: 0 2px 15px rgba(0,0,0,0.08);
            --radius: 12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: var(--dark);
        }

        /* Sidebar */
        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: var(--sidebar-width); background: var(--dark);
            z-index: 1000; overflow-y: auto;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
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
            background: rgba(255,255,255,0.05);
            color: white; border-left-color: var(--secondary);
        }
        .sidebar-menu a i { width: 22px; text-align: center; }
        .sidebar-menu .badge {
            margin-left: auto; background: var(--danger);
            color: white; padding: 2px 8px; border-radius: 20px;
            font-size: 0.7rem; font-weight: 600;
        }
        .sidebar-footer {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,0.6); text-decoration: none;
            font-size: 0.85rem; transition: all 0.3s;
        }
        .sidebar-footer a:hover { color: var(--danger); }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .top-bar {
            background: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: var(--shadow); position: sticky; top: 0; z-index: 100;
        }
        .top-bar h2 { font-size: 1.3rem; color: var(--dark); }
        .user-menu {
            display: flex; align-items: center; gap: 15px;
        }
        .user-menu span { font-size: 0.9rem; color: var(--gray); }
        .user-menu a {
            color: var(--gray); text-decoration: none;
            width: 35px; height: 35px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s;
        }
        .user-menu a:hover { background: var(--light); color: var(--danger); }

        .content-area { padding: 30px; }

        /* Stats Cards */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 20px; margin-bottom: 30px;
        }
        .stat-card {
            background: white; border-radius: var(--radius);
            padding: 25px; box-shadow: var(--shadow);
            display: flex; align-items: center; gap: 18px;
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .stat-icon {
            width: 55px; height: 55px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: white;
            flex-shrink: 0;
        }
        .stat-icon.blue { background: linear-gradient(135deg, #1a5276, #2e86c1); }
        .stat-icon.orange { background: linear-gradient(135deg, #e67e22, #f39c12); }
        .stat-icon.green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .stat-icon.red { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .stat-icon.purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); }
        .stat-icon.teal { background: linear-gradient(135deg, #16a085, #1abc9c); }
        .stat-info h3 { font-size: 1.6rem; color: var(--dark); }
        .stat-info p { font-size: 0.85rem; color: var(--gray); }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .dashboard-grid .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .dashboard-grid .card-body {
            flex: 1;
            overflow-y: auto;
            max-height: 500px;
        }

        /* Cards */
        .card {
            background: white; border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .card-header {
            padding: 20px 25px; border-bottom: 1px solid #e9ecef;
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h3 { font-size: 1.1rem; color: var(--dark); }
        .card-header a {
            color: var(--primary); text-decoration: none;
            font-size: 0.85rem; font-weight: 500;
        }
        .card-body { padding: 20px 25px; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; padding: 12px 15px;
            background: var(--light); color: var(--gray);
            font-weight: 600; font-size: 0.8rem; text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 14px 15px; border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
        }
        .data-table tr:hover td { background: rgba(26, 82, 118, 0.02); }
        .status-badge {
            display: inline-block; padding: 4px 12px;
            border-radius: 20px; font-size: 0.75rem; font-weight: 600;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-new { background: #fff3cd; color: #856404; }

        .btn-sm {
            padding: 6px 14px; border-radius: 6px; font-size: 0.8rem;
            font-weight: 500; border: none; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.3s;
        }
        .btn-edit { background: #e3f2fd; color: var(--primary); }
        .btn-edit:hover { background: var(--primary); color: white; }
        .btn-delete { background: #ffebee; color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }

        /* Flash Messages */
        .flash {
            padding: 15px 20px; border-radius: 10px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 12px; font-size: 0.9rem;
        }
        .flash-success { background: #d4edda; color: #155724; }
        .flash-error { background: #f8d7da; color: #721c24; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-shield-alt" style="color:var(--secondary);margin-right:8px;"></i>Admin Panel</h3>
            <p>CV MR Aluminium Kendari</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="services.php"><i class="fas fa-tools"></i> Layanan</a></li>
            <li><a href="projects.php"><i class="fas fa-project-diagram"></i> Proyek</a></li>
            <li><a href="gallery.php"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="news.php"><i class="fas fa-newspaper"></i> Berita</a></li>
            <li><a href="testimonials.php"><i class="fas fa-comments"></i> Testimoni</a></li>
            <li><a href="messages.php"><i class="fas fa-envelope"></i> Pesan <?php if($stats['messages']>0): ?><span class="badge"><?php echo $stats['messages']; ?></span><?php endif; ?></a></li>
            <li><a href="team.php"><i class="fas fa-users"></i> Tim</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h2><i class="fas fa-tachometer-alt" style="color:var(--primary);margin-right:10px;"></i>Dashboard</h2>
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

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-tools"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['services']; ?></h3>
                        <p>Layanan Aktif</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-project-diagram"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['projects']; ?></h3>
                        <p>Proyek</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-newspaper"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['news']; ?></h3>
                        <p>Berita</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-images"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['gallery']; ?></h3>
                        <p>Foto Galeri</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-envelope"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['messages']; ?></h3>
                        <p>Pesan Baru</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon teal"><i class="fas fa-comments"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['testimonials']; ?></h3>
                        <p>Testimoni</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content Grid -->
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-envelope" style="color:var(--primary);margin-right:8px;"></i>Pesan Terbaru</h3>
                        <a href="messages.php">Lihat Semua</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Subjek</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMessages as $msg): ?>
                                <tr>
                                    <td><strong><?php echo $msg['name']; ?></strong><br><small style="color:var(--gray);"><?php echo $msg['email']; ?></small></td>
                                    <td><?php echo $msg['subject'] ?: '-'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                                    <td><span class="status-badge <?php echo $msg['is_read'] ? 'status-active' : 'status-new'; ?>"><?php echo $msg['is_read'] ? 'Dibaca' : 'Baru'; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentMessages)): ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--gray);padding:30px;">Belum ada pesan</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-project-diagram" style="color:var(--secondary);margin-right:8px;"></i>Proyek Terbaru</h3>
                        <a href="projects.php">Lihat Semua</a>
                    </div>
                    <div class="card-body" style="padding:0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Proyek</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentProjects as $proj): ?>
                                <tr>
                                    <td><strong><?php echo $proj['title']; ?></strong></td>
                                    <td><?php echo $proj['category'] ?: '-'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($proj['created_at'])); ?></td>
                                    <td>
                                        <a href="projects-edit.php?id=<?php echo $proj['id']; ?>" class="btn-sm btn-edit"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentProjects)): ?>
                                <tr><td colspan="4" style="text-align:center;color:var(--gray);padding:30px;">Belum ada proyek</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
