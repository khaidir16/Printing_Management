<?php
// ========================================
// TEST USERS - VERIFIKASI DATA USERS
// ========================================

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Test Users - PrintPro Management</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin: 20px 0; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #007bff; color: white; }";
echo "tr:hover { background: #f5f5f5; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo ".btn:hover { background: #0056b3; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-danger { background: #dc3545; }";
echo ".test-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }";
echo ".role-admin { background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; }";
echo ".role-manager { background: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; }";
echo ".role-staff { background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>👥 Test Users - PrintPro Management</h1>";
echo "<p>Verifikasi dan Test User Accounts</p>";
echo "</div>";

try {
    // Test database connection
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Check users table
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        throw new Exception("Table 'users' tidak ditemukan. Jalankan installer terlebih dahulu.");
    }
    
    echo "<div class='success'>✅ Table 'users' exists</div>";
    
    // Get all users
    $stmt = $conn->query("SELECT id, username, email, full_name, role, is_active, last_login, created_at FROM users ORDER BY role, username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) == 0) {
        throw new Exception("Tidak ada users di database. Jalankan installer untuk membuat users.");
    }
    
    echo "<div class='info'>📊 Found " . count($users) . " users in database</div>";
    
    // Display users table
    echo "<h2>👥 All Users</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Created</th></tr>";
    
    foreach ($users as $user) {
        $status = $user['is_active'] ? '✅ Active' : '❌ Inactive';
        $roleClass = 'role-' . $user['role'];
        $lastLogin = $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Never';
        $created = date('d/m/Y H:i', strtotime($user['created_at']));
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td><span class='$roleClass'>" . strtoupper($user['role']) . "</span></td>";
        echo "<td>$status</td>";
        echo "<td>$lastLogin</td>";
        echo "<td>$created</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Statistics
    echo "<h2>📊 User Statistics</h2>";
    $stats = $conn->query("
        SELECT 
            role,
            COUNT(*) as total,
            SUM(is_active) as active,
            COUNT(*) - SUM(is_active) as inactive
        FROM users 
        GROUP BY role
        ORDER BY 
            CASE role 
                WHEN 'admin' THEN 1 
                WHEN 'manager' THEN 2 
                WHEN 'staff' THEN 3 
            END
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Role</th><th>Total</th><th>Active</th><th>Inactive</th></tr>";
    
    foreach ($stats as $stat) {
        $roleClass = 'role-' . $stat['role'];
        echo "<tr>";
        echo "<td><span class='$roleClass'>" . strtoupper($stat['role']) . "</span></td>";
        echo "<td>{$stat['total']}</td>";
        echo "<td style='color: green;'>{$stat['active']}</td>";
        echo "<td style='color: red;'>{$stat['inactive']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Login test form
    echo "<h2>🔐 Test Login</h2>";
    echo "<div class='test-form'>";
    echo "<h3>Quick Login Test</h3>";
    echo "<form id='testLoginForm'>";
    echo "<label>Select User:</label><br>";
    echo "<select id='testUsername' style='padding: 10px; margin: 10px 0; width: 300px;'>";
    
    foreach ($users as $user) {
        if ($user['is_active']) {
            echo "<option value='{$user['username']}'>{$user['full_name']} ({$user['username']}) - {$user['role']}</option>";
        }
    }
    
    echo "</select><br>";
    echo "<label>Password:</label><br>";
    echo "<input type='password' id='testPassword' value='admin123' style='padding: 10px; margin: 10px 0; width: 300px;' placeholder='Password'><br>";
    echo "<button type='submit' class='btn btn-success'>🔐 Test Login</button>";
    echo "</form>";
    echo "<div id='loginResult'></div>";
    echo "</div>";
    
    // Login credentials info
    echo "<div class='info'>";
    echo "<h3>🔑 Default Login Credentials</h3>";
    echo "<p><strong>Password untuk semua user: admin123</strong></p>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;'>";
    
    $roleGroups = [];
    foreach ($users as $user) {
        if ($user['is_active']) {
            $roleGroups[$user['role']][] = $user['username'];
        }
    }
    
    foreach ($roleGroups as $role => $usernames) {
        $roleIcon = ['admin' => '👑', 'manager' => '👔', 'staff' => '👷'][$role] ?? '👤';
        echo "<div>";
        echo "<strong>$roleIcon " . strtoupper($role) . ":</strong><br>";
        foreach ($usernames as $username) {
            echo "$username / admin123<br>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    echo "</div>";
    
    // Action buttons
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='login.php' class='btn btn-success'>🔐 Go to Login Page</a>";
    echo "<a href='index.php' class='btn'>🏠 Go to Dashboard</a>";
    echo "<a href='setup/install.php' class='btn'>🔄 Run Installer Again</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<h4>🔧 Solutions:</h4>";
    echo "<ul>";
    echo "<li>Make sure XAMPP/WAMP is running</li>";
    echo "<li>Check database connection in config/database.php</li>";
    echo "<li>Run the installer: <a href='setup/install.php'>setup/install.php</a></li>";
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

// JavaScript for login test
echo "<script>";
echo "document.getElementById('testLoginForm').addEventListener('submit', function(e) {";
echo "    e.preventDefault();";
echo "    const username = document.getElementById('testUsername').value;";
echo "    const password = document.getElementById('testPassword').value;";
echo "    ";
echo "    fetch('api/auth.php?action=login', {";
echo "        method: 'POST',";
echo "        headers: { 'Content-Type': 'application/json' },";
echo "        body: JSON.stringify({ username: username, password: password })";
echo "    })";
echo "    .then(response => response.json())";
echo "    .then(data => {";
echo "        const resultDiv = document.getElementById('loginResult');";
echo "        if (data.success) {";
echo "            resultDiv.innerHTML = '<div class=\"success\">✅ Login successful for: ' + data.user.full_name + ' (' + data.user.role + ')</div>';";
echo "        } else {";
echo "            resultDiv.innerHTML = '<div class=\"error\">❌ Login failed: ' + data.error + '</div>';";
echo "        }";
echo "    })";
echo "    .catch(error => {";
echo "        document.getElementById('loginResult').innerHTML = '<div class=\"error\">❌ Error: ' + error.message + '</div>';";
echo "    });";
echo "});";
echo "</script>";

echo "</body>";
echo "</html>";
?>
