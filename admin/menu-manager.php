<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Admin kontrolü
require_admin();

$page_title = 'Menü Yöneticisi';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
    
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
        
        .sortable-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 245, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: move;
            transition: all 0.3s ease;
        }
        
        .sortable-item:hover {
            background: rgba(0, 245, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .sortable-item.sortable-ghost {
            opacity: 0.4;
        }
        
        .menu-preview {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .menu-preview ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .menu-preview li {
            padding: 10px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .menu-preview li:last-child {
            border-bottom: none;
        }
        
        .menu-preview li i {
            margin-right: 10px;
            color: #00f5ff;
        }
        
        .text-muted {
            color: rgba(255, 255, 255, 0.6) !important;
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
                        <a class="nav-link" href="server-settings.php">
                            <i class="fas fa-server me-2"></i>Sunucu Ayarları
                        </a>
                        <a class="nav-link active" href="menu-manager.php">
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
                    <h2><i class="fas fa-bars me-2"></i><?php echo $page_title; ?></h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal">
                        <i class="fas fa-plus me-2"></i>Yeni Menü Öğesi
                    </button>
                </div>
                
                <div class="row">
                    <!-- Menu Items -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-list me-2"></i>Menü Öğeleri</h5>
                                <small class="text-muted">Sürükleyip bırakarak sıralayabilirsiniz</small>
                            </div>
                            <div class="card-body">
                                <div id="menuItems">
                                    <div class="text-center">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Yükleniyor...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Menu Preview -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-eye me-2"></i>Menü Önizleme</h5>
                            </div>
                            <div class="card-body">
                                <div class="menu-preview">
                                    <ul id="menuPreview">
                                        <li><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menu Modal -->
    <div class="modal fade" id="menuModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalTitle">Yeni Menü Öğesi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="menuForm">
                        <input type="hidden" id="menuId">
                        
                        <div class="mb-3">
                            <label for="menuTitle" class="form-label">Başlık *</label>
                            <input type="text" class="form-control" id="menuTitle" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="menuUrl" class="form-label">URL *</label>
                            <input type="text" class="form-control" id="menuUrl" required placeholder="/sayfa-adi veya https://example.com">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuIcon" class="form-label">İkon</label>
                                    <input type="text" class="form-control" id="menuIcon" placeholder="fas fa-home">
                                    <small class="text-muted">Font Awesome ikon sınıfı</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuTarget" class="form-label">Hedef</label>
                                    <select class="form-select" id="menuTarget">
                                        <option value="_self">Aynı Pencere</option>
                                        <option value="_blank">Yeni Pencere</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuOrder" class="form-label">Sıra</label>
                                    <input type="number" class="form-control" id="menuOrder" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuStatus" class="form-label">Durum</label>
                                    <select class="form-select" id="menuStatus">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Pasif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveMenu()">
                        <i class="fas fa-save me-2"></i>Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        let sortable;
        
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadMenus();
        });
        
        // Menüleri yükle
        function loadMenus() {
            fetch('../api/menus.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMenus(data.data);
                        updatePreview(data.data);
                        initSortable();
                    } else {
                        document.getElementById('menuItems').innerHTML = 
                            '<div class="text-center text-danger">Hata: ' + (data.message || 'Menüler yüklenemedi') + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('menuItems').innerHTML = 
                        '<div class="text-center text-danger">Menüler yüklenirken hata oluştu</div>';
                });
        }
        
        // Menüleri göster
        function displayMenus(menus) {
            const container = document.getElementById('menuItems');
            
            if (menus.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">Henüz menü öğesi bulunmuyor</div>';
                return;
            }
            
            container.innerHTML = menus.map(menu => `
                <div class="sortable-item" data-id="${menu.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-grip-vertical text-muted me-3"></i>
                                ${menu.icon ? `<i class="${menu.icon} me-2" style="color: #00f5ff;"></i>` : ''}
                                <strong>${menu.title}</strong>
                                <span class="badge ${menu.status === 'active' ? 'bg-success' : 'bg-secondary'} ms-2">
                                    ${menu.status === 'active' ? 'Aktif' : 'Pasif'}
                                </span>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-link me-1"></i>${menu.url}
                                <span class="ms-3"><i class="fas fa-sort-numeric-up me-1"></i>Sıra: ${menu.order_num}</span>
                                ${menu.target === '_blank' ? '<span class="ms-3"><i class="fas fa-external-link-alt me-1"></i>Yeni Pencere</span>' : ''}
                            </div>
                        </div>
                        <div class="ms-3">
                            <button class="btn btn-sm btn-warning me-2" onclick="editMenu(${menu.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMenu(${menu.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Önizlemeyi güncelle
        function updatePreview(menus) {
            const preview = document.getElementById('menuPreview');
            
            if (menus.length === 0) {
                preview.innerHTML = '<li class="text-muted">Menü öğesi bulunmuyor</li>';
                return;
            }
            
            const activeMenus = menus.filter(menu => menu.status === 'active');
            
            preview.innerHTML = activeMenus.map(menu => `
                <li>
                    ${menu.icon ? `<i class="${menu.icon}"></i>` : '<i class="fas fa-circle" style="font-size: 6px;"></i>'}
                    ${menu.title}
                    ${menu.target === '_blank' ? '<i class="fas fa-external-link-alt ms-2" style="font-size: 10px;"></i>' : ''}
                </li>
            `).join('');
        }
        
        // Sürükle-bırak özelliğini başlat
        function initSortable() {
            const container = document.getElementById('menuItems');
            
            if (sortable) {
                sortable.destroy();
            }
            
            sortable = Sortable.create(container, {
                handle: '.fa-grip-vertical',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    updateMenuOrder();
                }
            });
        }
        
        // Menü sırasını güncelle
        function updateMenuOrder() {
            const items = document.querySelectorAll('.sortable-item');
            const updates = [];
            
            items.forEach((item, index) => {
                const id = item.getAttribute('data-id');
                updates.push({
                    id: parseInt(id),
                    order_num: index + 1
                });
            });
            
            // Sıralamayı sunucuya gönder
            fetch('../api/menus.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_order',
                    items: updates
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Sıralama güncellenemedi:', data.message);
                    loadMenus(); // Hata durumunda yeniden yükle
                }
            })
            .catch(error => {
                console.error('Sıralama hatası:', error);
                loadMenus(); // Hata durumunda yeniden yükle
            });
        }
        
        // Menü düzenle
        function editMenu(id) {
            fetch(`../api/menus.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const menu = data.data;
                        
                        document.getElementById('menuId').value = menu.id;
                        document.getElementById('menuTitle').value = menu.title;
                        document.getElementById('menuUrl').value = menu.url;
                        document.getElementById('menuIcon').value = menu.icon || '';
                        document.getElementById('menuTarget').value = menu.target || '_self';
                        document.getElementById('menuOrder').value = menu.order_num || 0;
                        document.getElementById('menuStatus').value = menu.status;
                        
                        document.getElementById('menuModalTitle').textContent = 'Menü Düzenle';
                        new bootstrap.Modal(document.getElementById('menuModal')).show();
                    } else {
                        alert('Menü yüklenemedi: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Hata: ' + error.message);
                });
        }
        
        // Menü kaydet
        function saveMenu() {
            const form = document.getElementById('menuForm');
            const menuId = document.getElementById('menuId').value;
            const isEdit = menuId !== '';
            
            const menuData = {
                title: document.getElementById('menuTitle').value,
                url: document.getElementById('menuUrl').value,
                icon: document.getElementById('menuIcon').value,
                target: document.getElementById('menuTarget').value,
                order_num: parseInt(document.getElementById('menuOrder').value) || 0,
                status: document.getElementById('menuStatus').value
            };
            
            if (isEdit) {
                menuData.id = parseInt(menuId);
            }
            
            const method = isEdit ? 'PUT' : 'POST';
            
            fetch('../api/menus.php', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(menuData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || (isEdit ? 'Menü güncellendi!' : 'Menü eklendi!'));
                    bootstrap.Modal.getInstance(document.getElementById('menuModal')).hide();
                    loadMenus();
                    form.reset();
                    document.getElementById('menuId').value = '';
                    document.getElementById('menuModalTitle').textContent = 'Yeni Menü Öğesi';
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Hata: ' + error.message);
            });
        }
        
        // Menü sil
        function deleteMenu(id) {
            if (!confirm('Bu menü öğesini silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            fetch(`../api/menus.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Menü silindi!');
                    loadMenus();
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                alert('Hata: ' + error.message);
            });
        }
        
        // Modal kapandığında formu temizle
        document.getElementById('menuModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('menuForm').reset();
            document.getElementById('menuId').value = '';
            document.getElementById('menuModalTitle').textContent = 'Yeni Menü Öğesi';
        });
    </script>
</body>
</html>
