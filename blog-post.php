<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Post ID'sini al
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$post_id) {
    safe_redirect('blog.php');
}

try {
    // Blog yazısını çek
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? AND status = 'published'");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        safe_redirect('blog.php');
    }
    
    // Görüntüleme sayısını artır
    $stmt = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post_id]);
    
    // İlgili yazıları çek
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE category = ? AND id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$post['category'], $post_id]);
    $related_posts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    log_error("Blog post page database error: " . $e->getMessage());
    safe_redirect('blog.php');
}

// Sayfa başlığı ve meta bilgileri
$page_title = $post['title'];
$page_description = truncate_text(strip_tags($post['content']), 160);
$page_keywords = $post['category'] . ', cs 1.6, blog, ' . strtolower($post['title']);
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
            line-height: 1.6;
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
        
        .post-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 3rem 0;
            background: linear-gradient(135deg, rgba(0, 245, 255, 0.1) 0%, rgba(30, 60, 114, 0.1) 100%);
            border-radius: 15px;
        }
        
        .post-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00f5ff;
            margin-bottom: 1rem;
        }
        
        .post-meta {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .post-category {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .post-category:hover {
            color: white;
            transform: translateY(-2px);
        }
        
        .post-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1.1rem;
        }
        
        .post-content h1,
        .post-content h2,
        .post-content h3,
        .post-content h4,
        .post-content h5,
        .post-content h6 {
            color: #00f5ff;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
        }
        
        .post-content ul,
        .post-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        
        .post-content li {
            margin-bottom: 0.5rem;
        }
        
        .related-posts {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .related-post-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .related-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 245, 255, 0.2);
        }
        
        .related-post-title {
            color: #00f5ff;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .related-post-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .related-post-title a:hover {
            color: white;
        }
        
        .related-post-meta {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        .back-to-blog {
            background: linear-gradient(135deg, #00f5ff 0%, #0099cc 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .back-to-blog:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 245, 255, 0.4);
        }
        
        @media (max-width: 768px) {
            .post-title {
                font-size: 2rem;
            }
            
            .post-content {
                padding: 2rem;
            }
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
            <!-- Back to Blog -->
            <a href="blog.php" class="back-to-blog">
                <i class="fas fa-arrow-left me-2"></i>
                Blog'a Dön
            </a>
            
            <!-- Post Header -->
            <header class="post-header">
                <a href="blog.php?category=<?php echo urlencode($post['category']); ?>" 
                   class="post-category">
                    <?php echo clean_output($post['category']); ?>
                </a>
                
                <h1 class="post-title">
                    <?php echo clean_output($post['title']); ?>
                </h1>
                
                <div class="post-meta">
                    <i class="fas fa-calendar me-2"></i>
                    <?php echo format_date($post['created_at'], 'd F Y'); ?>
                    <span class="mx-3">|</span>
                    <i class="fas fa-eye me-2"></i>
                    <?php echo $post['views'] + 1; ?> görüntüleme
                </div>
            </header>
            
            <!-- Post Content -->
            <article class="post-content">
                <?php echo nl2br(clean_output($post['content'])); ?>
            </article>
            
            <!-- Related Posts -->
            <?php if (!empty($related_posts)): ?>
                <section class="related-posts">
                    <h3 class="mb-4">
                        <i class="fas fa-newspaper me-2"></i>
                        İlgili Yazılar
                    </h3>
                    
                    <div class="row">
                        <?php foreach ($related_posts as $related): ?>
                            <div class="col-md-4">
                                <div class="related-post-card">
                                    <h4 class="related-post-title">
                                        <a href="blog-post.php?id=<?php echo $related['id']; ?>">
                                            <?php echo clean_output($related['title']); ?>
                                        </a>
                                    </h4>
                                    <div class="related-post-meta">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo format_date($related['created_at']); ?>
                                        <span class="ms-3">
                                            <i class="fas fa-eye me-1"></i>
                                            <?php echo $related['views']; ?> görüntüleme
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
