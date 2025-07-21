<?php
// Script untuk generate password hash
echo "<h2>🔐 Password Hash Generator</h2>";

$passwords = [
    'admin123',
    'staff123',
    'password123'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Plain Password</th><th>Hashed Password</th><th>SQL Insert</th></tr>";

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td><strong>$password</strong></td>";
    echo "<td style='font-family: monospace; font-size: 12px;'>$hash</td>";
    echo "<td style='font-family: monospace; font-size: 11px;'>";
    echo "UPDATE users SET password='$hash' WHERE username='admin';";
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>✅ Test Password Verification:</h3>";

// Test password verification
$testPassword = 'admin123';
$testHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($testPassword, $testHash)) {
    echo "<p style='color: green;'>✅ Password '$testPassword' COCOK dengan hash</p>";
} else {
    echo "<p style='color: red;'>❌ Password '$testPassword' TIDAK COCOK dengan hash</p>";
}

echo "<h3>🔄 Update Password Commands:</h3>";
echo "<pre>";
echo "-- Update password admin\n";
echo "UPDATE users SET password='" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username='admin';\n\n";
echo "-- Update password staff1\n";
echo "UPDATE users SET password='" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username='staff1';\n";
echo "</pre>";
?>
