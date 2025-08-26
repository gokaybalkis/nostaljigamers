<?php
require_once 'config/database.php';

echo "<h1>Banner Test Sayfası</h1>";

try {
    // Banner verilerini kontrol et
    $stmt = $pdo->query("SELECT * FROM banners");
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Mevcut Banner'lar:</h2>";
    if (empty($banners)) {
        echo "<p style='color: red;'>Hiç banner bulunamadı!</p>";
        
        // Banner ekle
        $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, button_text, button_url, background_image, text_color, button_color, status, order_num) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'CS 1.6 TURKEY SERVER ✨',
            'Efsanevi Counter-Strike 1.6 deneyimini yaşayın! 🎮',
            'SUNUCUYA KATIL 🚀',
            '#',
            'https://images.unsplash.com/photo-1542751371-adc38448a05e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
            '#ffffff',
            '#00f5ff',
            'active',
            1
        ]);
        
        if ($result) {
            echo "<p style='color: green;'>Banner başarıyla eklendi!</p>";
        } else {
            echo "<p style='color: red;'>Banner eklenirken hata oluştu!</p>";
        }
    } else {
        foreach ($banners as $banner) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<h3>ID: " . $banner['id'] . "</h3>";
            echo "<p><strong>Başlık:</strong> " . htmlspecialchars($banner['title']) . "</p>";
            echo "<p><strong>Alt Başlık:</strong> " . htmlspecialchars($banner['subtitle']) . "</p>";
            echo "<p><strong>Buton:</strong> " . htmlspecialchars($banner['button_text']) . "</p>";
            echo "<p><strong>Durum:</strong> " . $banner['status'] . "</p>";
            echo "</div>";
        }
    }
    
    // Menü verilerini kontrol et
    echo "<h2>Mevcut Menüler:</h2>";
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY order_num");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($menus)) {
        echo "<p style='color: red;'>Hiç menü bulunamadı!</p>";
    } else {
        foreach ($menus as $menu) {
            echo "<div style='border: 1px solid #ccc; padding: 5px; margin: 5px 0;'>";
            echo "<strong>" . htmlspecialchars($menu['title']) . "</strong> - " . htmlspecialchars($menu['url']);
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<p><a href='index.php'>Ana Sayfaya Dön</a> | <a href='admin/content-manager.php'>Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?>
