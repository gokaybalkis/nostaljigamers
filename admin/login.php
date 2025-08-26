<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$login_attempts = $_SESSION['login_attempts'] ?? 0;
$last_attempt = $_SESSION['last_attempt'] ?? 0;

// Check if user is locked out
if ($login_attempts >= MAX_LOGIN_ATTEMPTS && (time() - $last_attempt) < LOGIN_LOCKOUT_TIME) {
    $remaining_time = LOGIN_LOCKOUT_TIME - (time() - $last_attempt);
    $error_message = "Çok fazla başarısız giriş denemesi. " . ceil($remaining_time / 60) . " dakika sonra tekrar deneyin.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error_message = 'Güvenlik hatası. Sayfayı yenileyin.';
    } elseif (empty($username) || empty($password)) {
        $error_message = 'Kullanıcı adı ve şifre gerekli.';
    } else {
        try {
            // Check for Xau/626200 credentials
            if ($username === 'Xau' && $password === '626200') {
                // Successful login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = 1;
                $_SESSION['admin_username'] = 'Xau';
                $_SESSION['login_time'] = time();
                
                // Reset login attempts
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt']);
                
                // Log successful login
                log_error("Admin login successful: " . $username . " from " . get_client_ip());
                
                header('Location: index.php');
                exit;
            } else {
                // Check database for other users
                $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Successful login
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['login_time'] = time();
                    
                    // Reset login attempts
                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['last_attempt']);
                    
                    // Update last login
                    $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Log successful login
                    log_error("Admin login successful: " . $username . " from " . get_client_ip());
                    
                    header('Location: index.php');
                    exit;
                } else {
                    // Failed login
                    $_SESSION['login_attempts'] = $login_attempts + 1;
                    $_SESSION['last_attempt'] = time();
                    
                    log_error("Admin login failed: " . $username . " from " . get_client_ip());
                    
                    $error_message = 'Kullanıcı adı veya şifre hatalı.';
                }
            }
        } catch (PDOException $e) {
            log_error("Admin login database error: " . $e->getMessage());
            $error_message = 'Sistem hatası. Lütfen daha sonra tekrar deneyin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Nostalji Gamers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00ff88;
            --secondary-color: #0066ff;
            --dark-bg: #0a0a0f;
            --dark-card: #1a1a2e;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-card) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(26, 26, 46, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            font-family: 'Orbitron', monospace;
            font-weight: 900;
            font-size: 2rem;
            color: var(--primary-color);
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
        
        .form-control:focus {
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
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: var(--dark-bg);
            font-weight: 700;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
            color: var(--dark-bg);
        }
        
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ff6b6b;
            border-radius: 10px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: var(--primary-color);
        }
        
        .input-group-text {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-secondary);
        }
        
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 255, 136, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .credentials-info {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .credentials-info h6 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .credentials-info small {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="login-title">
                <i class="fas fa-shield-alt me-2"></i>
                ADMIN
            </h1>
            <p class="login-subtitle">Nostalji Gamers Yönetim Paneli</p>
        </div>
        
        <div class="credentials-info">
            <h6><i class="fas fa-key me-2"></i>Giriş Bilgileri</h6>
            <small>Kullanıcı: <strong>Xau</strong> | Şifre: <strong>626200</strong></small>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo sanitize_input($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-2"></i>
                    Kullanıcı Adı
                </label>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Kullanıcı adınızı girin" required 
                       value="<?php echo sanitize_input($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>
                    Şifre
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Şifrenizi girin" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-login" id="loginBtn" 
                    <?php echo $error_message && strpos($error_message, 'dakika') !== false ? 'disabled' : ''; ?>>
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Giriş Yap
                </span>
                <div class="loading"></div>
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Ana Sayfaya Dön
            </a>
        </div>
        
        <?php if ($login_attempts > 0): ?>
            <div class="text-center mt-3">
                <small class="text-secondary">
                    Başarısız deneme: <?php echo $login_attempts; ?>/<?php echo MAX_LOGIN_ATTEMPTS; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = btn.querySelector('.btn-text');
            const loading = btn.querySelector('.loading');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            loading.style.display = 'inline-block';
        });
        
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Enter key handling
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>
