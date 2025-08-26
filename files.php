<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Sayfa parametreleri
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$items_per_page = 8;

// Sayfa başlığı
$page_title = 'Dosyalar';
$page_description = 'CS 1.6 için gerekli dosyalar, haritalar, konfigürasyonlar ve daha fazlası.';
$page_keywords = 'cs 1.6, dosyalar, haritalar, config, indirme';

try {
    // Toplam dosya sayısını hesapla
    $count_query = "SELECT COUNT(*) as total FROM files WHERE status = 'active'";
    $count_params = [];
    
    if ($category) {
        $count_query .= " AND category = ?";
        $count_params[] = $category;
    }
    
    if ($search) {
        $count_query .= " AND (title LIKE ? OR description LIKE ?)";
        $count_params[] = "%$search%";
        $count_params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($count_params);
    $total_files = $stmt->fetch()['total'];
    
    // Sayfalama hesapla
    $pagination = paginate($total_files, $items_per_page, $page);
    
    // Dosyaları çek
    $query = "SELECT * FROM files WHERE status = 'active'";
    $params = [];
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY download_count DESC, created_at DESC LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $files = $stmt->fetchAll();
    
    // Kategorileri çek
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM files WHERE status = 'active' ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    log_error("Files page database error: " . $e->getMessage());
    $files = [];
    $categories = [];
    $total_files = 0;
    $pagination = paginate(0, $items_per_page, 1);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php generate_meta_tags($page_title, $page_description, $page_keywords); ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #0a0a0a;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.95) 0%, rgba(0, 0, 0, 0.95) 100%);
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #00f5ff;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #00f5ff !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: #00f5ff !important;
        }
        
        .main-content {
            padding-top: 100px;
            min-height: 100vh;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title {
            font-size: 3rem;
            font-weight: bold;
            color: #00f5ff;
            margin-bottom: 1rem;
        }
        
        .search-filter {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 3rem;
            backdrop-filter: blur(10px);
        }
        
        .file-card {
            background: linear-gradient(135deg, rgba(0, 245, 255, 0.1) 0%, rgba(30, 60, 114, 0.1) 100%);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 245, 255, 0.2);
            margin-bottom: 30px;
            height: 100%;
        }
        
        .file-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 245, 255, 0.2);
        }
        
        .file-icon {
            font-size: 4rem;
            color: #00f5ff;
            margin-bottom: 1rem;
        }
        
        .file-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }
        
        .file-description {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .file-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .file-category {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .file-category:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        .download-btn {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 245, 255, 0.4);
            color: white;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 3rem;
        }
        
        .page-link {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .page-link:hover {
            background-color: #00f5ff;
            border-color: #00f5ff;
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: #00f5ff;
            border-color: #00f5ff;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0099cc 0%, #00f5ff 100%);
        }
        
        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #00f5ff;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(0, 245, 255, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: #00f5ff;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(0, 245, 255, 0.25);
        }
        
        .form-select option {
            background-color: #1a1a1a;
            color: white;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #00f5ff;
        }
        
        .stats-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gamepad me-2"></i>
                CS 1.6 SERVER
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>ANASAYFA
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">
                            <i class="fas fa-newspaper me-1"></i>BLOG
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="files.php">
                            <i class="fas fa-download me-1"></i>DOSYALAR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">
                            <i class="fas fa-envelope me-1"></i>İLETİŞİM
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-download me-3"></i>
                    Dosyalar
                </h1>
                <p class="lead">CS 1.6 için gerekli tüm dosyalar burada</p>
            </div>
            
            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $total_files; ?></div>
                        <div class="stats-label">Toplam Dosya</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo count($categories); ?></div>
                        <div class="stats-label">Kategori</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">
                            <?php 
                            $total_downloads = 0;
                            foreach ($files as $file) {
                                $total_downloads += $file['download_count'];
                            }
                            echo number_format($total_downloads);
                            ?>
                        </div>
                        <div class="stats-label">Toplam İndirme</div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter -->
            <div class="search-filter">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Dosya ara..." value="<?php echo clean_output($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="category">
                            <option value="">Tüm Kategoriler</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo clean_output($cat); ?>" 
                                        <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo clean_output($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Ara
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Files -->
            <?php if (empty($files)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-5x text-muted mb-3"></i>
                    <h3>Henüz dosya bulunmuyor</h3>
                    <p class="text-muted">Aradığınız kriterlere uygun dosya bulunamadı.</p>
                    <a href="files.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>Tümünü Göster
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($files as $file): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="file-card">
                                <a href="files.php?category=<?php echo urlencode($file['category']); ?>" 
                                   class="file-category">
                                    <?php echo clean_output($file['category']); ?>
                                </a>
                                
                                <div class="file-icon">
                                    <?php
                                    $extension = pathinfo($file['file_path'], PATHINFO_EXTENSION);
                                    switch (strtolower($extension)) {
                                        case 'zip':
                                        case 'rar':
                                        case '7z':
                                            echo '<i class="fas fa-file-archive"></i>';
                                            break;
                                        case 'exe':
                                        case 'msi':
                                            echo '<i class="fas fa-file-code"></i>';
                                            break;
                                        case 'cfg':
                                        case 'txt':
                                            echo '<i class="fas fa-file-alt"></i>';
                                            break;
                                        default:
                                            echo '<i class="fas fa-file"></i>';
                                    }
                                    ?>
                                </div>
                                
                                <h3 class="file-title"><?php echo clean_output($file['title']); ?></h3>
                                
                                <div class="file-meta">
                                    <span><?php echo clean_output($file['file_size']); ?></span>
                                    <span><?php echo $file['download_count']; ?> indirme</span>
                                </div>
                                
                                <p class="file-description">
                                    <?php echo clean_output($file['description']); ?>
                                </p>
                                
                                <a href="<?php echo clean_output($file['file_path']); ?>" 
                                   class="download-btn" 
                                   onclick="updateDownloadCount(<?php echo $file['id']; ?>)">
                                    <i class="fas fa-download me-2"></i>İndir
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Dosya sayfalama">
                        <ul class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // İndirme sayısını güncelle
        function updateDownloadCount(fileId) {
            fetch('/api/files.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'download',
                    file_id: fileId
                })
            }).catch(error => {
                console.error('Download count update error:', error);
            });
        }
    </script>
</body>
</html>
