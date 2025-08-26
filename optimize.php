<?php
// Performance optimizations for Nostalji Gamers website
echo "🔧 Nostalji Gamers Optimization Tool\n";
echo "=====================================\n\n";

// 1. Database optimization
echo "1. Database optimization...\n";
require_once 'config/database.php';

try {
    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_blog_status ON blog_posts(status)",
        "CREATE INDEX IF NOT EXISTS idx_blog_category ON blog_posts(category)",
        "CREATE INDEX IF NOT EXISTS idx_blog_created ON blog_posts(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_player_stats_rank ON player_stats(rank_position)",
        "CREATE INDEX IF NOT EXISTS idx_files_category ON files(category)",
        "CREATE INDEX IF NOT EXISTS idx_files_status ON files(status)",
        "CREATE INDEX IF NOT EXISTS idx_banners_status ON banners(status, order_num)",
        "CREATE INDEX IF NOT EXISTS idx_menu_items_order ON menu_items(order_num, status)",
        "CREATE INDEX IF NOT EXISTS idx_content_blocks_order ON content_blocks(order_num, status)"
    ];
    
    foreach ($indexes as $index) {
        $pdo->exec($index);
    }
    echo "   ✅ Database indexes created\n";
    
    // Vacuum database for SQLite
    $pdo->exec("VACUUM");
    echo "   ✅ Database optimized (VACUUM)\n";
    
} catch (Exception $e) {
    echo "   ❌ Database optimization failed: " . $e->getMessage() . "\n";
}

// 2. Cache directory setup
echo "\n2. Cache setup...\n";
$cache_dirs = [
    'cache',
    'cache/pages',
    'cache/images', 
    'cache/api'
];

foreach ($cache_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "   ✅ Created cache directory: $dir\n";
    }
}

// 3. Generate htaccess for performance
echo "\n3. Apache optimization (.htaccess)...\n";
$htaccess = '# Nostalji Gamers Performance Optimizations
RewriteEngine On

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Cache static files
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
    Header append Cache-Control "public, immutable"
</FilesMatch>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json
</IfModule>

# PHP security
<Files "*.php">
    Order allow,deny
    Allow from all
</Files>

# Protect sensitive files
<FilesMatch "\.(sql|log|conf)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Pretty URLs
RewriteRule ^blog/([^/]+)/?$ blog.php?slug=$1 [L,QSA]
RewriteRule ^page/([^/]+)/?$ page.php?slug=$1 [L,QSA]
';

file_put_contents('.htaccess', $htaccess);
echo "   ✅ .htaccess optimization created\n";

// 4. Image optimization placeholder
echo "\n4. Upload directory optimization...\n";
$upload_dirs = [
    'uploads/images/thumbnails',
    'uploads/files/cache',
    'uploads/temp'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "   ✅ Created upload directory: $dir\n";
    }
}

// Set proper permissions
exec('chmod -R 755 uploads/');
exec('chown -R www-data:www-data uploads/');
echo "   ✅ Upload directory permissions set\n";

// 5. Database maintenance info
echo "\n5. Database statistics...\n";
try {
    $tables = [
        'blog_posts' => 'Blog Posts',
        'player_stats' => 'Player Stats', 
        'files' => 'Files',
        'banners' => 'Banners',
        'users' => 'Users',
        'tournaments' => 'Tournaments'
    ];
    
    foreach ($tables as $table => $name) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "   📊 $name: $count records\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Could not get statistics: " . $e->getMessage() . "\n";
}

echo "\n🎉 Optimization completed!\n";
echo "=====================================\n";
echo "Admin Panel: http://localhost/admin/login.php\n";
echo "Credentials: Xau / 626200\n";
echo "Main Site: http://localhost/index.php\n";
echo "\nRecommendations:\n";
echo "• Monitor server performance regularly\n";
echo "• Keep database records up to date\n";
echo "• Backup database weekly\n";
echo "• Update content regularly for better SEO\n";
?>