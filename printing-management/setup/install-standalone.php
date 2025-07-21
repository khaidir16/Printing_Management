<?php
// ========================================
// STANDALONE INSTALLER - TIDAK PERLU FILE SQL EKSTERNAL
// ========================================

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>PrintPro Management - Standalone Installer</title>";
echo "<style>";
echo "* { margin: 0; padding: 0; box-sizing: border-box; }";
echo "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }";
echo ".header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }";
echo ".header h1 { font-size: 2.5em; margin-bottom: 10px; }";
echo ".header p { font-size: 1.2em; opacity: 0.9; }";
echo ".content { padding: 30px; }";
echo ".step { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 10px; border-left: 5px solid #007bff; }";
echo ".step h3 { color: #007bff; margin-bottom: 15px; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #28a745; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #dc3545; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #ffc107; }";
echo ".info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 5px solid #007bff; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #007bff; color: white; }";
echo "tr:hover { background: #f5f5f5; }";
echo ".btn { display: inline-block; padding: 12px 25px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: 600; transition: all 0.3s; }";
echo ".btn:hover { background: #0056b3; transform: translateY(-2px); }";
echo ".btn-success { background: #28a745; }";
echo ".btn-success:hover { background: #1e7e34; }";
echo ".progress { background: #e9ecef; border-radius: 10px; height: 20px; margin: 20px 0; overflow: hidden; }";
echo ".progress-bar { background: linear-gradient(90deg, #28a745, #20c997); height: 100%; transition: width 0.5s; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>🖨️ PrintPro Management</h1>";
echo "<p>Sistem Manajemen Percetakan Professional</p>";
echo "<p><strong>Standalone Installer (No External Files)</strong></p>";
echo "</div>";

echo "<div class='content'>";

