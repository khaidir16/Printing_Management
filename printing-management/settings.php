<?php
// ========================================
// SETTINGS PAGE - PHP VERSION
// ========================================
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // User not found, destroy session
            session_destroy();
            header('Location: login.php');
            exit();
        }
    } catch (Exception $e) {
        // Database error, redirect to login
        header('Location: login.php');
        exit();
    }
} else {
    // Not logged in, redirect to login
    header('Location: login.php');
    exit();
}

// Get role icon and color
function getRoleIcon($role) {
    switch ($role) {
        case 'admin': return '👑';
        case 'manager': return '👔';
        case 'staff': return '👷';
        default: return '👤';
    }
}

function getRoleColor($role) {
    switch ($role) {
        case 'admin': return 'bg-red-500 text-white';
        case 'manager': return 'bg-yellow-500 text-black';
        case 'staff': return 'bg-green-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - PrintPro Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
        }
        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .settings-card {
            transition: all 0.3s ease;
        }
        .settings-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center space-x-3">
                        <i class="fas fa-print text-3xl text-blue-600"></i>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">PrintPro Management</h1>
                            <p class="text-sm text-gray-500">Sistem Manajemen Percetakan Profesional</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <div class="flex items-center space-x-4">
                        <a href="index.php" class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-home text-gray-500"></i>
                            <span class="text-sm">Dashboard</span>
                        </a>
                        <button onclick="openNotificationsModal()" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors relative">
                            <i class="fas fa-bell text-gray-500"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </button>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative dropdown">
                        <button class="flex items-center space-x-3 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div class="text-left">
                                <div class="text-sm font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <div class="text-xs text-gray-500 flex items-center">
                                    <span class="mr-1"><?php echo getRoleIcon($user['role']); ?></span>
                                    <?php echo strtoupper($user['role']); ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border hidden">
                            <div class="p-4 border-b">
                                <div class="flex items-center space-x-3">
                                    <div class="user-avatar" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <span class="role-badge <?php echo getRoleColor($user['role']); ?> mt-1 inline-block">
                                            <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-2">
                                <a href="profile.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition-colors">
                                    <i class="fas fa-user w-4"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="settings.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded transition-colors">
                                    <i class="fas fa-cog w-4"></i>
                                    <span>Pengaturan</span>
                                </a>
                                <a href="help.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition-colors">
                                    <i class="fas fa-question-circle w-4"></i>
                                    <span>Bantuan</span>
                                </a>
                                <hr class="my-2">
                                <button onclick="logout()" class="flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded w-full text-left transition-colors">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    <span>Keluar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button onclick="toggleMobileMenu()" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-bars text-gray-500"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="mobile-menu md:hidden border-t bg-white py-4">
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 px-4">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                            <span class="role-badge <?php echo getRoleColor($user['role']); ?> mt-1 inline-block">
                                <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 px-4">
                        <a href="index.php" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-home text-gray-500"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="profile.php" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user text-gray-500"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="settings.php" class="flex items-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                        <button onclick="logout()" class="flex items-center space-x-2 w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-6 fade-in">
            <!-- Page Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold mb-2">⚙️ Pengaturan Sistem</h2>
                        <p class="opacity-90">
                            Kelola pengaturan aplikasi dan preferensi akun Anda
                        </p>
                    </div>
                    <div class="text-6xl opacity-20">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Account Settings -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Profile Settings -->
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6 border-b">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-user text-blue-600 mr-3"></i>
                                Pengaturan Profil
                            </h3>
                            <p class="text-gray-600 mt-1">Kelola informasi profil dan akun Anda</p>
                        </div>
                        <div class="p-6">
                            <form id="profileForm" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                        <input type="text" id="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-save mr-2"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6 border-b">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-shield-alt text-green-600 mr-3"></i>
                                Keamanan
                            </h3>
                            <p class="text-gray-600 mt-1">Kelola password dan pengaturan keamanan</p>
                        </div>
                        <div class="p-6">
                            <form id="passwordForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                                    <input type="password" id="currentPassword" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                        <input type="password" id="newPassword" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                        <input type="password" id="confirmPassword" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        <i class="fas fa-key mr-2"></i>
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- System Settings (Admin Only) -->
                    <?php if ($user['role'] === 'admin'): ?>
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6 border-b">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-cogs text-purple-600 mr-3"></i>
                                Pengaturan Sistem
                            </h3>
                            <p class="text-gray-600 mt-1">Konfigurasi sistem dan aplikasi</p>
                        </div>
                        <div class="p-6">
                            <form id="systemForm" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                                        <input type="text" id="companyName" value="PrintPro Management" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                        <select id="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB)</option>
                                            <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                            <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang</label>
                                        <select id="currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="IDR" selected>Rupiah (IDR)</option>
                                            <option value="USD">US Dollar (USD)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bahasa</label>
                                        <select id="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                            <option value="id" selected>Bahasa Indonesia</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                        <i class="fas fa-save mr-2"></i>
                                        Simpan Pengaturan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions Sidebar -->
                <div class="space-y-6">
                    <!-- Account Info -->
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Informasi Akun</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3">
                                    <div class="user-avatar" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                        <span class="role-badge <?php echo getRoleColor($user['role']); ?> mt-1 inline-block">
                                            <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="pt-3 border-t">
                                    <div class="text-sm text-gray-600">
                                        <div>Login terakhir: <span class="font-medium">Hari ini, 14:30</span></div>
                                        <div>Status: <span class="text-green-600 font-medium">Aktif</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
                            <div class="space-y-3">
                                <button onclick="exportData()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-download text-blue-600"></i>
                                    <span>Export Data</span>
                                </button>
                                <button onclick="backupData()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-database text-green-600"></i>
                                    <span>Backup Data</span>
                                </button>
                                <button onclick="clearCache()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-broom text-orange-600"></i>
                                    <span>Clear Cache</span>
                                </button>
                                <button onclick="viewLogs()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-file-alt text-purple-600"></i>
                                    <span>View Logs</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences -->
                    <div class="bg-white rounded-lg shadow-sm border settings-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Preferensi</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">Notifikasi Email</div>
                                        <div class="text-sm text-gray-500">Terima notifikasi via email</div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">Dark Mode</div>
                                        <div class="text-sm text-gray-500">Gunakan tema gelap</div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">Auto Save</div>
                                        <div class="text-sm text-gray-500">Simpan otomatis setiap 5 menit</div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notificationsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">🔔 Notifikasi</h3>
                <button onclick="closeNotificationsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="notificationsList" class="space-y-3">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-cog text-blue-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Pengaturan Diperbarui</div>
                            <div class="text-xs text-gray-600">Profil berhasil disimpan</div>
                            <div class="text-xs text-gray-500">5 menit yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-shield-alt text-green-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Password Diubah</div>
                            <div class="text-xs text-gray-600">Password berhasil diperbarui</div>
                            <div class="text-xs text-gray-500">1 jam yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-database text-yellow-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Backup Terjadwal</div>
                            <div class="text-xs text-gray-600">Backup otomatis dalam 2 jam</div>
                            <div class="text-xs text-gray-500">2 jam yang lalu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
        }

        // Modal Functions
        function openNotificationsModal() {
            document.getElementById('notificationsModal').classList.remove('hidden');
        }

        function closeNotificationsModal() {
            document.getElementById('notificationsModal').classList.add('hidden');
        }

        // Form Submissions
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                fullName: document.getElementById('fullName').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value
            };
            
            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                showNotification('Profil berhasil diperbarui!', 'success');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1500);
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showNotification('Password konfirmasi tidak cocok!', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showNotification('Password minimal 6 karakter!', 'error');
                return;
            }
            
            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupdate...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                showNotification('Password berhasil diperbarui!', 'success');
                e.target.reset();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1500);
        });

        <?php if ($user['role'] === 'admin'): ?>
        document.getElementById('systemForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                companyName: document.getElementById('companyName').value,
                timezone: document.getElementById('timezone').value,
                currency: document.getElementById('currency').value,
                language: document.getElementById('language').value
            };
            
            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                showNotification('Pengaturan sistem berhasil disimpan!', 'success');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1500);
        });
        <?php endif; ?>

        // Quick Actions
        function exportData() {
            showNotification('Memulai export data...', 'info');
            setTimeout(() => {
                showNotification('Data berhasil diexport!', 'success');
            }, 2000);
        }

        function backupData() {
            showNotification('Memulai backup data...', 'info');
            setTimeout(() => {
                showNotification('Backup data berhasil dibuat!', 'success');
            }, 3000);
        }

        function clearCache() {
            showNotification('Membersihkan cache...', 'info');
            setTimeout(() => {
                showNotification('Cache berhasil dibersihkan!', 'success');
            }, 1500);
        }

        function viewLogs() {
            showNotification('Membuka log sistem...', 'info');
            // Here you would typically open a logs viewer
        }

        // Utility Functions
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                showNotification('Logging out...', 'info');
                setTimeout(() => {
                    window.location.href = 'logout.php';
                }, 1000);
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-black',
                info: 'bg-blue-500 text-white'
            };
            
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };
            
            notification.className += ` ${colors[type]}`;
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="${icons[type]}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Animate out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Close modals when clicking outside
            document.querySelectorAll('.fixed.inset-0').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
