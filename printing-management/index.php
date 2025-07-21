<?php
// ========================================
// ENHANCED INDEX WITH LOGIN/PROFILE - PHP VERSION
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
            $isLoggedIn = false;
        }
    } catch (Exception $e) {
        // Database error, treat as not logged in
        $isLoggedIn = false;
    }
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
    <title>PrintPro Management - Dashboard</title>
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
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
                    <i class="fas fa-print text-3xl text-blue-600"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">PrintPro Management</h1>
                        <p class="text-sm text-gray-500">Sistem Manajemen Percetakan Profesional</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if ($isLoggedIn && $user): ?>
                        <!-- Logged in user -->
                        <div class="flex items-center space-x-4">
                            <button onclick="openSearchModal()" class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-search text-gray-500"></i>
                                <span class="text-sm">Cari</span>
                            </button>
                            <button onclick="openAddOrderModal()" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span class="text-sm">Tambah Pesanan</span>
                            </button>
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
                    <?php else: ?>
                        <!-- Not logged in -->
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-600">
                                Belum login? Akses fitur lengkap dengan masuk
                            </div>
                            <a href="login.php" class="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Masuk</span>
                            </a>
                        </div>
                    <?php endif; ?>
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
                <?php if ($isLoggedIn && $user): ?>
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
                            <button onclick="openSearchModal()" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-search text-gray-500"></i>
                                <span>Cari</span>
                            </button>
                            <button onclick="openAddOrderModal()" class="flex items-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Pesanan</span>
                            </button>
                            <a href="profile.php" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user text-gray-500"></i>
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
                <?php else: ?>
                    <div class="px-4 space-y-4">
                        <div class="text-center text-gray-600">
                            Belum login? Akses fitur lengkap dengan masuk
                        </div>
                        <a href="login.php" class="flex items-center justify-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Masuk</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (!$isLoggedIn): ?>
            <!-- Public Dashboard (Not Logged In) -->
            <div class="space-y-8 fade-in">
                <!-- Welcome Section -->
                <div class="text-center py-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Selamat Datang di PrintPro Management
                    </h2>
                    <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Sistem manajemen percetakan profesional untuk mengelola pelanggan, produk, dan pesanan dengan mudah dan efisien.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="login.php" class="inline-flex items-center justify-center space-x-2 px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-lg transition-colors">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Masuk ke Sistem</span>
                        </a>
                        <button onclick="showDemo()" class="inline-flex items-center justify-center space-x-2 px-8 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-lg transition-colors">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Demo</span>
                        </button>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center card-hover">
                        <i class="fas fa-users text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Manajemen Pelanggan</h3>
                        <p class="text-gray-600">
                            Kelola data pelanggan dengan mudah, termasuk informasi kontak dan riwayat pesanan.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center card-hover">
                        <i class="fas fa-box text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Katalog Produk</h3>
                        <p class="text-gray-600">
                            Atur produk dan layanan percetakan dengan harga, kategori, dan deskripsi lengkap.
                        </p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center card-hover">
                        <i class="fas fa-print text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Tracking Pesanan</h3>
                        <p class="text-gray-600">
                            Pantau status pesanan dari pending hingga selesai dengan sistem tracking terintegrasi.
                        </p>
                    </div>
                </div>

                <!-- Stats Preview -->
                <div class="bg-white rounded-lg shadow-sm border card-hover">
                    <div class="p-6 text-center border-b">
                        <h3 class="text-xl font-semibold">Statistik Sistem</h3>
                        <p class="text-gray-600">Data real-time dari sistem PrintPro Management</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                            <div>
                                <div class="text-3xl font-bold text-blue-600" id="customerCount">128</div>
                                <div class="text-gray-600">Total Pelanggan</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-green-600" id="productCount">42</div>
                                <div class="text-gray-600">Produk Tersedia</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-purple-600" id="orderCount">87</div>
                                <div class="text-gray-600">Pesanan Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demo Credentials -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4">🔑 Demo Login Credentials</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="bg-white p-4 rounded border">
                            <div class="font-semibold text-red-600">👑 Admin</div>
                            <div>Username: <code>admin</code></div>
                            <div>Password: <code>admin123</code></div>
                        </div>
                        <div class="bg-white p-4 rounded border">
                            <div class="font-semibold text-yellow-600">👔 Manager</div>
                            <div>Username: <code>manager1</code></div>
                            <div>Password: <code>admin123</code></div>
                        </div>
                        <div class="bg-white p-4 rounded border">
                            <div class="font-semibold text-green-600">👷 Staff</div>
                            <div>Username: <code>staff1</code></div>
                            <div>Password: <code>admin123</code></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Authenticated Dashboard -->
            <div class="space-y-6 fade-in">
                <!-- Welcome Message -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">
                                Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>! 👋
                            </h3>
                            <p class="opacity-90">
                                Anda login sebagai <?php echo strtoupper($user['role']); ?> • 
                                <?php echo date('l, d F Y'); ?>
                            </p>
                        </div>
                        <div class="text-6xl opacity-20">
                            <?php echo getRoleIcon($user['role']); ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button onclick="openAddOrderModal()" class="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-all text-left">
                        <i class="fas fa-plus text-blue-600 text-2xl mb-2"></i>
                        <div class="font-semibold">Pesanan Baru</div>
                        <div class="text-sm text-gray-500">Tambah pesanan</div>
                    </button>
                    <button onclick="openAddCustomerModal()" class="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-all text-left">
                        <i class="fas fa-user-plus text-green-600 text-2xl mb-2"></i>
                        <div class="font-semibold">Pelanggan Baru</div>
                        <div class="text-sm text-gray-500">Tambah pelanggan</div>
                    </button>
                    <button onclick="openAddProductModal()" class="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-all text-left">
                        <i class="fas fa-box text-purple-600 text-2xl mb-2"></i>
                        <div class="font-semibold">Produk Baru</div>
                        <div class="text-sm text-gray-500">Tambah produk</div>
                    </button>
                    <button onclick="openReportsModal()" class="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition-all text-left">
                        <i class="fas fa-chart-bar text-orange-600 text-2xl mb-2"></i>
                        <div class="font-semibold">Laporan</div>
                        <div class="text-sm text-gray-500">Lihat laporan</div>
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Total Pelanggan</h4>
                            <i class="fas fa-users text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authCustomerCount">128</div>
                        <p class="text-xs text-gray-500">Pelanggan aktif</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Total Produk</h4>
                            <i class="fas fa-box text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authProductCount">42</div>
                        <p class="text-xs text-gray-500">Jenis layanan</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Pesanan Bulan Ini</h4>
                            <i class="fas fa-print text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authOrderCount">15</div>
                        <p class="text-xs text-gray-500">Pesanan aktif</p>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold">Pesanan Terbaru</h3>
                                <p class="text-gray-600">Daftar pesanan yang masuk dalam sistem</p>
                            </div>
                            <a href="orders.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Lihat Semua →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="recentOrders">
                            <!-- Recent orders will be loaded here -->
                            <div class="border-b border-gray-200 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">PT. Maju Jaya</div>
                                        <div class="text-sm text-gray-600">Banner Vinyl 3x2m</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">Rp 450.000</div>
                                        <div class="text-xs">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                Processing
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                    <div>2023-05-15</div>
                                    <div>ID: #1001</div>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">CV. Berkah Abadi</div>
                                        <div class="text-sm text-gray-600">Kartu Nama 1000pcs</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">Rp 1.250.000</div>
                                        <div class="text-xs">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                Completed
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                    <div>2023-05-14</div>
                                    <div>ID: #1002</div>
                                </div>
                            </div>
                            <div class="py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium">Toko Sinar Baru</div>
                                        <div class="text-sm text-gray-600">Brosur A4 500lembar</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-green-600">Rp 750.000</div>
                                        <div class="text-xs">
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                                                Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                                    <div>2023-05-12</div>
                                    <div>ID: #1003</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Demo Modal -->
    <div id="demoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Demo Login</h3>
                <button onclick="closeDemoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-4">Pilih role untuk mencoba sistem:</p>
            <div class="space-y-2">
                <button onclick="demoLogin('admin')" class="w-full text-left p-3 border rounded hover:bg-gray-50 transition-colors">
                    <div class="font-semibold text-red-600">👑 Admin</div>
                    <div class="text-sm text-gray-500">Akses penuh ke semua fitur</div>
                </button>
                <button onclick="demoLogin('manager1')" class="w-full text-left p-3 border rounded hover:bg-gray-50 transition-colors">
                    <div class="font-semibold text-yellow-600">👔 Manager</div>
                    <div class="text-sm text-gray-500">Kelola operasional dan laporan</div>
                </button>
                <button onclick="demoLogin('staff1')" class="w-full text-left p-3 border rounded hover:bg-gray-50 transition-colors">
                    <div class="font-semibold text-green-600">👷 Staff</div>
                    <div class="text-sm text-gray-500">Input pesanan dan data pelanggan</div>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">🔍 Pencarian</h3>
                <button onclick="closeSearchModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="searchInput" placeholder="Cari pelanggan, produk, atau pesanan..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       oninput="performSearch()">
            </div>
            <div class="flex space-x-2 mb-4">
                <button onclick="searchFilter('all')" class="px-3 py-1 bg-blue-600 text-white rounded text-sm" id="filterAll">Semua</button>
                <button onclick="searchFilter('customers')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm" id="filterCustomers">Pelanggan</button>
                <button onclick="searchFilter('products')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm" id="filterProducts">Produk</button>
                <button onclick="searchFilter('orders')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm" id="filterOrders">Pesanan</button>
            </div>
            <div id="searchResults" class="max-h-96 overflow-y-auto">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Mulai mengetik untuk mencari...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Order Modal -->
    <div id="addOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">📋 Tambah Pesanan Baru</h3>
                <button onclick="closeAddOrderModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addOrderForm" class="space-y-4" onsubmit="submitOrder(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan</label>
                        <select id="orderCustomer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih Pelanggan</option>
                            <option value="1">PT. Maju Jaya</option>
                            <option value="2">CV. Berkah Abadi</option>
                            <option value="3">Toko Sinar Baru</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                        <select id="orderProduct" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="updateOrderPrice()">
                            <option value="">Pilih Produk</option>
                            <option value="1" data-price="150000">Banner Vinyl 3x2m - Rp 150.000</option>
                            <option value="2" data-price="250000">Kartu Nama 1000pcs - Rp 250.000</option>
                            <option value="3" data-price="1500">Brosur A4 500lembar - Rp 1.500</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                        <input type="number" id="orderQuantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required oninput="updateOrderPrice()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan</label>
                        <input type="number" id="orderUnitPrice" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                        <input type="number" id="orderTotalPrice" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesanan</label>
                    <input type="date" id="orderDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="orderNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Catatan tambahan..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddOrderModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <span id="orderSubmitText">Simpan Pesanan</span>
                        <i id="orderSubmitSpinner" class="fas fa-spinner fa-spin hidden ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">👥 Tambah Pelanggan Baru</h3>
                <button onclick="closeAddCustomerModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addCustomerForm" class="space-y-4" onsubmit="submitCustomer(event)">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan/Pelanggan</label>
                    <input type="text" id="customerName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="customerEmail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                    <input type="tel" id="customerPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea id="customerAddress" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddCustomerModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span id="customerSubmitText">Simpan Pelanggan</span>
                        <i id="customerSubmitSpinner" class="fas fa-spinner fa-spin hidden ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">📦 Tambah Produk Baru</h3>
                <button onclick="closeAddProductModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addProductForm" class="space-y-4" onsubmit="submitProduct(event)">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                    <input type="text" id="productName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select id="productCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Banner">Banner</option>
                            <option value="Sticker">Sticker</option>
                            <option value="Brosur">Brosur</option>
                            <option value="Kartu">Kartu</option>
                            <option value="Poster">Poster</option>
                            <option value="Display">Display</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                        <select id="productUnit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Pilih Satuan</option>
                            <option value="per m²">per m²</option>
                            <option value="per lembar">per lembar</option>
                            <option value="per unit">per unit</option>
                            <option value="per 1000 pcs">per 1000 pcs</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                    <input type="number" id="productPrice" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="productDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Deskripsi produk..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddProductModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <span id="productSubmitText">Simpan Produk</span>
                        <i id="productSubmitSpinner" class="fas fa-spinner fa-spin hidden ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Modal -->
    <div id="reportsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">📊 Laporan</h3>
                <button onclick="closeReportsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <button onclick="generateReport('daily')" class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left transition-colors">
                    <i class="fas fa-calendar-day text-blue-600 text-2xl mb-2"></i>
                    <div class="font-semibold">Laporan Harian</div>
                    <div class="text-sm text-gray-500">Pesanan hari ini</div>
                </button>
                <button onclick="generateReport('monthly')" class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left transition-colors">
                    <i class="fas fa-calendar-alt text-green-600 text-2xl mb-2"></i>
                    <div class="font-semibold">Laporan Bulanan</div>
                    <div class="text-sm text-gray-500">Pesanan bulan ini</div>
                </button>
                <button onclick="generateReport('yearly')" class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left transition-colors">
                    <i class="fas fa-calendar text-purple-600 text-2xl mb-2"></i>
                    <div class="font-semibold">Laporan Tahunan</div>
                    <div class="text-sm text-gray-500">Pesanan tahun ini</div>
                </button>
            </div>
            <div id="reportContent" class="border-t pt-4">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-chart-bar text-3xl mb-2"></i>
                    <p>Pilih jenis laporan di atas</p>
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
                        <i class="fas fa-plus-circle text-blue-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Pesanan Baru</div>
                            <div class="text-xs text-gray-600">PT. Maju Jaya - Banner Vinyl</div>
                            <div class="text-xs text-gray-500">2 jam yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Pesanan Selesai</div>
                            <div class="text-xs text-gray-600">CV. Berkah - Kartu Nama</div>
                            <div class="text-xs text-gray-500">5 jam yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-clock text-yellow-600 mt-1"></i>
                        <div>
                            <div class="font-medium text-sm">Deadline Mendekati</div>
                            <div class="text-xs text-gray-600">Toko Sinar - Brosur A4</div>
                            <div class="text-xs text-gray-500">1 hari yang lalu</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t">
                <button onclick="viewAllNotifications()" class="w-full text-center text-blue-600 hover:text-blue-700 text-sm transition-colors">
                    Lihat Semua Notifikasi
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentSearchFilter = 'all';
        let mockCustomers = [
            { id: 1, name: 'PT. Maju Jaya', email: 'info@majujaya.com', phone: '021-1234567', address: 'Jakarta Selatan' },
            { id: 2, name: 'CV. Berkah Abadi', email: 'berkah@email.com', phone: '021-2345678', address: 'Jakarta Timur' },
            { id: 3, name: 'Toko Sinar Baru', email: 'sinar@email.com', phone: '021-3456789', address: 'Jakarta Barat' },
            { id: 4, name: 'PT. Sukses Mandiri', email: 'sukses@email.com', phone: '021-4567890', address: 'Jakarta Utara' },
            { id: 5, name: 'CV. Mitra Sejati', email: 'mitra@email.com', phone: '021-5678901', address: 'Jakarta Pusat' }
        ];

        let mockProducts = [
            { id: 1, name: 'Banner Vinyl 3x2m', category: 'Banner', unit: 'per unit', price: 150000, description: 'Banner vinyl berkualitas tinggi' },
            { id: 2, name: 'Kartu Nama 1000pcs', category: 'Kartu', unit: 'per 1000 pcs', price: 250000, description: 'Kartu nama premium' },
            { id: 3, name: 'Brosur A4 500lembar', category: 'Brosur', unit: 'per lembar', price: 1500, description: 'Brosur full color' },
            { id: 4, name: 'Sticker Vinyl A3', category: 'Sticker', unit: 'per lembar', price: 25000, description: 'Sticker vinyl tahan air' },
            { id: 5, name: 'Poster A2', category: 'Poster', unit: 'per lembar', price: 35000, description: 'Poster berkualitas tinggi' }
        ];

        let mockOrders = [
            { id: 1001, customer: 'PT. Maju Jaya', product: 'Banner Vinyl 3x2m', total_price: 450000, status: 'Processing', order_date: '2023-05-15' },
            { id: 1002, customer: 'CV. Berkah Abadi', product: 'Kartu Nama 1000pcs', total_price: 1250000, status: 'Completed', order_date: '2023-05-14' },
            { id: 1003, customer: 'Toko Sinar Baru', product: 'Brosur A4 500lembar', total_price: 750000, status: 'Pending', order_date: '2023-05-12' }
        ];

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
        }

        // Modal Functions
        function showDemo() {
            document.getElementById('demoModal').classList.remove('hidden');
        }

        function closeDemoModal() {
            document.getElementById('demoModal').classList.add('hidden');
        }

        function demoLogin(role) {
            showNotification('Demo login sebagai ' + role + ' akan diproses', 'info');
            closeDemoModal();
            // Simulate login redirect
            setTimeout(() => {
                window.location.href = 'login.php?demo=' + role;
            }, 1000);
        }

        function openSearchModal() {
            document.getElementById('searchModal').classList.remove('hidden');
            document.getElementById('searchInput').focus();
        }

        function closeSearchModal() {
            document.getElementById('searchModal').classList.add('hidden');
            document.getElementById('searchInput').value = '';
            resetSearchResults();
        }

        function resetSearchResults() {
            document.getElementById('searchResults').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Mulai mengetik untuk mencari...</p>
                </div>
            `;
        }

        function openAddOrderModal() {
            document.getElementById('addOrderModal').classList.remove('hidden');
            loadCustomersAndProducts();
            document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
        }

        function closeAddOrderModal() {
            document.getElementById('addOrderModal').classList.add('hidden');
            document.getElementById('addOrderForm').reset();
        }

        function openAddCustomerModal() {
            document.getElementById('addCustomerModal').classList.remove('hidden');
        }

        function closeAddCustomerModal() {
            document.getElementById('addCustomerModal').classList.add('hidden');
            document.getElementById('addCustomerForm').reset();
        }

        function openAddProductModal() {
            document.getElementById('addProductModal').classList.remove('hidden');
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').classList.add('hidden');
            document.getElementById('addProductForm').reset();
        }

        function openReportsModal() {
            document.getElementById('reportsModal').classList.remove('hidden');
        }

        function closeReportsModal() {
            document.getElementById('reportsModal').classList.add('hidden');
        }

        function openNotificationsModal() {
            document.getElementById('notificationsModal').classList.remove('hidden');
        }

        function closeNotificationsModal() {
            document.getElementById('notificationsModal').classList.add('hidden');
        }

        // Search Functions
        function searchFilter(filter) {
            currentSearchFilter = filter;
            
            // Update button styles
            document.querySelectorAll('[id^="filter"]').forEach(btn => {
                btn.className = 'px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm';
            });
            document.getElementById('filter' + filter.charAt(0).toUpperCase() + filter.slice(1)).className = 'px-3 py-1 bg-blue-600 text-white rounded text-sm';
            
            performSearch();
        }

        function performSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const resultsContainer = document.getElementById('searchResults');
            
            if (query.length < 2) {
                resetSearchResults();
                return;
            }

            let results = [];
            
            // Search customers
            if (currentSearchFilter === 'all' || currentSearchFilter === 'customers') {
                const customerResults = mockCustomers.filter(customer => 
                    customer.name.toLowerCase().includes(query) ||
                    customer.email.toLowerCase().includes(query) ||
                    customer.phone.includes(query)
                ).map(customer => ({
                    type: 'customer',
                    icon: 'fas fa-user',
                    title: customer.name,
                    subtitle: customer.email,
                    extra: customer.phone,
                    id: customer.id
                }));
                results = results.concat(customerResults);
            }

            // Search products
            if (currentSearchFilter === 'all' || currentSearchFilter === 'products') {
                const productResults = mockProducts.filter(product => 
                    product.name.toLowerCase().includes(query) ||
                    product.category.toLowerCase().includes(query)
                ).map(product => ({
                    type: 'product',
                    icon: 'fas fa-box',
                    title: product.name,
                    subtitle: product.category,
                    extra: formatCurrency(product.price),
                    id: product.id
                }));
                results = results.concat(productResults);
            }

            // Search orders
            if (currentSearchFilter === 'all' || currentSearchFilter === 'orders') {
                const orderResults = mockOrders.filter(order => 
                    order.customer.toLowerCase().includes(query) ||
                    order.product.toLowerCase().includes(query) ||
                    order.id.toString().includes(query)
                ).map(order => ({
                    type: 'order',
                    icon: 'fas fa-print',
                    title: `#${order.id} - ${order.customer}`,
                    subtitle: order.product,
                    extra: formatCurrency(order.total_price),
                    status: order.status,
                    id: order.id
                }));
                results = results.concat(orderResults);
            }

            displaySearchResults(results);
        }

        function displaySearchResults(results) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-search text-3xl mb-2"></i>
                        <p>Tidak ada hasil ditemukan</p>
                    </div>
                `;
                return;
            }

            const resultsHTML = results.map(result => `
                <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors" onclick="selectSearchResult('${result.type}', ${result.id})">
                    <div class="flex items-center space-x-3">
                        <i class="${result.icon} text-gray-500"></i>
                        <div class="flex-1">
                            <div class="font-medium text-sm">${result.title}</div>
                            <div class="text-xs text-gray-600">${result.subtitle}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium">${result.extra}</div>
                            ${result.status ? `<span class="${getStatusColor(result.status)} px-2 py-1 rounded-full text-xs">${result.status}</span>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = resultsHTML;
        }

        function selectSearchResult(type, id) {
            showNotification(`Membuka ${type} dengan ID: ${id}`, 'info');
            closeSearchModal();
            // Here you would typically navigate to the specific item
        }

        // Form Submission Functions
        function loadCustomersAndProducts() {
            const customerSelect = document.getElementById('orderCustomer');
            const productSelect = document.getElementById('orderProduct');
            
            // Load customers
            customerSelect.innerHTML = '<option value="">Pilih Pelanggan</option>';
            mockCustomers.forEach(customer => {
                customerSelect.innerHTML += `<option value="${customer.id}">${customer.name}</option>`;
            });
            
            // Load products
            productSelect.innerHTML = '<option value="">Pilih Produk</option>';
            mockProducts.forEach(product => {
                productSelect.innerHTML += `<option value="${product.id}" data-price="${product.price}">${product.name} - ${formatCurrency(product.price)}</option>`;
            });
        }

        function updateOrderPrice() {
            const productSelect = document.getElementById('orderProduct');
            const quantityInput = document.getElementById('orderQuantity');
            const unitPriceInput = document.getElementById('orderUnitPrice');
            const totalPriceInput = document.getElementById('orderTotalPrice');
            
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.price) {
                const unitPrice = parseFloat(selectedOption.dataset.price);
                const quantity = parseFloat(quantityInput.value) || 0;
                
                unitPriceInput.value = unitPrice;
                totalPriceInput.value = unitPrice * quantity;
            } else {
                unitPriceInput.value = '';
                totalPriceInput.value = '';
            }
        }

        function submitOrder(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('orderSubmitText');
            const spinner = document.getElementById('orderSubmitSpinner');
            
            // Show loading state
            submitBtn.textContent = 'Menyimpan...';
            spinner.classList.remove('hidden');
            
            // Simulate API call
            setTimeout(() => {
                const formData = {
                    customer_id: document.getElementById('orderCustomer').value,
                    product_id: document.getElementById('orderProduct').value,
                    quantity: document.getElementById('orderQuantity').value,
                    unit_price: document.getElementById('orderUnitPrice').value,
                    total_price: document.getElementById('orderTotalPrice').value,
                    order_date: document.getElementById('orderDate').value,
                    notes: document.getElementById('orderNotes').value
                };
                
                // Add to mock orders
                const newOrder = {
                    id: mockOrders.length + 1001,
                    customer: mockCustomers.find(c => c.id == formData.customer_id)?.name || 'Unknown',
                    product: mockProducts.find(p => p.id == formData.product_id)?.name || 'Unknown',
                    total_price: parseFloat(formData.total_price),
                    status: 'Pending',
                    order_date: formData.order_date
                };
                mockOrders.unshift(newOrder);
                
                showNotification('Pesanan berhasil ditambahkan!', 'success');
                closeAddOrderModal();
                
                // Reset loading state
                submitBtn.textContent = 'Simpan Pesanan';
                spinner.classList.add('hidden');
            }, 1500);
        }

        function submitCustomer(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('customerSubmitText');
            const spinner = document.getElementById('customerSubmitSpinner');
            
            // Show loading state
            submitBtn.textContent = 'Menyimpan...';
            spinner.classList.remove('hidden');
            
            // Simulate API call
            setTimeout(() => {
                const formData = {
                    name: document.getElementById('customerName').value,
                    email: document.getElementById('customerEmail').value,
                    phone: document.getElementById('customerPhone').value,
                    address: document.getElementById('customerAddress').value
                };
                
                // Add to mock customers
                const newCustomer = {
                    id: mockCustomers.length + 1,
                    name: formData.name,
                    email: formData.email,
                    phone: formData.phone,
                    address: formData.address
                };
                mockCustomers.push(newCustomer);
                
                showNotification('Pelanggan berhasil ditambahkan!', 'success');
                closeAddCustomerModal();
                
                // Reset loading state
                submitBtn.textContent = 'Simpan Pelanggan';
                spinner.classList.add('hidden');
            }, 1500);
        }

        function submitProduct(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('productSubmitText');
            const spinner = document.getElementById('productSubmitSpinner');
            
            // Show loading state
            submitBtn.textContent = 'Menyimpan...';
            spinner.classList.remove('hidden');
            
            // Simulate API call
            setTimeout(() => {
                const formData = {
                    name: document.getElementById('productName').value,
                    category: document.getElementById('productCategory').value,
                    unit: document.getElementById('productUnit').value,
                    price: parseFloat(document.getElementById('productPrice').value),
                    description: document.getElementById('productDescription').value
                };
                
                // Add to mock products
                const newProduct = {
                    id: mockProducts.length + 1,
                    name: formData.name,
                    category: formData.category,
                    unit: formData.unit,
                    price: formData.price,
                    description: formData.description
                };
                mockProducts.push(newProduct);
                
                showNotification('Produk berhasil ditambahkan!', 'success');
                closeAddProductModal();
                
                // Reset loading state
                submitBtn.textContent = 'Simpan Produk';
                spinner.classList.add('hidden');
            }, 1500);
        }

        // Report Functions
        function generateReport(type) {
            const reportContent = document.getElementById('reportContent');
            
            reportContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Menghasilkan laporan ${type}...</p>
                </div>
            `;
            
            setTimeout(() => {
                let reportData = '';
                const currentDate = new Date().toLocaleDateString('id-ID');
                
                switch(type) {
                    case 'daily':
                        reportData = `
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-blue-800 mb-2">📅 Laporan Harian - ${currentDate}</h4>
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600">5</div>
                                            <div class="text-sm text-gray-600">Pesanan Baru</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-green-600">3</div>
                                            <div class="text-sm text-gray-600">Pesanan Selesai</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-purple-600">${formatCurrency(2500000)}</div>
                                            <div class="text-sm text-gray-600">Total Pendapatan</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border rounded-lg p-4">
                                    <h5 class="font-medium mb-3">Detail Pesanan Hari Ini</h5>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center py-2 border-b">
                                            <span>Banner Vinyl - PT. Maju Jaya</span>
                                            <span class="font-medium">${formatCurrency(450000)}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b">
                                            <span>Kartu Nama - CV. Berkah</span>
                                            <span class="font-medium">${formatCurrency(250000)}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span>Brosur A4 - Toko Sinar</span>
                                            <span class="font-medium">${formatCurrency(150000)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                    case 'monthly':
                        reportData = `
                            <div class="space-y-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-green-800 mb-2">📊 Laporan Bulanan - ${new Date().toLocaleDateString('id-ID', {month: 'long', year: 'numeric'})}</h4>
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <div class="text-2xl font-bold text-green-600">87</div>
                                            <div class="text-sm text-gray-600">Total Pesanan</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600">65</div>
                                            <div class="text-sm text-gray-600">Pesanan Selesai</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-purple-600">${formatCurrency(45000000)}</div>
                                            <div class="text-sm text-gray-600">Total Pendapatan</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white border rounded-lg p-4">
                                        <h5 class="font-medium mb-3">Produk Terlaris</h5>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span>Banner Vinyl</span>
                                                <span class="font-medium">25 pesanan</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Kartu Nama</span>
                                                <span class="font-medium">20 pesanan</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Brosur A4</span>
                                                <span class="font-medium">18 pesanan</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white border rounded-lg p-4">
                                        <h5 class="font-medium mb-3">Pelanggan Teratas</h5>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span>PT. Maju Jaya</span>
                                                <span class="font-medium">8 pesanan</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>CV. Berkah Abadi</span>
                                                <span class="font-medium">6 pesanan</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Toko Sinar Baru</span>
                                                <span class="font-medium">5 pesanan</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                    case 'yearly':
                        reportData = `
                            <div class="space-y-4">
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h4 class="font-semibold text-purple-800 mb-2">📈 Laporan Tahunan - ${new Date().getFullYear()}</h4>
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <div class="text-2xl font-bold text-purple-600">1,247</div>
                                            <div class="text-sm text-gray-600">Total Pesanan</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-green-600">1,156</div>
                                            <div class="text-sm text-gray-600">Pesanan Selesai</div>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600">${formatCurrency(580000000)}</div>
                                            <div class="text-sm text-gray-600">Total Pendapatan</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white border rounded-lg p-4">
                                    <h5 class="font-medium mb-3">Tren Bulanan</h5>
                                    <div class="grid grid-cols-4 gap-4 text-center">
                                        <div class="p-3 bg-gray-50 rounded">
                                            <div class="font-medium">Q1</div>
                                            <div class="text-sm text-gray-600">${formatCurrency(120000000)}</div>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded">
                                            <div class="font-medium">Q2</div>
                                            <div class="text-sm text-gray-600">${formatCurrency(145000000)}</div>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded">
                                            <div class="font-medium">Q3</div>
                                            <div class="text-sm text-gray-600">${formatCurrency(160000000)}</div>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded">
                                            <div class="font-medium">Q4</div>
                                            <div class="text-sm text-gray-600">${formatCurrency(155000000)}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                }
                
                reportContent.innerHTML = reportData + `
                    <div class="flex justify-end mt-4">
                        <button onclick="exportReport('${type}')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export PDF
                        </button>
                    </div>
                `;
            }, 2000);
        }

        function exportReport(type) {
            showNotification(`Mengexport laporan ${type} ke PDF...`, 'info');
            // Simulate export process
            setTimeout(() => {
                showNotification(`Laporan ${type} berhasil diexport!`, 'success');
            }, 2000);
        }

        // Notification Functions
        function viewAllNotifications() {
            showNotification('Membuka halaman notifikasi lengkap...', 'info');
            closeNotificationsModal();
            // Here you would navigate to notifications page
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

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'processing': return 'bg-blue-100 text-blue-800';
                case 'completed': return 'bg-green-100 text-green-800';
                case 'cancelled': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
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
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // ESC to close modals
                if (e.key === 'Escape') {
                    document.querySelectorAll('.fixed.inset-0:not(.hidden)').forEach(modal => {
                        modal.classList.add('hidden');
                    });
                }
                
                // Ctrl+K to open search
                if (e.ctrlKey && e.key === 'k') {
                    e.preventDefault();
                    openSearchModal();
                }
            });
        });
    </script>
</body>
</html>
