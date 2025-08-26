<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

// Oturum zaman aşımı kontrolü
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > ADMIN_SESSION_TIMEOUT) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Oturum zamanını güncelle
$_SESSION['login_time'] = time();

// İstatistikleri al
try {
    $stats = [];
    
    // Blog yazı sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'");
    $stats['blog_posts'] = $stmt->fetch()['count'];
    
    // Dosya sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM files WHERE status = 'active'");
    $stats['files'] = $stmt->fetch()['count'];
    
    // Oyuncu sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM player_stats");
    $stats['players'] = $stmt->fetch()['count'];
    
    // Turnuva sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tournaments WHERE status IN ('upcoming', 'active')");
    $stats['tournaments'] = $stmt->fetch()['count'];
    
    // Galeri sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM galleries WHERE status = 'active'");
    $stats['galleries'] = $stmt->fetch()['count'];
    
    // Video sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM videos WHERE status = 'active'");
    $stats['videos'] = $stmt->fetch()['count'];
    
    // Kullanıcı sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $stats['users'] = $stmt->fetch()['count'];
    
    // Sunucu istatistikleri
    $server_stats = get_server_stats($pdo);
    
    // Son aktiviteler
    $stmt = $pdo->query("SELECT title, created_at, 'blog' as type FROM blog_posts WHERE status = 'published' 
                        UNION ALL 
                        SELECT title, created_at, 'tournament' as type FROM tournaments WHERE status IN ('upcoming', 'active')
                        ORDER BY created_at DESC LIMIT 10");
    $recent_activities = $stmt->fetchAll();
    
    // En iyi oyuncular
    $top_players = get_top_players($pdo, 5);
    
} catch (PDOException $e) {
    log_error("Admin dashboard error: " . $e->getMessage());
    $stats = ['blog_posts' => 0, 'files' => 0, 'players' => 0, 'tournaments' => 0, 'galleries' => 0, 'videos' => 0, 'users' => 0];
    $server_stats = [];
    $recent_activities = [];
    $top_players = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nostalji Gamers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-card) 50%, var(--dark-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-surface) 100%);
            min-height: 100vh;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 2px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .sidebar-brand {
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--primary-color);
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: var(--text-secondary);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-bar {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary-color);
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-bg);
            font-weight: 700;
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
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(0, 102, 255, 0.1));
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 255, 136, 0.3);
        }
        
        .stat-number {
            font-family: 'Orbitron', monospace;
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-color);
            display: block;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
            font-weight: 600;
        }
        
        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        /* Activity Feed */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }
        
        .activity-item:hover {
            background: rgba(0, 255, 136, 0.05);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-bg);
            margin-right: 1rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        /* Buttons */
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
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: var(--dark-bg);
            transform: translateY(-2px);
        }
        
        /* Server Status */
        .server-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-online {
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(0, 255, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 1.5rem;
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
        
        /* Table Styles */
        .table {
            color: var(--text-primary);
        }
        
        .table th {
            border-color: rgba(0, 255, 136, 0.3);
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .table td {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .table-hover tbody tr:hover {
            background: rgba(0, 255, 136, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-crosshairs me-2"></i>
                NOSTALJI ADMIN
            </div>
            <div class="text-secondary mt-2">Yönetim Paneli</div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="content-manager.php" class="nav-link">
                    <i class="fas fa-edit"></i>
                    İçerik Yöneticisi
                </a>
            </div>
            <div class="nav-item">
                <a href="blog-manager.php" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    Blog Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="user-manager.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    Kullanıcı Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="tournament-manager.php" class="nav-link">
                    <i class="fas fa-trophy"></i>
                    Turnuva Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="file-manager.php" class="nav-link">
                    <i class="fas fa-download"></i>
                    Dosya Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="gallery-manager.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    Galeri Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="video-manager.php" class="nav-link">
                    <i class="fas fa-video"></i>
                    Video Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="server-settings.php" class="nav-link">
                    <i class="fas fa-server"></i>
                    Sunucu Ayarları
                </a>
            </div>
            <div class="nav-item">
                <a href="menu-manager.php" class="nav-link">
                    <i class="fas fa-bars"></i>
                    Menü Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="../index.php" class="nav-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Siteyi Görüntüle
                </a>
            </div>
            <hr style="border-color: rgba(0, 255, 136, 0.3); margin: 1rem;">
            <div class="nav-item">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Çıkış Yap
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary d-md-none me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="user-info">
                <div class="server-status">
                    <div class="status-online"></div>
                    <span class="text-secondary">Server Online</span>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo sanitize_input($_SESSION['admin_username']); ?></div>
                    <div class="text-secondary small">Administrator</div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number"><?php echo number_format($stats['players']); ?></span>
                    <div class="stat-label">Kayıtlı Oyuncu</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['blog_posts']; ?></span>
                    <div class="stat-label">Blog Yazısı</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['tournaments']; ?></span>
                    <div class="stat-label">Aktif Turnuva</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['files']; ?></span>
                    <div class="stat-label">Dosya</div>
                </div>
            </div>
        </div>
        
        <!-- Additional Stats Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['galleries']; ?></span>
                    <div class="stat-label">Galeri Resmi</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['videos']; ?></span>
                    <div class="stat-label">Video</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <span class="stat-number"><?php echo $stats['users']; ?></span>
                    <div class="stat-label">Üye</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-crosshairs"></i>
                    </div>
                    <span class="stat-number"><?php echo number_format($server_stats['total_kills'] ?? 0); ?></span>
                    <div class="stat-label">Toplam Kill</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Server Status -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-primary mb-4">
                            <i class="fas fa-server me-2"></i>
                            Sunucu Durumu
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-secondary">Sunucu IP:</label>
                                    <div class="fw-bold text-primary"><?php echo $server_stats['server_ip'] ?? 'N/A'; ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-secondary">Aktif Oyuncu:</label>
                                    <div class="fw-bold">
                                        <span class="text-primary"><?php echo $server_stats['current_players'] ?? 0; ?></span>
                                        <span class="text-secondary">/ <?php echo $server_stats['max_players'] ?? 32; ?></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-secondary">Aktif Harita:</label>
                                    <div class="fw-bold text-primary"><?php echo $server_stats['map_current'] ?? 'N/A'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-secondary">Ping:</label>
                                    <div class="fw-bold text-primary"><?php echo $server_stats['ping'] ?? 0; ?>ms</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-secondary">Uptime:</label>
                                    <div class="fw-bold text-primary"><?php echo $server_stats['uptime'] ?? 0; ?>%</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-secondary">Toplam Maç:</label>
                                    <div class="fw-bold text-primary"><?php echo number_format($server_stats['total_matches'] ?? 0); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-primary me-2" onclick="refreshServerStats()">
                                <i class="fas fa-sync-alt me-2"></i>Yenile
                            </button>
                            <button class="btn btn-outline-primary" onclick="restartServer()">
                                <i class="fas fa-power-off me-2"></i>Sunucuyu Yeniden Başlat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-primary mb-4">
                            <i class="fas fa-bolt me-2"></i>
                            Hızlı İşlemler
                        </h5>
                        <div class="d-grid gap-3">
                            <a href="content-manager.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Yeni İçerik Ekle
                            </a>
                            <a href="blog-manager.php" class="btn btn-outline-primary">
                                <i class="fas fa-newspaper me-2"></i>Blog Yazısı Ekle
                            </a>
                            <a href="tournament-manager.php" class="btn btn-outline-primary">
                                <i class="fas fa-trophy me-2"></i>Turnuva Oluştur
                            </a>
                            <a href="user-manager.php" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i>Kullanıcı Yönet
                            </a>
                            <button class="btn btn-outline-primary" onclick="backupDatabase()">
                                <i class="fas fa-database me-2"></i>Veritabanı Yedekle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Top Players -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-primary mb-4">
                            <i class="fas fa-crown me-2"></i>
                            En İyi Oyuncular
                        </h5>
                        <?php if (!empty($top_players)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sıra</th>
                                            <th>Oyuncu</th>
                                            <th>Kill</th>
                                            <th>Seviye</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_players as $index => $player): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-primary">#<?php echo $index + 1; ?></span>
                                                </td>
                                                <td><?php echo sanitize_input($player['player_name']); ?></td>
                                                <td><?php echo number_format($player['total_kills']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $player['best_level']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-secondary py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>Henüz oyuncu verisi bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-primary mb-4">
                            <i class="fas fa-clock me-2"></i>
                            Son Aktiviteler
                        </h5>
                        <?php if (!empty($recent_activities)): ?>
                            <div class="activity-feed">
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-<?php echo $activity['type'] === 'blog' ? 'newspaper' : 'trophy'; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title"><?php echo sanitize_input($activity['title']); ?></div>
                                            <div class="activity-time"><?php echo time_ago($activity['created_at']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-secondary py-4">
                                <i class="fas fa-clock fa-3x mb-3"></i>
                                <p>Henüz aktivite bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Auto-refresh server stats every 30 seconds
        setInterval(refreshServerStats, 30000);
        
        function refreshServerStats() {
            // Add loading state
            const refreshBtn = document.querySelector('button[onclick="refreshServerStats()"]');
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<div class="loading"></div> Yenileniyor...';
            refreshBtn.disabled = true;
            
            fetch('/api/server-status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update server stats in the UI
                        location.reload(); // Simple refresh for now
                    }
                })
                .catch(error => {
                    console.error('Server stats refresh failed:', error);
                })
                .finally(() => {
                    refreshBtn.innerHTML = originalText;
                    refreshBtn.disabled = false;
                });
        }
        
        function restartServer() {
            if (confirm('Sunucuyu yeniden başlatmak istediğinizden emin misiniz? Bu işlem tüm oyuncuları sunucudan atacaktır.')) {
                // Implement server restart functionality
                alert('Sunucu yeniden başlatma özelliği yakında eklenecek!');
            }
        }
        
        function backupDatabase() {
            if (confirm('Veritabanı yedeği oluşturulsun mu?')) {
                // Implement database backup functionality
                alert('Veritabanı yedekleme özelliği yakında eklenecek!');
            }
        }
        
        // Real-time notifications (placeholder)
        function checkNotifications() {
            // This would check for new notifications
            console.log('Checking for notifications...');
        }
        
        // Check notifications every minute
        setInterval(checkNotifications, 60000);
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