try {
    // Progress bar
    echo "<div class='progress'>";
    echo "<div class='progress-bar' style='width: 0%' id='progressBar'></div>";
    echo "</div>";
    echo "<p id='progressText'>Memulai instalasi standalone...</p>";
    
    // STEP 1: Check PHP Extensions
    echo "<div class='step'>";
    echo "<h3>📋 STEP 1: Checking PHP Requirements</h3>";
    
    $requirements = [
        'PDO' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'JSON' => extension_loaded('json'),
        'Session' => extension_loaded('session')
    ];
    
    echo "<table>";
    echo "<tr><th>Requirement</th><th>Status</th></tr>";
    
    $allOk = true;
    foreach ($requirements as $req => $status) {
        $statusText = $status ? '✅ OK' : '❌ Missing';
        $statusColor = $status ? 'color: green;' : 'color: red;';
        echo "<tr><td>$req</td><td style='$statusColor'><strong>$statusText</strong></td></tr>";
        if (!$status) $allOk = false;
    }
    echo "</table>";
    
    if (!$allOk) {
        throw new Exception("PHP requirements not met. Please install missing extensions.");
    }
    
    echo "<div class='success'>✅ All PHP requirements satisfied</div>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '15%'; document.getElementById('progressText').innerText = 'PHP requirements checked...';</script>";
    
    // STEP 2: Database Connection
    echo "<div class='step'>";
    echo "<h3>🔌 STEP 2: Testing Database Connection</h3>";
    
    require_once '../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Cannot connect to database. Please check your database configuration.");
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    echo "<div class='info'>📊 Connected to: printing_management database</div>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '30%'; document.getElementById('progressText').innerText = 'Database connection established...';</script>";
    
    // STEP 3: Drop existing tables
    echo "<div class='step'>";
    echo "<h3>🗑️ STEP 3: Cleaning Existing Tables</h3>";
    
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['orders', 'users', 'products', 'customers'];
    foreach ($tables as $table) {
        try {
            $conn->exec("DROP TABLE IF EXISTS $table");
            echo "<div class='success'>✅ Dropped table: $table</div>";
        } catch (Exception $e) {
            echo "<div class='info'>ℹ️ Table $table didn't exist</div>";
        }
    }
    
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '45%'; document.getElementById('progressText').innerText = 'Existing tables cleaned...';</script>";
    
    // STEP 4: Create customers table
    echo "<div class='step'>";
    echo "<h3>👥 STEP 4: Creating Customers Table</h3>";
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
    
    echo "<script>document.getElementById('progressBar').style.width = '55%'; document.getElementById('progressText').innerText = 'Customers table created...';</script>";
    
    // STEP 5: Create products table
    echo "<div class='step'>";
    echo "<h3>📦 STEP 5: Creating Products Table</h3>";
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
    
    echo "<script>document.getElementById('progressBar').style.width = '65%'; document.getElementById('progressText').innerText = 'Products table created...';</script>";
    
    // STEP 6: Create users table
    echo "<div class='step'>";
    echo "<h3>👤 STEP 6: Creating Users Table</h3>";
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role)
        )
    ");
    echo "<div class='success'>✅ Created users table</div>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '75%'; document.getElementById('progressText').innerText = 'Users table created...';</script>";
    
    // STEP 7: Create orders table
    echo "<div class='step'>";
    echo "<h3>📋 STEP 7: Creating Orders Table</h3>";
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
    
    echo "<script>document.getElementById('progressBar').style.width = '85%'; document.getElementById('progressText').innerText = 'Orders table created...';</script>";
    
    // STEP 8: Insert sample data
    echo "<div class='step'>";
    echo "<h3>📊 STEP 8: Inserting Sample Data</h3>";
    
    // Insert customers
    $conn->exec("
        INSERT INTO customers (name, email, phone, address) VALUES
        ('PT. Maju Jaya', 'info@majujaya.com', '021-1234567', 'Jl. Sudirman No. 123, Jakarta Selatan'),
        ('CV. Berkah Printing', 'order@berkah.com', '021-7654321', 'Jl. Thamrin No. 456, Jakarta Pusat'),
        ('Toko Sinar Terang', 'sinar@terang.com', '021-9876543', 'Jl. Gatot Subroto No. 789, Jakarta Timur'),
        ('UD. Cahaya Mandiri', 'cahaya@mandiri.com', '021-5555666', 'Jl. Kebon Jeruk No. 45, Jakarta Barat'),
        ('PT. Digital Print', 'digital@print.com', '021-7777888', 'Jl. Cikini Raya No. 67, Jakarta Pusat')
    ");
    echo "<div class='success'>✅ Inserted 5 customers</div>";
    
    // Insert products
    $conn->exec("
        INSERT INTO products (name, category, price, unit, description) VALUES
        ('Banner Vinyl', 'Banner', 25000.00, 'per m²', 'Banner vinyl berkualitas tinggi untuk outdoor'),
        ('Sticker Vinyl', 'Sticker', 15000.00, 'per m²', 'Sticker vinyl tahan air untuk berbagai keperluan'),
        ('Brosur A4', 'Brosur', 2500.00, 'per lembar', 'Brosur A4 full color dengan kertas art paper'),
        ('Kartu Nama', 'Kartu', 150000.00, 'per 1000 pcs', 'Kartu nama premium dengan finishing glossy'),
        ('Spanduk Kain', 'Banner', 35000.00, 'per m²', 'Spanduk kain untuk indoor dan outdoor'),
        ('Poster A3', 'Poster', 15000.00, 'per lembar', 'Poster A3 full color kertas photo'),
        ('X-Banner', 'Display', 85000.00, 'per unit', 'X-Banner portable untuk promosi'),
        ('Roll Banner', 'Display', 125000.00, 'per unit', 'Roll Banner premium dengan stand')
    ");
    echo "<div class='success'>✅ Inserted 8 products</div>";
    
    // Insert users (Password: admin123 untuk semua)
    $hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $conn->exec("
        INSERT INTO users (username, email, password, full_name, role, is_active) VALUES 
        ('admin', 'admin@printpro.com', '$hashedPassword', 'Administrator Utama', 'admin', TRUE),
        ('superadmin', 'superadmin@printpro.com', '$hashedPassword', 'Super Administrator', 'admin', TRUE),
        ('manager1', 'manager1@printpro.com', '$hashedPassword', 'Manager Operasional', 'manager', TRUE),
        ('manager2', 'manager2@printpro.com', '$hashedPassword', 'Manager Penjualan', 'manager', TRUE),
        ('staff1', 'staff1@printpro.com', '$hashedPassword', 'Staff Produksi', 'staff', TRUE),
        ('staff2', 'staff2@printpro.com', '$hashedPassword', 'Staff Customer Service', 'staff', TRUE),
        ('staff3', 'staff3@printpro.com', '$hashedPassword', 'Staff Design', 'staff', TRUE),
        ('staff4', 'staff4@printpro.com', '$hashedPassword', 'Staff Finishing', 'staff', TRUE),
        ('demo', 'demo@printpro.com', '$hashedPassword', 'Demo User', 'staff', TRUE),
        ('guest', 'guest@printpro.com', '$hashedPassword', 'Guest User', 'staff', FALSE)
    ");
    echo "<div class='success'>✅ Inserted 10 users</div>";
    
    // Insert orders
    $conn->exec("
        INSERT INTO orders (user_id, customer_id, product_id, quantity, unit_price, total_price, status, order_date, notes) VALUES
        (1, 1, 1, 10, 25000.00, 250000.00, 'Selesai', '2024-01-15', 'Banner untuk grand opening'),
        (2, 2, 2, 5, 15000.00, 75000.00, 'Proses', '2024-01-16', 'Sticker untuk promosi'),
        (3, 3, 3, 1000, 2500.00, 2500000.00, 'Pending', '2024-01-17', 'Brosur company profile'),
        (4, 4, 4, 2, 150000.00, 300000.00, 'Selesai', '2024-01-18', 'Kartu nama direktur'),
        (5, 5, 5, 8, 35000.00, 280000.00, 'Proses', '2024-01-19', 'Spanduk untuk event'),
        (1, 1, 6, 50, 15000.00, 750000.00, 'Pending', '2024-01-20', 'Poster promosi produk'),
        (2, 2, 7, 3, 85000.00, 255000.00, 'Selesai', '2024-01-21', 'X-Banner untuk pameran'),
        (3, 3, 8, 2, 125000.00, 250000.00, 'Proses', '2024-01-22', 'Roll Banner untuk showroom')
    ");
    echo "<div class='success'>✅ Inserted 8 orders</div>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '95%'; document.getElementById('progressText').innerText = 'Sample data inserted...';</script>";
    
    // STEP 9: Verification
    echo "<div class='step'>";
    echo "<h3>✅ STEP 9: Verifying Installation</h3>";
    
    $tables = ['customers', 'products', 'users', 'orders'];
    echo "<table>";
    echo "<tr><th>Table</th><th>Records</th><th>Status</th></tr>";
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td>$count records</td>";
        echo "<td style='color: green;'>✅ OK</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '100%'; document.getElementById('progressText').innerText = 'Installation completed successfully!';</script>";
    
    // STEP 10: Show Users
    echo "<div class='step'>";
    echo "<h3>👥 STEP 10: User Accounts Created</h3>";
    
    $stmt = $conn->query("SELECT id, username, email, full_name, role, is_active FROM users ORDER BY role, username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    
    foreach ($users as $user) {
        $status = $user['is_active'] ? '✅ Active' : '❌ Inactive';
        $roleColors = [
            'admin' => 'background: #dc3545; color: white;',
            'manager' => 'background: #ffc107; color: black;',
            'staff' => 'background: #28a745; color: white;'
        ];
        $roleStyle = $roleColors[$user['role']] ?? '';
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td><span style='$roleStyle padding: 4px 8px; border-radius: 4px; font-size: 0.8em;'>" . strtoupper($user['role']) . "</span></td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Final Success
    echo "<div class='success' style='text-align: center; padding: 30px;'>";
    echo "<h2>🎉 STANDALONE INSTALLATION COMPLETED!</h2>";
    echo "<p style='font-size: 1.2em; margin: 20px 0;'>PrintPro Management System is ready to use!</p>";
    
    echo "<div style='margin: 30px 0;'>";
    echo "<h3>🔑 Default Login Credentials:</h3>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<p><strong>Password untuk semua user: admin123</strong></p>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;'>";
    echo "<div><strong>👑 Admin:</strong><br>admin / admin123<br>superadmin / admin123</div>";
    echo "<div><strong>👔 Manager:</strong><br>manager1 / admin123<br>manager2 / admin123</div>";
    echo "<div><strong>👷 Staff:</strong><br>staff1 / admin123<br>staff2 / admin123<br>staff3 / admin123<br>staff4 / admin123</div>";
    echo "<div><strong>🧪 Demo:</strong><br>demo / admin123<br>guest / admin123</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin: 30px 0;'>";
    echo "<a href='../login.php' class='btn btn-success' style='font-size: 1.2em; padding: 15px 30px;'>🔐 LOGIN NOW</a>";
    echo "<a href='../index.php' class='btn' style='font-size: 1.2em; padding: 15px 30px;'>🏠 GO TO DASHBOARD</a>";
    echo "<a href='../test-users.php' class='btn' style='font-size: 1.2em; padding: 15px 30px;'>🧪 TEST USERS</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ STANDALONE INSTALLATION FAILED</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h4>🔧 Troubleshooting:</h4>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/WAMP is running</li>";
    echo "<li>Check if MySQL service is started</li>";
    echo "<li>Verify database connection in config/database.php</li>";
    echo "<li>Make sure database 'printing_management' exists</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
