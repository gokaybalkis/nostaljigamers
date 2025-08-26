<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

// Dosya işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload' && isset($_FILES['file'])) {
        $upload_result = handle_file_upload($_FILES['file']);
        
        if ($upload_result['success']) {
            // Veritabanına kaydet
            $filename = sanitize_input($_POST['title'] ?? $_FILES['file']['name']);
            $filepath = $upload_result['filepath'];
            $filesize = $_FILES['file']['size'];
            $filetype = $upload_result['type'];
            $description = sanitize_input($_POST['description'] ?? '');
            $category = sanitize_input($_POST['category'] ?? 'general');
            
            $stmt = $pdo->prepare("INSERT INTO files (title, description, file_name, file_path, file_size, file_type, category, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', datetime('now'))");
            $stmt->execute([$filename, $description, $_FILES['file']['name'], $filepath, $filesize, $filetype, $category]);
            
            $success_message = "Dosya başarıyla yüklendi.";
        } else {
            $error_message = $upload_result['message'];
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        
        // Dosya bilgilerini al
        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if ($file) {
            // Dosyayı sil
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            // Veritabanından sil
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$id]);
            
            $success_message = "Dosya başarıyla silindi.";
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $filename = sanitize_input($_POST['filename']);
        $description = sanitize_input($_POST['description']);
        $category = sanitize_input($_POST['category']);
        
        $stmt = $pdo->prepare("UPDATE files SET title = ?, description = ?, category = ?, updated_at = datetime('now') WHERE id = ?");
        $stmt->execute([$filename, $description, $category, $id]);
        
        $success_message = "Dosya bilgileri güncellendi.";
    }
}

// Dosyaları getir
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = ["status = 'active'"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "category = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = "WHERE " . implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("SELECT * FROM files $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$files = $stmt->fetchAll();

// Kategorileri getir
$stmt = $pdo->query("SELECT DISTINCT category FROM files WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// İstatistikler
$total_files = $pdo->query("SELECT COUNT(*) FROM files WHERE status = 'active'")->fetchColumn();
$total_size = $pdo->query("SELECT SUM(filesize) FROM files WHERE status = 'active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yönetimi - Nostalji Gamers</title>
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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        /* File Grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .file-item {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }
        
        .file-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }
        
        .file-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .file-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            word-break: break-word;
        }
        
        .file-info {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        /* Upload Zone */
        .upload-zone {
            border: 2px dashed rgba(0, 255, 136, 0.5);
            border-radius: var(--border-radius);
            padding: 3rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.05);
        }
        
        .upload-zone.dragover {
            border-color: var(--primary-color);
            background: rgba(0, 255, 136, 0.1);
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
        
        .btn-edit {
            background: linear-gradient(135deg, var(--secondary-color), #4dabf7);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 255, 0.4);
            color: white;
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
        
        /* Progress Bar */
        .progress {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transition: width 0.3s ease;
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
            
            .file-grid {
                grid-template-columns: 1fr;
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
            <div class="text-secondary mt-2">Dosya Yönetimi</div>
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
                <a href="user-manager.php" class="nav-link">
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
                <a href="file-manager.php" class="nav-link active">
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
                <h1 class="page-title">Dosya Yönetimi</h1>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-2"></i>Dosya Yükle
            </button>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_files; ?></div>
                    <div class="stats-label">Toplam Dosya</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo format_file_size($total_size); ?></div>
                    <div class="stats-label">Toplam Boyut</div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" placeholder="Dosya adı ara..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-5">
                        <select class="form-control" name="category">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Files Grid -->
        <div class="file-grid">
            <?php if (empty($files)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-folder-open fa-5x text-secondary mb-3"></i>
                <h5 class="text-secondary">Henüz dosya bulunmuyor</h5>
                <p class="text-muted">Dosya yüklemek için yukarıdaki "Dosya Yükle" butonunu kullanın.</p>
            </div>
            <?php else: ?>
            <?php foreach ($files as $file): ?>
            <div class="file-item">
                <div class="file-icon">
                    <i class="<?php echo get_file_icon(pathinfo($file['file_name'], PATHINFO_EXTENSION)); ?>"></i>
                </div>
                <div class="file-name"><?php echo htmlspecialchars($file['title']); ?></div>
                <div class="file-info">
                    <div><?php echo format_file_size($file['file_size']); ?></div>
                    <div><?php echo ucfirst($file['category']); ?></div>
                    <div><?php echo format_date($file['created_at'], 'd.m.Y'); ?></div>
                </div>
                <?php if ($file['description']): ?>
                <div class="mb-3">
                    <small class="text-muted"><?php echo htmlspecialchars($file['description']); ?></small>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?php echo $file['file_path']; ?>" class="btn btn-primary btn-sm" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="btn btn-edit btn-sm" onclick="editFile(<?php echo $file['id']; ?>, '<?php echo htmlspecialchars($file['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($file['description'], ENT_QUOTES); ?>', '<?php echo $file['category']; ?>')">>
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $file['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dosya Yükle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="mb-3">
                            <label class="form-label">Dosya Başlığı</label>
                            <input type="text" class="form-control" name="title" placeholder="Dosya başlığı..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dosya</label>
                            <input type="file" class="form-control" name="file" required accept=".jpg,.jpeg,.png,.gif,.webp,.zip,.rar,.exe,.cfg,.txt,.pdf">
                            <small class="text-muted">
                                Desteklenen formatlar: JPG, PNG, GIF, WebP, ZIP, RAR, EXE, CFG, TXT, PDF<br>
                                Maksimum boyut: <?php echo format_file_size(MAX_FILE_SIZE); ?>
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="category" required>
                                <option value="general">Genel</option>
                                <option value="maps">Haritalar</option>
                                <option value="plugins">Eklentiler</option>
                                <option value="configs">Konfigürasyonlar</option>
                                <option value="images">Resimler</option>
                                <option value="documents">Belgeler</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Dosya açıklaması..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Yükle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit File Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dosya Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editFileId">
                        
                        <div class="mb-3">
                            <label class="form-label">Dosya Başlığı</label>
                            <input type="text" class="form-control" name="filename" id="editFilename" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="category" id="editCategory" required>
                                <option value="general">Genel</option>
                                <option value="maps">Haritalar</option>
                                <option value="plugins">Eklentiler</option>
                                <option value="configs">Konfigürasyonlar</option>
                                <option value="images">Resimler</option>
                                <option value="documents">Belgeler</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
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
        
        function editFile(id, filename, description, category) {
            document.getElementById('editFileId').value = id;
            document.getElementById('editFilename').value = filename;
            document.getElementById('editDescription').value = description;
            document.getElementById('editCategory').value = category;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        // Drag and drop functionality
        const uploadZone = document.querySelector('.upload-zone');
        if (uploadZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadZone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, unhighlight, false);
            });
            
            uploadZone.addEventListener('drop', handleDrop, false);
        }
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            uploadZone.classList.add('dragover');
        }
        
        function unhighlight(e) {
            uploadZone.classList.remove('dragover');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                const fileInput = document.querySelector('input[name="file"]');
                fileInput.files = files;
                // You can add automatic upload here if needed
            }
        }
        
        // File size validation
        document.querySelector('input[name="file"]')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = <?php echo MAX_FILE_SIZE; ?>;
                if (file.size > maxSize) {
                    alert('Dosya boyutu çok büyük. Maksimum boyut: <?php echo format_file_size(MAX_FILE_SIZE); ?>');
                    e.target.value = '';
                }
            }
        });
    </script>
</body>
</html>
