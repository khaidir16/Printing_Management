<?php
// ========================================
// ORDERS PAGE - PHP VERSION
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
    <title>Kelola Pesanan - PrintPro Management</title>
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
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
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
                        <h2 class="text-3xl font-bold mb-2">📋 Kelola Pesanan</h2>
                        <p class="opacity-90">
                            Pantau dan kelola semua pesanan percetakan dengan mudah
                        </p>
                    </div>
                    <div class="text-6xl opacity-20">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm border p-6 order-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Total Pesanan</h4>
                        <i class="fas fa-clipboard-list text-blue-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900" id="totalOrders">87</div>
                    <p class="text-xs text-gray-500 mt-1">Semua pesanan</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 order-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Pending</h4>
                        <i class="fas fa-clock text-yellow-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-yellow-600" id="pendingOrders">12</div>
                    <p class="text-xs text-gray-500 mt-1">Menunggu proses</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 order-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Processing</h4>
                        <i class="fas fa-cog text-blue-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-blue-600" id="processingOrders">25</div>
                    <p class="text-xs text-gray-500 mt-1">Sedang dikerjakan</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border p-6 order-card">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-medium text-gray-600">Completed</h4>
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-green-600" id="completedOrders">50</div>
                    <p class="text-xs text-gray-500 mt-1">Selesai</p>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Status:</label>
                            <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="filterOrders()">
                                <option value="all">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Tanggal:</label>
                            <input type="date" id="dateFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="filterOrders()">
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="exportOrders()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export
                        </button>
                        <button onclick="openAddOrderModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Pesanan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-semibold">Daftar Pesanan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pesanan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Orders will be loaded here -->
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span id="showingStart">1</span> - <span id="showingEnd">10</span> dari <span id="totalOrdersCount">87</span> pesanan
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="previousPage()" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" id="prevBtn">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button onclick="nextPage()" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" id="nextBtn">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
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
                            <option value="4">PT. Sukses Mandiri</option>
                            <option value="5">CV. Mitra Sejati</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                        <select id="orderProduct" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required onchange="updateOrderPrice()">
                            <option value="">Pilih Produk</option>
                            <option value="1" data-price="150000">Banner Vinyl 3x2m - Rp 150.000</option>
                            <option value="2" data-price="250000">Kartu Nama 1000pcs - Rp 250.000</option>
                            <option value="3" data-price="1500">Brosur A4 500lembar - Rp 1.500</option>
                            <option value="4" data-price="25000">Sticker Vinyl A3 - Rp 25.000</option>
                            <option value="5" data-price="35000">Poster A2 - Rp 35.000</option>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesanan</label>
                        <input type="date" id="orderDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                        <input type="date" id="orderDeadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
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

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">📋 Detail Pesanan</h3>
                <button onclick="closeOrderDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="orderDetailContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div id="searchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center modal-overlay">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">🔍 Cari Pesanan</h3>
                <button onclick="closeSearchModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="searchInput" placeholder="Cari berdasarkan ID, pelanggan, atau produk..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       oninput="performSearch()">
            </div>
            <div id="searchResults" class="max-h-96 overflow-y-auto">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Mulai mengetik untuk mencari pesanan...</p>
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
        </div>
    </div>

    <script>
        // Global variables
        let currentPage = 1;
        let ordersPerPage = 10;
        let filteredOrders = [];
        
        // Mock data
        let mockOrders = [
            { id: 1001, customer: 'PT. Maju Jaya', product: 'Banner Vinyl 3x2m', quantity: 3, unit_price: 150000, total_price: 450000, status: 'processing', order_date: '2023-12-15', deadline: '2023-12-20', notes: 'Urgent order' },
            { id: 1002, customer: 'CV. Berkah Abadi', product: 'Kartu Nama 1000pcs', quantity: 5, unit_price: 250000, total_price: 1250000, status: 'completed', order_date: '2023-12-14', deadline: '2023-12-18', notes: 'Premium quality' },
            { id: 1003, customer: 'Toko Sinar Baru', product: 'Brosur A4 500lembar', quantity: 500, unit_price: 1500, total_price: 750000, status: 'pending', order_date: '2023-12-12', deadline: '2023-12-17', notes: 'Full color print' },
            { id: 1004, customer: 'PT. Sukses Mandiri', product: 'Sticker Vinyl A3', quantity: 100, unit_price: 25000, total_price: 2500000, status: 'processing', order_date: '2023-12-11', deadline: '2023-12-16', notes: 'Weather resistant' },
            { id: 1005, customer: 'CV. Mitra Sejati', product: 'Poster A2', quantity: 50, unit_price: 35000, total_price: 1750000, status: 'completed', order_date: '2023-12-10', deadline: '2023-12-15', notes: 'High resolution' },
            { id: 1006, customer: 'PT. Maju Jaya', product: 'Banner Vinyl 3x2m', quantity: 2, unit_price: 150000, total_price: 300000, status: 'pending', order_date: '2023-12-09', deadline: '2023-12-14', notes: 'Standard quality' },
            { id: 1007, customer: 'Toko Sinar Baru', product: 'Kartu Nama 1000pcs', quantity: 2, unit_price: 250000, total_price: 500000, status: 'cancelled', order_date: '2023-12-08', deadline: '2023-12-13', notes: 'Customer cancelled' },
            { id: 1008, customer: 'CV. Berkah Abadi', product: 'Brosur A4 500lembar', quantity: 1000, unit_price: 1500, total_price: 1500000, status: 'processing', order_date: '2023-12-07', deadline: '2023-12-12', notes: 'Bulk order' }
        ];

        let mockCustomers = [
            { id: 1, name: 'PT. Maju Jaya' },
            { id: 2, name: 'CV. Berkah Abadi' },
            { id: 3, name: 'Toko Sinar Baru' },
            { id: 4, name: 'PT. Sukses Mandiri' },
            { id: 5, name: 'CV. Mitra Sejati' }
        ];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            filteredOrders = [...mockOrders];
            loadOrders();
            updateStats();
            
            // Set default date to today
            document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
            
            // Set default deadline to 7 days from now
            const deadline = new Date();
            deadline.setDate(deadline.getDate() + 7);
            document.getElementById('orderDeadline').value = deadline.toISOString().split('T')[0];
        });

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('active');
        }

        // Modal Functions
        function openAddOrderModal() {
            document.getElementById('addOrderModal').classList.remove('hidden');
        }

        function closeAddOrderModal() {
            document.getElementById('addOrderModal').classList.add('hidden');
            document.getElementById('addOrderForm').reset();
        }

        function openOrderDetailModal(orderId) {
            const order = mockOrders.find(o => o.id === orderId);
            if (!order) return;

            const content = `
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Pesanan</label>
                            <div class="p-3 bg-gray-50 rounded-lg">#${order.id}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="status-badge ${getStatusColor(order.status)}">${getStatusText(order.status)}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${order.customer}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${order.product}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${order.quantity}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${formatCurrency(order.unit_price)}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                            <div class="p-3 bg-gray-50 rounded-lg font-semibold">${formatCurrency(order.total_price)}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesanan</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${formatDate(order.order_date)}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                            <div class="p-3 bg-gray-50 rounded-lg">${formatDate(order.deadline)}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <div class="p-3 bg-gray-50 rounded-lg">${order.notes || 'Tidak ada catatan'}</div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button onclick="updateOrderStatus(${order.id})" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Update Status
                        </button>
                        <button onclick="printOrder(${order.id})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('orderDetailContent').innerHTML = content;
            document.getElementById('orderDetailModal').classList.remove('hidden');
        }

        function closeOrderDetailModal() {
            document.getElementById('orderDetailModal').classList.add('hidden');
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

        function openNotificationsModal() {
            document.getElementById('notificationsModal').classList.remove('hidden');
        }

        function closeNotificationsModal() {
            document.getElementById('notificationsModal').classList.add('hidden');
        }

        // Orders Functions
        function loadOrders() {
            const tbody = document.getElementById('ordersTableBody');
            const startIndex = (currentPage - 1) * ordersPerPage;
            const endIndex = startIndex + ordersPerPage;
            const ordersToShow = filteredOrders.slice(startIndex, endIndex);

            tbody.innerHTML = ordersToShow.map(order => `
                <tr class="table-row">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-blue-600">#${order.id}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium">${order.customer}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">${order.product}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm">${order.quantity}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium">${formatCurrency(order.total_price)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge ${getStatusColor(order.status)}">${getStatusText(order.status)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm">${formatDate(order.order_date)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                            <button onclick="openOrderDetailModal(${order.id})" class="text-blue-600 hover:text-blue-700" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editOrder(${order.id})" class="text-green-600 hover:text-green-700" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteOrder(${order.id})" class="text-red-600 hover:text-red-700" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            updatePagination();
        }

        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            filteredOrders = mockOrders.filter(order => {
                const statusMatch = statusFilter === 'all' || order.status === statusFilter;
                const dateMatch = !dateFilter || order.order_date === dateFilter;
                return statusMatch && dateMatch;
            });

            currentPage = 1;
            loadOrders();
            updateStats();
        }

        function updateStats() {
            const total = filteredOrders.length;
            const pending = filteredOrders.filter(o => o.status === 'pending').length;
            const processing = filteredOrders.filter(o => o.status === 'processing').length;
            const completed = filteredOrders.filter(o => o.status === 'completed').length;

            document.getElementById('totalOrders').textContent = total;
            document.getElementById('pendingOrders').textContent = pending;
            document.getElementById('processingOrders').textContent = processing;
            document.getElementById('completedOrders').textContent = completed;
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredOrders.length / ordersPerPage);
            const startIndex = (currentPage - 1) * ordersPerPage + 1;
            const endIndex = Math.min(currentPage * ordersPerPage, filteredOrders.length);

            document.getElementById('showingStart').textContent = startIndex;
            document.getElementById('showingEnd').textContent = endIndex;
            document.getElementById('totalOrdersCount').textContent = filteredOrders.length;

            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                loadOrders();
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(filteredOrders.length / ordersPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                loadOrders();
            }
        }

        // Form Functions
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
                    deadline: document.getElementById('orderDeadline').value,
                    notes: document.getElementById('orderNotes').value
                };
                
                // Add to mock orders
                const newOrder = {
                    id: Math.max(...mockOrders.map(o => o.id)) + 1,
                    customer: mockCustomers.find(c => c.id == formData.customer_id)?.name || 'Unknown',
                    product: document.getElementById('orderProduct').options[document.getElementById('orderProduct').selectedIndex].text.split(' - ')[0],
                    quantity: parseInt(formData.quantity),
                    unit_price: parseFloat(formData.unit_price),
                    total_price: parseFloat(formData.total_price),
                    status: 'pending',
                    order_date: formData.order_date,
                    deadline: formData.deadline,
                    notes: formData.notes
                };
                mockOrders.unshift(newOrder);
                
                showNotification('Pesanan berhasil ditambahkan!', 'success');
                closeAddOrderModal();
                filterOrders(); // Refresh the display
                
                // Reset loading state
                submitBtn.textContent = 'Simpan Pesanan';
                spinner.classList.add('hidden');
            }, 1500);
        }

        // Search Functions
        function performSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const resultsContainer = document.getElementById('searchResults');
            
            if (query.length < 2) {
                resetSearchResults();
                return;
            }

            const results = mockOrders.filter(order => 
                order.id.toString().includes(query) ||
                order.customer.toLowerCase().includes(query) ||
                order.product.toLowerCase().includes(query)
            );

            displaySearchResults(results);
        }

        function displaySearchResults(results) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-search text-3xl mb-2"></i>
                        <p>Tidak ada pesanan ditemukan</p>
                    </div>
                `;
                return;
            }

            const resultsHTML = results.map(order => `
                <div class="p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors" onclick="selectSearchResult(${order.id})">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-sm">#${order.id} - ${order.customer}</div>
                            <div class="text-xs text-gray-600">${order.product}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium">${formatCurrency(order.total_price)}</div>
                            <span class="status-badge ${getStatusColor(order.status)}">${getStatusText(order.status)}</span>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = resultsHTML;
        }

        function selectSearchResult(orderId) {
            closeSearchModal();
            openOrderDetailModal(orderId);
        }

        function resetSearchResults() {
            document.getElementById('searchResults').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <p>Mulai mengetik untuk mencari pesanan...</p>
                </div>
            `;
        }

        // Action Functions
        function editOrder(orderId) {
            showNotification(`Membuka editor untuk pesanan #${orderId}`, 'info');
            // Here you would typically open an edit modal
        }

        function deleteOrder(orderId) {
            if (confirm('Apakah Anda yakin ingin menghapus pesanan ini?')) {
                const index = mockOrders.findIndex(o => o.id === orderId);
                if (index > -1) {
                    mockOrders.splice(index, 1);
                    filterOrders();
                    showNotification('Pesanan berhasil dihapus!', 'success');
                }
            }
        }

        function updateOrderStatus(orderId) {
            const order = mockOrders.find(o => o.id === orderId);
            if (!order) return;

            const statusOptions = ['pending', 'processing', 'completed', 'cancelled'];
            const currentIndex = statusOptions.indexOf(order.status);
            const nextIndex = (currentIndex + 1) % statusOptions.length;
            
            order.status = statusOptions[nextIndex];
            
            showNotification(`Status pesanan #${orderId} diubah ke ${getStatusText(order.status)}`, 'success');
            closeOrderDetailModal();
            filterOrders();
        }

        function printOrder(orderId) {
            showNotification(`Mencetak pesanan #${orderId}...`, 'info');
            setTimeout(() => {
                showNotification('Pesanan berhasil dicetak!', 'success');
            }, 2000);
        }

        function exportOrders() {
            showNotification('Mengexport data pesanan...', 'info');
            setTimeout(() => {
                showNotification('Data pesanan berhasil diexport!', 'success');
            }, 2000);
        }

        // Utility Functions
        function getStatusColor(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'bg-yellow-100 text-yellow-800';
                case 'processing': return 'bg-blue-100 text-blue-800';
                case 'completed': return 'bg-green-100 text-green-800';
                case 'cancelled': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        function getStatusText(status) {
            switch(status.toLowerCase()) {
                case 'pending': return 'Pending';
                case 'processing': return 'Processing';
                case 'completed': return 'Completed';
                case 'cancelled': return 'Cancelled';
                default: return status;
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('id-ID');
        }

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
