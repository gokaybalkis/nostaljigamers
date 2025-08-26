<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Tek dosya getir
                $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $file = $stmt->fetch();
                
                if ($file) {
                    echo json_encode($file);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'File not found']);
                }
            } else {
                // Tüm dosyaları getir
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
                $offset = ($page - 1) * $limit;
                
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                
                $where_conditions = [];
                $params = [];
                
                if ($search) {
                    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                
                if ($category) {
                    $where_conditions[] = "category = ?";
                    $params[] = $category;
                }
                
                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                
                // Toplam sayı
                $count_query = "SELECT COUNT(*) as total FROM files $where_clause";
                $stmt = $pdo->prepare($count_query);
                $stmt->execute($params);
                $total = $stmt->fetch()['total'];
                
                // Dosyalar
                $query = "SELECT * FROM files $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $files = $stmt->fetchAll();
                
                echo json_encode([
                    'files' => $files,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]);
            }
            break;
            
        case 'POST':
            if (isset($input['action']) && $input['action'] === 'download') {
                // İndirme sayısını artır
                $stmt = $pdo->prepare("UPDATE files SET download_count = download_count + 1 WHERE id = ?");
                $stmt->execute([$input['file_id']]);
                echo json_encode(['success' => true]);
            } else {
                // Yeni dosya ekle
                $stmt = $pdo->prepare("
                    INSERT INTO files (title, description, category, file_path, file_size, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $input['title'],
                    $input['description'],
                    $input['category'],
                    $input['file_path'],
                    $input['file_size'],
                    $input['status'] ?? 'active'
                ]);
                
                echo json_encode([
                    'success' => true,
                    'id' => $pdo->lastInsertId(),
                    'message' => 'File added successfully'
                ]);
            }
            break;
            
        case 'PUT':
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE files 
                SET title = ?, description = ?, category = ?, file_path = ?, file_size = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['category'],
                $input['file_path'],
                $input['file_size'],
                $input['status'],
                $input['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'File updated successfully']);
            break;
            
        case 'DELETE':
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            // Dosyayı sil
            $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
            $stmt->execute([$input['id']]);
            $file = $stmt->fetch();
            
            if ($file && file_exists('../' . $file['file_path'])) {
                unlink('../' . $file['file_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
