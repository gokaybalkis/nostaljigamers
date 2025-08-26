<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page_title = 'Sunucu Ayarları';

// Ayarları kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                $stmt = $pdo->prepare("
                    INSERT INTO server_settings (setting_key, setting_value) 
                    VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);
            }
        }
        $success_message = 'Ayarlar başarıyla kaydedildi!';
    } catch (PDOException $e) {
        $error_message = 'Hata: ' . $e->getMessage();
    }
}

// Mevcut ayarları çek
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM server_settings");
    $stmt->execute();
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $settings = [];
    $error_message = 'Ayarlar yüklenirken hata oluştu: ' . $e->getMessage();
}

// Varsayılan değerler
$default_settings = [
    'server_name' => 'CS 1.6 Server',
    'server_ip' => '127.0.0.1',
    'server_port' => '27015',
    'rcon_password' => '',
    'max_players' => '32',
    'map_cycle' => 'de_dust2,de_inferno,de_mirage,de_cache',
    'admin_steam_ids' => '',
    'server_password' => '',
    'sv_gravity' => '800',
    'sv_airaccelerate' => '10',
    'mp_timelimit' => '30',
    'mp_roundtime' => '1.75',
    'mp_maxrounds' => '30',
    'mp_startmoney' => '800',
    'mp_buytime' => '1.5',
    'mp_freezetime' => '6',
    'mp_c4timer' => '35',
    'mp_friendlyfire' => '1',
    'mp_autokick' => '1',
    'mp_autoteambalance' => '1',
    'sv_alltalk' => '0',
    'sv_pausable' => '0',
    'allow_spectators' => '1',
    'decalfrequency' => '30',
    'host_framerate' => '100',
    'sys_ticrate' => '100'
];

