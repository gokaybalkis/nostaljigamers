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
                $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $post = $stmt->fetch();
                
                if ($post) {
                    echo json_encode(['success' => true, 'data' => $post]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Blog post not found']);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
                $posts = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $posts]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, category, tags, status, featured_image, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))");
            $stmt->execute([
                $input['title'],
                create_slug($input['title']),
                $input['content'],
                $input['excerpt'],
                $input['category'],
                $input['tags'],
                $input['status'],
                $input['featured_image']
            ]);
            echo json_encode(['success' => true, 'message' => 'Blog post created']);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, category = ?, tags = ?, status = ?, featured_image = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([
                $input['title'],
                create_slug($input['title']),
                $input['content'],
                $input['excerpt'],
                $input['category'],
                $input['tags'],
                $input['status'],
                $input['featured_image'],
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Blog post updated']);
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Blog post deleted']);
            }
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>