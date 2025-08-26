<?php
session_start();
require_once '../includes/functions.php';

// Log logout
if (isset($_SESSION['admin_username'])) {
    log_error("Admin logout: " . $_SESSION['admin_username'] . " from " . get_client_ip());
}

// Destroy session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: login.php');
exit;
?>
