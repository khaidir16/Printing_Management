<?php
echo "<h2>🔍 Login Debug Test</h2>";

try {
    // Test database connection
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connection: OK</p>";
    
    // Check if users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Table 'users': EXISTS</p>";
        
        // Check users data
        $stmt = $conn->query("SELECT id, username, email, full_name, role, is_active FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>👥 Users in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Active</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test password verification
        echo "<h3>🔐 Password Test:</h3>";
        $testUsername = 'admin';
        $testPassword = 'admin123';
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$testUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p>✅ User '$testUsername' found in database</p>";
            
            if (password_verify($testPassword, $user['password'])) {
                echo "<p style='color: green;'>✅ Password '$testPassword' is CORRECT</p>";
            } else {
                echo "<p style='color: red;'>❌ Password '$testPassword' is WRONG</p>";
                echo "<p>Stored hash: <code>{$user['password']}</code></p>";
                
                // Generate new hash
                $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                echo "<p>New hash for '$testPassword': <code>$newHash</code></p>";
                echo "<p><strong>Run this SQL:</strong></p>";
                echo "<pre>UPDATE users SET password='$newHash' WHERE username='$testUsername';</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ User '$testUsername' NOT FOUND</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Table 'users': NOT EXISTS</p>";
        echo "<p><strong>Please create users table first!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>🔧 Quick Fix Commands:</h3>";
echo "<pre>";
echo "-- 1. Create users table (if not exists)\n";
echo "CREATE TABLE users (\n";
echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
echo "    username VARCHAR(50) NOT NULL UNIQUE,\n";
echo "    email VARCHAR(255) NOT NULL UNIQUE,\n";
echo "    password VARCHAR(255) NOT NULL,\n";
echo "    full_name VARCHAR(255) NOT NULL,\n";
echo "    role ENUM('admin', 'staff') DEFAULT 'staff',\n";
echo "    is_active BOOLEAN DEFAULT TRUE,\n";
echo "    last_login TIMESTAMP NULL,\n";
echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
echo ");\n\n";

echo "-- 2. Insert demo users\n";
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$staffHash = password_hash('admin123', PASSWORD_DEFAULT);
echo "INSERT INTO users (username, email, password, full_name, role) VALUES\n";
echo "('admin', 'admin@printpro.com', '$adminHash', 'Administrator', 'admin'),\n";
echo "('staff1', 'staff@printpro.com', '$staffHash', 'Staff Percetakan', 'staff');\n";
echo "</pre>";
?>
