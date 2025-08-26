<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İçerik Yöneticisi - Nostalji Gamers</title>
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
        
        /* Tabs */
        .nav-tabs {
            border-bottom: 2px solid rgba(0, 255, 136, 0.3);
        }
        
        .nav-tabs .nav-link {
            color: var(--text-secondary);
            border: none;
            border-radius: 0;
            padding: 1rem 2rem;
            margin: 0;
            background: transparent;
            transform: none;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            background: rgba(0, 255, 136, 0.1);
        }
        
        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: var(--dark-bg);
            border-color: var(--primary-color);
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
        
        /* Content Preview */
        .content-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-secondary);
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
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 255, 136, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
        
        /* Text color fixes for dark theme */
        .text-muted {
            color: var(--text-secondary) !important;
        }
        
        .text-secondary {
            color: var(--text-secondary) !important;
        }
        
        .small, small {
            color: var(--text-secondary);
        }
        
        /* Alert fixes */
        .alert {
            color: var(--text-primary);
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #dc3545;
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
            <div class="text-secondary mt-2">İçerik Yöneticisi</div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="content-manager.php" class="nav-link active">
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
                <h1 class="page-title">İçerik Yöneticisi</h1>
            </div>
            <button class="btn btn-primary" onclick="loadContent()">
                <i class="fas fa-sync-alt me-2"></i>Yenile
            </button>
        </div>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="contentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="banners-tab" data-bs-toggle="tab" data-bs-target="#banners" type="button" role="tab">
                    <i class="fas fa-image me-2"></i>Bannerlar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content" type="button" role="tab">
                    <i class="fas fa-file-alt me-2"></i>İçerik Blokları
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="menus-tab" data-bs-toggle="tab" data-bs-target="#menus" type="button" role="tab">
                    <i class="fas fa-bars me-2"></i>Menüler
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">
                    <i class="fas fa-file me-2"></i>Sayfalar
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="contentTabsContent">
            <!-- Banners Tab -->
            <div class="tab-pane fade show active" id="banners" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-image me-2"></i>Banner Yönetimi</h5>
                            <button class="btn btn-success" onclick="editBanner()">
                                <i class="fas fa-plus me-2"></i>Yeni Banner
                            </button>
                        </div>
                        <div id="banners-list">
                            <div class="text-center">
                                <div class="loading"></div>
                                <p class="mt-2">Yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Blocks Tab -->
            <div class="tab-pane fade" id="content" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-file-alt me-2"></i>İçerik Blokları</h5>
                            <button class="btn btn-success" onclick="editContent()">
                                <i class="fas fa-plus me-2"></i>Yeni İçerik
                            </button>
                        </div>
                        <div id="content-list">
                            <div class="text-center">
                                <div class="loading"></div>
                                <p class="mt-2">Yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Menus Tab -->
            <div class="tab-pane fade" id="menus" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-bars me-2"></i>Menü Yönetimi</h5>
                            <button class="btn btn-success" onclick="editMenu()">
                                <i class="fas fa-plus me-2"></i>Yeni Menü
                            </button>
                        </div>
                        <div id="menus-list">
                            <div class="text-center">
                                <div class="loading"></div>
                                <p class="mt-2">Yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pages Tab -->
            <div class="tab-pane fade" id="pages" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="fas fa-file me-2"></i>Sayfa Yönetimi</h5>
                            <button class="btn btn-success" onclick="editPage()">
                                <i class="fas fa-plus me-2"></i>Yeni Sayfa
                            </button>
                        </div>
                        <div id="pages-list">
                            <div class="text-center">
                                <div class="loading"></div>
                                <p class="mt-2">Yükleniyor...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <div id="editFormContent">
                            <!-- Form content will be loaded here -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveContent()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEditType = '';
        let currentEditId = null;
        
        // Sayfa yüklendiğinde içerikleri yükle
        document.addEventListener('DOMContentLoaded', function() {
            loadContent();
        });
        
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // İçerikleri yükle
        function loadContent() {
            loadBanners();
            loadContentBlocks();
            loadMenus();
            loadPages();
        }
        
        // Bannerları yükle
        function loadBanners() {
            fetch('/api/banner.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('banners-list');
                    if (data.success && data.data.length > 0) {
                        container.innerHTML = data.data.map(banner => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>${banner.title}</h6>
                                            <p class="text-muted mb-1">${banner.subtitle || 'Alt başlık yok'}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-eye me-1"></i>
                                                Durum: ${banner.status === 'active' ? 'Aktif' : 'Pasif'}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-edit btn-sm me-2" onclick="editBanner(${banner.id})">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteBanner(${banner.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-muted">Henüz banner bulunmuyor.</p>';
                    }
                })
                .catch(error => {
                    console.error('Banner yükleme hatası:', error);
                    document.getElementById('banners-list').innerHTML = '<p class="text-danger">Banner yüklenirken hata oluştu.</p>';
                });
        }
        
        // İçerik bloklarını yükle
        function loadContentBlocks() {
            fetch('/api/content.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('content-list');
                    if (data.success && data.data.length > 0) {
                        container.innerHTML = data.data.map(content => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>${content.title}</h6>
                                            <div class="content-preview">${content.content}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                Tip: ${content.type} | Durum: ${content.status === 'active' ? 'Aktif' : 'Pasif'}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-edit btn-sm me-2" onclick="editContent(${content.id})">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteContent(${content.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-muted">Henüz içerik bulunmuyor.</p>';
                    }
                })
                .catch(error => {
                    console.error('İçerik yükleme hatası:', error);
                    document.getElementById('content-list').innerHTML = '<p class="text-danger">İçerik yüklenirken hata oluştu.</p>';
                });
        }
        
        // Menüleri yükle
        function loadMenus() {
            fetch('/api/menus.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('menus-list');
                    if (data.success && data.data.length > 0) {
                        container.innerHTML = data.data.map(menu => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>
                                                ${menu.icon ? `<i class="${menu.icon} me-2"></i>` : ''}
                                                ${menu.title}
                                            </h6>
                                            <p class="text-muted mb-1">${menu.url}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-sort me-1"></i>
                                                Sıra: ${menu.order_num} | Durum: ${menu.status === 'active' ? 'Aktif' : 'Pasif'}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-edit btn-sm me-2" onclick="editMenu(${menu.id})">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteMenu(${menu.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-muted">Henüz menü bulunmuyor.</p>';
                    }
                })
                .catch(error => {
                    console.error('Menü yükleme hatası:', error);
                    document.getElementById('menus-list').innerHTML = '<p class="text-danger">Menü yüklenirken hata oluştu.</p>';
                });
        }
        
        // Sayfaları yükle
        function loadPages() {
            fetch('/api/pages.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('pages-list');
                    if (data.success && data.data.length > 0) {
                        container.innerHTML = data.data.map(page => `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>${page.title}</h6>
                                            <p class="text-muted mb-1">Slug: /${page.slug}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                ${new Date(page.created_at).toLocaleDateString('tr-TR')} | 
                                                Durum: ${page.status === 'published' ? 'Yayında' : 'Taslak'}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-edit btn-sm me-2" onclick="editPage(${page.id})">
                                                <i class="fas fa-edit"></i> Düzenle
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deletePage(${page.id})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-muted">Henüz sayfa bulunmuyor.</p>';
                    }
                })
                .catch(error => {
                    console.error('Sayfa yükleme hatası:', error);
                    document.getElementById('pages-list').innerHTML = '<p class="text-danger">Sayfa yüklenirken hata oluştu.</p>';
                });
        }
        
        // Banner düzenleme
        function editBanner(id = null) {
            currentEditType = 'banner';
            currentEditId = id;
            
            const title = id ? 'Banner Düzenle' : 'Yeni Banner';
            document.getElementById('editModalTitle').textContent = title;
            
            if (id) {
                // Mevcut banner verilerini yükle
                fetch(`/api/banner.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showBannerForm(data.data);
                        }
                    });
            } else {
                showBannerForm();
            }
        }
        
        function showBannerForm(data = {}) {
            document.getElementById('editFormContent').innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" class="form-control" id="banner_title" value="${data.title || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alt Başlık</label>
                    <textarea class="form-control" id="banner_subtitle" rows="3">${data.subtitle || ''}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Buton Metni</label>
                            <input type="text" class="form-control" id="banner_button_text" value="${data.button_text || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Buton URL</label>
                            <input type="text" class="form-control" id="banner_button_url" value="${data.button_url || ''}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Arkaplan Resmi URL</label>
                    <input type="url" class="form-control" id="banner_background_image" value="${data.background_image || ''}">
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Metin Rengi</label>
                            <input type="color" class="form-control" id="banner_text_color" value="${data.text_color || '#ffffff'}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Buton Rengi</label>
                            <input type="color" class="form-control" id="banner_button_color" value="${data.button_color || '#00ff88'}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select class="form-control" id="banner_status">
                                <option value="active" ${data.status === 'active' ? 'selected' : ''}>Aktif</option>
                                <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        // İçerik düzenleme
        function editContent(id = null) {
            currentEditType = 'content';
            currentEditId = id;
            
            const title = id ? 'İçerik Düzenle' : 'Yeni İçerik';
            document.getElementById('editModalTitle').textContent = title;
            
            if (id) {
                fetch(`/api/content.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showContentForm(data.data);
                        }
                    });
            } else {
                showContentForm();
            }
        }
        
        function showContentForm(data = {}) {
            document.getElementById('editFormContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tip</label>
                            <select class="form-control" id="content_type" required>
                                <option value="welcome" ${data.type === 'welcome' ? 'selected' : ''}>Hoş Geldin</option>
                                <option value="announcement" ${data.type === 'announcement' ? 'selected' : ''}>Duyuru</option>
                                <option value="server_info" ${data.type === 'server_info' ? 'selected' : ''}>Sunucu Bilgisi</option>
                                <option value="news" ${data.type === 'news' ? 'selected' : ''}>Haberler</option>
                                <option value="guide" ${data.type === 'guide' ? 'selected' : ''}>Rehber</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select class="form-control" id="content_status">
                                <option value="active" ${data.status === 'active' ? 'selected' : ''}>Aktif</option>
                                <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" class="form-control" id="content_title" value="${data.title || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">İçerik</label>
                    <textarea class="form-control" id="content_content" rows="6" required>${data.content || ''}</textarea>
                    <small class="form-text">HTML etiketleri kullanabilirsiniz.</small>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        // Menü düzenleme
        function editMenu(id = null) {
            currentEditType = 'menu';
            currentEditId = id;
            
            const title = id ? 'Menü Düzenle' : 'Yeni Menü';
            document.getElementById('editModalTitle').textContent = title;
            
            if (id) {
                fetch(`/api/menus.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMenuForm(data.data);
                        }
                    });
            } else {
                showMenuForm();
            }
        }
        
        function showMenuForm(data = {}) {
            document.getElementById('editFormContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="menu_title" value="${data.title || ''}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="text" class="form-control" id="menu_url" value="${data.url || ''}" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">İkon (Font Awesome)</label>
                            <input type="text" class="form-control" id="menu_icon" value="${data.icon || ''}" placeholder="fas fa-home">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Hedef</label>
                            <select class="form-control" id="menu_target">
                                <option value="_self" ${data.target === '_self' ? 'selected' : ''}>Aynı Pencere</option>
                                <option value="_blank" ${data.target === '_blank' ? 'selected' : ''}>Yeni Pencere</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" id="menu_order_num" value="${data.order_num || 0}">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Durum</label>
                    <select class="form-control" id="menu_status">
                        <option value="active" ${data.status === 'active' ? 'selected' : ''}>Aktif</option>
                        <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Pasif</option>
                    </select>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        // Sayfa düzenleme
        function editPage(id = null) {
            currentEditType = 'page';
            currentEditId = id;
            
            const title = id ? 'Sayfa Düzenle' : 'Yeni Sayfa';
            document.getElementById('editModalTitle').textContent = title;
            
            if (id) {
                fetch(`/api/pages.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showPageForm(data.data);
                        }
                    });
            } else {
                showPageForm();
            }
        }
        
        function showPageForm(data = {}) {
            document.getElementById('editFormContent').innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control" id="page_title" value="${data.title || ''}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <select class="form-control" id="page_status">
                                <option value="draft" ${data.status === 'draft' ? 'selected' : ''}>Taslak</option>
                                <option value="published" ${data.status === 'published' ? 'selected' : ''}>Yayında</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">İçerik</label>
                    <textarea class="form-control" id="page_content" rows="8" required>${data.content || ''}</textarea>
                    <small class="form-text">HTML etiketleri kullanabilirsiniz.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Açıklama</label>
                    <textarea class="form-control" id="page_meta_description" rows="2">${data.meta_description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Anahtar Kelimeler</label>
                    <input type="text" class="form-control" id="page_meta_keywords" value="${data.meta_keywords || ''}" placeholder="anahtar, kelime, virgül, ile, ayırın">
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        // İçerik kaydetme
        function saveContent() {
            let data = {};
            let url = '';
            let method = currentEditId ? 'PUT' : 'POST';
            
            if (currentEditId) {
                data.id = currentEditId;
            }
            
            switch (currentEditType) {
                case 'banner':
                    url = '/api/banner.php';
                    data = {
                        ...data,
                        title: document.getElementById('banner_title').value,
                        subtitle: document.getElementById('banner_subtitle').value,
                        button_text: document.getElementById('banner_button_text').value,
                        button_url: document.getElementById('banner_button_url').value,
                        background_image: document.getElementById('banner_background_image').value,
                        text_color: document.getElementById('banner_text_color').value,
                        button_color: document.getElementById('banner_button_color').value,
                        status: document.getElementById('banner_status').value
                    };
                    break;
                    
                case 'content':
                    url = '/api/content.php';
                    data = {
                        ...data,
                        type: document.getElementById('content_type').value,
                        title: document.getElementById('content_title').value,
                        content: document.getElementById('content_content').value,
                        status: document.getElementById('content_status').value
                    };
                    break;
                    
                case 'menu':
                    url = '/api/menus.php';
                    data = {
                        ...data,
                        title: document.getElementById('menu_title').value,
                        url: document.getElementById('menu_url').value,
                        icon: document.getElementById('menu_icon').value,
                        target: document.getElementById('menu_target').value,
                        order_num: parseInt(document.getElementById('menu_order_num').value) || 0,
                        status: document.getElementById('menu_status').value
                    };
                    break;
                    
                case 'page':
                    url = '/api/pages.php';
                    data = {
                        ...data,
                        title: document.getElementById('page_title').value,
                        content: document.getElementById('page_content').value,
                        meta_description: document.getElementById('page_meta_description').value,
                        meta_keywords: document.getElementById('page_meta_keywords').value,
                        status: document.getElementById('page_status').value
                    };
                    break;
            }
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    loadContent();
                    
                    // Başarı mesajı göster
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        ${result.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.main-content').firstChild);
                    
                    // 3 saniye sonra otomatik kapat
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 3000);
                } else {
                    alert('Hata: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Kaydetme hatası:', error);
                alert('Kaydetme sırasında hata oluştu.');
            });
        }
        
        // Silme fonksiyonları
        function deleteBanner(id) {
            if (confirm('Bu banner\'ı silmek istediğinizden emin misiniz?')) {
                fetch(`/api/banner.php?id=${id}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadBanners();
                        } else {
                            alert('Silme hatası: ' + result.message);
                        }
                    });
            }
        }
        
        function deleteContent(id) {
            if (confirm('Bu içeriği silmek istediğinizden emin misiniz?')) {
                fetch(`/api/content.php?id=${id}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadContentBlocks();
                        } else {
                            alert('Silme hatası: ' + result.message);
                        }
                    });
            }
        }
        
        function deleteMenu(id) {
            if (confirm('Bu menü öğesini silmek istediğinizden emin misiniz?')) {
                fetch(`/api/menus.php?id=${id}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadMenus();
                        } else {
                            alert('Silme hatası: ' + result.message);
                        }
                    });
            }
        }
        
        function deletePage(id) {
            if (confirm('Bu sayfayı silmek istediğinizden emin misiniz?')) {
                fetch(`/api/pages.php?id=${id}`, { method: 'DELETE' })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            loadPages();
                        } else {
                            alert('Silme hatası: ' + result.message);
                        }
                    });
            }
        }
    </script>
</body>
</html>
