<?php
// File: public/logout.php
session_start();

// 1. Hapus semua data session
$_SESSION = array();

// 2. Hapus session cookie dari browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Hancurkan session server-side
session_destroy();

// 4. Set pesan logout berhasil (opsional)
$_SESSION['flash_message'] = [
    'type' => 'success',
    'message' => 'Anda telah berhasil logout'
];

// 5. Redirect ke halaman login
header("Location: login.php");
exit();
?>