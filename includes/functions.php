<?php
// Core functions for Nostalji Gamers CS 1.6 Server

// Security functions
function sanitize_input($data) {
    if ($data === null) return null;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function clean_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Date and time functions
function format_date($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'az önce';
    if ($time < 3600) return floor($time/60) . ' dakika önce';
    if ($time < 86400) return floor($time/3600) . ' saat önce';
    if ($time < 2592000) return floor($time/86400) . ' gün önce';
    if ($time < 31536000) return floor($time/2592000) . ' ay önce';
    return floor($time/31536000) . ' yıl önce';
}

// Text processing functions
function truncate_text($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

function create_slug($text) {
    // Turkish character replacement
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'i', 'o', 's', 'u'];
    $text = str_replace($turkish, $english, $text);
    
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

// File handling functions
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

function get_file_icon($extension) {
    $icons = [
        'zip' => 'fas fa-file-archive',
        'rar' => 'fas fa-file-archive',
        'exe' => 'fas fa-file-code',
        'cfg' => 'fas fa-file-code',
        'txt' => 'fas fa-file-alt',
        'pdf' => 'fas fa-file-pdf',
        'jpg' => 'fas fa-file-image',
        'jpeg' => 'fas fa-file-image',
        'png' => 'fas fa-file-image',
        'gif' => 'fas fa-file-image',
        'webp' => 'fas fa-file-image',
        'mp3' => 'fas fa-file-audio',
        'wav' => 'fas fa-file-audio',
        'mp4' => 'fas fa-file-video',
        'avi' => 'fas fa-file-video'
    ];
    
    return $icons[strtolower($extension)] ?? 'fas fa-file';
}

// Network functions
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Session management
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

function is_admin() {
    start_secure_session();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin() {
    if (!is_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Database helper functions
function execute_query($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        return false;
    }
}

// GunGame specific functions
function get_weapon_progression($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM weapon_progression ORDER BY level ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Weapon progression error: " . $e->getMessage());
        return [];
    }
}

function get_top_players($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM player_stats ORDER BY rank_position ASC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Top players error: " . $e->getMessage());
        return [];
    }
}

function get_server_stats($pdo) {
    try {
        // Server settings tablosundan ayarları çek
        $stmt = $pdo->query("SELECT * FROM server_settings LIMIT 1");
        $settings = $stmt->fetch();
        
        if (empty($settings)) {
            return [
                'server_name' => 'Nostalji Gamers GunGame',
                'server_ip' => '213.238.173.12:27015',
                'current_players' => 0,
                'max_players' => 32,
                'server_status' => 1,
                'tickrate' => 100,
                'ping' => 15,
                'uptime' => 99.90,
                'map_current' => 'gg_simpsons_vs_flanders',
                'game_mode' => 'GunGame',
                'total_kills' => 0,
                'total_matches' => 0
            ];
        }
        
        return [
            'server_name' => $settings['server_name'] ?? 'Nostalji Gamers',
            'server_ip' => $settings['server_ip'] ?? '213.238.173.12:27015',
            'current_players' => intval($settings['current_players'] ?? 0),
            'max_players' => intval($settings['max_players'] ?? 32),
            'server_status' => intval($settings['server_status'] ?? 1),
            'tickrate' => intval($settings['tickrate'] ?? 100),
            'ping' => intval($settings['ping'] ?? 15),
            'uptime' => floatval($settings['uptime'] ?? 99.90),
            'map_current' => $settings['map_current'] ?? 'gg_simpsons_vs_flanders',
            'game_mode' => $settings['game_mode'] ?? 'GunGame',
            'total_kills' => intval($settings['total_kills'] ?? 0),
            'total_matches' => intval($settings['total_matches'] ?? 0)
        ];
        
    } catch (PDOException $e) {
        error_log("Server stats error: " . $e->getMessage());
        return [
            'server_name' => 'Nostalji Gamers GunGame',
            'server_ip' => '213.238.173.12:27015',
            'current_players' => 0,
            'max_players' => 32,
            'server_status' => 1,
            'tickrate' => 100,
            'ping' => 15,
            'uptime' => 99.90,
            'map_current' => 'gg_simpsons_vs_flanders',
            'game_mode' => 'GunGame',
            'total_kills' => 0,
            'total_matches' => 0
        ];
    }
}

function get_active_tournaments($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM tournaments WHERE status IN ('upcoming', 'active') ORDER BY start_date ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Tournaments error: " . $e->getMessage());
        return [];
    }
}

function get_server_maps($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM server_maps WHERE is_active = 1 ORDER BY times_played DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Server maps error: " . $e->getMessage());
        return [];
    }
}

