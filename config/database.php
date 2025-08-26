<?php
// MySQL Database configuration for Nostalji Gamers CS 1.6 Server
// cPanel için hazırlanmış MySQL bağlantısı

// MySQL Database Settings - cPanel değerlerinizi buraya girin
define('DB_HOST', 'localhost');          // MySQL sunucu adresi (genellikle localhost)
define('DB_NAME', 'your_db_name');       // MySQL veritabanı adı (cPanel'de oluşturduğunuz)
define('DB_USER', 'your_db_user');       // MySQL kullanıcı adı
define('DB_PASS', 'your_db_password');   // MySQL şifresi
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Nostalji Gamers');
define('SITE_URL', 'https://yourdomain.com'); // Kendi domain'inizi yazın
define('SITE_VERSION', '2.1.0');

// Security settings
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_EXPIRE', 1800); // 30 minutes

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 300); // 5 minutes

// File upload settings
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('UPLOAD_PATH', 'uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'rar', 'exe', 'cfg', 'txt', 'pdf']);

// MySQL Database connection with error handling
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Log the error
    error_log("MySQL connection failed: " . $e->getMessage());
    
    // Show user-friendly error in development
    if (defined('DEBUG') && DEBUG) {
        die("MySQL connection failed: " . $e->getMessage());
    } else {
        die("Site temporarily unavailable. Please try again later.");
    }
}

// Set timezone
date_default_timezone_set('Europe/Istanbul');

// Start output buffering
ob_start();

// Error reporting based on environment
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'error.log');
}
?>
