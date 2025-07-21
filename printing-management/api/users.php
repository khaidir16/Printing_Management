<?php
// ========================================
// USERS API - CRUD OPERATIONS
// ========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch($method) {
        case 'GET':
            getUsers($conn);
            break;
            
        case 'POST':
            createUser($conn);
            break;
            
        case 'PUT':
            updateUser($conn);
            break;
            
        case 'DELETE':
            deleteUser($conn);
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

// GET - Ambil semua users
function getUsers($conn) {
    $query = "SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY role, username";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Data users berhasil diambil',
        'data' => $users,
        'count' => count($users)
    ]);
}

// POST - Buat user baru
function createUser($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['email']) || !isset($input['password'])) {
        throw new Exception('Data user tidak lengkap');
    }
    
    // Validasi input
    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    $full_name = trim($input['full_name'] ?? '');
    $role = $input['role'] ?? 'staff';
    $is_active = $input['is_active'] ?? true;
    
    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email tidak valid');
    }
    
    // Validasi password
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
    $insertQuery = "INSERT INTO users (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([$username, $email, $hashedPassword, $full_name, $role, $is_active]);
    
    $userId = $conn->lastInsertId();
    
    // Ambil data user yang baru dibuat
    $selectQuery = "SELECT id, username, email, full_name, role, is_active, created_at FROM users WHERE id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->execute([$userId]);
    $newUser = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'User berhasil dibuat',
        'data' => $newUser
    ]);
}

// PUT - Update user
function updateUser($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID user tidak ditemukan');
    }
    
    $id = $input['id'];
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $full_name = trim($input['full_name'] ?? '');
    $role = $input['role'] ?? 'staff';
    $is_active = $input['is_active'] ?? true;
    
    // Check user exists
    $checkQuery = "SELECT id FROM users WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() == 0) {
        throw new Exception('User tidak ditemukan');
    }
    
    // Check username dan email conflict (exclude current user)
    if (!empty($username) || !empty($email)) {
        $conflictQuery = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $conflictStmt = $conn->prepare($conflictQuery);
        $conflictStmt->execute([$username, $email, $id]);
        
        if ($conflictStmt->rowCount() > 0) {
            throw new Exception('Username atau email sudah digunakan user lain');
        }
    }
    
    // Build update query
    $updateFields = [];
    $updateValues = [];
    
    if (!empty($username)) {
        $updateFields[] = "username = ?";
        $updateValues[] = $username;
    }
    
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }
        $updateFields[] = "email = ?";
        $updateValues[] = $email;
    }
    
    if (!empty($full_name)) {
        $updateFields[] = "full_name = ?";
        $updateValues[] = $full_name;
    }
    
    if (!empty($role)) {
        $updateFields[] = "role = ?";
        $updateValues[] = $role;
    }
    
    $updateFields[] = "is_active = ?";
    $updateValues[] = $is_active;
    
    // Update password jika ada
    if (!empty($input['password'])) {
        if (strlen($input['password']) < 6) {
            throw new Exception('Password minimal 6 karakter');
        }
        $updateFields[] = "password = ?";
        $updateValues[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    
    $updateValues[] = $id;
    
    $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->execute($updateValues);
    
    // Ambil data user yang sudah diupdate
    $selectQuery = "SELECT id, username, email, full_name, role, is_active, created_at FROM users WHERE id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->execute([$id]);
    $updatedUser = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'User berhasil diupdate',
        'data' => $updatedUser
    ]);
}

// DELETE - Hapus user
function deleteUser($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID user tidak ditemukan');
    }
    
    $id = $input['id'];
    
    // Check user exists
    $checkQuery = "SELECT username FROM users WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() == 0) {
        throw new Exception('User tidak ditemukan');
    }
    
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Jangan hapus admin utama
    if ($user['username'] === 'admin') {
        throw new Exception('Admin utama tidak bisa dihapus');
    }
    
    // Set orders.user_id to NULL before deleting user
    $updateOrdersQuery = "UPDATE orders SET user_id = NULL WHERE user_id = ?";
    $updateOrdersStmt = $conn->prepare($updateOrdersQuery);
    $updateOrdersStmt->execute([$id]);
    
    // Delete user
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'User berhasil dihapus'
    ]);
}
?>
