<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Auto-create table if not exists
    $check_table = $conn->query("SHOW TABLES LIKE 'products'");
    if ($check_table->rowCount() == 0) {
        $create_table = "
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($create_table);
        
        // Insert sample data
        $insert_data = "
        INSERT INTO products (name, category, price, unit, description) VALUES
        ('Banner Vinyl', 'Banner', 25000.00, 'per m²', 'Banner vinyl berkualitas tinggi untuk outdoor'),
        ('Sticker Vinyl', 'Sticker', 15000.00, 'per m²', 'Sticker vinyl tahan air untuk berbagai keperluan'),
        ('Brosur A4', 'Brosur', 2500.00, 'per lembar', 'Brosur A4 full color dengan kertas art paper'),
        ('Kartu Nama', 'Kartu', 150000.00, 'per 1000 pcs', 'Kartu nama premium dengan finishing glossy'),
        ('Spanduk', 'Banner', 35000.00, 'per m²', 'Spanduk bahan flexi untuk promosi'),
        ('Poster A3', 'Poster', 15000.00, 'per lembar', 'Poster A3 full color kertas art carton')";
        $conn->exec($insert_data);
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch($method) {
        case 'GET':
            $query = "SELECT * FROM products ORDER BY category, name";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Data produk berhasil diambil',
                'count' => count($products),
                'data' => $products
            ]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['name'])) {
                throw new Exception('Data produk tidak lengkap');
            }
            
            $query = "INSERT INTO products (name, category, price, unit, description) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $input['name'],
                $input['category'] ?? '',
                $input['price'] ?? 0,
                $input['unit'] ?? '',
                $input['description'] ?? ''
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'id' => $conn->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID produk tidak ditemukan');
            }
            
            $query = "UPDATE products SET name=?, category=?, price=?, unit=?, description=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $input['name'],
                $input['category'],
                $input['price'],
                $input['unit'],
                $input['description'],
                $input['id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produk berhasil diupdate'
            ]);
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID produk tidak ditemukan');
            }
            
            // Check if product has orders
            $checkQuery = "SELECT COUNT(*) as count FROM orders WHERE product_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$input['id']]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Produk tidak dapat dihapus karena sedang digunakan dalam pesanan');
            }
            
            $query = "DELETE FROM products WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$input['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Produk berhasil dihapus'
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