<?php
// ========================================
// TEST AUTH API DIRECTLY
// ========================================

echo "<h1>🧪 Test Auth API Directly</h1>";

// Test 1: Direct password verification
echo "<h2>Test 1: Direct Password Verification</h2>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p>✅ User found: {$user['username']}</p>";
        echo "<p>🔐 Stored hash: " . substr($user['password'], 0, 30) . "...</p>";
        
        if (password_verify('admin123', $user['password'])) {
            echo "<p style='color: green; font-size: 18px;'>✅ PASSWORD VERIFICATION SUCCESS!</p>";
        } else {
            echo "<p style='color: red; font-size: 18px;'>❌ PASSWORD VERIFICATION FAILED!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin user not found!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 2: Simulate API call
echo "<h2>Test 2: Simulate API Call</h2>";

$loginData = [
    'username' => 'admin',
    'password' => 'admin123'
];

echo "<p>📤 Sending login data:</p>";
echo "<pre>" . json_encode($loginData, JSON_PRETTY_PRINT) . "</pre>";

// Simulate the auth.php logic
try {
    session_start();
    
    $username = trim($loginData['username']);
    $password = $loginData['password'];
    
    // Find user
    $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([$username, $username]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p style='color: red;'>❌ User not found or inactive</p>";
    } else {
        echo "<p>✅ User found: {$user['full_name']}</p>";
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            echo "<p style='color: red;'>❌ Password verification failed</p>";
        } else {
            echo "<p style='color: green;'>✅ Password verification success</p>";
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['login_time'] = time();
            
            echo "<p style='color: green; font-size: 18px;'>🎉 LOGIN SIMULATION SUCCESS!</p>";
            echo "<p>Session data set:</p>";
            echo "<ul>";
            echo "<li>User ID: {$_SESSION['user_id']}</li>";
            echo "<li>Username: {$_SESSION['username']}</li>";
            echo "<li>Full Name: {$_SESSION['full_name']}</li>";
            echo "<li>Role: {$_SESSION['role']}</li>";
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Simulation error: " . $e->getMessage() . "</p>";
}

echo "<h2>🚀 Next Steps</h2>";
echo "<p><a href='login-fixed.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>🔐 Try Fixed Login Page</a></p>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🔄 Try Original Login</a></p>";
?>
