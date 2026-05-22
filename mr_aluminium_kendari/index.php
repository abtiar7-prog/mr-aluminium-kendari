<?php
require_once 'includes/config.php';

// Get settings
$logo = getSetting('site_logo');
$banner = getSetting('site_banner');
$aboutContent = getSetting('about_content');
$aboutImage = getSetting('about_image');

// Get data from database
try {
    $db = getDB();
    $services = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY order_num ASC LIMIT 8")->fetchAll();
    $projects = $db->query("SELECT * FROM projects WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
    $testimonials = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
    $news = $db->query("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
    $gallery = $db->query("SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch (Exception $e) {
    $services = [];
    $projects = [];
    $testimonials = [];
    $news = [];
    $gallery = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_TAGLINE; ?></title>
    <meta name="description" content="<?php echo getSetting('meta_description'); ?>">
    <meta name="keywords" content="<?php echo getSetting('meta_keywords'); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $logo ? 'uploads/logo/' . $logo : 'assets/images/favicon.ico'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <style>
        :root {
            --primary: #1a5276;
            --primary-dark: #154360;
            --primary-light: #2e86c1;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --accent: #f39c12;
            --dark: #1a1a2e;
            --dark-light: #16213e;
            --light: #f8f9fa;
            --gray: #6c757d;
            --white: #ffffff;
            --gradient-primary: linear-gradient(135deg, #1a5276 0%, #2e86c1 100%);
            --gradient-secondary: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-md: 0 5px 25px rgba(0,0,0,0.12);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.15);
            --radius: 12px;
            --radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            line-height: 1.7;
            overflow-x: hidden;
            background: var(--light);
        }
        h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; font-weight: 700; }

        /* Preloader */
        .preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--primary); z-index: 99999;
            display: flex; align-items: center; justify-content: center;
            transition: opacity 0.5s, visibility 0.5s;
        }
        .preloader.hidden { opacity: 0; visibility: hidden; }
        .preloader-content { text-align: center; }
        .preloader-logo {
            width: 80px; height: 80px; border: 4px solid rgba(255,255,255,0.3);
            border-top-color: var(--white); border-radius: 50%;
            animation: spin 1s linear infinite; margin: 0 auto 20px;
        }
        .preloader h3 { color: var(--white); font-size: 1.2rem; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Top Bar */
        .top-bar {
            background: var(--dark);
            padding: 8px 0;
            font-size: 0.85rem;
        }
        .top-bar-inner {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .top-bar a { color: rgba(255,255,255,0.8); text-decoration: none; transition: var(--transition); }
        .top-bar a:hover { color: var(--accent); }
        .top-bar i { margin-right: 6px; color: var(--secondary); }
        .top-bar-left, .top-bar-right { display: flex; gap: 25px; align-items: center; }

        /* Header */
        .main-header {
            position: sticky; top: 0; z-index: 1000;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        .main-header.scrolled { box-shadow: var(--shadow-md); }
        .header-inner {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: flex; justify-content: space-between; align-items: center;
            height: 80px;
        }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-img { width: 55px; height: 55px; object-fit: contain; border-radius: 8px; }
        .logo-text h1 { font-size: 1.3rem; color: var(--primary); line-height: 1.2; letter-spacing: -0.5px; }
        .logo-text span { font-size: 0.7rem; color: var(--secondary); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }

        /* Navigation */
        .main-nav { display: flex; gap: 5px; }
        .main-nav a {
            text-decoration: none; color: var(--dark); font-weight: 500; font-size: 0.9rem;
            padding: 8px 16px; border-radius: 8px; transition: var(--transition); position: relative;
        }
        .main-nav a:hover, .main-nav a.active { color: var(--primary); background: rgba(26, 82, 118, 0.08); }
        .main-nav a::after {
            content: ''; position: absolute; bottom: 0; left: 50%; width: 0; height: 2px;
            background: var(--secondary); transition: var(--transition); transform: translateX(-50%);
        }
        .main-nav a:hover::after, .main-nav a.active::after { width: 60%; }
        .nav-cta {
            background: var(--gradient-secondary) !important; color: var(--white) !important;
            padding: 10px 24px !important; border-radius: 50px !important; font-weight: 600 !important;
        }
        .nav-cta:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(230, 126, 34, 0.4); }
        .nav-cta::after { display: none !important; }
        .mobile-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary); cursor: pointer; }

        /* Hero Section */
        .hero { position: relative; min-height: 75vh; display: flex; align-items: center; overflow: hidden; padding-bottom: 0; }
        .hero-bg { position: absolute; inset: 0; background: url('<?php echo $banner ? "uploads/banner/" . $banner : "assets/images/hero-bg.jpg"; ?>') center/cover; }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(26, 82, 118, 0.92) 0%, rgba(22, 33, 62, 0.88) 50%, rgba(26, 82, 118, 0.85) 100%);
        }
        .hero-content { position: relative; z-index: 2; max-width: 1300px; margin: 0 auto; padding: 80px 20px 180px; width: 100%; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
            padding: 8px 20px; border-radius: 50px; color: var(--accent);
            font-weight: 600; font-size: 0.85rem; margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .hero-badge i { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .hero h2 {
            font-size: clamp(2.2rem, 5vw, 4rem); color: var(--white); line-height: 1.15;
            margin-bottom: 20px; font-weight: 800;
        }
        .hero h2 span { color: var(--accent); }
        .hero p { font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 600px; margin-bottom: 35px; }
        .hero-buttons { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
        .btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 32px; border-radius: 50px; font-weight: 600;
            font-size: 0.95rem; text-decoration: none; transition: var(--transition); border: none; cursor: pointer;
        }
        .btn-primary {
            background: var(--gradient-secondary); color: var(--white);
            box-shadow: 0 5px 25px rgba(230, 126, 34, 0.4);
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(230, 126, 34, 0.5); }
        .btn-outline {
            background: transparent; color: var(--white); border: 2px solid rgba(255,255,255,0.4);
        }
        .btn-outline:hover { background: var(--white); color: var(--primary); border-color: var(--white); }

        /* Hero Stats */
        .hero-stats { 
            position: absolute; bottom: 0; left: 0; right: 0; 
            background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); 
            border-top: 1px solid rgba(255,255,255,0.2); z-index: 3;
        }
        .stats-inner { max-width: 1300px; margin: 0 auto; padding: 25px 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
        .stat-item { text-align: center; }
        .stat-item h3 { font-size: 2.5rem; color: var(--accent); font-weight: 800; }
        .stat-item p { color: rgba(255,255,255,0.8); font-size: 0.9rem; }

        /* Section Styles */
        .section { padding: 100px 0; position: relative; }
        .section-header { text-align: center; max-width: 700px; margin: 0 auto 60px; }
        .section-label {
            display: inline-block; padding: 6px 20px; background: rgba(26, 82, 118, 0.1);
            color: var(--primary); border-radius: 50px; font-size: 0.8rem;
            font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px;
        }
        .section-header h2 { font-size: clamp(1.8rem, 4vw, 2.5rem); color: var(--dark); margin-bottom: 15px; }
        .section-header p { color: var(--gray); font-size: 1.05rem; }

        /* Services */
        .services-grid {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px;
        }
        .service-card {
            background: var(--white); border-radius: var(--radius-lg); padding: 35px 30px;
            text-align: center; box-shadow: var(--shadow-sm); transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.04); position: relative; overflow: hidden;
        }
        .service-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: var(--gradient-primary); transform: scaleX(0); transition: var(--transition);
        }
        .service-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
        .service-card:hover::before { transform: scaleX(1); }
        .service-icon {
            width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 18px;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
            font-size: 1.5rem; color: var(--white); transition: var(--transition);
        }
        .service-card:hover .service-icon { transform: rotateY(360deg); background: var(--gradient-secondary); }
        .service-card h3 { font-size: 1.2rem; color: var(--dark); margin-bottom: 12px; }
        .service-card p { color: var(--gray); font-size: 0.9rem; line-height: 1.7; }

        /* About Section */
        .about-section { background: var(--white); }
        .about-inner {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
        }
        .about-images { position: relative; }
        .about-img-main { width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); }
        .about-experience {
            position: absolute; top: 30px; left: -20px; background: var(--gradient-secondary);
            color: var(--white); padding: 20px 25px; border-radius: var(--radius); text-align: center;
            box-shadow: var(--shadow-md);
        }
        .about-experience h4 { font-size: 2rem; font-weight: 800; }
        .about-experience p { font-size: 0.8rem; font-weight: 500; }
        .about-content h2 { margin-bottom: 20px; }
        .about-content > p { color: var(--gray); margin-bottom: 25px; }
        .about-features { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
        .about-feature { display: flex; align-items: center; gap: 15px; }
        .about-feature i {
            width: 40px; height: 40px; background: rgba(26, 82, 118, 0.1);
            color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center;
        }
        .about-feature h4 { font-size: 1rem; color: var(--dark); }
        .about-feature p { font-size: 0.85rem; color: var(--gray); }
        .vm-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .vm-card { padding: 25px; border-radius: var(--radius); background: var(--light); border-left: 4px solid var(--secondary); }
        .vm-card h4 { color: var(--primary); margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .vm-card p { font-size: 0.9rem; color: var(--gray); }

        /* Projects */
        .projects-section { background: var(--dark); color: var(--white); }
        .projects-section .section-header h2 { color: var(--white); }
        .projects-section .section-header p { color: rgba(255,255,255,0.7); }
        .projects-grid {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px;
        }
        .project-card { position: relative; border-radius: var(--radius-lg); overflow: hidden; height: 350px; cursor: pointer; }
        .project-card img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .project-card:hover img { transform: scale(1.1); }
        .project-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(26, 82, 118, 0.95) 0%, transparent 60%);
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 30px; opacity: 0; transition: var(--transition);
        }
        .project-card:hover .project-overlay { opacity: 1; }
        .project-overlay span {
            display: inline-block; padding: 4px 12px; background: var(--secondary);
            color: var(--white); border-radius: 4px; font-size: 0.75rem; font-weight: 600;
            margin-bottom: 10px; width: fit-content;
        }
        .project-overlay h3 { font-size: 1.3rem; margin-bottom: 8px; }
        .project-overlay p { font-size: 0.9rem; color: rgba(255,255,255,0.8); }

        /* Gallery */
        .gallery-grid {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;
        }
        .gallery-item { position: relative; border-radius: var(--radius); overflow: hidden; height: 250px; cursor: pointer; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .gallery-item:hover img { transform: scale(1.1); }
        .gallery-overlay {
            position: absolute; inset: 0; background: rgba(26, 82, 118, 0.8);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: var(--transition);
        }
        .gallery-item:hover .gallery-overlay { opacity: 1; }
        .gallery-overlay i { color: var(--white); font-size: 2rem; }

        /* Testimonials */
        .testimonials-section { background: var(--light); }
        .testimonials-slider { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        .testimonial-card {
            background: var(--white); border-radius: var(--radius-lg); padding: 40px;
            box-shadow: var(--shadow-sm); text-align: center; margin: 10px;
        }
        .testimonial-card .quote-icon { font-size: 3rem; color: var(--secondary); opacity: 0.3; margin-bottom: 15px; }
        .testimonial-card p { font-size: 1.05rem; color: var(--gray); font-style: italic; margin-bottom: 25px; line-height: 1.8; }
        .testimonial-author img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 12px; border: 3px solid var(--secondary); }
        .testimonial-author h4 { font-size: 1.1rem; color: var(--dark); }
        .testimonial-author span { font-size: 0.85rem; color: var(--gray); }
        .stars { color: var(--accent); margin-top: 10px; }

        /* News */
        .news-grid {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px;
        }
        .news-card {
            background: var(--white); border-radius: var(--radius-lg); overflow: hidden;
            box-shadow: var(--shadow-sm); transition: var(--transition);
        }
        .news-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
        .news-card .news-img { height: 220px; overflow: hidden; }
        .news-card .news-img img { width: 100%; height: 100%; object-fit: cover; transition: var(--transition); }
        .news-card:hover .news-img img { transform: scale(1.1); }
        .news-content { padding: 25px; }
        .news-meta { display: flex; gap: 15px; margin-bottom: 12px; font-size: 0.8rem; color: var(--gray); }
        .news-meta i { color: var(--secondary); }
        .news-content h3 { font-size: 1.15rem; color: var(--dark); margin-bottom: 12px; line-height: 1.4; }
        .news-content p { color: var(--gray); font-size: 0.9rem; margin-bottom: 15px; }
        .read-more { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; }
        .read-more:hover { color: var(--secondary); }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-primary); padding: 80px 0; position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute; inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/></svg>');
            background-size: 100px;
        }
        .cta-inner {
            max-width: 1300px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px;
        }
        .cta-content h2 { color: var(--white); font-size: 2.2rem; margin-bottom: 10px; }
        .cta-content p { color: rgba(255,255,255,0.85); font-size: 1.1rem; }
        .cta-btn {
            background: var(--white); color: var(--primary); padding: 16px 40px; border-radius: 50px;
            font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 10px;
            transition: var(--transition);
        }
        .cta-btn:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }

        /* Footer */
        .main-footer { background: var(--dark); color: rgba(255,255,255,0.8); padding-top: 80px; }
        .footer-inner {
            max-width: 1300px; margin: 0 auto; padding: 0 20px;
            display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 40px; padding-bottom: 60px;
        }
        .footer-brand h3 { color: var(--white); font-size: 1.5rem; margin-bottom: 15px; }
        .footer-brand p { margin-bottom: 20px; line-height: 1.8; }
        .social-links { display: flex; gap: 12px; }
        .social-links a {
            width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.1);
            color: var(--white); display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: var(--transition);
        }
        .social-links a:hover { background: var(--secondary); transform: translateY(-3px); }
        .footer-col h4 { color: var(--white); font-size: 1.1rem; margin-bottom: 25px; position: relative; padding-bottom: 12px; }
        .footer-col h4::after { content: ''; position: absolute; bottom: 0; left: 0; width: 40px; height: 3px; background: var(--secondary); border-radius: 2px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 12px; }
        .footer-col ul li a { color: rgba(255,255,255,0.7); text-decoration: none; transition: var(--transition); display: flex; align-items: center; gap: 8px; }
        .footer-col ul li a:hover { color: var(--accent); padding-left: 5px; }
        .footer-col ul li a i { font-size: 0.7rem; color: var(--secondary); }
        .contact-info li { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 15px; }
        .contact-info li i {
            width: 35px; height: 35px; background: rgba(255,255,255,0.1); border-radius: 8px;
            display: flex; align-items: center; justify-content: center; color: var(--secondary); flex-shrink: 0;
        }
        .contact-info li span { font-size: 0.9rem; line-height: 1.6; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.1); padding: 25px 0; text-align: center; font-size: 0.9rem; }
        .footer-bottom a { color: var(--accent); text-decoration: none; }

        /* WhatsApp Float */
        .wa-float {
            position: fixed; bottom: 30px; right: 30px; z-index: 999; width: 60px; height: 60px;
            background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: var(--white); font-size: 1.8rem; text-decoration: none;
            box-shadow: 0 5px 25px rgba(37, 211, 102, 0.4); transition: var(--transition);
            animation: wa-bounce 2s infinite;
        }
        .wa-float:hover { transform: scale(1.1); }
        @keyframes wa-bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        /* Back to Top */
        .back-to-top {
            position: fixed; bottom: 30px; left: 30px; z-index: 999; width: 45px; height: 45px;
            background: var(--primary); border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: var(--white); cursor: pointer; opacity: 0; visibility: hidden;
            transition: var(--transition); border: none; box-shadow: var(--shadow-md);
        }
        .back-to-top.visible { opacity: 1; visibility: visible; }
        .back-to-top:hover { background: var(--secondary); transform: translateY(-3px); }

        /* Admin Access - Pojok Kanan Bawah */
        /* Letak: Sudut kanan bawah layar, area 20x20 pixel */
        /* Cara pakai: Klik 3x cepat di area tersebut */
        .admin-trigger {
            position: fixed; bottom: 5px; right: 5px; z-index: 9999;
            width: 20px; height: 20px; opacity: 0; cursor: pointer;
            background: transparent;
        }
        .admin-trigger:hover {
            opacity: 0.3;
            background: var(--secondary);
            border-radius: 4px;
        }

        /* Lightbox */
        .lightbox { display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,0.95); align-items: center; justify-content: center; }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90%; max-height: 90%; border-radius: 8px; }
        .lightbox-close { position: absolute; top: 20px; right: 30px; color: var(--white); font-size: 2.5rem; cursor: pointer; }

        /* Contact Form */
        .contact-form input:focus, .contact-form textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(26, 82, 118, 0.1); outline: none; }

        /* Responsive */
        @media (max-width: 992px) {
            .about-inner { grid-template-columns: 1fr; }
            .about-images { order: -1; }
            .about-experience { left: 20px; }
            .footer-inner { grid-template-columns: 1fr 1fr; }
            .stats-inner { grid-template-columns: repeat(2, 1fr); }
            .vm-cards { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .main-nav {
                position: fixed; top: 80px; left: 0; right: 0; background: var(--white);
                flex-direction: column; padding: 20px; box-shadow: var(--shadow-md);
                transform: translateY(-150%); transition: var(--transition);
            }
            .main-nav.active { transform: translateY(0); }
            .main-nav a { padding: 12px 20px; }
            .hero-stats { position: relative; }
            .stats-inner { grid-template-columns: repeat(2, 1fr); padding: 20px; }
            .footer-inner { grid-template-columns: 1fr; }
            .cta-inner { flex-direction: column; text-align: center; }
            .projects-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo"></div>
            <h3>CV MR Aluminium Kendari</h3>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-inner">
            <div class="top-bar-left">
                <a href="tel:<?php echo COMPANY_PHONE; ?>"><i class="fas fa-phone"></i> <?php echo COMPANY_PHONE; ?></a>
                <a href="mailto:<?php echo COMPANY_EMAIL; ?>"><i class="fas fa-envelope"></i> <?php echo COMPANY_EMAIL; ?></a>
            </div>
            <div class="top-bar-right">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <span><i class="fas fa-clock"></i> <?php echo getSetting('office_hours', 'Senin - Sabtu: 08.00 - 17.00 WITA'); ?></span>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header" id="header">
        <div class="header-inner">
            <div class="logo-section">
                <?php if ($logo): ?>
                    <img src="uploads/logo/<?php echo $logo; ?>" alt="<?php echo SITE_NAME; ?>" class="logo-img">
                <?php else: ?>
                    <div style="width:55px;height:55px;background:var(--gradient-primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1.2rem;">MR</div>
                <?php endif; ?>
                <div class="logo-text">
                    <h1>CV. MR Aluminium</h1>
                    <span>Kendari - Sulawesi Tenggara</span>
                </div>
            </div>
            <nav class="main-nav" id="mainNav">
                <a href="#home" class="active">Beranda</a>
                <a href="#about">Tentang</a>
                <a href="#services">Layanan</a>
                <a href="#projects">Proyek</a>
                <a href="#gallery">Galeri</a>
                <a href="#news">Berita</a>
                <a href="#contact">Kontak</a>
                <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>" class="nav-cta" target="_blank"><i class="fab fa-whatsapp"></i> Hubungi Kami</a>
            </nav>
            <button class="mobile-toggle" id="mobileToggle"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content" data-aos="fade-up">
            <div class="hero-badge">
                <i class="fas fa-award"></i> Kontraktor Terpercaya di Kendari
            </div>
            <h2>Solusi <span>Aluminium, Besi & Stainless</span> Profesional untuk Bangunan Anda</h2>
            <p>CV MR Aluminium Kendari menyediakan layanan kontraktor interior & eksterior berkualitas tinggi dengan material terbaik dan tenaga ahli berpengalaman.</p>
            <div class="hero-buttons">
                <a href="#services" class="btn btn-primary"><i class="fas fa-th-large"></i> Lihat Layanan</a>
                <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>" class="btn btn-outline" target="_blank"><i class="fab fa-whatsapp"></i> Konsultasi Gratis</a>
            </div>
        </div>
        <div class="hero-stats" id="heroStats">
            <div class="stats-inner">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="0">
                    <h3 class="counter" data-target="100">0</h3>
                    <p>Proyek Selesai</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="counter" data-target="50">0</h3>
                    <p>Klien Puas</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="counter" data-target="4">0</h3>
                    <p>Tahun Pengalaman</p>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="counter" data-target="15">0</h3>
                    <p>Tim Profesional</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section" id="services">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Layanan Kami</span>
            <h2>Layanan Profesional MR Aluminium</h2>
            <p>Kami menyediakan berbagai layanan kontraktor dengan material aluminium, besi, dan stainless steel berkualitas</p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $index => $service): ?>
            <div class="service-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="service-icon"><i class="fas <?php echo $service['icon']; ?>"></i></div>
                <h3><?php echo $service['title']; ?></h3>
                <p><?php echo $service['description']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about-section" id="about">
        <div class="about-inner">
            <div class="about-images" data-aos="fade-right">
                <img src="<?php echo $aboutImage ? 'uploads/gallery/' . $aboutImage : 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600'; ?>" alt="Tentang Kami" class="about-img-main">
                <div class="about-experience">
                    <h4>4+</h4>
                    <p>Tahun<br>Pengalaman</p>
                </div>
            </div>
            <div class="about-content" data-aos="fade-left">
                <span class="section-label">Tentang Kami</span>
                <h2>CV MR Aluminium Kendari</h2>
                <p><?php echo nl2br($aboutContent); ?></p>
                <div class="about-features">
                    <div class="about-feature">
                        <i class="fas fa-check-circle"></i>
                        <div><h4>Material Berkualitas</h4><p>Menggunakan aluminium, besi & stainless terbaik</p></div>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-users"></i>
                        <div><h4>Tim Profesional</h4><p>Tenaga ahli berpengalaman dan bersertifikat</p></div>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-shield-alt"></i>
                        <div><h4>Garansi Pekerjaan</h4><p>Jaminan kepuasan dan garansi hasil pekerjaan</p></div>
                    </div>
                </div>
                <div class="vm-cards">
                    <div class="vm-card">
                        <h4><i class="fas fa-eye"></i> Visi</h4>
                        <p><?php echo getSetting('company_vision'); ?></p>
                    </div>
                    <div class="vm-card">
                        <h4><i class="fas fa-bullseye"></i> Misi</h4>
                        <p><?php echo getSetting('company_mission'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="section projects-section" id="projects">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label" style="background:rgba(255,255,255,0.15);color:var(--accent);">Portfolio</span>
            <h2>Proyek Terbaru Kami</h2>
            <p>Berbagai proyek interior & eksterior yang telah kami kerjakan di Kendari dan sekitarnya</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($projects as $index => $project): ?>
            <div class="project-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <img src="<?php echo $project['image'] ? 'uploads/gallery/' . $project['image'] : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600'; ?>" alt="<?php echo $project['title']; ?>">
                <div class="project-overlay">
                    <span><?php echo $project['category']; ?></span>
                    <h3><?php echo $project['title']; ?></h3>
                    <p><?php echo truncateText($project['description'], 100); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($projects)): ?>
            <div style="text-align:center;grid-column:1/-1;color:rgba(255,255,255,0.6);padding:40px;">
                <i class="fas fa-folder-open" style="font-size:3rem;margin-bottom:15px;display:block;"></i>
                <p>Proyek akan segera ditampilkan. Hubungi kami untuk informasi proyek yang telah kami kerjakan.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="section" id="gallery">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Galeri</span>
            <h2>Dokumentasi Pekerjaan</h2>
            <p>Galeri foto hasil pekerjaan dan proses pengerjaan proyek kami</p>
        </div>
        <div class="gallery-grid">
            <?php foreach ($gallery as $index => $item): ?>
            <div class="gallery-item" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>" onclick="openLightbox(this)">
                <img src="uploads/gallery/<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                <div class="gallery-overlay"><i class="fas fa-search-plus"></i></div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($gallery)): ?>
            <div style="text-align:center;grid-column:1/-1;color:var(--gray);padding:40px;">
                <i class="fas fa-images" style="font-size:3rem;margin-bottom:15px;display:block;color:var(--primary);"></i>
                <p>Galeri akan segera diperbarui. Kunjungi kami untuk melihat langsung hasil pekerjaan.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section testimonials-section" id="testimonials">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Testimoni</span>
            <h2>Apa Kata Klien Kami</h2>
            <p>Ulasan dari klien yang telah menggunakan jasa kami</p>
        </div>
        <div class="testimonials-slider swiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php foreach ($testimonials as $t): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p>"<?php echo $t['content']; ?>"</p>
                        <div class="testimonial-author">
                            <img src="<?php echo $t['image'] ? 'uploads/gallery/' . $t['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($t['name']) . '&background=e67e22&color=fff'; ?>" alt="<?php echo $t['name']; ?>">
                            <h4><?php echo $t['name']; ?></h4>
                            <span><?php echo $t['position']; ?><?php echo $t['company'] ? ' - ' . $t['company'] : ''; ?></span>
                            <div class="stars">
                                <?php for($i=0; $i<$t['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- News Section -->
    <section class="section" id="news">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Berita</span>
            <h2>Berita & Informasi Terbaru</h2>
            <p>Update terbaru seputar dunia konstruksi aluminium dan aktivitas perusahaan kami</p>
        </div>
        <div class="news-grid">
            <?php foreach ($news as $index => $item): ?>
            <div class="news-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="news-img">
                    <img src="<?php echo $item['image'] ? 'uploads/news/' . $item['image'] : 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=500'; ?>" alt="<?php echo $item['title']; ?>">
                </div>
                <div class="news-content">
                    <div class="news-meta">
                        <span><i class="far fa-calendar"></i> <?php echo formatDate($item['created_at']); ?></span>
                        <span><i class="far fa-eye"></i> <?php echo $item['views']; ?> views</span>
                    </div>
                    <h3><?php echo $item['title']; ?></h3>
                    <p><?php echo truncateText($item['excerpt'] ?: $item['content'], 120); ?></p>
                    <a href="pages/news-detail.php?id=<?php echo $item['id']; ?>" class="read-more">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($news)): ?>
            <div style="text-align:center;grid-column:1/-1;color:var(--gray);padding:40px;">
                <i class="far fa-newspaper" style="font-size:3rem;margin-bottom:15px;display:block;color:var(--primary);"></i>
                <p>Belum ada berita. Kunjungi kembali untuk update terbaru.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-inner">
            <div class="cta-content">
                <h2>Siap Memulai Proyek Anda?</h2>
                <p>Hubungi kami sekarang untuk konsultasi gratis dan penawaran harga terbaik</p>
            </div>
            <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>?text=Halo%20MR%20Aluminium%20Kendari,%20saya%20ingin%20konsultasi%20tentang%20proyek%20saya." class="cta-btn" target="_blank">
                <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
            </a>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section" id="contact" style="background:var(--white);">
        <div class="section-header" data-aos="fade-up">
            <span class="section-label">Kontak</span>
            <h2>Hubungi Kami</h2>
            <p>Konsultasikan kebutuhan proyek Anda dengan tim profesional kami</p>
        </div>
        <div style="max-width:1300px;margin:0 auto;padding:0 20px;display:grid;grid-template-columns:1fr 1fr;gap:50px;" data-aos="fade-up">
            <div>
                <h3 style="font-size:1.5rem;margin-bottom:20px;color:var(--dark);">Informasi Kontak</h3>
                <ul class="contact-info" style="list-style:none;">
                    <li><i class="fas fa-map-marker-alt"></i><span><?php echo COMPANY_ADDRESS; ?></span></li>
                    <li><i class="fas fa-phone"></i><span><?php echo COMPANY_PHONE; ?></span></li>
                    <li><i class="fas fa-envelope"></i><span><?php echo COMPANY_EMAIL; ?></span></li>
                    <li><i class="fab fa-whatsapp"></i><span>+<?php echo COMPANY_WHATSAPP; ?></span></li>
                    <li><i class="fas fa-clock"></i><span><?php echo getSetting('office_hours'); ?></span></li>
                </ul>
                <div style="margin-top:30px;">
                    <h4 style="margin-bottom:15px;color:var(--dark);">Lokasi Kami</h4>
                    <div style="border-radius:var(--radius);overflow:hidden;height:250px;background:var(--light);display:flex;align-items:center;justify-content:center;">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3980.8!2d122.5!3d-3.97!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwNTgnMTIuMCJTIDEyMsKwMzAnMDAuMCJF!5e0!3m2!1sid!2sid!4v1" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
            <div>
                <h3 style="font-size:1.5rem;margin-bottom:20px;color:var(--dark);">Kirim Pesan</h3>
                <form id="contactForm" class="contact-form" style="display:flex;flex-direction:column;gap:18px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                        <input type="text" name="name" placeholder="Nama Lengkap" required style="padding:14px 18px;border:2px solid #e9ecef;border-radius:10px;font-family:inherit;font-size:0.95rem;">
                        <input type="email" name="email" placeholder="Email" required style="padding:14px 18px;border:2px solid #e9ecef;border-radius:10px;font-family:inherit;font-size:0.95rem;">
                    </div>
                    <input type="text" name="phone" placeholder="No. Telepon / WhatsApp" style="padding:14px 18px;border:2px solid #e9ecef;border-radius:10px;font-family:inherit;font-size:0.95rem;">
                    <input type="text" name="subject" placeholder="Subjek" required style="padding:14px 18px;border:2px solid #e9ecef;border-radius:10px;font-family:inherit;font-size:0.95rem;">
                    <textarea name="message" rows="5" placeholder="Pesan Anda" required style="padding:14px 18px;border:2px solid #e9ecef;border-radius:10px;font-family:inherit;font-size:0.95rem;resize:vertical;"></textarea>
                    <button type="submit" class="btn btn-primary" style="justify-content:center;border-radius:10px;">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <h3><i class="fas fa-industry" style="color:var(--secondary);margin-right:10px;"></i>MR Aluminium Kendari</h3>
                <p>Kontraktor Interior & Eksterior terpercaya di Kendari, Sulawesi Tenggara. Spesialis Aluminium, Besi & Stainless Steel dengan kualitas terbaik.</p>
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
                    <li><a href="#home"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                    <li><a href="#about"><i class="fas fa-chevron-right"></i> Tentang Kami</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Layanan</a></li>
                    <li><a href="#projects"><i class="fas fa-chevron-right"></i> Proyek</a></li>
                    <li><a href="#gallery"><i class="fas fa-chevron-right"></i> Galeri</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Kusen Aluminium</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Partisi Ruangan</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Canopy & Atap</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Railing & Tangga</a></li>
                    <li><a href="#services"><i class="fas fa-chevron-right"></i> Kitchen Set</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i><span><?php echo COMPANY_ADDRESS; ?></span></li>
                    <li><i class="fas fa-phone"></i><span><?php echo COMPANY_PHONE; ?></span></li>
                    <li><i class="fas fa-envelope"></i><span><?php echo COMPANY_EMAIL; ?></span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> CV MR Aluminium Kendari. All Rights Reserved. | Dibuat dengan <i class="fas fa-heart" style="color:var(--secondary);"></i> di Kendari</p>
        </div>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/<?php echo COMPANY_WHATSAPP; ?>?text=Halo%20MR%20Aluminium%20Kendari,%20saya%20ingin%20bertanya%20tentang%20layanan%20Anda." class="wa-float" target="_blank" title="Chat WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></button>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img src="" alt="Gallery" id="lightboxImg">
    </div>

    <!-- ADMIN ACCESS - Klik 3x di pojok kanan bawah halaman untuk masuk admin -->
    <!-- Pojok kanan bawah = area 20x20 pixel di sudut kanan bawah layar -->
    <div class="admin-trigger" id="adminTrigger" title="Klik 3x untuk akses admin"></div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Preloader
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('preloader').classList.add('hidden');
            }, 1000);
        });

        // AOS Animation
        AOS.init({ duration: 800, once: true, offset: 100 });

        // Swiper Testimonials
        new Swiper('.testimonials-slider', {
            slidesPerView: 1, spaceBetween: 30, loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
        });

        // Header scroll effect
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Mobile menu
        const mobileToggle = document.getElementById('mobileToggle');
        const mainNav = document.getElementById('mainNav');
        mobileToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            mobileToggle.innerHTML = mainNav.classList.contains('active') ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    mainNav.classList.remove('active');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        });

        // Counter animation
        const counters = document.querySelectorAll('.counter');
        const animateCounter = (counter) => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            const update = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(update);
                } else {
                    counter.textContent = target + '+';
                }
            };
            update();
        };
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });
        counters.forEach(counter => counterObserver.observe(counter));

        // Back to top
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('visible', window.scrollY > 500);
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Lightbox
        function openLightbox(element) {
            const img = element.querySelector('img');
            document.getElementById('lightboxImg').src = img.src;
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Contact Form
        document.getElementById('contactForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            btn.disabled = true;
            try {
                const formData = new FormData(form);
                const response = await fetch('pages/contact-submit.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    alert('Pesan berhasil dikirim! Kami akan menghubungi Anda segera.');
                    form.reset();
                } else {
                    alert('Gagal mengirim pesan. Silakan coba lagi.');
                }
            } catch (error) {
                alert('Terjadi kesalahan. Silakan hubungi kami langsung via WhatsApp.');
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });

        // Admin trigger (triple click)
        let clickCount = 0;
        let clickTimer;
        document.getElementById('adminTrigger').addEventListener('click', () => {
            clickCount++;
            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => { clickCount = 0; }, 1000);
            if (clickCount >= 3) {
                window.location.href = 'admin/login.php';
            }
        });

        // Active nav on scroll
        const sections = document.querySelectorAll('section[id]');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });
            document.querySelectorAll('.main-nav a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
