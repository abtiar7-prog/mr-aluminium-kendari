<?php
require_once '../includes/config.php';

$id = $_GET['id'] ?? 0;

try {
    $db = getDB();

    // Update views
    $db->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$id]);

    // Get news
    $stmt = $db->prepare("SELECT * FROM news WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    $news = $stmt->fetch();

    if (!$news) {
        redirect(SITE_URL);
    }

    // Get related news
    $related = $db->prepare("SELECT * FROM news WHERE id != ? AND is_active = 1 ORDER BY created_at DESC LIMIT 3");
    $related->execute([$id]);
    $relatedNews = $related->fetchAll();

} catch (Exception $e) {
    redirect(SITE_URL);
}

$logo = getSetting('site_logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['title']; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo truncateText(strip_tags($news['content']), 150); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary: #1a5276; --primary-light: #2e86c1; --secondary: #e67e22; --dark: #1a1a2e; --light: #f8f9fa; --gray: #6c757d; --shadow: 0 2px 15px rgba(0,0,0,0.08); --radius: 12px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; color: var(--dark); line-height: 1.7; background: var(--light); }
        .top-bar { background: var(--dark); padding: 8px 0; font-size: 0.85rem; }
        .top-bar-inner { max-width: 1300px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .top-bar a:hover { color: var(--secondary); }
        .top-bar i { margin-right: 6px; color: var(--secondary); }
        .main-header { position: sticky; top: 0; z-index: 1000; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0,0,0,0.05); }
        .header-inner { max-width: 1300px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; height: 80px; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-img { width: 55px; height: 55px; object-fit: contain; border-radius: 8px; }
        .logo-text h1 { font-size: 1.3rem; color: var(--primary); line-height: 1.2; font-family: 'Montserrat', sans-serif; }
        .logo-text span { font-size: 0.7rem; color: var(--secondary); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .main-nav { display: flex; gap: 5px; }
        .main-nav a { text-decoration: none; color: var(--dark); font-weight: 500; font-size: 0.9rem; padding: 8px 16px; border-radius: 8px; transition: all 0.3s; }
        .main-nav a:hover { color: var(--primary); background: rgba(26, 82, 118, 0.08); }
        .nav-cta { background: linear-gradient(135deg, #e67e22, #f39c12) !important; color: white !important; padding: 10px 24px !important; border-radius: 50px !important; font-weight: 600 !important; }
        .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(230, 126, 34, 0.4); }
        .mobile-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer; }

        .news-hero { background: linear-gradient(135deg, var(--primary), var(--dark)); padding: 60px 0; color: white; }
        .news-hero-inner { max-width: 1300px; margin: 0 auto; padding: 0 20px; }
        .news-hero h1 { font-size: 2rem; margin-bottom: 15px; font-family: 'Montserrat', sans-serif; }
        .news-meta { display: flex; gap: 20px; font-size: 0.9rem; opacity: 0.9; }
        .news-meta i { margin-right: 5px; }

        .news-content { max-width: 900px; margin: 0 auto; padding: 50px 20px; }
        .news-image { width: 100%; border-radius: var(--radius); margin-bottom: 30px; box-shadow: var(--shadow); }
        .news-body { font-size: 1.05rem; line-height: 1.9; color: var(--dark); }
        .news-body p { margin-bottom: 20px; }
        .news-body h2, .news-body h3 { margin: 30px 0 15px; color: var(--primary); }
        .news-body ul, .news-body ol { margin-left: 25px; margin-bottom: 20px; }
        .news-body li { margin-bottom: 8px; }
        .news-body img { max-width: 100%; border-radius: var(--radius); margin: 20px 0; }

        .related-news { max-width: 1300px; margin: 0 auto; padding: 50px 20px; border-top: 1px solid #e9ecef; }
        .related-news h2 { font-size: 1.5rem; margin-bottom: 25px; color: var(--dark); }
        .related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .related-card { background: white; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); transition: all 0.3s; text-decoration: none; color: inherit; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
        .related-card img { width: 100%; height: 180px; object-fit: cover; }
        .related-card-body { padding: 20px; }
        .related-card-body h3 { font-size: 1.1rem; margin-bottom: 10px; color: var(--dark); }
        .related-card-body p { font-size: 0.9rem; color: var(--gray); }
        .related-card-body .date { font-size: 0.8rem; color: var(--secondary); margin-top: 10px; }

        .main-footer { background: var(--dark); color: rgba(255,255,255,0.8); padding-top: 60px; }
        .footer-inner { max-width: 1300px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 40px; padding-bottom: 50px; }
        .footer-brand h3 { color: white; font-size: 1.5rem; margin-bottom: 15px; }
        .footer-brand p { margin-bottom: 20px; line-height: 1.8; }
        .social-links { display: flex; gap: 12px; }
        .social-links a { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.1); color: white; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s; }
        .social-links a:hover { background: var(--secondary); transform: translateY(-3px); }
        .footer-col h4 { color: white; font-size: 1.1rem; margin-bottom: 25px; position: relative; padding-bottom: 12px; }
        .footer-col h4::after { content: ''; position: absolute; bottom: 0; left: 0; width: 40px; height: 3px; background: var(--secondary); border-radius: 2px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 12px; }
        .footer-col ul li a { color: rgba(255,255,255,0.7); text-decoration: none; transition: all 0.3s; display: flex; align-items: center; gap: 8px; }
        .footer-col ul li a:hover { color: var(--accent); padding-left: 5px; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding: 25px 0; text-align: center; font-size: 0.9rem; }
        .wa-float { position: fixed; bottom: 30px; right: 30px; z-index: 999; width: 60px; height: 60px; background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; text-decoration: none; box-shadow: 0 5px 25px rgba(37, 211, 102, 0.4); transition: all 0.3s; }
        .wa-float:hover { transform: scale(1.1); }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none; margin-top: 20px; opacity: 0.9; transition: all 0.3s; }
        .back-link:hover { opacity: 1; }

        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .main-nav { display: none; }
            .footer-inner { grid-template-columns: 1fr; }
            .news-hero h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-inner">
            <div>
                <a href="tel:<?php echo COMPANY_PHONE; ?>"><i class="fas fa-phone"></i> <?php echo COMPANY_PHONE; ?></a>
                <a href="mailto:<?php echo COMPANY_EMAIL; ?>" style="margin-left:20px;"><i class="fas fa-envelope"></i> <?php echo COMPANY_EMAIL; ?></a>
            </div>
            <div>
                <span><i class="fas fa-clock"></i> <?php echo getSetting('office_hours'); ?></span>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="header-inner">
            <div class="logo-section">
                <?php if ($logo): ?>
                    <img src="../uploads/logo/<?php echo $logo; ?>" alt="<?php echo SITE_NAME; ?>" class="logo-img">
                <?php else: ?>
                    <div style="width:55px;height:55px;background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.2rem;">MR</div>
                <?php endif; ?>
                <div class="logo-text">
                    <h1>MR Aluminium</h1>
                    <span>Kendari</span>
                </div>
            </div>
            <nav class="main-nav">
                <a href="../index.php#home">Beranda</a>
                <a href="../index.php#about">Tentang</a>
                <a href="../index.php#services">Layanan</a>
                <a href="../index.php#projects">Proyek</a>
                <a href="../index.php#news">Berita</a>
                <a href="../index.php#contact">Kontak</a>
                <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>" class="nav-cta" target="_blank"><i class="fab fa-whatsapp"></i> Hubungi</a>
            </nav>
            <button class="mobile-toggle"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <section class="news-hero">
        <div class="news-hero-inner">
            <a href="../index.php#news" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Berita</a>
            <h1><?php echo $news['title']; ?></h1>
            <div class="news-meta">
                <span><i class="far fa-calendar"></i> <?php echo formatDate($news['created_at']); ?></span>
                <span><i class="far fa-user"></i> <?php echo $news['author']; ?></span>
                <span><i class="far fa-eye"></i> <?php echo $news['views']; ?> views</span>
            </div>
        </div>
    </section>

    <article class="news-content">
        <?php if ($news['image']): ?>
        <img src="../uploads/news/<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>" class="news-image">
        <?php endif; ?>
        <div class="news-body">
            <?php echo nl2br($news['content']); ?>
        </div>
    </article>

    <?php if (!empty($relatedNews)): ?>
    <section class="related-news">
        <h2><i class="fas fa-newspaper" style="color:var(--primary);margin-right:10px;"></i>Berita Terkait</h2>
        <div class="related-grid">
            <?php foreach ($relatedNews as $item): ?>
            <a href="news-detail.php?id=<?php echo $item['id']; ?>" class="related-card">
                <img src="<?php echo $item['image'] ? '../uploads/news/' . $item['image'] : '../assets/images/news-default.jpg'; ?>" alt="<?php echo $item['title']; ?>" onerror="this.src='https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400'">
                <div class="related-card-body">
                    <h3><?php echo truncateText($item['title'], 60); ?></h3>
                    <p><?php echo truncateText($item['excerpt'] ?: $item['content'], 80); ?></p>
                    <div class="date"><i class="far fa-calendar"></i> <?php echo formatDate($item['created_at']); ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <footer class="main-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <h3><i class="fas fa-industry" style="color:var(--secondary);margin-right:10px;"></i>MR Aluminium Kendari</h3>
                <p>Kontraktor Interior & Eksterior terpercaya di Kendari, Sulawesi Tenggara. Spesialis Aluminium, Besi & Stainless Steel.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>" title="WhatsApp" target="_blank"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Menu</h4>
                <ul>
                    <li><a href="../index.php#home"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                    <li><a href="../index.php#about"><i class="fas fa-chevron-right"></i> Tentang Kami</a></li>
                    <li><a href="../index.php#services"><i class="fas fa-chevron-right"></i> Layanan</a></li>
                    <li><a href="../index.php#projects"><i class="fas fa-chevron-right"></i> Proyek</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="../index.php#services"><i class="fas fa-chevron-right"></i> Kusen Aluminium</a></li>
                    <li><a href="../index.php#services"><i class="fas fa-chevron-right"></i> Partisi Ruangan</a></li>
                    <li><a href="../index.php#services"><i class="fas fa-chevron-right"></i> Canopy & Atap</a></li>
                    <li><a href="../index.php#services"><i class="fas fa-chevron-right"></i> Kitchen Set</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <ul style="list-style:none;">
                    <li style="margin-bottom:15px;display:flex;align-items:flex-start;gap:12px;"><i class="fas fa-map-marker-alt" style="width:35px;height:35px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--secondary);flex-shrink:0;"></i><span style="font-size:0.9rem;line-height:1.6;"><?php echo COMPANY_ADDRESS; ?></span></li>
                    <li style="margin-bottom:15px;display:flex;align-items:flex-start;gap:12px;"><i class="fas fa-phone" style="width:35px;height:35px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--secondary);flex-shrink:0;"></i><span style="font-size:0.9rem;line-height:1.6;"><?php echo COMPANY_PHONE; ?></span></li>
                    <li style="margin-bottom:15px;display:flex;align-items:flex-start;gap:12px;"><i class="fas fa-envelope" style="width:35px;height:35px;background:rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--secondary);flex-shrink:0;"></i><span style="font-size:0.9rem;line-height:1.6;"><?php echo COMPANY_EMAIL; ?></span></li>
                </ul>
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.1);padding:25px 0;text-align:center;font-size:0.9rem;color:rgba(255,255,255,0.8);">
            <p>&copy; <?php echo date('Y'); ?> CV MR Aluminium Kendari. All Rights Reserved.</p>
        </div>
    </footer>

    <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>?text=Halo%20MR%20Aluminium%20Kendari" class="wa-float" target="_blank" title="Chat WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
</body>
</html>
