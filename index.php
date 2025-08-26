<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Start session for any dynamic content
start_secure_session();

// Page meta information
$page_title = 'Nostalji Gamers - Ultimate GunGame Experience';
$page_description = 'Türkiye\'nin en iyi Counter-Strike 1.6 GunGame sunucusu. 26 silah seviyesi, özel haritalar ve aktif topluluk ile unutulmaz deneyimler yaşayın.';
$page_keywords = 'cs 1.6, gungame, counter strike, türkiye, nostalji gamers, fps, online oyun, silah ilerlemesi';

// Get cached data or fetch from database
$cache_key = 'homepage_data';
$homepage_data = get_cache($cache_key);

if (!$homepage_data) {
    try {
        // Get server statistics
        $server_stats = get_server_stats($pdo);
        
        // Get weapon progression
        $weapon_progression = get_weapon_progression($pdo);
        
        // Get top players
        $top_players = get_top_players($pdo, 8);
        
        // Get active tournaments
        $tournaments = get_active_tournaments($pdo);
        
        // Get server maps
        $server_maps = get_server_maps($pdo);
        
        // Get banner
        $stmt = $pdo->query("SELECT * FROM banners WHERE status = 'active' ORDER BY order_num ASC LIMIT 1");
        $banner = $stmt->fetch();
        
        // Get menu items
        $stmt = $pdo->query("SELECT * FROM menu_items WHERE status = 'active' ORDER BY order_num ASC");
        $menu_items = $stmt->fetchAll();
        
        // Get content blocks
        $stmt = $pdo->query("SELECT * FROM content_blocks WHERE status = 'active'");
        $content_blocks = [];
        while ($row = $stmt->fetch()) {
            $content_blocks[$row['type']] = $row;
        }
        
        // Get recent blog posts
        $stmt = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        $recent_posts = $stmt->fetchAll();
        
        $homepage_data = [
            'server_stats' => $server_stats,
            'weapon_progression' => $weapon_progression,
            'top_players' => $top_players,
            'tournaments' => $tournaments,
            'server_maps' => $server_maps,
            'banner' => $banner,
            'menu_items' => $menu_items,
            'content_blocks' => $content_blocks,
            'recent_posts' => $recent_posts
        ];
        
        // Cache the data
        set_cache($cache_key, $homepage_data);
        
    } catch (PDOException $e) {
        log_error("Homepage database error: " . $e->getMessage());
        // Use default values
        $homepage_data = [
            'server_stats' => get_server_stats($pdo),
            'weapon_progression' => [],
            'top_players' => [],
            'tournaments' => [],
            'server_maps' => [],
            'banner' => null,
            'menu_items' => [],
            'content_blocks' => [],
            'recent_posts' => []
        ];
    }
}

