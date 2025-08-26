<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

// Blog işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = sanitize_input($_POST['title']);
        $content = $_POST['content']; // HTML içerik için sanitize etmiyoruz
        $excerpt = sanitize_input($_POST['excerpt']);
        $category = sanitize_input($_POST['category']);
        $tags = sanitize_input($_POST['tags']);
        $status = sanitize_input($_POST['status']);
        $featured_image = sanitize_input($_POST['featured_image']);
        $slug = create_slug($title);
        
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, category, tags, status, featured_image, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, datetime('now'))");
            $stmt->execute([$title, $slug, $content, $excerpt, $category, $tags, $status, $featured_image]);
            $success_message = "Blog yazısı başarıyla eklendi.";
        } else {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, category = ?, tags = ?, status = ?, featured_image = ?, updated_at = datetime('now') WHERE id = ?");
            $stmt->execute([$title, $slug, $content, $excerpt, $category, $tags, $status, $featured_image, $id]);
            $success_message = "Blog yazısı başarıyla güncellendi.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = "Blog yazısı başarıyla silindi.";
    }
}

// Blog yazılarını getir
$stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
$blog_posts = $stmt->fetchAll();

// Kategorileri getir
$stmt = $pdo->query("SELECT DISTINCT category FROM blog_posts WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yönetimi - Nostalji Gamers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        
        /* Blog Post Preview */
        .blog-excerpt {
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-secondary);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-published {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-draft {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
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
            <div class="text-secondary mt-2">Blog Yönetimi</div>
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
                <a href="blog-manager.php" class="nav-link active">
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
                <h1 class="page-title">Blog Yönetimi</h1>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#blogModal" onclick="resetForm()">
                <i class="fas fa-plus me-2"></i>Yeni Blog Yazısı
            </button>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Blog Posts List -->
        <div class="card">
            <div class="card-body">
                <h5><i class="fas fa-newspaper me-2"></i>Blog Yazıları</h5>
                
                <?php if (empty($blog_posts)): ?>
                <p class="text-muted">Henüz blog yazısı bulunmuyor.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Başlık</th>
                                <th>Kategori</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <br>
                                    <small class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></small>
                                </td>
                                <td>
                                    <?php if ($post['category']): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($post['category']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $post['status']; ?>">
                                        <?php echo $post['status'] === 'published' ? 'Yayında' : 'Taslak'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_date($post['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-edit btn-sm me-2" onclick="editPost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
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
    
    <!-- Blog Modal -->
    <div class="modal fade" id="blogModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="blogModalTitle">Yeni Blog Yazısı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="blogForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="postId">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Başlık</label>
                                    <input type="text" class="form-control" name="title" id="postTitle" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Durum</label>
                                    <select class="form-control" name="status" id="postStatus">
                                        <option value="draft">Taslak</option>
                                        <option value="published">Yayında</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <input type="text" class="form-control" name="category" id="postCategory" list="categories">
                                    <datalist id="categories">
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Etiketler (virgülle ayırın)</label>
                                    <input type="text" class="form-control" name="tags" id="postTags">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Öne Çıkan Görsel URL</label>
                            <input type="url" class="form-control" name="featured_image" id="postFeaturedImage">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Özet</label>
                            <textarea class="form-control" name="excerpt" id="postExcerpt" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">İçerik</label>
                            <textarea class="form-control" name="content" id="postContent" rows="10"></textarea>
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
        // TinyMCE Editor
        tinymce.init({
            selector: '#postContent',
            height: 400,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: Rajdhani, sans-serif; font-size: 14px; color: #ffffff; background-color: #1a1a2e; }'
        });
        
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        function resetForm() {
            document.getElementById('blogForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('blogModalTitle').textContent = 'Yeni Blog Yazısı';
            document.getElementById('postId').value = '';
            tinymce.get('postContent').setContent('');
        }
        
        function editPost(id) {
            // AJAX ile post verilerini getir ve formu doldur
            fetch(`../api/blog.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const post = data.data;
                        document.getElementById('formAction').value = 'edit';
                        document.getElementById('postId').value = post.id;
                        document.getElementById('postTitle').value = post.title;
                        document.getElementById('postCategory').value = post.category || '';
                        document.getElementById('postTags').value = post.tags || '';
                        document.getElementById('postFeaturedImage').value = post.featured_image || '';
                        document.getElementById('postExcerpt').value = post.excerpt || '';
                        document.getElementById('postStatus').value = post.status;
                        document.getElementById('blogModalTitle').textContent = 'Blog Yazısını Düzenle';
                        
                        tinymce.get('postContent').setContent(post.content || '');
                        
                        new bootstrap.Modal(document.getElementById('blogModal')).show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Blog yazısı yüklenirken hata oluştu.');
                });
        }
        
        // Modal kapandığında TinyMCE'yi temizle
        document.getElementById('blogModal').addEventListener('hidden.bs.modal', function () {
            if (tinymce.get('postContent')) {
                tinymce.get('postContent').setContent('');
            }
        });
    </script>
</body>
</html>
