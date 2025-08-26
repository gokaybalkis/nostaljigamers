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
                $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $page = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $page]);
            } else {
                $stmt = $pdo->query("SELECT * FROM pages ORDER BY created_at DESC");
                $pages = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $pages]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, meta_description, meta_keywords, status, template, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
            $stmt->execute([
                $input['title'],
                create_slug($input['title']),
                $input['content'],
                $input['meta_description'],
                $input['meta_keywords'],
                $input['status'],
                $input['template']
            ]);
            echo json_encode(['success' => true, 'message' => 'Page created']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, meta_description = ?, meta_keywords = ?, status = ?, template = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([
                $input['title'],
                create_slug($input['title']),
                $input['content'],
                $input['meta_description'],
                $input['meta_keywords'],
                $input['status'],
                $input['template'],
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Page updated']);
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Page deleted']);
            }
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>