// Extract data for easier use
extract($homepage_data);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php generate_meta_tags($page_title, $page_description, $page_keywords); ?>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
    
    <!-- Stylesheets -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #00ff88;
            --secondary-color: #0066ff;
            --accent-color: #ff6600;
            --dark-bg: #0a0a0f;
            --dark-card: #1a1a2e;
            --dark-surface: #16213e;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --neon-glow: 0 0 20px;
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-card) 50%, var(--dark-bg) 100%);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }
        
        /* RESPONSIVE NAVIGATION - FIXED */
        .navbar {
            background: rgba(10, 10, 15, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid var(--primary-color);
            padding: 0.75rem 0;
            transition: var(--transition);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
        }
        
        .navbar.scrolled {
            background: rgba(10, 10, 15, 0.98);
            padding: 0.5rem 0;
        }
        
        .navbar-brand {
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            color: var(--primary-color) !important;
            text-shadow: var(--neon-glow) var(--primary-color);
            text-decoration: none;
            white-space: nowrap;
        }
        
        .navbar-nav {
            align-items: center;
        }
        
        .nav-link {
            font-weight: 500;
            color: var(--text-secondary) !important;
            transition: var(--transition);
            position: relative;
            margin: 0 0.25rem;
            padding: 0.5rem 0.75rem !important;
            border-radius: 8px;
            white-space: nowrap;
            font-size: 0.95rem;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(0, 255, 136, 0.1);
            transform: translateY(-1px);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transition: var(--transition);
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 80%;
        }
        
        /* Hamburger Menu Styling */
        .navbar-toggler {
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 0.5rem;
            background: transparent;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
        }
        
        .navbar-toggler-icon {
            background-image: none;
            width: 24px;
            height: 24px;
            position: relative;
        }
        
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after,
        .navbar-toggler-icon {
            background: var(--primary-color);
        }
        
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after {
            content: '';
            position: absolute;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
            transition: var(--transition);
        }
        
        .navbar-toggler-icon::before {
            top: -8px;
        }
        
        .navbar-toggler-icon::after {
            bottom: -8px;
        }
        
        .navbar-toggler[aria-expanded="true"] .navbar-toggler-icon {
            background: transparent;
        }
        
        .navbar-toggler[aria-expanded="true"] .navbar-toggler-icon::before {
            transform: rotate(45deg);
            top: 0;
        }
        
        .navbar-toggler[aria-expanded="true"] .navbar-toggler-icon::after {
            transform: rotate(-45deg);
            bottom: 0;
        }
        
        /* Mobile Menu Improvements */
        .navbar-collapse {
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        @media (max-width: 991.98px) {
            .navbar-nav {
                text-align: center;
                gap: 0.5rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem !important;
                margin: 0.25rem 0;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .nav-link:hover {
                background: rgba(0, 255, 136, 0.15);
            }
        }
        
        /* Join Server Button - Responsive */
        .btn-join-server {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            color: var(--dark-bg) !important;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.3);
            font-size: 0.9rem;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-join-server:hover {
            transform: translateY(-3px);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.5);
            color: var(--dark-bg) !important;
        }
        
        @media (max-width: 991.98px) {
            .btn-join-server {
                margin-top: 1rem;
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .btn-join-server {
                font-size: 0.8rem;
                padding: 0.5rem 1rem;
            }
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5)), 
                        url('<?php echo $banner['background_image'] ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(0, 255, 136, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.6; }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .hero-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2.5rem, 8vw, 6rem);
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(0, 255, 136, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { filter: brightness(1); }
            to { filter: brightness(1.2); }
        }
        
        .hero-subtitle {
            font-size: clamp(1rem, 3vw, 1.8rem);
            margin-bottom: 2rem;
            color: var(--text-secondary);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stat-card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.3);
        }
        
        .stat-number {
            font-family: 'Orbitron', monospace;
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: clamp(0.8rem, 2vw, 1rem);
            margin-top: 0.5rem;
        }
        
        /* Section Styling */
        .section {
            padding: 5rem 0;
            position: relative;
        }
        
        @media (max-width: 768px) {
            .section {
                padding: 3rem 0;
            }
        }
        
        .section-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: clamp(1rem, 3vw, 1.2rem);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Cards */
        .card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            transition: var(--transition);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-color);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.2);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
        }
        
        /* Weapon Progression */
        .weapon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 576px) {
            .weapon-grid {
                grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
                gap: 0.5rem;
            }
        }
        
        .weapon-item {
            background: rgba(26, 26, 46, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            transition: var(--transition);
            position: relative;
        }
        
        .weapon-item:hover {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }
        
        .weapon-level {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        .weapon-name {
            font-size: clamp(0.7rem, 2vw, 0.9rem);
            color: var(--text-primary);
            margin-top: 0.5rem;
        }
        
        /* Leaderboard */
        .leaderboard {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .leaderboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--dark-bg);
            padding: 1rem 2rem;
            font-weight: 700;
        }
        
        .player-row {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }
        
        .player-row:hover {
            background: rgba(0, 255, 136, 0.1);
        }
        
        .player-rank {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            width: 50px;
            flex-shrink: 0;
        }
        
        .player-name {
            flex: 1;
            font-weight: 600;
            margin-left: 1rem;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .player-stats {
            display: flex;
            gap: 2rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .player-row {
                padding: 0.75rem 1rem;
                flex-wrap: wrap;
            }
            
            .player-stats {
                flex-direction: column;
                gap: 0.25rem;
                font-size: 0.8rem;
                width: 100%;
                margin-top: 0.5rem;
            }
            
            .leaderboard-header {
                padding: 1rem;
            }
        }
        
        /* Tournament Cards */
        .tournament-card {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(0, 102, 255, 0.1));
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            transition: var(--transition);
        }
        
        .tournament-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.3);
        }
        
        .tournament-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        
        .status-upcoming {
            background: linear-gradient(135deg, var(--accent-color), #ff9900);
            color: var(--dark-bg);
        }
        
        .status-active {
            background: linear-gradient(135deg, var(--primary-color), #00cc66);
            color: var(--dark-bg);
        }
        
        /* Maps Grid */
        .maps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 576px) {
            .maps-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        
        .map-card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .map-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        .map-image {
            height: 200px;
            background: linear-gradient(135deg, var(--dark-surface), var(--dark-card));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            font-size: 3rem;
        }
        
        .map-info {
            padding: 1.5rem;
        }
        
        .map-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .map-stats {
            display: flex;
            justify-content: space-between;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Blog Cards */
        .blog-card {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        .blog-meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .blog-title {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .blog-excerpt {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-bg), var(--dark-card));
            border-top: 2px solid var(--primary-color);
            padding: 3rem 0 1rem;
        }
        
        .footer-section h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .footer-link {
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .footer-link:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            color: var(--text-secondary);
            margin-right: 1rem;
            transition: var(--transition);
        }
        
        .social-links a:hover {
            background: var(--primary-color);
            color: var(--dark-bg);
            transform: translateY(-3px);
        }
        
        /* Responsive Design Improvements */
        @media (max-width: 1200px) {
            .hero-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .weapon-grid {
                grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            }
            
            .maps-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                padding-top: 100px;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .section-title {
                margin-bottom: 2rem;
            }
            
            .tournament-card {
                padding: 1.5rem;
            }
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 255, 136, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Utility Classes */
        .text-primary { color: var(--primary-color) !important; }
        .text-secondary { color: var(--text-secondary) !important; }
        .bg-dark-card { background: var(--dark-card) !important; }
        .border-primary { border-color: var(--primary-color) !important; }
        
        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--dark-bg);
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--neon-glow) rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: var(--dark-bg);
            transform: translateY(-2px);
        }
        
        /* Mobile Navigation Improvements */
        @media (max-width: 991.98px) {
            .navbar-collapse.show {
                animation: slideDown 0.3s ease-out;
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation - FULLY RESPONSIVE -->
    <nav class="navbar navbar-expand-lg" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-crosshairs me-2"></i>
                NOSTALJI GAMERS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($menu_items)): ?>
                        <?php foreach ($menu_items as $menu): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo sanitize_input($menu['url']); ?>" 
                                   target="<?php echo $menu['target']; ?>">
                                    <?php if ($menu['icon']): ?>
                                        <i class="<?php echo sanitize_input($menu['icon']); ?> me-1"></i>
                                    <?php endif; ?>
                                    <?php echo sanitize_input($menu['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="#home">Ana Sayfa</a></li>
                        <li class="nav-item"><a class="nav-link" href="#stats">İstatistikler</a></li>
                        <li class="nav-item"><a class="nav-link" href="#rules">Kurallar</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tournaments">Turnuvalar</a></li>
                        <li class="nav-item"><a class="nav-link" href="files.php">İndirmeler</a></li>
                        <li class="nav-item"><a class="nav-link" href="gallery.php">Galeri</a></li>
                        <li class="nav-item"><a class="nav-link" href="videos.php">Videolar</a></li>
                        <li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
                        <li class="nav-item"><a class="nav-link" href="#contact">İletişim</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="steam://connect/<?php echo $server_stats['server_ip']; ?>" class="btn-join-server">
                            <i class="fas fa-play me-2"></i>SUNUCUYA KATIL
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <?php echo sanitize_input($banner['title'] ?? 'NOSTALJI GAMERS'); ?>
                </h1>
                <p class="hero-subtitle">
                    <?php echo sanitize_input($banner['subtitle'] ?? 'Ultimate GunGame Deneyimini Yaşa! 26 Silah Seviyesi, Sınırsız Aksiyon'); ?>
                </p>
                
                <!-- Real-time Server Stats -->
                <div class="hero-stats">
                    <div class="stat-card">
                        <span class="stat-number" id="current-players"><?php echo $server_stats['current_players']; ?></span>
                        <span class="stat-label">/ <?php echo $server_stats['max_players']; ?> Oyuncu</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $server_stats['ping']; ?>ms</span>
                        <span class="stat-label">Düşük Ping</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $server_stats['tickrate']; ?></span>
                        <span class="stat-label">FPS Tickrate</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo number_format($server_stats['total_kills']); ?></span>
                        <span class="stat-label">Toplam Kill</span>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-secondary mb-2">
                        <i class="fas fa-server me-2"></i>
                        Sunucu IP: <span class="text-primary"><?php echo $server_stats['server_ip']; ?></span>
                    </p>
                    <p class="text-secondary">
                        <i class="fas fa-map me-2"></i>
                        Aktif Harita: <span class="text-primary"><?php echo $server_stats['map_current']; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- GunGame Features Section -->
    <section id="rules" class="section">
        <div class="container">
            <h2 class="section-title">GunGame Nasıl Oynanır?</h2>
            <p class="section-subtitle">
                <?php echo sanitize_input($content_blocks['gungame_info']['content'] ?? 'Her öldürme ile bir sonraki silaha geçin. 26 seviyeyi tamamlayıp bıçakla son öldürmeyi yapan kazanır!'); ?>
            </p>
            
            <!-- Weapon Progression -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="text-primary mb-4">
                                <i class="fas fa-crosshairs me-2"></i>
                                Silah İlerlemesi (26 Seviye)
                            </h4>
                            <div class="weapon-grid">
                                <?php if (!empty($weapon_progression)): ?>
                                    <?php foreach (array_slice($weapon_progression, 0, 12) as $weapon): ?>
                                        <div class="weapon-item">
                                            <div class="weapon-level"><?php echo $weapon['level']; ?></div>
                                            <i class="fas fa-crosshairs fa-2x text-primary mb-2"></i>
                                            <div class="weapon-name"><?php echo sanitize_input($weapon['weapon_display']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center text-secondary">
                                        <i class="fas fa-crosshairs fa-3x mb-3"></i>
                                        <p>Silah ilerlemesi yükleniyor...</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mt-4">
                                <button class="btn btn-outline-primary" onclick="showAllWeapons()">
                                    <i class="fas fa-list me-2"></i>Tüm Silahları Göster
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Server Statistics & Leaderboard -->
    <section id="stats" class="section">
        <div class="container">
            <h2 class="section-title">Sunucu İstatistikleri</h2>
            <p class="section-subtitle">En iyi oyuncular ve gerçek zamanlı istatistikler</p>
            
            <div class="row">
                <!-- Top Players Leaderboard -->
                <div class="col-lg-8 mb-4">
                    <div class="leaderboard">
                        <div class="leaderboard-header">
                            <i class="fas fa-trophy me-2"></i>
                            En İyi GunGame Oyuncuları
                        </div>
                        <?php if (!empty($top_players)): ?>
                            <?php foreach ($top_players as $index => $player): ?>
                                <div class="player-row">
                                    <div class="player-rank">#<?php echo $index + 1; ?></div>
                                    <div class="player-name">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo sanitize_input($player['player_name']); ?>
                                    </div>
                                    <div class="player-stats">
                                        <div>
                                            <i class="fas fa-crosshairs me-1"></i>
                                            <?php echo number_format($player['total_kills']); ?> Kill
                                        </div>
                                        <div>
                                            <i class="fas fa-trophy me-1"></i>
                                            <?php echo $player['total_wins']; ?> Galibiyet
                                        </div>
                                        <div>
                                            <i class="fas fa-level-up-alt me-1"></i>
                                            Seviye <?php echo $player['best_level']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="player-row">
                                <div class="col-12 text-center text-secondary py-4">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p>İstatistikler yükleniyor...</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Server Info -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-4">
                                <i class="fas fa-server me-2"></i>
                                Sunucu Bilgileri
                            </h5>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Durum:</span>
                                    <span class="text-primary">
                                        <i class="fas fa-circle me-1"></i>Online
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Uptime:</span>
                                    <span class="text-primary"><?php echo $server_stats['uptime']; ?>%</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Toplam Maç:</span>
                                    <span class="text-primary"><?php echo number_format($server_stats['total_matches']); ?></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <span>Oyun Modu:</span>
                                    <span class="text-primary"><?php echo $server_stats['game_mode']; ?></span>
                                </div>
                            </div>
                            <a href="steam://connect/<?php echo $server_stats['server_ip']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-play me-2"></i>Hemen Katıl
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tournaments Section -->
    <section id="tournaments" class="section">
        <div class="container">
            <h2 class="section-title">Turnuvalar</h2>
            <p class="section-subtitle">Yaklaşan etkinlikler ve ödül havuzları</p>
            
            <div class="row">
                <?php if (!empty($tournaments)): ?>
                    <?php foreach ($tournaments as $tournament): ?>
                        <div class="col-md-6 mb-4">
                            <div class="tournament-card">
                                <div class="tournament-status status-<?php echo $tournament['status']; ?>">
                                    <?php 
                                    $status_text = [
                                        'upcoming' => 'Yaklaşan',
                                        'active' => 'Aktif',
                                        'completed' => 'Tamamlandı',
                                        'cancelled' => 'İptal'
                                    ];
                                    echo $status_text[$tournament['status']] ?? 'Bilinmeyen';
                                    ?>
                                </div>
                                <h4 class="text-primary mb-3"><?php echo sanitize_input($tournament['title']); ?></h4>
                                <p class="text-secondary mb-3"><?php echo sanitize_input($tournament['description']); ?></p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-secondary">Başlangıç:</small>
                                        <div class="fw-bold"><?php echo format_date($tournament['start_date'], 'd.m.Y H:i'); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-secondary">Ödül Havuzu:</small>
                                        <div class="fw-bold text-primary"><?php echo sanitize_input($tournament['prize_pool']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary">
                                        <i class="fas fa-users me-1"></i>
                                        <?php echo $tournament['current_participants']; ?>/<?php echo $tournament['max_participants']; ?> Katılımcı
                                    </span>
                                    <?php if ($tournament['registration_open'] && $tournament['status'] === 'upcoming'): ?>
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-user-plus me-1"></i>Kayıt Ol
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="card">
                            <div class="card-body py-5">
                                <i class="fas fa-trophy fa-4x text-secondary mb-3"></i>
                                <h4 class="text-secondary">Yaklaşan Turnuva Yok</h4>
                                <p class="text-secondary">Yeni turnuvalar için Discord kanalımızı takip edin!</p>
                                <a href="https://discord.gg/nostaljigamers" class="btn btn-primary" target="_blank">
                                    <i class="fab fa-discord me-2"></i>Discord'a Katıl
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Server Maps Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Sunucu Haritaları</h2>
            <p class="section-subtitle">GunGame için özel olarak seçilmiş haritalar</p>
            
            <div class="maps-grid">
                <?php if (!empty($server_maps)): ?>
                    <?php foreach ($server_maps as $map): ?>
                        <div class="map-card">
                            <div class="map-image">
                                <i class="fas fa-map"></i>
                            </div>
                            <div class="map-info">
                                <div class="map-name"><?php echo sanitize_input($map['map_display']); ?></div>
                                <div class="map-stats">
                                    <span>
                                        <i class="fas fa-play me-1"></i>
                                        <?php echo number_format($map['times_played']); ?> kez oynandı
                                    </span>
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        ~<?php echo round($map['average_duration'] / 60); ?> dk
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-secondary">
                        <i class="fas fa-map fa-3x mb-3"></i>
                        <p>Harita bilgileri yükleniyor...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Community Section -->
    <section id="community" class="section">
        <div class="container">
            <h2 class="section-title">Topluluk</h2>
            <p class="section-subtitle">
                <?php echo sanitize_input($content_blocks['welcome']['content'] ?? 'Türkiye\'nin en aktif Counter-Strike 1.6 GunGame topluluğuna katılın.'); ?>
            </p>
            
            <div class="row">
                <!-- Recent Blog Posts -->
                <div class="col-lg-8 mb-4">
                    <h4 class="text-primary mb-4">
                        <i class="fas fa-newspaper me-2"></i>
                        Son Haberler
                    </h4>
                    <div class="row">
                        <?php if (!empty($recent_posts)): ?>
                            <?php foreach ($recent_posts as $post): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="blog-card">
                                        <div class="card-body">
                                            <div class="blog-meta">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo format_date($post['created_at']); ?>
                                                <span class="ms-3">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <?php echo $post['views']; ?> görüntüleme
                                                </span>
                                            </div>
                                            <h5 class="blog-title"><?php echo sanitize_input($post['title']); ?></h5>
                                            <p class="blog-excerpt">
                                                <?php echo truncate_text(strip_tags($post['content']), 120); ?>
                                            </p>
                                            <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                Devamını Oku <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-secondary">
                                <i class="fas fa-newspaper fa-3x mb-3"></i>
                                <p>Henüz haber bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center">
                        <a href="blog.php" class="btn btn-primary">
                            <i class="fas fa-newspaper me-2"></i>Tüm Haberler
                        </a>
                    </div>
                </div>
                
                <!-- Community Stats -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-4">
                                <i class="fas fa-users me-2"></i>
                                Topluluk İstatistikleri
                            </h5>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Kayıtlı Oyuncu:</span>
                                    <span class="text-primary"><?php echo count($top_players); ?>+</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Aktif Harita:</span>
                                    <span class="text-primary"><?php echo count($server_maps); ?></span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <span>Toplam Kill:</span>
                                    <span class="text-primary"><?php echo number_format($server_stats['total_kills']); ?></span>
                                </div>
                            </div>
                            <a href="https://discord.gg/nostaljigamers" class="btn btn-primary w-100 mb-3" target="_blank">
                                <i class="fab fa-discord me-2"></i>Discord'a Katıl
                            </a>
                            <a href="steam://connect/<?php echo $server_stats['server_ip']; ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-play me-2"></i>Oyuna Katıl
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
        <div class="container">
            <h2 class="section-title">İletişim</h2>
            <p class="section-subtitle">Bizimle iletişime geçin ve topluluğumuzun bir parçası olun</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-server fa-3x text-primary mb-3"></i>
                                    <h5>Sunucu IP</h5>
                                    <p class="text-secondary"><?php echo $server_stats['server_ip']; ?></p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="copyToClipboard('<?php echo $server_stats['server_ip']; ?>')">
                                        <i class="fas fa-copy me-1"></i>Kopyala
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fab fa-discord fa-3x text-primary mb-3"></i>
                                    <h5>Discord</h5>
                                    <p class="text-secondary">Topluluk sohbeti</p>
                                    <a href="https://discord.gg/nostaljigamers" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fab fa-discord me-1"></i>Katıl
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                    <h5>E-posta</h5>
                                    <p class="text-secondary">info@nostaljigamers.com</p>
                                    <a href="mailto:info@nostaljigamers.com" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-envelope me-1"></i>Gönder
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5>
                            <i class="fas fa-crosshairs me-2"></i>
                            Nostalji Gamers
                        </h5>
                        <p class="text-secondary mb-4">
                            Türkiye'nin en iyi Counter-Strike 1.6 GunGame sunucusu. 
                            2010'dan beri hizmetinizdeyiz.
                        </p>
                        <div class="social-links">
                            <a href="https://discord.gg/nostaljigamers" target="_blank">
                                <i class="fab fa-discord"></i>
                            </a>
                            <a href="#" target="_blank">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" target="_blank">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Hızlı Linkler</h5>
                        <a href="#home" class="footer-link">Ana Sayfa</a>
                        <a href="#stats" class="footer-link">İstatistikler</a>
                        <a href="#rules" class="footer-link">Kurallar</a>
                        <a href="#tournaments" class="footer-link">Turnuvalar</a>
                        <a href="files.php" class="footer-link">İndirmeler</a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-section">
                        <h5>Topluluk</h5>
                        <a href="blog.php" class="footer-link">Blog</a>
                        <a href="https://discord.gg/nostaljigamers" class="footer-link" target="_blank">Discord</a>
                        <a href="#" class="footer-link">Forum</a>
                        <a href="#contact" class="footer-link">İletişim</a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5>Sunucu Bilgileri</h5>
                        <p class="text-secondary mb-2">
                            <i class="fas fa-server me-2"></i>
                            IP: <?php echo $server_stats['server_ip']; ?>
                        </p>
                        <p class="text-secondary mb-2">
                            <i class="fas fa-users me-2"></i>
                            Oyuncular: <?php echo $server_stats['current_players']; ?>/<?php echo $server_stats['max_players']; ?>
                        </p>
                        <p class="text-secondary mb-2">
                            <i class="fas fa-map me-2"></i>
                            Harita: <?php echo $server_stats['map_current']; ?>
                        </p>
                        <p class="text-secondary">
                            <i class="fas fa-clock me-2"></i>
                            7/24 Aktif
                        </p>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(0, 255, 136, 0.3);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-secondary mb-0">
                        &copy; 2024 Nostalji Gamers. Tüm hakları saklıdır.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/admin/" class="footer-link">Admin Paneli</a>
                    <span class="text-secondary mx-2">|</span>
                    <span class="text-secondary">v<?php echo SITE_VERSION; ?></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const navbarHeight = document.querySelector('.navbar').offsetHeight;
                    const targetPosition = target.offsetTop - navbarHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Mobile menu auto-close on link click
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            });
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success';
                toast.style.zIndex = '9999';
                toast.innerHTML = '<i class="fas fa-check me-2"></i>IP adresi kopyalandı!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        // Show all weapons modal
        function showAllWeapons() {
            // This would open a modal with all weapons
            alert('Tüm silahlar modalı yakında eklenecek!');
        }

        // Real-time player count update
        function updatePlayerCount() {
            fetch('/api/server-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('current-players').textContent = data.current_players;
                    }
                })
                .catch(error => console.log('Player count update failed:', error));
        }

        // Update player count every 30 seconds
        setInterval(updatePlayerCount, 30000);

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards for animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card, .stat-card, .weapon-item, .tournament-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });

        // Preload critical images
        function preloadImages() {
            const images = [
                '<?php echo $banner['background_image'] ?? ''; ?>'
            ];
            
            images.forEach(src => {
                if (src) {
                    const img = new Image();
                    img.src = src;
                }
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            preloadImages();
        });
    </script>
</body>
</html>
