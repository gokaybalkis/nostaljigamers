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
            if (isset($_GET['categories'])) {
                // Kategorileri getir
                $stmt = $pdo->prepare("SELECT DISTINCT category FROM videos WHERE status = 'active' ORDER BY category");
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo json_encode(['categories' => $categories]);
            } elseif (isset($_GET['id'])) {
                // Tek video getir
                $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $video = $stmt->fetch();
                
                if ($video) {
                    echo json_encode($video);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Video not found']);
                }
            } else {
                // Tüm videoları getir
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
                $offset = ($page - 1) * $limit;
                
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                $status = isset($_GET['status']) ? $_GET['status'] : '';
                
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
                
                if ($status) {
                    $where_conditions[] = "status = ?";
                    $params[] = $status;
                }
                
                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                
                // Toplam sayı
                $count_query = "SELECT COUNT(*) as total FROM videos $where_clause";
                $stmt = $pdo->prepare($count_query);
                $stmt->execute($params);
                $total = $stmt->fetch()['total'];
                
                // İstatistikler
                $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                    SUM(view_count) as total_views,
                    COUNT(DISTINCT category) as category_count
                    FROM videos";
                $stmt = $pdo->prepare($stats_query);
                $stmt->execute();
                $stats = $stmt->fetch();
                
                // Videolar
                $query = "SELECT * FROM videos $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $videos = $stmt->fetchAll();
                
                echo json_encode([
                    'videos' => $videos,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit),
                    'total' => $stats['total'],
                    'active_count' => $stats['active_count'],
                    'total_views' => $stats['total_views'],
                    'category_count' => $stats['category_count']
                ]);
            }
            break;
            
        case 'POST':
            // Yeni video ekle
            $stmt = $pdo->prepare("
                INSERT INTO videos (title, description, category, video_url, thumbnail_url, duration, tags, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['category'],
                $input['video_url'],
                $input['thumbnail_url'],
                $input['duration'],
                $input['tags'],
                $input['status'] ?? 'active'
            ]);
            
            echo json_encode([
                'success' => true,
                'id' => $pdo->lastInsertId(),
                'message' => 'Video added successfully'
            ]);
            break;
            
        case 'PUT':
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE videos 
                SET title = ?, description = ?, category = ?, video_url = ?, thumbnail_url = ?, duration = ?, tags = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['title'],
                $input['description'],
                $input['category'],
                $input['video_url'],
                $input['thumbnail_url'],
                $input['duration'],
                $input['tags'],
                $input['status'],
                $input['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Video updated successfully']);
            break;
            
        case 'DELETE':
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Video deleted successfully']);
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
