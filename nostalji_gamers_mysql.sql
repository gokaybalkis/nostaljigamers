-- Nostalji Gamers MySQL Database Export
-- cPanel için hazırlanmış
-- Kullanım: phpMyAdmin'de bu dosyayı import edin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database charset
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Admin Users Table
--
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Banners Table
--
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` text,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(255) DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `text_color` varchar(7) DEFAULT '#ffffff',
  `button_color` varchar(7) DEFAULT '#00ff88',
  `status` varchar(20) DEFAULT 'active',
  `order_num` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_banners_status` (`status`,`order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Blog Posts Table
--
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text,
  `featured_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'genel',
  `tags` text,
  `status` varchar(20) DEFAULT 'draft',
  `views` int(11) DEFAULT '0',
  `author_id` int(11) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_blog_status` (`status`),
  KEY `idx_blog_category` (`category`),
  KEY `idx_blog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Content Blocks Table
--
CREATE TABLE `content_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `order_num` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_content_blocks_order` (`order_num`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Files Table
--
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'genel',
  `download_count` int(11) DEFAULT '0',
  `status` varchar(20) DEFAULT 'active',
  `uploaded_by` int(11) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_files_category` (`category`),
  KEY `idx_files_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Galleries Table
--
CREATE TABLE `galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `image_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'genel',
  `status` varchar(20) DEFAULT 'active',
  `order_num` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Menu Items Table
--
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `target` varchar(10) DEFAULT '_self',
  `parent_id` int(11) DEFAULT NULL,
  `order_num` int(11) DEFAULT '0',
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_menu_items_order` (`order_num`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Pages Table
--
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `status` varchar(20) DEFAULT 'draft',
  `template` varchar(100) DEFAULT 'default',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Player Stats Table
--
CREATE TABLE `player_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_name` varchar(100) NOT NULL,
  `steam_id` varchar(50) DEFAULT NULL,
  `total_kills` int(11) DEFAULT '0',
  `total_deaths` int(11) DEFAULT '0',
  `total_wins` int(11) DEFAULT '0',
  `best_level` int(11) DEFAULT '1',
  `total_playtime` int(11) DEFAULT '0',
  `rank_position` int(11) DEFAULT '0',
  `last_seen` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `steam_id` (`steam_id`),
  KEY `idx_player_stats_rank` (`rank_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Server Maps Table
--
CREATE TABLE `server_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `map_name` varchar(100) NOT NULL,
  `map_display` varchar(150) NOT NULL,
  `map_image` varchar(255) DEFAULT NULL,
  `times_played` int(11) DEFAULT '0',
  `average_duration` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `map_name` (`map_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Server Settings Table
--
CREATE TABLE `server_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_name` varchar(255) DEFAULT 'Nostalji Gamers GunGame',
  `server_ip` varchar(50) DEFAULT '213.238.173.12:27015',
  `current_players` int(11) DEFAULT '0',
  `max_players` int(11) DEFAULT '32',
  `server_status` tinyint(1) DEFAULT '1',
  `tickrate` int(11) DEFAULT '100',
  `ping` int(11) DEFAULT '15',
  `uptime` decimal(5,2) DEFAULT '99.90',
  `map_current` varchar(100) DEFAULT 'gg_simpsons_vs_flanders',
  `game_mode` varchar(50) DEFAULT 'GunGame',
  `total_kills` int(11) DEFAULT '0',
  `total_matches` int(11) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tournaments Table
--
CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `prize_pool` varchar(100) DEFAULT NULL,
  `max_participants` int(11) DEFAULT '32',
  `current_participants` int(11) DEFAULT '0',
  `registration_open` tinyint(1) DEFAULT '1',
  `status` varchar(20) DEFAULT 'upcoming',
  `rules` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Users Table
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `steam_id` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active',
  `is_banned` tinyint(1) DEFAULT '0',
  `ban_reason` text,
  `ban_date` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Videos Table
--
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `video_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `video_type` varchar(20) DEFAULT 'youtube',
  `category` varchar(100) DEFAULT 'genel',
  `status` varchar(20) DEFAULT 'active',
  `views` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Weapon Progression Table
--
CREATE TABLE `weapon_progression` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  `weapon_name` varchar(50) NOT NULL,
  `weapon_display` varchar(100) NOT NULL,
  `weapon_icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Insert default data
--

-- Admin User (Xau / 626200)
INSERT INTO `admin_users` (`username`, `password`, `email`, `role`, `status`) VALUES
('Xau', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@nostaljigamers.com', 'admin', 'active');

-- Default Banner
INSERT INTO `banners` (`title`, `subtitle`, `button_text`, `button_url`, `background_image`, `text_color`, `button_color`, `status`, `order_num`) VALUES
('NOSTALJI GAMERS', 'Ultimate GunGame Deneyimini Yaşa! 26 Silah Seviyesi, Sınırsız Aksiyon', 'SUNUCUYA KATIL', 'steam://connect/213.238.173.12:27015', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80', '#ffffff', '#00ff88', 'active', 1);

-- Menu Items
INSERT INTO `menu_items` (`title`, `url`, `icon`, `target`, `order_num`, `status`) VALUES
('Ana Sayfa', '#home', 'fas fa-home', '_self', 1, 'active'),
('İstatistikler', '#stats', 'fas fa-chart-line', '_self', 2, 'active'),
('Kurallar', '#rules', 'fas fa-book', '_self', 3, 'active'),
('Turnuvalar', '#tournaments', 'fas fa-trophy', '_self', 4, 'active'),
('İndirmeler', 'files.php', 'fas fa-download', '_self', 5, 'active'),
('Galeri', 'gallery.php', 'fas fa-images', '_self', 6, 'active'),
('Videolar', 'videos.php', 'fas fa-video', '_self', 7, 'active'),
('Blog', 'blog.php', 'fas fa-newspaper', '_self', 8, 'active'),
('İletişim', '#contact', 'fas fa-envelope', '_self', 9, 'active');

-- Content Blocks
INSERT INTO `content_blocks` (`type`, `title`, `content`, `status`, `order_num`) VALUES
('welcome', 'Nostalji Gamers\'a Hoş Geldiniz!', 'Türkiye\'nin en iyi Counter-Strike 1.6 GunGame sunucusu. 26 silah seviyesi, özel haritalar ve aktif topluluk ile unutulmaz deneyimler yaşayın.', 'active', 1),
('gungame_info', 'GunGame Nasıl Oynanır?', 'Her öldürme ile bir sonraki silaha geçin. 26 seviyeyi tamamlayıp bıçakla son öldürmeyi yapan kazanır! Hızlı tempolu aksiyon ve strateji bir arada.', 'active', 2),
('announcement', 'Önemli Duyuru', 'Yeni turnuva kayıtları başladı! Discord kanalımıza katılarak detaylı bilgi alabilir ve kayıt olabilirsiniz.', 'active', 3);

-- Server Settings
INSERT INTO `server_settings` (`server_name`, `server_ip`, `current_players`, `max_players`, `server_status`, `tickrate`, `ping`, `uptime`, `map_current`, `game_mode`, `total_kills`, `total_matches`) VALUES
('Nostalji Gamers GunGame', '213.238.173.12:27015', 18, 32, 1, 100, 15, 99.90, 'gg_simpsons_vs_flanders', 'GunGame', 125847, 3421);

-- Weapon Progression
INSERT INTO `weapon_progression` (`level`, `weapon_name`, `weapon_display`, `is_active`) VALUES
(1, 'glock', 'Glock 18', 1),
(2, 'usp', 'USP', 1),
(3, 'p228', 'P228', 1),
(4, 'deagle', 'Desert Eagle', 1),
(5, 'elite', 'Dual Elites', 1),
(6, 'fiveseven', 'Five-Seven', 1),
(7, 'm3', 'M3 Shotgun', 1),
(8, 'xm1014', 'XM1014', 1),
(9, 'mac10', 'MAC-10', 1),
(10, 'tmp', 'TMP', 1),
(11, 'mp5navy', 'MP5 Navy', 1),
(12, 'ump45', 'UMP45', 1),
(13, 'p90', 'P90', 1),
(14, 'galil', 'Galil', 1),
(15, 'famas', 'FAMAS', 1),
(16, 'ak47', 'AK-47', 1),
(17, 'm4a1', 'M4A1', 1),
(18, 'sg552', 'SG552', 1),
(19, 'aug', 'AUG', 1),
(20, 'scout', 'Scout', 1),
(21, 'awp', 'AWP', 1),
(22, 'g3sg1', 'G3SG1', 1),
(23, 'sg550', 'SG550', 1),
(24, 'm249', 'M249', 1),
(25, 'hegrenade', 'HE Grenade', 1),
(26, 'knife', 'Knife', 1);

-- Sample Player Stats
INSERT INTO `player_stats` (`player_name`, `steam_id`, `total_kills`, `total_deaths`, `total_wins`, `best_level`, `total_playtime`, `rank_position`, `last_seen`) VALUES
('ProGamer_TR', 'STEAM_0:1:12345678', 2847, 1923, 156, 26, 45600, 1, NOW()),
('CS_Legend', 'STEAM_0:0:87654321', 2634, 2011, 142, 26, 42300, 2, NOW()),
('GunMaster', 'STEAM_0:1:11111111', 2456, 1876, 138, 25, 38900, 3, NOW()),
('Headshot_King', 'STEAM_0:0:22222222', 2298, 1945, 129, 24, 36700, 4, NOW()),
('Nostaljici', 'STEAM_0:1:33333333', 2187, 1823, 125, 26, 35200, 5, NOW()),
('OldSchool_Pro', 'STEAM_0:0:44444444', 2098, 1756, 118, 23, 33800, 6, NOW()),
('Turkish_Sniper', 'STEAM_0:1:55555555', 1987, 1698, 112, 22, 32100, 7, NOW()),
('GG_Master', 'STEAM_0:0:66666666', 1876, 1634, 108, 25, 30500, 8, NOW());

-- Server Maps
INSERT INTO `server_maps` (`map_name`, `map_display`, `times_played`, `average_duration`, `is_active`) VALUES
('gg_simpsons_vs_flanders', 'Simpsons vs Flanders', 1247, 420, 1),
('gg_mario_world', 'Mario World', 1156, 380, 1),
('gg_dust2_unlimited', 'Dust2 Unlimited', 1089, 450, 1),
('gg_poolparty', 'Pool Party', 987, 360, 1),
('gg_aztec_temple', 'Aztec Temple', 876, 390, 1),
('gg_office_warfare', 'Office Warfare', 765, 340, 1),
('gg_italy_classic', 'Italy Classic', 654, 370, 1),
('gg_inferno_heat', 'Inferno Heat', 543, 400, 1);

-- Sample Blog Posts
INSERT INTO `blog_posts` (`title`, `slug`, `content`, `excerpt`, `category`, `status`, `views`, `published_at`) VALUES
('GunGame Rehberi: Başlangıç İpuçları', 'gungame-rehberi-baslangic-ipuclari', '<h2>GunGame Nasıl Oynanır?</h2><p>GunGame, Counter-Strike 1.6\'nın en popüler modlarından biridir. Bu rehberde başlangıç seviyesindeki oyuncular için temel ipuçlarını paylaşacağız.</p><h3>Temel Kurallar</h3><p>Her öldürme ile bir sonraki silaha geçersiniz. 26 seviyeyi tamamlayıp bıçakla son öldürmeyi yapan oyunu kazanır.</p>', 'GunGame oynamaya yeni başlayanlar için temel ipuçları ve stratejiler.', 'rehber', 'published', 1247, NOW()),
('Sunucu Güncellemesi v2.1', 'sunucu-guncellemesi-v21', '<h2>Yeni Özellikler</h2><p>Sunucumuzda önemli güncellemeler yapıldı. Yeni haritalar, gelişmiş anti-cheat sistemi ve performans iyileştirmeleri.</p><ul><li>3 yeni harita eklendi</li><li>Ping optimizasyonu</li><li>Yeni silah sesleri</li></ul>', 'Sunucumuzda yapılan son güncellemeler ve yeni özellikler.', 'duyuru', 'published', 892, NOW()),
('Turnuva Sonuçları - Mart 2024', 'turnuva-sonuclari-mart-2024', '<h2>Mart Ayı Turnuva Sonuçları</h2><p>Bu ay düzenlenen GunGame turnuvasında heyecan dorukta yaşandı. 64 oyuncunun katıldığı turnuvada şampiyonumuz ProGamer_TR oldu.</p><h3>Final Sonuçları</h3><ol><li>ProGamer_TR - 500 TL</li><li>CS_Legend - 300 TL</li><li>GunMaster - 200 TL</li></ol>', 'Mart ayında düzenlenen turnuvanın sonuçları ve kazananlar.', 'turnuva', 'published', 1456, NOW());

-- Sample Tournament
INSERT INTO `tournaments` (`title`, `description`, `start_date`, `prize_pool`, `max_participants`, `current_participants`, `registration_open`, `status`, `rules`) VALUES
('Nisan 2024 GunGame Şampiyonası', 'Aylık GunGame turnuvası. Tüm oyuncular katılabilir.', '2024-04-15 20:00:00', '1000 TL', 64, 23, 1, 'upcoming', 'Turnuva kuralları:\n1. Hile yasak\n2. Saygılı davranış\n3. Zamanında katılım\n4. Admin kararları kesindir');

-- Reset AUTO_INCREMENT values
ALTER TABLE `admin_users` AUTO_INCREMENT = 2;
ALTER TABLE `banners` AUTO_INCREMENT = 2;
ALTER TABLE `blog_posts` AUTO_INCREMENT = 4;
ALTER TABLE `content_blocks` AUTO_INCREMENT = 4;
ALTER TABLE `files` AUTO_INCREMENT = 1;
ALTER TABLE `galleries` AUTO_INCREMENT = 1;
ALTER TABLE `menu_items` AUTO_INCREMENT = 10;
ALTER TABLE `pages` AUTO_INCREMENT = 1;
ALTER TABLE `player_stats` AUTO_INCREMENT = 9;
ALTER TABLE `server_maps` AUTO_INCREMENT = 9;
ALTER TABLE `server_settings` AUTO_INCREMENT = 2;
ALTER TABLE `tournaments` AUTO_INCREMENT = 2;
ALTER TABLE `users` AUTO_INCREMENT = 1;
ALTER TABLE `videos` AUTO_INCREMENT = 1;
ALTER TABLE `weapon_progression` AUTO_INCREMENT = 27;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;