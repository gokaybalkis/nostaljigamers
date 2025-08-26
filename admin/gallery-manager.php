<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

// Galeri işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $image_url = sanitize_input($_POST['image_url']);
        $category = sanitize_input($_POST['category']);
        $status = sanitize_input($_POST['status']);
        
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO galleries (title, description, image_url, category, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $image_url, $category, $status]);
            $success_message = "Galeri resmi başarıyla eklendi.";
        } else {
            $stmt = $pdo->prepare("UPDATE galleries SET title = ?, description = ?, image_url = ?, category = ?, status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $image_url, $category, $status, $id]);
            $success_message = "Galeri resmi başarıyla güncellendi.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM galleries WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = "Galeri resmi başarıyla silindi.";
    }
}

// Galeri resimlerini getir
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
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

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$stmt = $pdo->prepare("SELECT * FROM galleries $where_clause ORDER BY created_at DESC");
$stmt->execute($params);
$galleries = $stmt->fetchAll();

// Kategorileri getir
$stmt = $pdo->query("SELECT DISTINCT category FROM galleries WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// İstatistikler
$total_images = $pdo->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
$active_images = $pdo->query("SELECT COUNT(*) FROM galleries WHERE status = 'active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Yönetimi - Nostalji Gamers</title>
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
        
        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .gallery-item {
            background: rgba(26, 26, 46, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .gallery-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 255, 136, 0.2);
        }
        
        .gallery-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--dark-surface);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
        }
        
        .gallery-content {
            padding: 1.5rem;
        }
        
        .gallery-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .gallery-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .gallery-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
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
        
        /* Status Badge */
        .status-badge {
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
        
        .status-inactive {
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
            
            .gallery-grid {
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
            <div class="text-secondary mt-2">Galeri Yönetimi</div>
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
                <a href="file-manager.php" class="nav-link">
                    <i class="fas fa-download"></i>
                    Dosya Yönetimi
                </a>
            </div>
            <div class="nav-item">
                <a href="gallery-manager.php" class="nav-link active">
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
                <h1 class="page-title">Galeri Yönetimi</h1>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#galleryModal" onclick="resetForm()">
                <i class="fas fa-plus me-2"></i>Yeni Resim
            </button>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $total_images; ?></div>
                    <div class="stats-label">Toplam Resim</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $active_images; ?></div>
                    <div class="stats-label">Aktif Resim</div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="search" placeholder="Başlık veya açıklama ara..." value="<?php echo htmlspecialchars($search); ?>">
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
        
        <!-- Gallery Grid -->
        <div class="gallery-grid">
            <?php if (empty($galleries)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-images fa-5x text-secondary mb-3"></i>
                <h5 class="text-secondary">Henüz galeri resmi bulunmuyor</h5>
                <p class="text-muted">Resim eklemek için yukarıdaki "Yeni Resim" butonunu kullanın.</p>
            </div>
            <?php else: ?>
            <?php foreach ($galleries as $gallery): ?>
            <div class="gallery-item">
                <?php if ($gallery['image_url']): ?>
                <img src="<?php echo htmlspecialchars($gallery['image_url']); ?>" alt="<?php echo htmlspecialchars($gallery['title']); ?>" class="gallery-image" loading="lazy">
                <?php else: ?>
                <div class="gallery-image">
                    <i class="fas fa-image fa-3x"></i>
                </div>
                <?php endif; ?>
                <div class="gallery-content">
                    <div class="gallery-title"><?php echo htmlspecialchars($gallery['title']); ?></div>
                    <?php if ($gallery['description']): ?>
                    <div class="gallery-description"><?php echo htmlspecialchars($gallery['description']); ?></div>
                    <?php endif; ?>
                    <div class="gallery-meta">
                        <div><?php echo ucfirst($gallery['category']); ?></div>
                        <div><?php echo format_date($gallery['created_at'], 'd.m.Y'); ?></div>
                        <span class="status-badge status-<?php echo $gallery['status']; ?>">
                            <?php echo $gallery['status'] === 'active' ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button class="btn btn-edit btn-sm" onclick="editGallery(<?php echo $gallery['id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bu resmi silmek istediğinizden emin misiniz?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $gallery['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalTitle">Yeni Resim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="galleryForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="galleryId">
                        
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" name="title" id="galleryTitle" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Resim URL</label>
                            <input type="url" class="form-control" name="image_url" id="galleryImageUrl" required>
                            <small class="text-muted">Resim dosyasının tam URL adresini girin</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" id="galleryDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-control" name="category" id="galleryCategory" required>
                                        <option value="screenshots">Ekran Görüntüleri</option>
                                        <option value="maps">Harita Resimleri</option>
                                        <option value="tournaments">Turnuva Resimleri</option>
                                        <option value="team">Takım Fotoğrafları</option>
                                        <option value="events">Etkinlik Fotoğrafları</option>
                                        <option value="general">Genel</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Durum</label>
                                    <select class="form-control" name="status" id="galleryStatus">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Pasif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
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
        
        function resetForm() {
            document.getElementById('galleryForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('galleryModalTitle').textContent = 'Yeni Resim';
            document.getElementById('galleryId').value = '';
        }
        
        function editGallery(id) {
            // AJAX ile galeri verilerini getir ve formu doldur
            fetch(`../api/galleries.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const gallery = data.data;
                        document.getElementById('formAction').value = 'edit';
                        document.getElementById('galleryId').value = gallery.id;
                        document.getElementById('galleryTitle').value = gallery.title;
                        document.getElementById('galleryImageUrl').value = gallery.image_url || '';
                        document.getElementById('galleryDescription').value = gallery.description || '';
                        document.getElementById('galleryCategory').value = gallery.category;
                        document.getElementById('galleryStatus').value = gallery.status;
                        document.getElementById('galleryModalTitle').textContent = 'Resim Düzenle';
                        
                        new bootstrap.Modal(document.getElementById('galleryModal')).show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Galeri resmi yüklenirken hata oluştu.');
                });
        }
        
        // URL değiştiğinde resim önizlemesi göster
        document.getElementById('galleryImageUrl')?.addEventListener('blur', function() {
            const url = this.value;
            if (url) {
                // Basit URL doğrulama
                const img = new Image();
                img.onload = function() {
                    console.log('Resim geçerli');
                };
                img.onerror = function() {
                    console.log('Resim yüklenemedi');
                };
                img.src = url;
            }
        });
    </script>
</body>
</html>
