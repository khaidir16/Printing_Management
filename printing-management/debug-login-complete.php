<?php
// ========================================
// COMPLETE LOGIN DIAGNOSTIC TOOL
// ========================================

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Complete Login Debug - PrintPro</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center; }";
echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".step { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 10px; border-left: 5px solid #dc3545; }";
echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
echo "th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #dc3545; color: white; }";
echo "tr:hover { background: #f5f5f5; }";
echo ".btn { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }";
echo ".btn:hover { background: #c82333; }";
echo ".btn-success { background: #28a745; }";
echo ".btn-warning { background: #ffc107; color: black; }";
echo ".test-box { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #dc3545; }";
echo "code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: monospace; }";
echo ".fix-button { background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px; }";
echo ".fix-button:hover { background: #1e7e34; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<div class='header'>";
echo "<h1>🔍 Complete Login Diagnostic Tool</h1>";
echo "<p>Mencari dan Memperbaiki Masalah Login</p>";
echo "</div>";

try {
    // STEP 1: Database Connection Test
    echo "<div class='step'>";
    echo "<h3>🔌 STEP 1: Database Connection Test</h3>";
    
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed!");
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    echo "</div>";
    
    // STEP 2: Check Tables Exist
    echo "<div class='step'>";
    echo "<h3>📋 STEP 2: Check Required Tables</h3>";
    
    $requiredTables = ['users', 'customers', 'products', 'orders'];
    $existingTables = [];
    
    foreach ($requiredTables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' missing</div>";
        }
    }
    
    if (count($existingTables) != count($requiredTables)) {
        echo "<div class='warning'>⚠️ Some tables are missing. Run installer first!</div>";
    }
    echo "</div>";
    
    // STEP 3: Check Users Data
    echo "<div class='step'>";
    echo "<h3>👥 STEP 3: Check Users Data</h3>";
    
    if (in_array('users', $existingTables)) {
        $stmt = $conn->query("SELECT id, username, email, password, full_name, role, is_active FROM users ORDER BY role, username");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) == 0) {
            echo "<div class='error'>❌ No users found in database!</div>";
            echo "<div class='warning'>⚠️ Database is empty. Need to run installer.</div>";
        } else {
            echo "<div class='success'>✅ Found " . count($users) . " users in database</div>";
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Active</th><th>Password Hash (First 20 chars)</th></tr>";
            
            foreach ($users as $user) {
                $status = $user['is_active'] ? '✅ Yes' : '❌ No';
                $passwordPreview = substr($user['password'], 0, 20) . '...';
                
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td><strong>{$user['username']}</strong></td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['full_name']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>$status</td>";
                echo "<td><code>$passwordPreview</code></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='error'>❌ Users table doesn't exist!</div>";
    }
    echo "</div>";
    
    // STEP 4: Test Password Verification
    echo "<div class='step'>";
    echo "<h3>🔐 STEP 4: Password Verification Test</h3>";
    
    if (in_array('users', $existingTables) && count($users) > 0) {
        $testUsername = 'admin';
        $testPassword = 'admin123';
        
        // Find admin user
        $adminUser = null;
        foreach ($users as $user) {
            if ($user['username'] === $testUsername) {
                $adminUser = $user;
                break;
            }
        }
        
        if ($adminUser) {
            echo "<div class='info'>🔍 Testing user: <strong>{$adminUser['username']}</strong></div>";
            echo "<div class='info'>📧 Email: {$adminUser['email']}</div>";
            echo "<div class='info'>👤 Full Name: {$adminUser['full_name']}</div>";
            echo "<div class='info'>🔑 Stored Password Hash: <code>" . substr($adminUser['password'], 0, 50) . "...</code></div>";
            
            // Test password verification
            if (password_verify($testPassword, $adminUser['password'])) {
                echo "<div class='success'>✅ Password verification SUCCESSFUL!</div>";
                echo "<div class='success'>🎉 Password 'admin123' matches the stored hash</div>";
            } else {
                echo "<div class='error'>❌ Password verification FAILED!</div>";
                echo "<div class='error'>🚨 Password 'admin123' does NOT match the stored hash</div>";
                
                // Generate new correct hash
                $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                echo "<div class='warning'>🔧 Generated new hash for 'admin123': <code>" . substr($newHash, 0, 50) . "...</code></div>";
                
                echo "<div class='test-box'>";
                echo "<h4>🛠️ AUTOMATIC FIX AVAILABLE</h4>";
                echo "<p>Click button below to fix the password hash for admin user:</p>";
                echo "<button class='fix-button' onclick='fixAdminPassword()'>🔧 FIX ADMIN PASSWORD</button>";
                echo "<div id='fixResult'></div>";
                echo "</div>";
            }
        } else {
            echo "<div class='error'>❌ Admin user not found!</div>";
            echo "<div class='warning'>⚠️ No user with username 'admin' exists</div>";
        }
    }
    echo "</div>";
    
    // STEP 5: Test Auth API
    echo "<div class='step'>";
    echo "<h3>🌐 STEP 5: Test Authentication API</h3>";
    
    $authFile = 'api/auth.php';
    if (file_exists($authFile)) {
        echo "<div class='success'>✅ Auth API file exists: $authFile</div>";
        
        // Test API endpoint
        echo "<div class='test-box'>";
        echo "<h4>🧪 Live API Test</h4>";
        echo "<p>Test login API with admin credentials:</p>";
        echo "<button class='fix-button' onclick='testLoginAPI()'>🧪 TEST LOGIN API</button>";
        echo "<div id='apiTestResult'></div>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Auth API file missing: $authFile</div>";
    }
    echo "</div>";
    
    // STEP 6: Check Session Configuration
    echo "<div class='step'>";
    echo "<h3>🍪 STEP 6: Session Configuration</h3>";
    
    echo "<table>";
    echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
    
    $sessionSettings = [
        'session.auto_start' => ini_get('session.auto_start'),
        'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
        'session.use_cookies' => ini_get('session.use_cookies'),
        'session.use_only_cookies' => ini_get('session.use_only_cookies'),
        'session.cookie_httponly' => ini_get('session.cookie_httponly')
    ];
    
    foreach ($sessionSettings as $setting => $value) {
        $status = $value ? '✅ OK' : '⚠️ Check';
        echo "<tr><td>$setting</td><td><code>$value</code></td><td>$status</td></tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // STEP 7: Manual Login Test
    echo "<div class='step'>";
    echo "<h3>🔐 STEP 7: Manual Login Test</h3>";
    
    echo "<div class='test-box'>";
    echo "<h4>🎯 Direct Login Test</h4>";
    echo "<form id='manualLoginForm'>";
    echo "<label>Username:</label><br>";
    echo "<input type='text' id='manualUsername' value='admin' style='padding: 10px; margin: 5px 0; width: 300px;'><br>";
    echo "<label>Password:</label><br>";
    echo "<input type='password' id='manualPassword' value='admin123' style='padding: 10px; margin: 5px 0; width: 300px;'><br>";
    echo "<button type='submit' class='fix-button'>🔐 TEST LOGIN</button>";
    echo "</form>";
    echo "<div id='manualLoginResult'></div>";
    echo "</div>";
    echo "</div>";
    
    // STEP 8: Quick Fixes
    echo "<div class='step'>";
    echo "<h3>🛠️ STEP 8: Quick Fixes</h3>";
    
    echo "<div class='test-box'>";
    echo "<h4>⚡ One-Click Fixes</h4>";
    echo "<button class='fix-button' onclick='recreateAdminUser()'>👑 RECREATE ADMIN USER</button>";
    echo "<button class='fix-button' onclick='resetAllPasswords()'>🔄 RESET ALL PASSWORDS</button>";
    echo "<button class='fix-button' onclick='clearSessions()'>🧹 CLEAR SESSIONS</button>";
    echo "<div id='quickFixResult'></div>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ DIAGNOSTIC FAILED</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div>";

// JavaScript for interactive tests
echo "<script>";
echo "
function fixAdminPassword() {
    fetch('debug-login-complete.php?action=fix_admin_password', {
        method: 'POST'
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('fixResult').innerHTML = data;
    })
    .catch(error => {
        document.getElementById('fixResult').innerHTML = '<div class=\"error\">Error: ' + error.message + '</div>';
    });
}

function testLoginAPI() {
    fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: 'admin', password: 'admin123' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('apiTestResult').innerHTML = '<div class=\"success\">✅ API Test SUCCESS: ' + data.message + '</div>';
        } else {
            document.getElementById('apiTestResult').innerHTML = '<div class=\"error\">❌ API Test FAILED: ' + data.error + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('apiTestResult').innerHTML = '<div class=\"error\">❌ API Error: ' + error.message + '</div>';
    });
}

