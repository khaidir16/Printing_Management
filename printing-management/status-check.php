<!DOCTYPE html>
<html>
<head>
    <title>PrintPro System Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>🖨️ PrintPro System Status Check</h1>
    
    <?php
    // 1. Cek PHP Version
    echo "<div class='status success'>";
    echo "<h3>✅ PHP Version: " . phpversion() . "</h3>";
    echo "</div>";
    
    // 2. Cek Database Connection
    try {
        $conn = new PDO("mysql:host=localhost;dbname=printing_management", "root", "");
        echo "<div class='status success'>";
        echo "<h3>✅ Database Connection: OK</h3>";
        echo "</div>";
        
        // 3. Cek Tables
        $tables = ['customers', 'products', 'orders', 'users'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<div class='status success'>";
                echo "<h4>✅ Table '$table': $count records</h4>";
                echo "</div>";
            } catch (Exception $e) {
                echo "<div class='status warning'>";
                echo "<h4>⚠️ Table '$table': Not found or empty</h4>";
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div class='status error'>";
        echo "<h3>❌ Database Connection: FAILED</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    // 4. Cek File Structure
    $files = [
        'config/database.php',
        'api/customers.php',
        'api/products.php',
        'api/orders.php',
        'index.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "<div class='status success'>";
            echo "<h4>✅ File '$file': EXISTS</h4>";
            echo "</div>";
        } else {
            echo "<div class='status error'>";
            echo "<h4>❌ File '$file': MISSING</h4>";
            echo "</div>";
        }
    }
    
    // 5. Cek API Endpoints
    echo "<h2>🔗 API Endpoints Test:</h2>";
    $apis = ['customers', 'products', 'orders'];
    
    foreach ($apis as $api) {
        $url = "http://localhost/printing-management/api/$api.php";
        echo "<div class='status'>";
        echo "<h4>🔗 <a href='$url' target='_blank'>$url</a></h4>";
        echo "</div>";
    }
    ?>
    
    <h2>📋 Next Steps:</h2>
    <ol>
        <li>Jika ada status ❌, perbaiki masalah tersebut</li>
        <li>Klik link API untuk test manual</li>
        <li>Akses <a href="index.php">Dashboard Utama</a></li>
        <li>Jika sudah buat login, akses <a href="login.php">Halaman Login</a></li>
    </ol>
</body>
</html>