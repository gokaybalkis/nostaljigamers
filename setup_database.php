<?php
// Setup SQLite database for Nostalji Gamers CS 1.6 Server
require_once 'config/database.php';

try {
    // Create database tables for SQLite
    $sql = "
    -- Admin Users Table
    CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role VARCHAR(20) DEFAULT 'admin',
        last_login TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'active'
    );

    -- Banners Table
    CREATE TABLE IF NOT EXISTS banners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        subtitle TEXT,
        button_text VARCHAR(100),
        button_url VARCHAR(255),
        background_image VARCHAR(255),
        text_color VARCHAR(7) DEFAULT '#ffffff',
        button_color VARCHAR(7) DEFAULT '#00ff88',
        status VARCHAR(20) DEFAULT 'active',
        order_num INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Blog Posts Table
    CREATE TABLE IF NOT EXISTS blog_posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        excerpt TEXT,
        featured_image VARCHAR(255),
        category VARCHAR(100) DEFAULT 'genel',
        tags TEXT,
        status VARCHAR(20) DEFAULT 'draft',
        views INTEGER DEFAULT 0,
        author_id INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        published_at TIMESTAMP
    );

    -- Content Blocks Table
    CREATE TABLE IF NOT EXISTS content_blocks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        order_num INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Files Table
    CREATE TABLE IF NOT EXISTS files (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_size VARCHAR(20),
        file_type VARCHAR(50),
        category VARCHAR(100) DEFAULT 'genel',
        download_count INTEGER DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        uploaded_by INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Menu Items Table
    CREATE TABLE IF NOT EXISTS menu_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        icon VARCHAR(100),
        target VARCHAR(10) DEFAULT '_self',
        parent_id INTEGER,
        order_num INTEGER DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Pages Table
    CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content TEXT NOT NULL,
        meta_description TEXT,
        meta_keywords TEXT,
        status VARCHAR(20) DEFAULT 'draft',
        template VARCHAR(100) DEFAULT 'default',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Player Stats Table
    CREATE TABLE IF NOT EXISTS player_stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        player_name VARCHAR(100) NOT NULL,
        steam_id VARCHAR(50) UNIQUE,
        total_kills INTEGER DEFAULT 0,
        total_deaths INTEGER DEFAULT 0,
        total_wins INTEGER DEFAULT 0,
        best_level INTEGER DEFAULT 1,
        total_playtime INTEGER DEFAULT 0,
        rank_position INTEGER DEFAULT 0,
        last_seen TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Server Maps Table
    CREATE TABLE IF NOT EXISTS server_maps (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        map_name VARCHAR(100) NOT NULL UNIQUE,
        map_display VARCHAR(150) NOT NULL,
        map_image VARCHAR(255),
        times_played INTEGER DEFAULT 0,
        average_duration INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Server Settings Table
    CREATE TABLE IF NOT EXISTS server_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_name VARCHAR(255) DEFAULT 'Nostalji Gamers GunGame',
        server_ip VARCHAR(50) DEFAULT '213.238.173.12:27015',
        current_players INTEGER DEFAULT 0,
        max_players INTEGER DEFAULT 32,
        server_status INTEGER DEFAULT 1,
        tickrate INTEGER DEFAULT 100,
        ping INTEGER DEFAULT 15,
        uptime REAL DEFAULT 99.90,
        map_current VARCHAR(100) DEFAULT 'gg_simpsons_vs_flanders',
        game_mode VARCHAR(50) DEFAULT 'GunGame',
        total_kills INTEGER DEFAULT 0,
        total_matches INTEGER DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Tournaments Table
    CREATE TABLE IF NOT EXISTS tournaments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_date TIMESTAMP NOT NULL,
        end_date TIMESTAMP,
        prize_pool VARCHAR(100),
        max_participants INTEGER DEFAULT 32,
        current_participants INTEGER DEFAULT 0,
        registration_open INTEGER DEFAULT 1,
        status VARCHAR(20) DEFAULT 'upcoming',
        rules TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Users Table
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        steam_id VARCHAR(50),
        role VARCHAR(20) DEFAULT 'user',
        status VARCHAR(20) DEFAULT 'active',
        is_banned INTEGER DEFAULT 0,
        ban_reason TEXT,
        ban_date TIMESTAMP,
        last_login TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Weapon Progression Table
    CREATE TABLE IF NOT EXISTS weapon_progression (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        level INTEGER NOT NULL UNIQUE,
        weapon_name VARCHAR(50) NOT NULL,
        weapon_display VARCHAR(100) NOT NULL,
        weapon_icon VARCHAR(255),
        is_active INTEGER DEFAULT 1
    );

    -- Galleries Table
    CREATE TABLE IF NOT EXISTS galleries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image_path VARCHAR(255) NOT NULL,
        thumbnail_path VARCHAR(255),
        category VARCHAR(100) DEFAULT 'genel',
        status VARCHAR(20) DEFAULT 'active',
        order_num INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Videos Table
    CREATE TABLE IF NOT EXISTS videos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        video_url VARCHAR(255) NOT NULL,
        thumbnail_url VARCHAR(255),
        video_type VARCHAR(20) DEFAULT 'youtube',
        category VARCHAR(100) DEFAULT 'genel',
        status VARCHAR(20) DEFAULT 'active',
        views INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";

    // Execute the SQL
    $pdo->exec($sql);

    // Insert default data
    $pdo->beginTransaction();

    // Insert Admin User (Xau / 626200)
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO admin_users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Xau', password_hash('626200', PASSWORD_DEFAULT), 'admin@nostaljigamers.com', 'admin', 'active']);

    // Insert Default Banner
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO banners (title, subtitle, button_text, button_url, background_image, text_color, button_color, status, order_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['NOSTALJI GAMERS', 'Ultimate GunGame Deneyimini Yaşa! 26 Silah Seviyesi, Sınırsız Aksiyon', 'SUNUCUYA KATIL', 'steam://connect/213.238.173.12:27015', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80', '#ffffff', '#00ff88', 'active', 1]);

    // Insert Menu Items
    $menu_items = [
        ['Ana Sayfa', '#home', 'fas fa-home', '_self', 1, 'active'],
        ['İstatistikler', '#stats', 'fas fa-chart-line', '_self', 2, 'active'],
        ['Kurallar', '#rules', 'fas fa-book', '_self', 3, 'active'],
        ['Turnuvalar', '#tournaments', 'fas fa-trophy', '_self', 4, 'active'],
        ['İndirmeler', 'files.php', 'fas fa-download', '_self', 5, 'active'],
        ['Galeri', 'gallery.php', 'fas fa-images', '_self', 6, 'active'],
        ['Videolar', 'videos.php', 'fas fa-video', '_self', 7, 'active'],
        ['Blog', 'blog.php', 'fas fa-newspaper', '_self', 8, 'active'],
        ['İletişim', '#contact', 'fas fa-envelope', '_self', 9, 'active']
    ];

    $stmt = $pdo->prepare("INSERT INTO menu_items (title, url, icon, target, order_num, status) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($menu_items as $item) {
        $stmt->execute($item);
    }

    // Insert Content Blocks
    $content_blocks = [
        ['welcome', 'Nostalji Gamers\'a Hoş Geldiniz!', 'Türkiye\'nin en iyi Counter-Strike 1.6 GunGame sunucusu. 26 silah seviyesi, özel haritalar ve aktif topluluk ile unutulmaz deneyimler yaşayın.', 'active', 1],
        ['gungame_info', 'GunGame Nasıl Oynanır?', 'Her öldürme ile bir sonraki silaha geçin. 26 seviyeyi tamamlayıp bıçakla son öldürmeyi yapan kazanır! Hızlı tempolu aksiyon ve strateji bir arada.', 'active', 2],
        ['announcement', 'Önemli Duyuru', 'Yeni turnuva kayıtları başladı! Discord kanalımıza katılarak detaylı bilgi alabilir ve kayıt olabilirsiniz.', 'active', 3]
    ];

    $stmt = $pdo->prepare("INSERT INTO content_blocks (type, title, content, status, order_num) VALUES (?, ?, ?, ?, ?)");
    foreach ($content_blocks as $block) {
        $stmt->execute($block);
    }

    // Insert Server Settings
    $stmt = $pdo->prepare("INSERT INTO server_settings (server_name, server_ip, current_players, max_players, server_status, tickrate, ping, uptime, map_current, game_mode, total_kills, total_matches) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Nostalji Gamers GunGame', '213.238.173.12:27015', 18, 32, 1, 100, 15, 99.90, 'gg_simpsons_vs_flanders', 'GunGame', 125847, 3421]);

    // Insert Weapon Progression
    $weapons = [
        [1, 'glock', 'Glock 18', 1],
        [2, 'usp', 'USP', 1],
        [3, 'p228', 'P228', 1],
        [4, 'deagle', 'Desert Eagle', 1],
        [5, 'elite', 'Dual Elites', 1],
        [6, 'fiveseven', 'Five-Seven', 1],
        [7, 'm3', 'M3 Shotgun', 1],
        [8, 'xm1014', 'XM1014', 1],
        [9, 'mac10', 'MAC-10', 1],
        [10, 'tmp', 'TMP', 1],
        [11, 'mp5navy', 'MP5 Navy', 1],
        [12, 'ump45', 'UMP45', 1],
        [13, 'p90', 'P90', 1],
        [14, 'galil', 'Galil', 1],
        [15, 'famas', 'FAMAS', 1],
        [16, 'ak47', 'AK-47', 1],
        [17, 'm4a1', 'M4A1', 1],
        [18, 'sg552', 'SG552', 1],
        [19, 'aug', 'AUG', 1],
        [20, 'scout', 'Scout', 1],
        [21, 'awp', 'AWP', 1],
        [22, 'g3sg1', 'G3SG1', 1],
        [23, 'sg550', 'SG550', 1],
        [24, 'm249', 'M249', 1],
        [25, 'hegrenade', 'HE Grenade', 1],
        [26, 'knife', 'Knife', 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO weapon_progression (level, weapon_name, weapon_display, is_active) VALUES (?, ?, ?, ?)");
    foreach ($weapons as $weapon) {
        $stmt->execute($weapon);
    }

    // Insert Sample Player Stats
    $players = [
        ['ProGamer_TR', 'STEAM_0:1:12345678', 2847, 1923, 156, 26, 45600, 1],
        ['CS_Legend', 'STEAM_0:0:87654321', 2634, 2011, 142, 26, 42300, 2],
        ['GunMaster', 'STEAM_0:1:11111111', 2456, 1876, 138, 25, 38900, 3],
        ['Headshot_King', 'STEAM_0:0:22222222', 2298, 1945, 129, 24, 36700, 4],
        ['Nostaljici', 'STEAM_0:1:33333333', 2187, 1823, 125, 26, 35200, 5],
        ['OldSchool_Pro', 'STEAM_0:0:44444444', 2098, 1756, 118, 23, 33800, 6],
        ['Turkish_Sniper', 'STEAM_0:1:55555555', 1987, 1698, 112, 22, 32100, 7],
        ['GG_Master', 'STEAM_0:0:66666666', 1876, 1634, 108, 25, 30500, 8]
    ];

    $stmt = $pdo->prepare("INSERT INTO player_stats (player_name, steam_id, total_kills, total_deaths, total_wins, best_level, total_playtime, rank_position, last_seen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))");
    foreach ($players as $player) {
        $stmt->execute($player);
    }

    // Insert Server Maps
    $maps = [
        ['gg_simpsons_vs_flanders', 'Simpsons vs Flanders', 1247, 420, 1],
        ['gg_mario_world', 'Mario World', 1156, 380, 1],
        ['gg_dust2_unlimited', 'Dust2 Unlimited', 1089, 450, 1],
        ['gg_poolparty', 'Pool Party', 987, 360, 1],
        ['gg_aztec_temple', 'Aztec Temple', 876, 390, 1],
        ['gg_office_warfare', 'Office Warfare', 765, 340, 1],
        ['gg_italy_classic', 'Italy Classic', 654, 370, 1],
        ['gg_inferno_heat', 'Inferno Heat', 543, 400, 1]
    ];

    $stmt = $pdo->prepare("INSERT INTO server_maps (map_name, map_display, times_played, average_duration, is_active) VALUES (?, ?, ?, ?, ?)");
    foreach ($maps as $map) {
        $stmt->execute($map);
    }

    // Insert Sample Blog Posts
    $blog_posts = [
        ['GunGame Rehberi: Başlangıç İpuçları', 'gungame-rehberi-baslangic-ipuclari', '<h2>GunGame Nasıl Oynanır?</h2><p>GunGame, Counter-Strike 1.6\'nın en popüler modlarından biridir. Bu rehberde başlangıç seviyesindeki oyuncular için temel ipuçlarını paylaşacağız.</p><h3>Temel Kurallar</h3><p>Her öldürme ile bir sonraki silaha geçersiniz. 26 seviyeyi tamamlayıp bıçakla son öldürmeyi yapan oyunu kazanır.</p>', 'GunGame oynamaya yeni başlayanlar için temel ipuçları ve stratejiler.', 'rehber', 'published', 1247, 'datetime(\'now\')'],
        ['Sunucu Güncellemesi v2.1', 'sunucu-guncellemesi-v21', '<h2>Yeni Özellikler</h2><p>Sunucumuzda önemli güncellemeler yapıldı. Yeni haritalar, gelişmiş anti-cheat sistemi ve performans iyileştirmeleri.</p><ul><li>3 yeni harita eklendi</li><li>Ping optimizasyonu</li><li>Yeni silah sesleri</li></ul>', 'Sunucumuzda yapılan son güncellemeler ve yeni özellikler.', 'duyuru', 'published', 892, 'datetime(\'now\')'],
        ['Turnuva Sonuçları - Mart 2024', 'turnuva-sonuclari-mart-2024', '<h2>Mart Ayı Turnuva Sonuçları</h2><p>Bu ay düzenlenen GunGame turnuvasında heyecan dorukta yaşandı. 64 oyuncunun katıldığı turnuvada şampiyonumuz ProGamer_TR oldu.</p><h3>Final Sonuçları</h3><ol><li>ProGamer_TR - 500 TL</li><li>CS_Legend - 300 TL</li><li>GunMaster - 200 TL</li></ol>', 'Mart ayında düzenlenen turnuvanın sonuçları ve kazananlar.', 'turnuva', 'published', 1456, 'datetime(\'now\')']
    ];

    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, category, status, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($blog_posts as $post) {
        $stmt->execute($post);
    }

    // Insert Sample Tournament
    $stmt = $pdo->prepare("INSERT INTO tournaments (title, description, start_date, prize_pool, max_participants, current_participants, registration_open, status, rules) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Nisan 2024 GunGame Şampiyonası', 'Aylık GunGame turnuvası. Tüm oyuncular katılabilir.', '2024-04-15 20:00:00', '1000 TL', 64, 23, 1, 'upcoming', 'Turnuva kuralları:\n1. Hile yasak\n2. Saygılı davranış\n3. Zamanında katılım\n4. Admin kararları kesindir']);

    $pdo->commit();

    echo "Database setup completed successfully!\n";
    echo "Admin credentials: Xau / 626200\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>