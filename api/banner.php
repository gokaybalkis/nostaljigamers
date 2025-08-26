<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $banner = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $banner]);
            } else {
                $stmt = $pdo->query("SELECT * FROM banners ORDER BY order_num ASC");
                $banners = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $banners]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO banners (title, subtitle, button_text, button_url, background_image, text_color, button_color, status, order_num, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $input['title'],
                $input['subtitle'],
                $input['button_text'],
                $input['button_url'],
                $input['background_image'],
                $input['text_color'],
                $input['button_color'],
                $input['status'],
                $input['order_num']
            ]);
            echo json_encode(['success' => true, 'message' => 'Banner created']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE banners SET title = ?, subtitle = ?, button_text = ?, button_url = ?, background_image = ?, text_color = ?, button_color = ?, status = ?, order_num = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([
                $input['title'],
                $input['subtitle'],
                $input['button_text'],
                $input['button_url'],
                $input['background_image'],
                $input['text_color'],
                $input['button_color'],
                $input['status'],
                $input['order_num'],
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Banner updated']);
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Banner deleted']);
            }
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>