document.getElementById('manualLoginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const username = document.getElementById('manualUsername').value;
    const password = document.getElementById('manualPassword').value;
    
    fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: username, password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('manualLoginResult').innerHTML = '<div class=\"success\">✅ Manual Login SUCCESS! User: ' + data.user.full_name + '</div>';
        } else {
            document.getElementById('manualLoginResult').innerHTML = '<div class=\"error\">❌ Manual Login FAILED: ' + data.error + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('manualLoginResult').innerHTML = '<div class=\"error\">❌ Login Error: ' + error.message + '</div>';
    });
});

function recreateAdminUser() {
    fetch('debug-login-complete.php?action=recreate_admin', {
        method: 'POST'
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('quickFixResult').innerHTML = data;
    });
}

function resetAllPasswords() {
    fetch('debug-login-complete.php?action=reset_passwords', {
        method: 'POST'
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('quickFixResult').innerHTML = data;
    });
}

function clearSessions() {
    fetch('debug-login-complete.php?action=clear_sessions', {
        method: 'POST'
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('quickFixResult').innerHTML = data;
    });
}
";
echo "</script>";

// Handle AJAX actions
if (isset($_GET['action'])) {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    switch ($_GET['action']) {
        case 'fix_admin_password':
            try {
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
                $stmt->execute([$newHash]);
                echo "<div class='success'>✅ Admin password fixed! New hash: " . substr($newHash, 0, 30) . "...</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Fix failed: " . $e->getMessage() . "</div>";
            }
            break;
            
        case 'recreate_admin':
            try {
                $conn->exec("DELETE FROM users WHERE username = 'admin'");
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role, is_active) VALUES ('admin', 'admin@printpro.com', ?, 'Administrator', 'admin', 1)");
                $stmt->execute([$newHash]);
                echo "<div class='success'>✅ Admin user recreated successfully!</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Recreate failed: " . $e->getMessage() . "</div>";
            }
            break;
            
        case 'reset_passwords':
            try {
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ?");
                $stmt->execute([$newHash]);
                echo "<div class='success'>✅ All passwords reset to 'admin123'!</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Reset failed: " . $e->getMessage() . "</div>";
            }
            break;
            
        case 'clear_sessions':
            session_start();
            session_destroy();
            echo "<div class='success'>✅ Sessions cleared!</div>";
            break;
    }
    exit;
}

echo "</body>";
echo "</html>";
?>
