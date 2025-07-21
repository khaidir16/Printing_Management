<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PrintPro Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.05) 10px,
                rgba(255,255,255,0.05) 20px
            );
            animation: slide 20s linear infinite;
        }
        
        @keyframes slide {
            0% { transform: translateX(-50px) translateY(-50px); }
            100% { transform: translateX(50px) translateY(50px); }
        }
        
        .login-header-content {
            position: relative;
            z-index: 1;
        }
        
        .login-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .login-form {
            padding: 40px 30px;
        }
        
        .demo-credentials {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .demo-credentials h4 {
            color: #1976d2;
            margin-bottom: 10px;
            font-size: 0.9em;
        }
        
        .demo-credentials p {
            color: #1976d2;
            font-size: 0.8em;
            margin: 5px 0;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 1em;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .loading {
            display: none;
            text-align: center;
            color: #6c757d;
        }
        
        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .login-footer p {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-header-content">
                <h1>🖨️ PrintPro</h1>
                <p>Sistem Manajemen Percetakan</p>
            </div>
        </div>
        
        <div class="login-form">
            <div class="demo-credentials">
                <h4>🔑 Demo Login:</h4>
                <p><strong>Admin:</strong> admin / admin123</p>
                <p><strong>Staff:</strong> staff1 / admin123</p>
            </div>
            
            <div id="alert-container"></div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">👤 Username atau Email</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           placeholder="Masukkan username atau email">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="Masukkan password">
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <span id="loginText">Masuk</span>
                    <span id="loginLoading" class="loading">Memproses</span>
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            <p>&copy; 2024 PrintPro Management. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            const loginLoading = document.getElementById('loginLoading');
            
            if (!username || !password) {
                showAlert('Username dan password harus diisi', 'error');
                return;
            }
            
            // Show loading state
            loginBtn.disabled = true;
            loginText.style.display = 'none';
            loginLoading.style.display = 'inline';
            
            // Send login request
            fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: username,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showAlert(data.error || 'Login gagal', 'error');
                }
            })
            .catch(error => {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            })
            .finally(() => {
                // Reset loading state
                loginBtn.disabled = false;
                loginText.style.display = 'inline';
                loginLoading.style.display = 'none';
            });
        });
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }
        
        // Check if already logged in
        fetch('api/auth.php?action=check')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    window.location.href = 'index.php';
                }
            })
            .catch(error => {
                console.log('Session check failed:', error);
            });
    </script>
</body>
</html>