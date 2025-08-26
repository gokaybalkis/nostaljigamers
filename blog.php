<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Sayfa parametreleri
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$items_per_page = 6;

// Sayfa başlığı
$page_title = 'Blog';
$page_description = 'CS 1.6 sunucusu hakkında son haberler, güncellemeler ve duyurular.';
$page_keywords = 'cs 1.6, blog, haberler, güncellemeler, duyurular';

try {
    // Toplam yazı sayısını hesapla
    $count_query = "SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'";
    $count_params = [];
    
    if ($category) {
        $count_query .= " AND category = ?";
        $count_params[] = $category;
    }
    
    if ($search) {
        $count_query .= " AND (title LIKE ? OR content LIKE ?)";
        $count_params[] = "%$search%";
        $count_params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($count_params);
    $total_posts = $stmt->fetch()['total'];
    
    // Sayfalama hesapla
    $pagination = paginate($total_posts, $items_per_page, $page);
    
    // Blog yazılarını çek
    $query = "SELECT * FROM blog_posts WHERE status = 'published'";
    $params = [];
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $query .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $pagination['offset'];
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    
    // Kategorileri çek
    $stmt = $pdo->prepare("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' ORDER BY category");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    log_error("Blog page database error: " . $e->getMessage());
    $posts = [];
    $categories = [];
    $total_posts = 0;
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
        
        .blog-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }
        
        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 245, 255, 0.2);
        }
        
        .blog-card-body {
            padding: 25px;
        }
        
        .blog-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #00f5ff;
        }
        
        .blog-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .blog-title a:hover {
            color: #ffffff;
        }
        
        .blog-meta {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .blog-category {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .blog-category:hover {
            color: white;
            transform: translateY(-2px);
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
                        <a class="nav-link active" href="blog.php">
                            <i class="fas fa-newspaper me-1"></i>BLOG
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="files.php">
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
                    <i class="fas fa-newspaper me-3"></i>
                    Blog
                </h1>
                <p class="lead">CS 1.6 sunucusu hakkında son haberler ve güncellemeler</p>
            </div>
            
            <!-- Search and Filter -->
            <div class="search-filter">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Yazı ara..." value="<?php echo clean_output($search); ?>">
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
            
            <!-- Blog Posts -->
            <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-5x text-muted mb-3"></i>
                    <h3>Henüz blog yazısı bulunmuyor</h3>
                    <p class="text-muted">Aradığınız kriterlere uygun yazı bulunamadı.</p>
                    <a href="blog.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>Tümünü Göster
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 col-lg-4">
                            <article class="blog-card">
                                <div class="blog-card-body">
                                    <a href="blog-post.php?category=<?php echo urlencode($post['category']); ?>" 
                                       class="blog-category">
                                        <?php echo clean_output($post['category']); ?>
                                    </a>
                                    
                                    <h2 class="blog-title">
                                        <a href="blog-post.php?id=<?php echo $post['id']; ?>">
                                            <?php echo clean_output($post['title']); ?>
                                        </a>
                                    </h2>
                                    
                                    <div class="blog-meta">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo format_date($post['created_at']); ?>
                                        <span class="ms-3">
                                            <i class="fas fa-eye me-1"></i>
                                            <?php echo $post['views']; ?> görüntüleme
                                        </span>
                                    </div>
                                    
                                    <p class="blog-excerpt">
                                        <?php echo truncate_text(strip_tags($post['content']), 150); ?>
                                    </p>
                                    
                                    <a href="blog-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        Devamını Oku <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Blog sayfalama">
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
</body>
</html>
