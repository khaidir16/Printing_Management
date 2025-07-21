<?php
session_start();

// Fungsi untuk cek apakah user sudah login
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk cek role user
function requireRole($requiredRole) {
    requireLogin();
    
    if ($_SESSION['role'] !== $requiredRole && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied. Insufficient permissions.');
    }
}

// Fungsi untuk ambil data user yang sedang login
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email']
    ];
}

// Fungsi untuk cek apakah sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Auto logout setelah 8 jam tidak aktif
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        $sessionLifetime = 8 * 60 * 60; // 8 jam
        if (time() - $_SESSION['login_time'] > $sessionLifetime) {
            session_destroy();
            header('Location: login.php?timeout=1');
            exit();
        }
    }
}

// Panggil fungsi ini di setiap halaman yang dilindungi
checkSessionTimeout();
?>