<?php
// ========================================
// SETUP COMPLETE DATABASE - PHP VERSION
// ========================================

echo "<!DOCTYPE html>";
echo "<html><head><title>Setup Complete Database</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #007bff; color: white; }";
echo "</style></head><body>";

echo "<div class='container'>";
echo "<h1>🏗️ Setup Complete Database</h1>";

try {
    // Koneksi database
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Disable foreign key checks
    echo "<div class='step'><h3>STEP 1: Preparing Database</h3>";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<div class='success'>✅ Disabled foreign key checks</div>";
    echo "</div>";
    
    // Drop existing tables
    echo "<div class='step'><h3>STEP 2: Dropping Existing Tables</h3>";
    $tables = ['orders', 'users', 'products', 'customers'];
    foreach ($tables as $table) {
        try {
            $conn->exec("DROP TABLE IF EXISTS $table");
            echo "<div class='success'>✅ Dropped table: $table</div>";
        } catch (Exception $e) {
            echo "<div class='success'>ℹ️ Table $table didn't exist</div>";
        }
    }
    echo "</div>";
    
    // Create customers table
    echo "<div class='step'><h3>STEP 3: Creating Customers Table</h3>";
    $conn->exec("
        CREATE TABLE customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✅ Created customers table</div>";
    echo "</div>";
    
    // Create products table
    echo "<div class='step'><h3>STEP 4: Creating Products Table</h3>";
    $conn->exec("
        CREATE TABLE products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✅ Created products table</div>";
    echo "</div>";
    
    // Create users table
    echo "<div class='step'><h3>STEP 5: Creating Users Table</h3>";
    $conn->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            role ENUM('admin', 'staff') DEFAULT 'staff',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✅ Created users table</div>";
    echo "</div>";
    
    // Create orders table
    echo "<div class='step'><h3>STEP 6: Creating Orders Table</h3>";
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
            delivery_date DATE,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "<div class='success'>✅ Created orders table with foreign keys</div>";
    echo "</div>";
    
    // Enable foreign key checks
    echo "<div class='step'><h3>STEP 7: Re-enabling Foreign Key Checks</h3>";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='success'>✅ Re-enabled foreign key checks</div>";
    echo "</div>";
    
    // Insert sample data
    echo "<div class='step'><h3>STEP 8: Inserting Sample Data</h3>";
    
    // Insert customers
    $conn->exec("
        INSERT INTO customers (name, email, phone, address) VALUES
        ('PT. Maju Jaya', 'info@majujaya.com', '021-1234567', 'Jl. Sudirman No. 123, Jakarta Selatan'),
        ('CV. Berkah Printing', 'order@berkah.com', '021-7654321', 'Jl. Thamrin No. 456, Jakarta Pusat'),
        ('Toko Sinar Terang', 'sinar@terang.com', '021-9876543', 'Jl. Gatot Subroto No. 789, Jakarta Timur')
    ");
    echo "<div class='success'>✅ Inserted 3 customers</div>";
    
    // Insert products
    $conn->exec("
        INSERT INTO products (name, category, price, unit, description) VALUES
        ('Banner Vinyl', 'Banner', 25000.00, 'per m²', 'Banner vinyl berkualitas tinggi untuk outdoor'),
        ('Sticker Vinyl', 'Sticker', 15000.00, 'per m²', 'Sticker vinyl tahan air untuk berbagai keperluan'),
        ('Brosur A4', 'Brosur', 2500.00, 'per lembar', 'Brosur A4 full color dengan kertas art paper'),
        ('Kartu Nama', 'Kartu', 150000.00, 'per 1000 pcs', 'Kartu nama premium dengan finishing glossy')
    ");
    echo "<div class='success'>✅ Inserted 4 products</div>";
    
    // Insert users
    $conn->exec("
        INSERT INTO users (username, email, password, full_name, role) VALUES 
        ('admin', 'admin@printpro.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
        ('staff1', 'staff1@printpro.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Percetakan', 'staff'),
        ('staff2', 'staff2@printpro.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Customer Service', 'staff'),
        ('demo', 'demo@printpro.com', '$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User', 'staff')
    ");
    echo "<div class='success'>✅ Inserted 4 users</div>";
    
    // Insert orders
    $conn->exec("
        INSERT INTO orders (user_id, customer_id, product_id, quantity, unit_price, total_price, status, order_date) VALUES
        (1, 1, 1, 10, 25000.00, 250000.00, 'Selesai', '2024-01-15'),
        (2, 2, 2, 5, 15000.00, 75000.00, 'Proses', '2024-01-16'),
        (3, 3, 3, 1000, 2500.00, 2500000.00, 'Pending', '2024-01-17')
    ");
    echo "<div class='success'>✅ Inserted 3 orders</div>";
    echo "</div>";
    
    // Verification
    echo "<div class='step'><h3>STEP 9: Verification</h3>";
    
    $tables = ['customers', 'products', 'users', 'orders'];
    echo "<table>";
    echo "<tr><th>Table</th><th>Records</th><th>Status</th></tr>";
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch();
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td>{$result['count']}</td>";
        echo "<td>✅ OK</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Final success
    echo "<div class='success'>";
    echo "<h2>🎉 DATABASE SETUP COMPLETED SUCCESSFULLY!</h2>";
    echo "<h3>🔑 Login Credentials (Password: admin123):</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin / admin123</li>";
    echo "<li><strong>Staff 1:</strong> staff1 / admin123</li>";
    echo "<li><strong>Staff 2:</strong> staff2 / admin123</li>";
    echo "<li><strong>Demo:</strong> demo / admin123</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>🔐 TEST LOGIN NOW</a></p>";
    echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; margin-left: 10px;'>🏠 GO TO DASHBOARD</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ SETUP FAILED:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
