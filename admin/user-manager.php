<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

// Kullanıcı işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ban' || $action === 'unban') {
        $user_id = $_POST['user_id'];
        $ban_status = ($action === 'ban') ? 1 : 0;
        $ban_reason = sanitize_input($_POST['ban_reason'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE users SET is_banned = ?, ban_reason = ?, ban_date = ? WHERE id = ?");
        $ban_date = $ban_status ? date('Y-m-d H:i:s') : null;
        $stmt->execute([$ban_status, $ban_reason, $ban_date, $user_id]);
        
        $success_message = $ban_status ? "Kullanıcı başarıyla banlandı." : "Kullanıcının banı kaldırıldı.";
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Kullanıcı başarıyla silindi.";
    } elseif ($action === 'add') {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = sanitize_input($_POST['role']);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, datetime('now'))");
        $stmt->execute([$username, $email, $password, $role]);
        $success_message = "Kullanıcı başarıyla eklendi.";
    }
}

// Kullanıcıları getir
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'banned') {
    $where_conditions[] = "is_banned = 1";
} elseif ($filter === 'active') {
    $where_conditions[] = "is_banned = 0";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$stmt = $pdo->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

// İstatistikler
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_banned = 0")->fetchColumn(),
    'banned' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_banned = 1")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Nostalji Gamers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00ff88;
            --secondary-color: #0066ff;
            --accent-color: #ff6600;
            --dark-bg: #0a0a0f;
            --dark-card: #1a1a2e;
            --dark-surface: #16213e;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-card) 50%, var(--dark-bg) 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--dark-card) 0%, var(--dark-surface) 100%);
            min-height: 100vh;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            border-right: 2px solid var(--primary-color);
            transition: var(--transition);
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .sidebar-brand {
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--primary-color);
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: var(--text-secondary);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .top-bar {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Cards */
        .card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            transition: var(--transition);
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--dark-card), var(--dark-surface));
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }
        
        .stats-number {
            font-family: 'Orbitron', monospace;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--dark-bg);
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.4);
            color: var(--dark-bg);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            border: none;
            color: var(--dark-bg);
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
            color: var(--dark-bg);
        }
        
        /* Form Controls */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: var(--text-primary);
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 136, 0.25);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
        }
        
        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        /* Modal */
        .modal-content {
            background: var(--dark-card);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: var(--border-radius);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .modal-title {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        /* User Status Badges */
        .user-status {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-banned {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .role-user {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-crosshairs me-2"></i>
                NOSTALJI ADMIN
            </div>
            <div class="text-secondary mt-2">Kullanıcı Yönetimi</div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="content-manager.php" class="nav-link">
                    <i class="fas fa-edit"></i>
                    İçerik Yöneticisi
                </a>
            </div>
            <div class="nav-item">
                <a href="blog-manager.php" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    Blog Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="user-manager.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    Kullanıcı Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="tournament-manager.php" class="nav-link">
                    <i class="fas fa-trophy"></i>
                    Turnuva Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="file-manager.php" class="nav-link">
                    <i class="fas fa-download"></i>
                    Dosya Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="gallery-manager.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    Galeri Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="video-manager.php" class="nav-link">
                    <i class="fas fa-video"></i>
                    Video Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="server-settings.php" class="nav-link">
                    <i class="fas fa-server"></i>
                    Sunucu Ayarları
                </a>
            </div>
            <div class="nav-item">
                <a href="menu-manager.php" class="nav-link">
                    <i class="fas fa-bars"></i>
                    Menü Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="../index.php" class="nav-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    Siteyi Görüntüle
                </a>
            </div>
            <hr style="border-color: rgba(0, 255, 136, 0.3); margin: 1rem;">
            <div class="nav-item">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Çıkış Yap
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary d-md-none me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Kullanıcı Yönetimi</h1>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="fas fa-plus me-2"></i>Yeni Kullanıcı
            </button>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total']; ?></div>
                    <div class="stats-label">Toplam Kullanıcı</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['active']; ?></div>
                    <div class="stats-label">Aktif Kullanıcı</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['banned']; ?></div>
                    <div class="stats-label">Banlı Kullanıcı</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['admins']; ?></div>
                    <div class="stats-label">Admin</div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" placeholder="Kullanıcı adı veya e-posta ara..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" name="filter">
                            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tüm Kullanıcılar</option>
                            <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Aktif Kullanıcılar</option>
                            <option value="banned" <?php echo $filter === 'banned' ? 'selected' : ''; ?>>Banlı Kullanıcılar</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Users List -->
        <div class="card">
            <div class="card-body">
                <h5><i class="fas fa-users me-2"></i>Kullanıcılar</h5>
                
                <?php if (empty($users)): ?>
                <p class="text-muted">Kullanıcı bulunamadı.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Kullanıcı</th>
                                <th>E-posta</th>
                                <th>Rol</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['is_banned']): ?>
                                    <br><small class="text-danger">Ban Nedeni: <?php echo htmlspecialchars($user['ban_reason'] ?: 'Belirtilmemiş'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'Kullanıcı'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="user-status status-<?php echo $user['is_banned'] ? 'banned' : 'active'; ?>">
                                        <?php echo $user['is_banned'] ? 'Banlı' : 'Aktif'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="unban">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm me-2">
                                            <i class="fas fa-unlock"></i> Ban Kaldır
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-warning btn-sm me-2" onclick="banUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                        <i class="fas fa-ban"></i> Banla
                                    </button>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Kullanıcı Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Şifre</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-control" name="role" required>
                                <option value="user">Kullanıcı</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kullanıcı Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Ban User Modal -->
    <div class="modal fade" id="banModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kullanıcıyı Banla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="ban">
                        <input type="hidden" name="user_id" id="banUserId">
                        
                        <p>Kullanıcıyı banlamak istediğinizden emin misiniz: <strong id="banUsername"></strong>?</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Ban Nedeni</label>
                            <textarea class="form-control" name="ban_reason" rows="3" placeholder="Ban nedenini açıklayın..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Kullanıcıyı Banla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        function banUser(userId, username) {
            document.getElementById('banUserId').value = userId;
            document.getElementById('banUsername').textContent = username;
            new bootstrap.Modal(document.getElementById('banModal')).show();
        }
    </script>
</body>
</html>