// Cache functions
function get_cache_key($key) {
    return 'ng_' . md5($key);
}

function set_cache($key, $data, $duration = 300) {
    if (!CACHE_ENABLED) return false;
    
    $cache_file = sys_get_temp_dir() . '/' . get_cache_key($key) . '.cache';
    $cache_data = [
        'data' => $data,
        'expires' => time() + $duration
    ];
    
    return file_put_contents($cache_file, serialize($cache_data)) !== false;
}

function get_cache($key) {
    if (!CACHE_ENABLED) return false;
    
    $cache_file = sys_get_temp_dir() . '/' . get_cache_key($key) . '.cache';
    
    if (!file_exists($cache_file)) {
        return false;
    }
    
    $cache_data = unserialize(file_get_contents($cache_file));
    
    if ($cache_data['expires'] < time()) {
        unlink($cache_file);
        return false;
    }
    
    return $cache_data['data'];
}

// Meta tag generation
function generate_meta_tags($title, $description = '', $keywords = '', $image = '') {
    $site_title = 'Nostalji Gamers - GunGame Server';
    $full_title = $title ? $title . ' - ' . $site_title : $site_title;
    
    echo '<title>' . sanitize_input($full_title) . '</title>' . "\n";
    echo '<meta name="description" content="' . sanitize_input($description) . '">' . "\n";
    echo '<meta name="keywords" content="' . sanitize_input($keywords) . '">' . "\n";
    echo '<meta property="og:title" content="' . sanitize_input($full_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . sanitize_input($description) . '">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    
    if ($image) {
        echo '<meta property="og:image" content="' . sanitize_input($image) . '">' . "\n";
    }
}

// Logging function
function log_error($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $ip = get_client_ip();
    $log_message = "[$timestamp] [$ip] $message" . PHP_EOL;
    
    error_log($log_message, 3, $file);
}

// Pagination function
function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'items_per_page' => $items_per_page,
        'total_items' => $total_items
    ];
}

// Safe redirect function
function safe_redirect($url, $default = '/') {
    $parsed_url = parse_url($url);
    if (isset($parsed_url['host']) && $parsed_url['host'] !== $_SERVER['HTTP_HOST']) {
        $url = $default;
    }
    
    header('Location: ' . $url);
    exit;
}

// File upload function
function handle_file_upload($file, $upload_dir = 'uploads/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Geçersiz dosya parametresi.'];
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'Dosya seçilmedi.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'Dosya boyutu çok büyük.'];
        default:
            return ['success' => false, 'message' => 'Bilinmeyen hata.'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Dosya boyutu ' . format_file_size(MAX_FILE_SIZE) . ' limitini aşıyor.'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Dosya türü desteklenmiyor.'];
    }
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = sprintf('%s_%s.%s',
        uniqid(),
        bin2hex(random_bytes(8)),
        $extension
    );
    
    $filepath = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Dosya yüklenemedi.'];
    }
    
    return [
        'success' => true,
        'filename' => $filename,
        'filepath' => $filepath,
        'size' => format_file_size($file['size']),
        'type' => $mime
    ];
}

// Image resize function
function resize_image($source, $destination, $max_width = 800, $max_height = 600, $quality = 85) {
    $info = getimagesize($source);
    if (!$info) return false;
    
    list($width, $height, $type) = $info;
    
    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // Create image resources
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $dst = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save image
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dst, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dst, $destination);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dst, $destination);
            break;
    }
    
    imagedestroy($src);
    imagedestroy($dst);
    
    return $result;
}
?>
