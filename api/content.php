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
                $stmt = $pdo->prepare("SELECT * FROM content_blocks WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $content = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $content]);
            } else {
                $stmt = $pdo->query("SELECT * FROM content_blocks ORDER BY order_num ASC");
                $contents = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $contents]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO content_blocks (type, title, content, status, order_num, created_at) VALUES (?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $input['type'],
                $input['title'],
                $input['content'],
                $input['status'],
                $input['order_num']
            ]);
            echo json_encode(['success' => true, 'message' => 'Content block created']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE content_blocks SET type = ?, title = ?, content = ?, status = ?, order_num = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([
                $input['type'],
                $input['title'],
                $input['content'],
                $input['status'],
                $input['order_num'],
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Content block updated']);
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM content_blocks WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Content block deleted']);
            }
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>