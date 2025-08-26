<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

start_secure_session();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Tek turnuvayı getir
                $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $tournament = $stmt->fetch();
                
                if ($tournament) {
                    echo json_encode(['success' => true, 'data' => $tournament]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Turnuva bulunamadı.']);
                }
            } else {
                // Tüm turnuvaları getir
                $stmt = $pdo->query("SELECT * FROM tournaments ORDER BY start_date DESC");
                $tournaments = $stmt->fetchAll();
                
                echo json_encode(['success' => true, 'data' => $tournaments]);
            }
            break;
            
        case 'POST':
            if (!is_admin()) {
                echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $name = sanitize_input($input['name']);
            $description = $input['description'];
            $start_date = $input['start_date'];
            $end_date = $input['end_date'];
            $max_teams = (int)$input['max_teams'];
            $prize_pool = sanitize_input($input['prize_pool'] ?? '');
            $status = sanitize_input($input['status'] ?? 'upcoming');
            $rules = $input['rules'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO tournaments (name, description, start_date, end_date, max_teams, prize_pool, status, rules, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $description, $start_date, $end_date, $max_teams, $prize_pool, $status, $rules]);
            
            echo json_encode(['success' => true, 'message' => 'Turnuva başarıyla eklendi.', 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'PUT':
            if (!is_admin()) {
                echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $id = $input['id'];
            $name = sanitize_input($input['name']);
            $description = $input['description'];
            $start_date = $input['start_date'];
            $end_date = $input['end_date'];
            $max_teams = (int)$input['max_teams'];
            $prize_pool = sanitize_input($input['prize_pool'] ?? '');
            $status = sanitize_input($input['status'] ?? 'upcoming');
            $rules = $input['rules'] ?? '';
            
            $stmt = $pdo->prepare("UPDATE tournaments SET name = ?, description = ?, start_date = ?, end_date = ?, max_teams = ?, prize_pool = ?, status = ?, rules = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $description, $start_date, $end_date, $max_teams, $prize_pool, $status, $rules, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Turnuva başarıyla güncellendi.']);
            break;
            
        case 'DELETE':
            if (!is_admin()) {
                echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
                exit;
            }
            
            $id = $_GET['id'];
            $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Turnuva başarıyla silindi.']);
            break;
    }
    
} catch (Exception $e) {
    log_error("Tournament API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()]);
}
?>
