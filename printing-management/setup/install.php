<?php
// ========================================
// INSTALLER UNTUK PRINTING MANAGEMENT SYSTEM
// ========================================

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>PrintPro Management - Installer</title>";
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
echo "<p><strong>Installer & Database Setup</strong></p>";
echo "</div>";

echo "<div class='content'>";

try {
    // Progress bar
    echo "<div class='progress'>";
    echo "<div class='progress-bar' style='width: 0%' id='progressBar'></div>";
    echo "</div>";
    echo "<p id='progressText'>Memulai instalasi...</p>";
    
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
    
    echo "<script>document.getElementById('progressBar').style.width = '20%'; document.getElementById('progressText').innerText = 'PHP requirements checked...';</script>";
    
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
    
    echo "<script>document.getElementById('progressBar').style.width = '40%'; document.getElementById('progressText').innerText = 'Database connection established...';</script>";
    
    // STEP 3: Create Tables
    echo "<div class='step'>";
    echo "<h3>🏗️ STEP 3: Creating Database Tables</h3>";
    
    // Read SQL file
    $sqlFile = '../database/setup_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL setup file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    $statements = explode(';', $sql);
    
    $executedCount = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $conn->exec($statement);
                $executedCount++;
            } catch (Exception $e) {
                // Ignore some errors like "table already exists"
                if (!strpos($e->getMessage(), 'already exists')) {
                    echo "<div class='warning'>⚠️ SQL Warning: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    
    echo "<div class='success'>✅ Executed $executedCount SQL statements</div>";
    echo "<div class='info'>📋 Database tables created successfully</div>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '70%'; document.getElementById('progressText').innerText = 'Database tables created...';</script>";
    
    // STEP 4: Verify Installation
    echo "<div class='step'>";
    echo "<h3>✅ STEP 4: Verifying Installation</h3>";
    
    $tables = ['customers', 'products', 'users', 'orders'];
    echo "<table>";
    echo "<tr><th>Table</th><th>Records</th><th>Status</th></tr>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>$count records</td>";
            echo "<td style='color: green;'>✅ OK</td>";
            echo "</tr>";
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td>-</td>";
            echo "<td style='color: red;'>❌ Error</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    echo "</div>";
    
    echo "<script>document.getElementById('progressBar').style.width = '90%'; document.getElementById('progressText').innerText = 'Verifying installation...';</script>";
    
    // STEP 5: Show Users
    echo "<div class='step'>";
    echo "<h3>👥 STEP 5: User Accounts Created</h3>";
    
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
    
    echo "<script>document.getElementById('progressBar').style.width = '100%'; document.getElementById('progressText').innerText = 'Installation completed successfully!';</script>";
    
    // Final Success
    echo "<div class='success' style='text-align: center; padding: 30px;'>";
    echo "<h2>🎉 INSTALLATION COMPLETED SUCCESSFULLY!</h2>";
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
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ INSTALLATION FAILED</h3>";
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
