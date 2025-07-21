<?php
// ========================================
// PROFILE PAGE - PHP VERSION
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
        
        $stmt = $conn->prepare("SELECT id, username, email, full_name, role, created_at FROM users WHERE id = ?");
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

function getRoleDescription($role) {
    switch ($role) {
        case 'admin': return 'Administrator dengan akses penuh ke semua fitur sistem';
        case 'manager': return 'Manager yang dapat mengelola operasional dan laporan';
        case 'staff': return 'Staff yang dapat menginput pesanan dan data pelanggan';
        default: return 'Pengguna sistem';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PrintPro Management</title>
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
        .large-avatar {
            width: 8rem;
            height: 8rem;
            font-size: 3rem;
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
        .profile-card {
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .activity-item {
            border-left: 3px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        .activity-item:hover {
            border-left-color: #3b82f6;
            background-color: #f8fafc;
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
                                <a href="profile.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded transition-colors">
                                    <i class="fas fa-user w-4"></i>
                                    <span>Profil Saya</span>
                                </a>
                                <a href="settings.php" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded transition-colors">
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
                        <a href="profile.php" class="flex items-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg">
                            <i class="fas fa-user"></i>
                            <span>Profil Saya</span>
                        </a>
                        <a href="settings.php" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-cog text-gray-500"></i>
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
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <div class="user-avatar large-avatar">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <div class="text-center md:text-left flex-1">
                        <h2 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4 mb-4">
                            <span class="role-badge <?php echo getRoleColor($user['role']); ?> inline-block">
                                <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                            </span>
                            <span class="text-blue-100">•</span>
                            <span class="opacity-90"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <p class="opacity-90 mb-4"><?php echo getRoleDescription($user['role']); ?></p>
                        <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                            <button onclick="openEditProfileModal()" class="px-6 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Profil
                            </button>
                            <a href="settings.php" class="px-6 py-2 border border-white text-white rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-medium text-center">
                                <i class="fas fa-cog mr-2"></i>
                                Pengaturan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm border p-6 profile-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Bergabung Sejak</h4>
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo date('M Y', strtotime($user['created_at'] ?? '2023-01-01')); ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <?php 
                        $joinDate = new DateTime($user['created_at'] ?? '2023-01-01');
                        $now = new DateTime();
                        $diff = $now->diff($joinDate);
                        echo $diff->days . ' hari yang lalu';
                        ?>
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 profile-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Total Pesanan</h4>
                        <i class="fas fa-shopping-cart text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">47</div>
                    <p class="text-xs text-gray-500 mt-1">Pesanan diproses</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 profile-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Pelanggan</h4>
                        <i class="fas fa-users text-purple-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">23</div>
                    <p class="text-xs text-gray-500 mt-1">Pelanggan aktif</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 profile-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Login Terakhir</h4>
                        <i class="fas fa-clock text-orange-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">Hari ini</div>
                    <p class="text-xs text-gray-500 mt-1">14:30 WIB</p>
                </div>
            </div>

            <!-- Profile Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow-sm border profile-card">
                        <div class="p-6 border-b">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-id-card text-blue-600 mr-3"></i>
                                Informasi Personal
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <?php echo htmlspecialchars($user['full_name']); ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <div class="p-3 bg-gray-50 rounded-lg border">
                                        <span class="role-badge <?php echo getRoleColor($user['role']); ?>">
                                            <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow-sm border profile-card">
                        <div class="p-6 border-b">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-history text-green-600 mr-3"></i>
                                Aktivitas Terbaru
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="activity-item pl-4 py-3 rounded-lg">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-plus text-blue-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm">Menambahkan pesanan baru</div>
                                            <div class="text-xs text-gray-600">Banner Vinyl untuk PT. Maju Jaya</div>
                                            <div class="text-xs text-gray-500 mt-1">2 jam yang lalu</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-item pl-4 py-3 rounded-lg">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-green-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm">Menyelesaikan pesanan</div>
                                            <div class="text-xs text-gray-600">Kartu Nama untuk CV. Berkah Abadi</div>
                                            <div class="text-xs text-gray-500 mt-1">5 jam yang lalu</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-item pl-4 py-3 rounded-lg">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user-plus text-purple-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm">Menambahkan pelanggan baru</div>
                                            <div class="text-xs text-gray-600">Toko Sinar Baru</div>
                                            <div class="text-xs text-gray-500 mt-1">1 hari yang lalu</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="activity-item pl-4 py-3 rounded-lg">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-edit text-orange-600 text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-sm">Memperbarui profil</div>
                                            <div class="text-xs text-gray-600">Mengubah informasi kontak</div>
                                            <div class="text-xs text-gray-500 mt-1">2 hari yang lalu</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 text-center">
                                <button onclick="viewAllActivity()" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors">
                                    Lihat Semua Aktivitas →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm border profile-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Aksi Cepat</h3>
                            <div class="space-y-3">
                                <button onclick="openEditProfileModal()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-edit text-blue-600"></i>
                                    <span>Edit Profil</span>
                                </button>
                                <button onclick="changePassword()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-key text-green-600"></i>
                                    <span>Ubah Password</span>
                                </button>
                                <button onclick="downloadProfile()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-download text-purple-600"></i>
                                    <span>Download Data</span>
                                </button>
                                <button onclick="viewActivity()" class="w-full flex items-center space-x-3 p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-history text-orange-600"></i>
                                    <span>Log Aktivitas</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Account Security -->
                    <div class="bg-white rounded-lg shadow-sm border profile-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Keamanan Akun</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-sm">Two-Factor Auth</div>
                                        <div class="text-xs text-gray-500">Keamanan tambahan</div>
                                    </div>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                        Nonaktif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-sm">Login Alerts</div>
                                        <div class="text-xs text-gray-500">Notifikasi login</div>
                                    </div>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        Aktif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-sm">Session Timeout</div>
                                        <div class="text-xs text-gray-500">Auto logout</div>
                                    </div>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                        30 menit
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button onclick="securitySettings()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    Kelola Keamanan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="bg-white rounded-lg shadow-sm border profile-card">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Info Sistem</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">User ID:</span>
                                    <span class="font-medium">#<?php echo $user['id']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bergabung:</span>
                                    <span class="font-medium"><?php echo date('d/m/Y', strtotime($user['created_at'] ?? '2023-01-01')); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        Aktif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Timezone:</span>
                                    <span class="font-medium">Asia/Jakarta</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">✏️ Edit Profil</h3>
                <button onclick="closeEditProfileModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editProfileForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="editFullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="editUsername" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditProfileModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <span id="editSubmitText">Simpan Perubahan</span>
                        <i id="editSubmitSpinner" class="fas fa-spinner fa-spin hidden ml-2"></i>
                    </button>
                </div>
            </form>
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
                        <i class="fas fa-user text-blue-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Profil Diperbarui</div>
                            <div class="text-xs text-gray-600">Informasi profil berhasil disimpan</div>
                            <div class="text-xs text-gray-500">10 menit yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-shield-alt text-green-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Login Berhasil</div>
                            <div class="text-xs text-gray-600">Login dari perangkat baru</div>
                            <div class="text-xs text-gray-500">2 jam yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Keamanan</div>
                            <div class="text-xs text-gray-600">Aktifkan 2FA untuk keamanan</div>
                            <div class="text-xs text-gray-500">1 hari yang lalu</div>
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
        function openEditProfileModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
        }

        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
        }

        function openNotificationsModal() {
            document.getElementById('notificationsModal').classList.remove('hidden');
        }

        function closeNotificationsModal() {
            document.getElementById('notificationsModal').classList.add('hidden');
        }

        // Profile Functions
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('editSubmitText');
            const spinner = document.getElementById('editSubmitSpinner');
            
            // Show loading state
            submitBtn.textContent = 'Menyimpan...';
            spinner.classList.remove('hidden');
            
            // Simulate API call
            setTimeout(() => {
                showNotification('Profil berhasil diperbarui!', 'success');
                closeEditProfileModal();
                
                // Update displayed information
                const newName = document.getElementById('editFullName').value;
                const newEmail = document.getElementById('editEmail').value;
                
                // Update all name displays
                document.querySelectorAll('.user-name').forEach(el => {
                    el.textContent = newName;
                });
                
                // Reset loading state
                submitBtn.textContent = 'Simpan Perubahan';
                spinner.classList.add('hidden');
            }, 1500);
        });

        // Quick Action Functions
        function changePassword() {
            showNotification('Mengarahkan ke pengaturan keamanan...', 'info');
            setTimeout(() => {
                window.location.href = 'settings.php#security';
            }, 1000);
        }

        function downloadProfile() {
            showNotification('Mempersiapkan download data profil...', 'info');
            setTimeout(() => {
                showNotification('Data profil berhasil didownload!', 'success');
            }, 2000);
        }

        function viewActivity() {
            showNotification('Membuka log aktivitas lengkap...', 'info');
            // Here you would typically open activity logs page
        }

        function viewAllActivity() {
            showNotification('Membuka halaman aktivitas lengkap...', 'info');
            // Here you would typically navigate to full activity page
        }

        function securitySettings() {
            showNotification('Membuka pengaturan keamanan...', 'info');
            setTimeout(() => {
                window.location.href = 'settings.php#security';
            }, 1000);
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
