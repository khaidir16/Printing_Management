<?php
// ========================================
// QUICK SETUP - SIMPLE DATABASE CREATOR
// ========================================

echo "<h1>🚀 Quick Setup - PrintPro Management</h1>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✅ Database connected</p>";
    
    // Disable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop tables
    $conn->exec("DROP TABLE IF EXISTS orders");
    $conn->exec("DROP TABLE IF EXISTS users");
    $conn->exec("DROP TABLE IF EXISTS products");
    $conn->exec("DROP TABLE IF EXISTS customers");
    
    echo "<p>✅ Old tables dropped</p>";
    
    // Create tables
    $conn->exec("
        CREATE TABLE customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $conn->exec("
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $conn->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            role ENUM('admin', 'staff', 'manager') DEFAULT 'staff',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $conn->exec("
        CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            customer_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            status ENUM('Pending', 'Proses', 'Selesai', 'Dibatalkan') DEFAULT 'Pending',
            order_date DATE NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    echo "<p>✅ Tables created</p>";
    
    // Insert data
    $conn->exec("
        INSERT INTO customers (name, email, phone, address) VALUES
        ('PT. Maju Jaya', 'info@majujaya.com', '021-1234567', 'Jakarta Selatan'),
        ('CV. Berkah Printing', 'order@berkah.com', '021-7654321', 'Jakarta Pusat')
    ");
    
    $conn->exec("
        INSERT INTO products (name, category, price, unit, description) VALUES
        ('Banner Vinyl', 'Banner', 25000.00, 'per m²', 'Banner vinyl outdoor'),
        ('Sticker Vinyl', 'Sticker', 15000.00, 'per m²', 'Sticker vinyl tahan air')
    ");
    
    $hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $conn->exec("
        INSERT INTO users (username, email, password, full_name, role) VALUES 
        ('admin', 'admin@printpro.com', '$hashedPassword', 'Administrator', 'admin'),
        ('staff1', 'staff1@printpro.com', '$hashedPassword', 'Staff Percetakan', 'staff'),
        ('staff2', 'staff2@printpro.com', '$hashedPassword', 'Staff Customer Service', 'staff'),
        ('manager1', 'manager1@printpro.com', '$hashedPassword', 'Manager Operasional', 'manager'),
        ('demo', 'demo@printpro.com', '$hashedPassword', 'Demo User', 'staff')
    ");
    
    $conn->exec("
        INSERT INTO orders (user_id, customer_id, product_id, quantity, unit_price, total_price, status, order_date) VALUES
        (1, 1, 1, 10, 25000.00, 250000.00, 'Selesai', '2024-01-15'),
        (2, 2, 2, 5, 15000.00, 75000.00, 'Proses', '2024-01-16')
    ");
    
    // Enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<p>✅ Sample data inserted</p>";
    
    // Show results
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    echo "<h2>🎉 SETUP COMPLETED!</h2>";
    echo "<p><strong>Users created: $userCount</strong></p>";
    echo "<p><strong>Password untuk semua user: admin123</strong></p>";
    
    echo "<h3>Login Credentials:</h3>";
    echo "<ul>";
    echo "<li>admin / admin123</li>";
    echo "<li>staff1 / admin123</li>";
    echo "<li>staff2 / admin123</li>";
    echo "<li>manager1 / admin123</li>";
    echo "<li>demo / admin123</li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 LOGIN NOW</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
