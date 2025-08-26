<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../includes/functions.php';

try {
    // Get current server stats
    $server_stats = get_server_stats($pdo);
    
    // Simulate real-time data (in a real implementation, this would query the actual game server)
    $current_players = rand(8, 28); // Simulate player count fluctuation
    $ping = rand(12, 25); // Simulate ping variation
    
    // Update database with simulated data
    $stmt = $pdo->prepare("UPDATE server_settings SET current_players = ?, ping = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$current_players, $ping]);
    
    // Return updated stats
    echo json_encode([
        'success' => true,
        'current_players' => $current_players,
        'max_players' => $server_stats['max_players'],
        'ping' => $ping,
        'server_status' => 1,
        'map_current' => $server_stats['map_current'],
        'uptime' => $server_stats['uptime'],
        'total_kills' => $server_stats['total_kills'],
        'timestamp' => time()
    ]);
    
} catch (PDOException $e) {
    log_error("Server status API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
} catch (Exception $e) {
    log_error("Server status API general error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Server error'
    ]);
}
?>
