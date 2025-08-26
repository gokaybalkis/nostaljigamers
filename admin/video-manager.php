<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page_title = 'Video Yöneticisi';
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
        
        .btn-danger {
            background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%);
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
        
        .table-dark {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .table-dark td, .table-dark th {
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.95) 0%, rgba(0, 0, 0, 0.95) 100%);
            border: 1px solid rgba(0, 245, 255, 0.2);
        }
        
        .video-thumbnail {
            width: 120px;
            height: 68px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .video-preview {
            max-width: 100%;
            border-radius: 10px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, rgba(0, 245, 255, 0.1) 0%, rgba(30, 60, 114, 0.1) 100%);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
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
                        <a class="nav-link active" href="video-manager.php">
                            <i class="fas fa-video me-2"></i>Video Yönetimi
                        </a>
                        <a class="nav-link" href="tournament-manager.php">
                            <i class="fas fa-trophy me-2"></i>Turnuva Yönetimi
                        </a>
                        <a class="nav-link" href="server-settings.php">
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
                    <h2><i class="fas fa-video me-2"></i><?php echo $page_title; ?></h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal">
                        <i class="fas fa-plus me-2"></i>Yeni Video Ekle
                    </button>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="totalVideos">0</div>
                            <div class="stats-label">Toplam Video</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="activeVideos">0</div>
                            <div class="stats-label">Aktif Video</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="totalViews">0</div>
                            <div class="stats-label">Toplam İzlenme</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="totalCategories">0</div>
                            <div class="stats-label">Kategori</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="searchInput" placeholder="Video ara...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">Tüm Kategoriler</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="statusFilter">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Pasif</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" onclick="loadVideos()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Videos Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Başlık</th>
                                        <th>Kategori</th>
                                        <th>Süre</th>
                                        <th>İzlenme</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="videosTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Yükleniyor...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Video pagination" class="mt-3">
                            <ul class="pagination justify-content-center" id="pagination">
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalTitle">Yeni Video Ekle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="videoForm">
                        <input type="hidden" id="videoId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoTitle" class="form-label">Başlık *</label>
                                    <input type="text" class="form-control" id="videoTitle" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoCategory" class="form-label">Kategori *</label>
                                    <input type="text" class="form-control" id="videoCategory" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="videoDescription" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="videoDescription" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoUrl" class="form-label">Video URL *</label>
                                    <input type="url" class="form-control" id="videoUrl" required>
                                    <div class="form-text">YouTube, Vimeo veya doğrudan video linki</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoDuration" class="form-label">Süre (dakika)</label>
                                    <input type="number" class="form-control" id="videoDuration" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoThumbnail" class="form-label">Thumbnail URL</label>
                                    <input type="url" class="form-control" id="videoThumbnail">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videoStatus" class="form-label">Durum</label>
                                    <select class="form-select" id="videoStatus">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Pasif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="videoTags" class="form-label">Etiketler</label>
                            <input type="text" class="form-control" id="videoTags" placeholder="cs16, gameplay, tutorial (virgülle ayırın)">
                        </div>
                        
                        <!-- Video Preview -->
                        <div id="videoPreview" class="mb-3" style="display: none;">
                            <label class="form-label">Önizleme</label>
                            <div id="videoPreviewContainer"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveVideo()">
                        <i class="fas fa-save me-2"></i>Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentPage = 1;
        let totalPages = 1;
        
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadVideos();
            loadCategories();
            
            // Video URL değiştiğinde önizleme göster
            document.getElementById('videoUrl').addEventListener('input', function() {
                previewVideo(this.value);
            });
        });
        
        // Videoları yükle
        function loadVideos(page = 1) {
            currentPage = page;
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            const params = new URLSearchParams({
                page: page,
                limit: 10
            });
            
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);
            
            fetch(`../api/videos.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    displayVideos(data.videos);
                    updatePagination(data.page, data.total_pages);
                    updateStats(data);
                    totalPages = data.total_pages;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('videosTableBody').innerHTML = 
                        '<tr><td colspan="8" class="text-center text-danger">Hata: ' + error.message + '</td></tr>';
                });
        }
        
        // Videoları göster
        function displayVideos(videos) {
            const tbody = document.getElementById('videosTableBody');
            
            if (videos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Video bulunamadı</td></tr>';
                return;
            }
            
            tbody.innerHTML = videos.map(video => `
                <tr>
                    <td>
                        <img src="${video.thumbnail_url || '/placeholder.svg?height=68&width=120'}" 
                             alt="${video.title}" class="video-thumbnail">
                    </td>
                    <td>
                        <strong>${video.title}</strong>
                        <br><small class="text-muted">${video.description ? video.description.substring(0, 50) + '...' : ''}</small>
                    </td>
                    <td><span class="badge bg-info">${video.category}</span></td>
                    <td>${video.duration ? video.duration + ' dk' : '-'}</td>
                    <td>${video.view_count || 0}</td>
                    <td>
                        <span class="badge ${video.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                            ${video.status === 'active' ? 'Aktif' : 'Pasif'}
                        </span>
                    </td>
                    <td>${new Date(video.created_at).toLocaleDateString('tr-TR')}</td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1" onclick="editVideo(${video.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteVideo(${video.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        
        // Kategorileri yükle
        function loadCategories() {
            fetch('../api/videos.php?categories=1')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('categoryFilter');
                    const currentValue = select.value;
                    
                    select.innerHTML = '<option value="">Tüm Kategoriler</option>';
                    
                    if (data.categories) {
                        data.categories.forEach(category => {
                            select.innerHTML += `<option value="${category}">${category}</option>`;
                        });
                    }
                    
                    select.value = currentValue;
                })
                .catch(error => console.error('Error loading categories:', error));
        }
        
        // İstatistikleri güncelle
        function updateStats(data) {
            document.getElementById('totalVideos').textContent = data.total || 0;
            document.getElementById('activeVideos').textContent = data.active_count || 0;
            document.getElementById('totalViews').textContent = data.total_views || 0;
            document.getElementById('totalCategories').textContent = data.category_count || 0;
        }
        
        // Sayfalama güncelle
        function updatePagination(currentPage, totalPages) {
            const pagination = document.getElementById('pagination');
            let html = '';
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            // Önceki sayfa
            if (currentPage > 1) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadVideos(${currentPage - 1})">Önceki</a>
                </li>`;
            }
            
            // Sayfa numaraları
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadVideos(${i})">${i}</a>
                </li>`;
            }
            
            // Sonraki sayfa
            if (currentPage < totalPages) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadVideos(${currentPage + 1})">Sonraki</a>
                </li>`;
            }
            
            pagination.innerHTML = html;
        }
        
        // Video önizleme
        function previewVideo(url) {
            const previewContainer = document.getElementById('videoPreviewContainer');
            const preview = document.getElementById('videoPreview');
            
            if (!url) {
                preview.style.display = 'none';
                return;
            }
            
            let embedHtml = '';
            
            // YouTube
            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                const videoId = extractYouTubeId(url);
                if (videoId) {
                    embedHtml = `<iframe width="100%" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
                }
            }
            // Vimeo
            else if (url.includes('vimeo.com')) {
                const videoId = extractVimeoId(url);
                if (videoId) {
                    embedHtml = `<iframe width="100%" height="315" src="https://player.vimeo.com/video/${videoId}" frameborder="0" allowfullscreen></iframe>`;
                }
            }
            // Doğrudan video
            else if (url.match(/\.(mp4|webm|ogg)$/i)) {
                embedHtml = `<video width="100%" height="315" controls>
                    <source src="${url}" type="video/mp4">
                    Tarayıcınız video etiketini desteklemiyor.
                </video>`;
            }
            
            if (embedHtml) {
                previewContainer.innerHTML = embedHtml;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
        
        // YouTube video ID çıkar
        function extractYouTubeId(url) {
            const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
            const match = url.match(regex);
            return match ? match[1] : null;
        }
        
        // Vimeo video ID çıkar
        function extractVimeoId(url) {
            const regex = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/;
            const match = url.match(regex);
            return match ? match[3] : null;
        }
        
        // Video düzenle
        function editVideo(id) {
            fetch(`../api/videos.php?id=${id}`)
                .then(response => response.json())
                .then(video => {
                    if (video.error) {
                        throw new Error(video.error);
                    }
                    
                    document.getElementById('videoId').value = video.id;
                    document.getElementById('videoTitle').value = video.title;
                    document.getElementById('videoCategory').value = video.category;
                    document.getElementById('videoDescription').value = video.description || '';
                    document.getElementById('videoUrl').value = video.video_url;
                    document.getElementById('videoDuration').value = video.duration || '';
                    document.getElementById('videoThumbnail').value = video.thumbnail_url || '';
                    document.getElementById('videoStatus').value = video.status;
                    document.getElementById('videoTags').value = video.tags || '';
                    
                    document.getElementById('videoModalTitle').textContent = 'Video Düzenle';
                    previewVideo(video.video_url);
                    
                    new bootstrap.Modal(document.getElementById('videoModal')).show();
                })
                .catch(error => {
                    alert('Hata: ' + error.message);
                });
        }
        
        // Video kaydet
        function saveVideo() {
            const form = document.getElementById('videoForm');
            const formData = new FormData(form);
            
            const videoData = {
                title: document.getElementById('videoTitle').value,
                category: document.getElementById('videoCategory').value,
                description: document.getElementById('videoDescription').value,
                video_url: document.getElementById('videoUrl').value,
                duration: document.getElementById('videoDuration').value || null,
                thumbnail_url: document.getElementById('videoThumbnail').value || null,
                status: document.getElementById('videoStatus').value,
                tags: document.getElementById('videoTags').value
            };
            
            const videoId = document.getElementById('videoId').value;
            const isEdit = videoId !== '';
            
            if (isEdit) {
                videoData.id = videoId;
            }
            
            const method = isEdit ? 'PUT' : 'POST';
            
            fetch('../api/videos.php', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(videoData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                alert(data.message || (isEdit ? 'Video güncellendi!' : 'Video eklendi!'));
                bootstrap.Modal.getInstance(document.getElementById('videoModal')).hide();
                loadVideos(currentPage);
                form.reset();
                document.getElementById('videoId').value = '';
                document.getElementById('videoModalTitle').textContent = 'Yeni Video Ekle';
                document.getElementById('videoPreview').style.display = 'none';
            })
            .catch(error => {
                alert('Hata: ' + error.message);
            });
        }
        
        // Video sil
        function deleteVideo(id) {
            if (!confirm('Bu videoyu silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            fetch('../api/videos.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                alert(data.message || 'Video silindi!');
                loadVideos(currentPage);
            })
            .catch(error => {
                alert('Hata: ' + error.message);
            });
        }
        
        // Modal kapandığında formu temizle
        document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('videoForm').reset();
            document.getElementById('videoId').value = '';
            document.getElementById('videoModalTitle').textContent = 'Yeni Video Ekle';
            document.getElementById('videoPreview').style.display = 'none';
        });
    </script>
</body>
</html>
