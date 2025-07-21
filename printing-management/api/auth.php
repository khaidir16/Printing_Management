<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch($method) {
        case 'POST':
            if ($action === 'login') {
                handleLogin($conn);
            } elseif ($action === 'logout') {
                handleLogout();
            } elseif ($action === 'register') {
                handleRegister($conn);
            }
            break;
            
        case 'GET':
            if ($action === 'check') {
                checkSession();
            } elseif ($action === 'profile') {
                getUserProfile($conn);
            }
            break;
            
        default:
            throw new Exception('Method tidak didukung');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// ✅ FUNGSI LOGIN
function handleLogin($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        throw new Exception('Username dan password harus diisi');
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    // Cari user berdasarkan username atau email
    $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([$username, $username]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Username atau password salah');
    }
    
    // Verifikasi password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Username atau password salah');
    }
    
    // Update last login
    $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['login_time'] = time();
    
    // Response tanpa password
    unset($user['password']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => $user,
        'session_id' => session_id()
    ]);
}

// ✅ FUNGSI LOGOUT
function handleLogout() {
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil'
    ]);
}

// ✅ FUNGSI REGISTER (YANG HILANG!)
function handleRegister($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password']) || !isset($input['email'])) {
        throw new Exception('Data registrasi tidak lengkap');
    }
    
    // Validasi input
    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    $full_name = trim($input['full_name'] ?? '');
    $role = $input['role'] ?? 'staff';
    
    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validasi password strength
    if (strlen($password) < 6) {
        throw new Exception('Password minimal 6 karakter');
    }
    
    // Check username dan email sudah ada
    $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$username, $email]);
    
    if ($checkStmt->rowCount() > 0) {
        throw new Exception('Username atau email sudah digunakan');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $insertQuery = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([$username, $email, $hashedPassword, $full_name, $role]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'user_id' => $conn->lastInsertId()
    ]);
}

// ✅ FUNGSI CEK SESSION
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session tidak valid',
            'logged_in' => false
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Session valid',
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'email' => $_SESSION['email']
        ]
    ]);
}

// ✅ FUNGSI GET USER PROFILE
function getUserProfile($conn) {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User tidak login');
    }
    
    $query = "SELECT id, username, email, full_name, role, last_login, created_at FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User tidak ditemukan');
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}
?>