// Ayarları birleştir
foreach ($default_settings as $key => $default_value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default_value;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #0a0a0a;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.95) 0%, rgba(0, 0, 0, 0.95) 100%);
            min-height: 100vh;
            border-right: 2px solid #00f5ff;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 10px;
            margin: 5px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(0, 245, 255, 0.2);
            color: #00f5ff;
        }
        
        .main-content {
            background-color: #0a0a0a;
            min-height: 100vh;
            padding: 20px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(0, 245, 255, 0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(0, 245, 255, 0.2) 0%, rgba(30, 60, 114, 0.2) 100%);
            border-bottom: 1px solid rgba(0, 245, 255, 0.2);
            border-radius: 15px 15px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0099cc 0%, #00f5ff 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffa502 0%, #ff6348 100%);
            border: none;
        }
        
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #00f5ff;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(0, 245, 255, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(46, 213, 115, 0.2) 0%, rgba(0, 245, 255, 0.2) 100%);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 71, 87, 0.2) 0%, rgba(255, 56, 56, 0.2) 100%);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff4757;
        }
        
        .setting-group {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .setting-group h5 {
            color: #00f5ff;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 245, 255, 0.2);
        }
        
        .form-text {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .server-status {
            background: linear-gradient(135deg, rgba(0, 245, 255, 0.1) 0%, rgba(30, 60, 114, 0.1) 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .status-online {
            color: #2ed573;
        }
        
        .status-offline {
            color: #ff4757;
        }
        
        .config-preview {
            background: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-gamepad me-2" style="color: #00f5ff;"></i>
                        Admin Panel
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="content-manager.php">
                            <i class="fas fa-edit me-2"></i>İçerik Yönetimi
                        </a>
                        <a class="nav-link" href="blog-manager.php">
                            <i class="fas fa-newspaper me-2"></i>Blog Yönetimi
                        </a>
                        <a class="nav-link" href="user-manager.php">
                            <i class="fas fa-users me-2"></i>Kullanıcı Yönetimi
                        </a>
                        <a class="nav-link" href="file-manager.php">
                            <i class="fas fa-folder me-2"></i>Dosya Yönetimi
                        </a>
                        <a class="nav-link" href="gallery-manager.php">
                            <i class="fas fa-images me-2"></i>Galeri Yönetimi
                        </a>
                        <a class="nav-link" href="video-manager.php">
                            <i class="fas fa-video me-2"></i>Video Yönetimi
                        </a>
                        <a class="nav-link" href="tournament-manager.php">
                            <i class="fas fa-trophy me-2"></i>Turnuva Yönetimi
                        </a>
                        <a class="nav-link active" href="server-settings.php">
                            <i class="fas fa-server me-2"></i>Sunucu Ayarları
                        </a>
                        <a class="nav-link" href="menu-manager.php">
                            <i class="fas fa-bars me-2"></i>Menü Yönetimi
                        </a>
                        <hr>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-server me-2"></i><?php echo $page_title; ?></h2>
                    <div>
                        <button class="btn btn-success me-2" onclick="testServerConnection()">
                            <i class="fas fa-plug me-2"></i>Bağlantı Test Et
                        </button>
                        <button class="btn btn-warning" onclick="generateConfig()">
                            <i class="fas fa-file-code me-2"></i>Config Oluştur
                        </button>
                    </div>
                </div>
                
                <!-- Server Status -->
                <div class="server-status">
                    <h5>Sunucu Durumu</h5>
                    <div id="serverStatus">
                        <i class="fas fa-spinner fa-spin me-2"></i>Kontrol ediliyor...
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <!-- Temel Sunucu Ayarları -->
                    <div class="setting-group">
                        <h5><i class="fas fa-server me-2"></i>Temel Sunucu Ayarları</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="server_name" class="form-label">Sunucu Adı</label>
                                    <input type="text" class="form-control" id="server_name" name="server_name" 
                                           value="<?php echo clean_output($settings['server_name']); ?>">
                                    <div class="form-text">Sunucunuzun görünen adı</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="server_ip" class="form-label">Sunucu IP</label>
                                    <input type="text" class="form-control" id="server_ip" name="server_ip" 
                                           value="<?php echo clean_output($settings['server_ip']); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="server_port" class="form-label">Port</label>
                                    <input type="number" class="form-control" id="server_port" name="server_port" 
                                           value="<?php echo clean_output($settings['server_port']); ?>" min="1" max="65535">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_players" class="form-label">Maksimum Oyuncu</label>
                                    <input type="number" class="form-control" id="max_players" name="max_players" 
                                           value="<?php echo clean_output($settings['max_players']); ?>" min="1" max="32">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="server_password" class="form-label">Sunucu Şifresi</label>
                                    <input type="password" class="form-control" id="server_password" name="server_password" 
                                           value="<?php echo clean_output($settings['server_password']); ?>">
                                    <div class="form-text">Boş bırakın şifresiz sunucu için</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rcon_password" class="form-label">RCON Şifresi</label>
                                    <input type="password" class="form-control" id="rcon_password" name="rcon_password" 
                                           value="<?php echo clean_output($settings['rcon_password']); ?>">
                                    <div class="form-text">Uzaktan yönetim şifresi</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="map_cycle" class="form-label">Harita Döngüsü</label>
                            <input type="text" class="form-control" id="map_cycle" name="map_cycle" 
                                   value="<?php echo clean_output($settings['map_cycle']); ?>">
                            <div class="form-text">Haritaları virgülle ayırın (örn: de_dust2,de_inferno,de_mirage)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_steam_ids" class="form-label">Admin Steam ID'leri</label>
                            <textarea class="form-control" id="admin_steam_ids" name="admin_steam_ids" rows="3"><?php echo clean_output($settings['admin_steam_ids']); ?></textarea>
                            <div class="form-text">Her satıra bir Steam ID yazın</div>
                        </div>
                    </div>
                    
                    <!-- Oyun Ayarları -->
                    <div class="setting-group">
                        <h5><i class="fas fa-gamepad me-2"></i>Oyun Ayarları</h5>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_timelimit" class="form-label">Zaman Sınırı (dk)</label>
                                    <input type="number" class="form-control" id="mp_timelimit" name="mp_timelimit" 
                                           value="<?php echo clean_output($settings['mp_timelimit']); ?>" min="0" max="60">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_roundtime" class="form-label">Round Süresi (dk)</label>
                                    <input type="number" class="form-control" id="mp_roundtime" name="mp_roundtime" 
                                           value="<?php echo clean_output($settings['mp_roundtime']); ?>" min="1" max="9" step="0.25">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_maxrounds" class="form-label">Maksimum Round</label>
                                    <input type="number" class="form-control" id="mp_maxrounds" name="mp_maxrounds" 
                                           value="<?php echo clean_output($settings['mp_maxrounds']); ?>" min="1" max="50">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_startmoney" class="form-label">Başlangıç Parası</label>
                                    <input type="number" class="form-control" id="mp_startmoney" name="mp_startmoney" 
                                           value="<?php echo clean_output($settings['mp_startmoney']); ?>" min="0" max="16000">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_buytime" class="form-label">Satın Alma Süresi (dk)</label>
                                    <input type="number" class="form-control" id="mp_buytime" name="mp_buytime" 
                                           value="<?php echo clean_output($settings['mp_buytime']); ?>" min="0" max="5" step="0.5">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_freezetime" class="form-label">Donma Süresi (sn)</label>
                                    <input type="number" class="form-control" id="mp_freezetime" name="mp_freezetime" 
                                           value="<?php echo clean_output($settings['mp_freezetime']); ?>" min="0" max="15">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="mp_c4timer" class="form-label">C4 Zamanlayıcı (sn)</label>
                                    <input type="number" class="form-control" id="mp_c4timer" name="mp_c4timer" 
                                           value="<?php echo clean_output($settings['mp_c4timer']); ?>" min="15" max="90">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="sv_gravity" class="form-label">Yerçekimi</label>
                                    <input type="number" class="form-control" id="sv_gravity" name="sv_gravity" 
                                           value="<?php echo clean_output($settings['sv_gravity']); ?>" min="1" max="2000">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="mp_friendlyfire" class="form-label">Dost Ateşi</label>
                                    <select class="form-select" id="mp_friendlyfire" name="mp_friendlyfire">
                                        <option value="0" <?php echo $settings['mp_friendlyfire'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                        <option value="1" <?php echo $settings['mp_friendlyfire'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="mp_autoteambalance" class="form-label">Otomatik Takım Dengesi</label>
                                    <select class="form-select" id="mp_autoteambalance" name="mp_autoteambalance">
                                        <option value="0" <?php echo $settings['mp_autoteambalance'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                        <option value="1" <?php echo $settings['mp_autoteambalance'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sv_alltalk" class="form-label">Tüm Konuşma</label>
                                    <select class="form-select" id="sv_alltalk" name="sv_alltalk">
                                        <option value="0" <?php echo $settings['sv_alltalk'] == '0' ? 'selected' : ''; ?>>Kapalı</option>
                                        <option value="1" <?php echo $settings['sv_alltalk'] == '1' ? 'selected' : ''; ?>>Açık</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Performans Ayarları -->
                    <div class="setting-group">
                        <h5><i class="fas fa-tachometer-alt me-2"></i>Performans Ayarları</h5>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sys_ticrate" class="form-label">Tickrate</label>
                                    <select class="form-select" id="sys_ticrate" name="sys_ticrate">
                                        <option value="66" <?php echo $settings['sys_ticrate'] == '66' ? 'selected' : ''; ?>>66</option>
                                        <option value="100" <?php echo $settings['sys_ticrate'] == '100' ? 'selected' : ''; ?>>100</option>
                                        <option value="128" <?php echo $settings['sys_ticrate'] == '128' ? 'selected' : ''; ?>>128</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="host_framerate" class="form-label">FPS Sınırı</label>
                                    <input type="number" class="form-control" id="host_framerate" name="host_framerate" 
                                           value="<?php echo clean_output($settings['host_framerate']); ?>" min="60" max="1000">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sv_airaccelerate" class="form-label">Hava İvmesi</label>
                                    <input type="number" class="form-control" id="sv_airaccelerate" name="sv_airaccelerate" 
                                           value="<?php echo clean_output($settings['sv_airaccelerate']); ?>" min="1" max="100">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="decalfrequency" class="form-label">Decal Frekansı</label>
                                    <input type="number" class="form-control" id="decalfrequency" name="decalfrequency" 
                                           value="<?php echo clean_output($settings['decalfrequency']); ?>" min="10" max="300">
                                    <div class="form-text">Duvar izlerinin temizlenme sıklığı (saniye)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="allow_spectators" class="form-label">İzleyicilere İzin Ver</label>
                                    <select class="form-select" id="allow_spectators" name="allow_spectators">
                                        <option value="0" <?php echo $settings['allow_spectators'] == '0' ? 'selected' : ''; ?>>Hayır</option>
                                        <option value="1" <?php echo $settings['allow_spectators'] == '1' ? 'selected' : ''; ?>>Evet</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Ayarları Kaydet
                        </button>
                    </div>
                </form>
                
                <!-- Config Preview Modal -->
                <div class="modal fade" id="configModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Server.cfg Önizleme</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="config-preview" id="configPreview">
                                    // Config yükleniyor...
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                <button type="button" class="btn btn-primary" onclick="downloadConfig()">
                                    <i class="fas fa-download me-2"></i>İndir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sayfa yüklendiğinde sunucu durumunu kontrol et
        document.addEventListener('DOMContentLoaded', function() {
            checkServerStatus();
        });
        
        // Sunucu durumunu kontrol et
        function checkServerStatus() {
            const serverIp = document.getElementById('server_ip').value;
            const serverPort = document.getElementById('server_port').value;
            
            fetch(`../api/server-status.php?ip=${serverIp}&port=${serverPort}`)
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('serverStatus');
                    
                    if (data.online) {
                        statusDiv.innerHTML = `
                            <i class="fas fa-circle status-online me-2"></i>
                            <strong>Çevrimiçi</strong> - ${data.players}/${data.max_players} oyuncu
                            <br><small>Harita: ${data.map} | Ping: ${data.ping}ms</small>
                        `;
                    } else {
                        statusDiv.innerHTML = `
                            <i class="fas fa-circle status-offline me-2"></i>
                            <strong>Çevrimdışı</strong>
                            <br><small>Sunucuya bağlanılamıyor</small>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('serverStatus').innerHTML = `
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <strong>Durum Bilinmiyor</strong>
                        <br><small>Bağlantı hatası</small>
                    `;
                });
        }
        
        // Sunucu bağlantısını test et
        function testServerConnection() {
            const serverIp = document.getElementById('server_ip').value;
            const serverPort = document.getElementById('server_port').value;
            const rconPassword = document.getElementById('rcon_password').value;
            
            if (!serverIp || !serverPort) {
                alert('Lütfen sunucu IP ve port bilgilerini girin.');
                return;
            }
            
            // Test butonunu devre dışı bırak
            const testBtn = event.target;
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Test ediliyor...';
            
            fetch('../api/server-test.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ip: serverIp,
                    port: serverPort,
                    rcon_password: rconPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Bağlantı başarılı!\n\n' + 
                          'Sunucu: ' + data.server_name + '\n' +
                          'Oyuncular: ' + data.players + '/' + data.max_players + '\n' +
                          'Harita: ' + data.map + '\n' +
                          'Ping: ' + data.ping + 'ms');
                } else {
                    alert('❌ Bağlantı başarısız!\n\nHata: ' + data.error);
                }
            })
            .catch(error => {
                alert('❌ Test sırasında hata oluştu: ' + error.message);
            })
            .finally(() => {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-plug me-2"></i>Bağlantı Test Et';
            });
        }
        
        // Config dosyası oluştur
        function generateConfig() {
            const formData = new FormData(document.querySelector('form'));
            const settings = {};
            
            for (let [key, value] of formData.entries()) {
                if (key !== 'submit') {
                    settings[key] = value;
                }
            }
            
            let config = `// CS 1.6 Server Configuration
// Generated by Admin Panel on ${new Date().toLocaleString('tr-TR')}

// Basic Server Settings
hostname "${settings.server_name}"
sv_password "${settings.server_password}"
rcon_password "${settings.rcon_password}"
maxplayers ${settings.max_players}

// Game Settings
mp_timelimit ${settings.mp_timelimit}
mp_roundtime ${settings.mp_roundtime}
mp_maxrounds ${settings.mp_maxrounds}
mp_startmoney ${settings.mp_startmoney}
mp_buytime ${settings.mp_buytime}
mp_freezetime ${settings.mp_freezetime}
mp_c4timer ${settings.mp_c4timer}
mp_friendlyfire ${settings.mp_friendlyfire}
mp_autokick ${settings.mp_autokick}
mp_autoteambalance ${settings.mp_autoteambalance}

// Physics Settings
sv_gravity ${settings.sv_gravity}
sv_airaccelerate ${settings.sv_airaccelerate}

// Communication Settings
sv_alltalk ${settings.sv_alltalk}
sv_pausable ${settings.sv_pausable}

// Performance Settings
sys_ticrate ${settings.sys_ticrate}
host_framerate ${settings.host_framerate}
decalfrequency ${settings.decalfrequency}

// Spectator Settings
allow_spectators ${settings.allow_spectators}

// Map Cycle
mapcyclefile "mapcycle.txt"

// Admin Steam IDs
${settings.admin_steam_ids.split('\n').filter(id => id.trim()).map(id => `// Admin: ${id.trim()}`).join('\n')}

// Map Cycle Content (save as mapcycle.txt)
${settings.map_cycle.split(',').map(map => `// ${map.trim()}`).join('\n')}

// End of Configuration`;
            
            document.getElementById('configPreview').textContent = config;
            new bootstrap.Modal(document.getElementById('configModal')).show();
        }
        
        // Config dosyasını indir
        function downloadConfig() {
            const config = document.getElementById('configPreview').textContent;
            const blob = new Blob([config], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'server.cfg';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
