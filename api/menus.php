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
                $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $menu = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $menu]);
            } else {
                $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY order_num ASC");
                $menus = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $menus]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO menu_items (title, url, icon, target, parent_id, order_num, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $input['title'],
                $input['url'],
                $input['icon'],
                $input['target'],
                $input['parent_id'],
                $input['order_num'],
                $input['status']
            ]);
            echo json_encode(['success' => true, 'message' => 'Menu item created']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE menu_items SET title = ?, url = ?, icon = ?, target = ?, parent_id = ?, order_num = ?, status = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([
                $input['title'],
                $input['url'],
                $input['icon'],
                $input['target'],
                $input['parent_id'],
                $input['order_num'],
                $input['status'],
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Menu item updated']);
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Menu item deleted']);
            }
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>