<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch($method) {
        case 'GET':
            $query = "SELECT * FROM customers ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $customers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $customers[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Data berhasil diambil',
                'count' => count($customers),
                'data' => $customers
            ]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['name']) || !isset($input['email'])) {
                throw new Exception('Data tidak lengkap');
            }
            
            // Check if email already exists
            $checkQuery = "SELECT id FROM customers WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$input['email']]);
            
            if ($checkStmt->rowCount() > 0) {
                throw new Exception('Email sudah digunakan');
            }
            
            $query = "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['address'] ?? ''
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan',
                'id' => $conn->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID customer tidak ditemukan');
            }
            
            // Check if email already exists for other customers
            $checkQuery = "SELECT id FROM customers WHERE email = ? AND id != ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$input['email'], $input['id']]);
            
            if ($checkStmt->rowCount() > 0) {
                throw new Exception('Email sudah digunakan oleh customer lain');
            }
            
            $query = "UPDATE customers SET name=?, email=?, phone=?, address=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'],
                $input['address'],
                $input['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Customer berhasil diupdate'
            ]);
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID customer tidak ditemukan');
            }
            
            // Check if customer has orders
            $checkQuery = "SELECT COUNT(*) as count FROM orders WHERE customer_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$input['id']]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Customer tidak dapat dihapus karena memiliki pesanan');
            }
            
            $query = "DELETE FROM customers WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$input['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Customer berhasil dihapus'
            ]);
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
?>